<?php
/** Shared helpers: output escaping, CSRF, flash, dates, uploads, audit trail. */

/** Escape everything on the way out. Never echo raw user input. */
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build an in-app URL: url('students.view', ['id'=>3]) */
function url(string $route = 'dashboard', array $params = []): string
{
    $qs = array_merge(['r' => $route], $params);
    return 'index.php?' . http_build_query($qs);
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

function back(): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? url('dashboard');
    header('Location: ' . $ref);
    exit;
}

// ── Flash messages ──────────────────────────────────────────────────────────
function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
function take_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ── CSRF ────────────────────────────────────────────────────────────────────
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}
/** Call at the top of every POST handler. */
function csrf_check(): void
{
    $sent = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('<h3 style="font:600 16px system-ui;padding:24px">This form expired. Go back, reload the page and try again.</h3>');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Trimmed POST field. */
function post(string $key, $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : (string) $v;
}
function postInt(string $key, int $default = 0): int
{
    return (int) ($_POST[$key] ?? $default);
}
function getInt(string $key, int $default = 0): int
{
    return (int) ($_GET[$key] ?? $default);
}
function getStr(string $key, string $default = ''): string
{
    $v = $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}
/** POST value or NULL — for optional date / foreign-key columns. */
function postNull(string $key): ?string
{
    $v = trim((string) ($_POST[$key] ?? ''));
    return $v === '' ? null : $v;
}

// ── Formatting ──────────────────────────────────────────────────────────────
function now(): string
{
    return date('Y-m-d H:i:s');
}
function d(?string $date, string $fmt = 'd M Y'): string
{
    if (!$date) return '—';
    $ts = strtotime($date);
    return $ts ? date($fmt, $ts) : '—';
}
function money($amount): string
{
    return 'TZS ' . number_format((float) $amount, 0);
}
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) $out .= mb_strtoupper(mb_substr($p, 0, 1));
    return $out ?: '?';
}
function day_name(int $dow): string
{
    return ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$dow] ?? '';
}
function month_label(string $ym): string
{
    $ts = strtotime($ym . '-01');
    return $ts ? date('F Y', $ts) : $ym;
}
function pct($num, $den): float
{
    return $den > 0 ? round(($num / $den) * 100, 1) : 0.0;
}

// ── Settings ────────────────────────────────────────────────────────────────
function settings(): array
{
    static $s = null;
    if ($s === null) {
        $s = [];
        foreach (rows('SELECT skey, svalue FROM settings') as $r) $s[$r['skey']] = $r['svalue'];
    }
    return $s;
}
function setting(string $key, string $default = ''): string
{
    $s = settings();
    return $s[$key] ?? $default;
}
function set_setting(string $key, string $value): void
{
    if (val('SELECT 1 FROM settings WHERE skey = ?', [$key])) {
        q('UPDATE settings SET svalue = ? WHERE skey = ?', [$value, $key]);
    } else {
        q('INSERT INTO settings (skey, svalue) VALUES (?, ?)', [$key, $value]);
    }
}

// ── Audit trail ─────────────────────────────────────────────────────────────
function audit(string $action, string $entity = '', $entityId = '', string $details = ''): void
{
    $u = current_user();
    insert('audit_logs', [
        'user_id'    => $u['id'] ?? null,
        'user_name'  => $u['name'] ?? 'guest',
        'role'       => $u['role'] ?? '-',
        'action'     => $action,
        'entity'     => $entity,
        'entity_id'  => (string) $entityId,
        'details'    => mb_substr($details, 0, 250),
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? '-',
        'created_at' => now(),
    ]);
}

// ── Reference numbers ───────────────────────────────────────────────────────
/** EY-2026-0001 — sequential within the year, gap-tolerant. */
function next_student_no(): string
{
    $year   = date('Y');
    $prefix = STUDENT_NO_PREFIX . '-' . $year . '-';
    $last   = val('SELECT student_no FROM students WHERE student_no LIKE ? ORDER BY student_no DESC LIMIT 1', [$prefix . '%']);
    $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
    return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
}

function next_serial(string $table, string $column, string $prefix): string
{
    $like = $prefix . '%';
    $last = val("SELECT $column FROM $table WHERE $column LIKE ? ORDER BY $column DESC LIMIT 1", [$like]);
    $seq  = $last ? ((int) substr($last, -4)) + 1 : 1;
    return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
}

/** Human-typeable verification code, no confusable characters. */
function verify_code(): string
{
    $alphabet = 'ACDEFGHJKLMNPQRTUVWXY34679';
    do {
        $code = '';
        for ($i = 0; $i < 3; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        $code .= '-';
        for ($i = 0; $i < 4; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    } while (val('SELECT 1 FROM certificates WHERE verify_code = ?', [$code]));
    return $code;
}

// ── Photo upload ────────────────────────────────────────────────────────────
/**
 * Validated student photo upload. Returns the stored filename, or null.
 * Only real images are accepted; the extension comes from the detected type.
 */
function save_photo(string $field, ?string &$error = null): ?string
{
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 1) === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];

    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'The photo did not upload. Try a smaller file.'; return null; }
    if ($f['size'] > 3 * 1024 * 1024) { $error = 'Photo is larger than 3 MB. Use a smaller image.'; return null; }

    $info = @getimagesize($f['tmp_name']);
    $map  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($map[$info[2]])) { $error = 'Only JPG, PNG or WEBP photos are accepted.'; return null; }

    if (!is_dir(UPLOAD_PATH)) @mkdir(UPLOAD_PATH, 0775, true);
    $name = 'stu_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_PATH . '/' . $name)) {
        $error = 'Could not save the photo. Check that storage/uploads/photos is writable.';
        return null;
    }
    return $name;
}

/** Photos live outside the served folders, so they stream through a route. */
function photo_url(?string $file): ?string
{
    return $file ? url('media.photo', ['f' => $file]) : null;
}

// ── Small view helpers ──────────────────────────────────────────────────────
function badge(string $text, string $tone = 'neutral'): string
{
    return '<span class="badge badge--' . e($tone) . '">' . e($text) . '</span>';
}

function status_tone(string $status): string
{
    return match (strtolower($status)) {
        'active', 'valid', 'acknowledged', 'reviewed', 'completed', 'present' => 'green',
        'pending', 'submitted', 'late'                                        => 'orange',
        'withdrawn', 'suspended', 'revoked', 'changes_requested', 'absent', 'inactive' => 'red',
        'deferred', 'excused', 'graduated'                                    => 'blue',
        default                                                               => 'neutral',
    };
}

function attendance_label(string $code): string
{
    return ['P' => 'Present', 'A' => 'Absent', 'L' => 'Late', 'E' => 'Excused'][$code] ?? $code;
}

/** Paginate: returns [limit, offset, page]. */
function paging(int $perPage = 20): array
{
    $page = max(1, getInt('page', 1));
    return [$perPage, ($page - 1) * $perPage, $page];
}

function pager(int $total, int $perPage, int $page, string $route, array $params = []): string
{
    $pages = (int) ceil($total / max(1, $perPage));
    if ($pages <= 1) return '';
    $out = '<nav class="pager">';
    $out .= '<span class="pager__meta">' . $total . ' record' . ($total === 1 ? '' : 's') . '</span>';
    for ($i = 1; $i <= $pages; $i++) {
        if ($pages > 9 && $i > 3 && $i < $pages - 2 && abs($i - $page) > 1) {
            if ($i === 4) $out .= '<span class="pager__gap">…</span>';
            continue;
        }
        $cls = $i === $page ? ' is-current' : '';
        $out .= '<a class="pager__link' . $cls . '" href="' . e(url($route, $params + ['page' => $i])) . '">' . $i . '</a>';
    }
    return $out . '</nav>';
}
