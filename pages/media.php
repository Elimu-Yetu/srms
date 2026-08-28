<?php
/**
 * Streams student photos. Files live outside the served folders, so the name is
 * validated to a basename and checked against the uploads directory only.
 */
$f = basename(getStr('f'));
if ($f === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $f)) { http_response_code(400); exit; }

$path = UPLOAD_PATH . '/' . $f;
$real = realpath($path);
if (!$real || !str_starts_with($real, realpath(UPLOAD_PATH) ?: '@') || !is_file($real)) {
    http_response_code(404); exit;
}
$info = @getimagesize($real);
$mime = $info['mime'] ?? 'application/octet-stream';
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) { http_response_code(415); exit; }

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($real));
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
readfile($real);
