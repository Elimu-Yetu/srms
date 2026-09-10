<?php
/**
 * Elimu Yetu SRMS — single entry point.
 * Every request lands here: bootstrap → resolve route → check access → render.
 */

require __DIR__ . '/app/config.php';

// ── HTTPS enforcement ────────────────────────────────────────────────────────
if (defined('ENFORCE_HTTPS') && ENFORCE_HTTPS && !isset($_SERVER['HTTPS']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') !== 'https') {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: https://' . $host . $uri, true, 301);
    exit;
}

// ── Security headers ─────────────────────────────────────────────────────────
// Prevent browsers from sniffing MIME types (stops some XSS attacks via file upload).
header('X-Content-Type-Options: nosniff');
// Prevent the app from being embedded in iframes (clickjacking protection).
header('X-Frame-Options: SAMEORIGIN');
// Enable the browser's built-in XSS filter (legacy browsers).
header('X-XSS-Protection: 1; mode=block');
// Don't send the full URL as referrer to external sites.
header('Referrer-Policy: strict-origin-when-cross-origin');
// Limit which browser features are accessible.
header('Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=()');
// Tell HTTPS browsers to always use HTTPS for this domain (1 year).
// Only sent over HTTPS to avoid breaking HTTP access during development.
if (isset($_SERVER['HTTPS'])) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
// Content Security Policy — restricts where scripts, styles, images can load from.
// 'unsafe-inline' is needed because the app uses inline styles in some pages.
// Tighten further (remove unsafe-inline) once the app moves to external stylesheets only.
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self'; " .
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
    "font-src 'self' https://fonts.gstatic.com; " .
    "img-src 'self' data:; " .
    "object-src 'none'; " .
    "base-uri 'self'; " .
    "form-action 'self';"
);



date_default_timezone_set(TIMEZONE);
if (DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');
}

require __DIR__ . '/app/lib/db.php';
require __DIR__ . '/app/lib/helpers.php';
require __DIR__ . '/app/lib/auth.php';
require __DIR__ . '/app/lib/schema.php';
require __DIR__ . '/app/routes.php';

// Not installed yet? Send them to the installer.
if (!table_exists('settings')) {
    header('Location: install.php');
    exit;
}

start_session();

$routes  = routes();
$current = getStr('r', 'dashboard');
if (!isset($routes[$current])) {
    http_response_code(404);
    $current = is_logged_in() ? 'dashboard' : 'login';
    flash('warn', 'That page does not exist. Here is your dashboard instead.');
}
$route  = $routes[$current];
$layout = $route['layout'] ?? 'app';

// ── Access control, before a single line of the page runs ───────────────────
if (empty($route['public'])) {
    require_login();
    if (isset($route['cap'])) {
        if (!can($route['cap'])) deny();
    } elseif (isset($route['roles'])) {
        // Allow superadmin to open any role-restricted page
        if (role() !== 'superadmin' && !in_array(role(), $route['roles'], true)) deny();
    } else {
        deny();
    }
} elseif (is_logged_in() && $current === 'login') {
    redirect('dashboard');
}

// Signed-in users get sent to a page they can actually use.
if ($current === 'dashboard' && is_role('kitchen')) $current = 'kitchen.index';

$page_title   = $routes[$current]['title'] ?? APP_NAME;
$page_sub     = '';
$page_actions = '';
$page_file    = BASE_PATH . '/pages/' . $routes[$current]['file'];

if (!is_file($page_file)) {
    http_response_code(500);
    exit('Page file missing: ' . e($routes[$current]['file']));
}

// 'raw' pages send their own output (file streams, CSV, redirects).
if ($layout === 'raw') {
    require $page_file;
    exit;
}

ob_start();
require $page_file;
$content = ob_get_clean();

require BASE_PATH . '/views/layout_' . $layout . '.php';