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
/**
 * Intake number (1–4) for 4 intakes in a year:
 * 1: Jan–Mar, 2: Apr–Jun, 3: Jul–Sep, 4: Oct–Dec.
 */
function current_intake(?string $date = null): int
{
    $m = (int) ($date ? date('n', strtotime($date)) : date('n'));
    return (int) min(4, max(1, (int) ceil($m / 3)));
}

function intake_label(int $intake): string
{
    $labels = [
        1 => 'Intake 01 (Jan – Mar)',
        2 => 'Intake 02 (Apr – Jun)',
        3 => 'Intake 03 (Jul – Sep)',
        4 => 'Intake 04 (Oct – Dec)',
    ];
    return $labels[$intake] ?? ('Intake ' . str_pad((string) $intake, 2, '0', STR_PAD_LEFT));
}

/** EY-00-0000-0000 (e.g. EY-01-2026-0001) — sequential within intake and year, gap-tolerant. */
function next_student_no(?int $intake = null, ?int $year = null): string
{
    $intake    = $intake ?: current_intake();
    $year      = $year   ?: (int) date('Y');
    $intakePad = str_pad((string) $intake, 2, '0', STR_PAD_LEFT);
    $prefix    = STUDENT_NO_PREFIX . '-' . $intakePad . '-' . $year . '-';
    $last      = val('SELECT student_no FROM students WHERE student_no LIKE ? ORDER BY student_no DESC LIMIT 1', [$prefix . '%']);
    $seq       = $last ? ((int) substr($last, -4)) + 1 : 1;
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
    // Prefer normal file upload if present
    if (!empty($_FILES[$field]['name']) && ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_NO_FILE) {
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

    // Fallback: accept a base64 data URL posted as e.g. photo_data
    $dataKey = $field . '_data';
    if (!empty($_POST[$dataKey])) {
        $data = $_POST[$dataKey];
        // Strip data URL prefix if present
        if (strpos($data, 'data:') === 0) {
            $parts = explode(',', $data, 2);
            if (count($parts) !== 2) { $error = 'Invalid photo data.'; return null; }
            $meta = $parts[0];
            $data = $parts[1];
        }
        $decoded = base64_decode($data);
        if ($decoded === false) { $error = 'Could not decode photo data.'; return null; }
        if (strlen($decoded) > 3 * 1024 * 1024) { $error = 'Photo is larger than 3 MB. Use a smaller image.'; return null; }

        $info = @getimagesizefromstring($decoded);
        $map  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($map[$info[2]])) { $error = 'Only JPG, PNG or WEBP photos are accepted.'; return null; }

        if (!is_dir(UPLOAD_PATH)) @mkdir(UPLOAD_PATH, 0775, true);
        $name = 'stu_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$info[2]];
        if (file_put_contents(UPLOAD_PATH . '/' . $name, $decoded) === false) {
            $error = 'Could not save the photo. Check that storage/uploads/photos is writable.';
            return null;
        }
        return $name;
    }

    return null;
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
    return ['P' => 'Present', 'A' => 'Absent', 'E' => 'Excused'][$code] ?? $code;
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

/**
 * Sanitize a limited subset of HTML produced by the rich editor.
 * Keeps a small safe whitelist of tags and strips unsafe attributes.
 */
function sanitize_html(string $html): string
{
    $allowed = ['p','br','strong','b','em','i','ul','ol','li','a','blockquote','pre','code','h1','h2','h3'];
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    // Wrap in a container to preserve fragments
    $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>');
    $container = $doc->getElementsByTagName('div')->item(0);
    if (!$container) return '';

    $nodes = [];
    foreach ($container->getElementsByTagName('*') as $n) $nodes[] = $n;
    foreach ($nodes as $n) {
        $name = $n->nodeName;
        if (!in_array($name, $allowed, true)) {
            // unwrap the node
            while ($n->firstChild) $n->parentNode->insertBefore($n->firstChild, $n);
            $n->parentNode->removeChild($n);
            continue;
        }
        // sanitize attributes
        if ($name === 'a') {
            $href = $n->getAttribute('href');
            if (!$href || preg_match('/^\s*javascript:/i', $href) || preg_match('/^\s*data:/i', $href)) {
                $n->removeAttribute('href');
            } else {
                $n->setAttribute('href', htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                $n->setAttribute('rel', 'noopener noreferrer');
                $n->setAttribute('target', '_blank');
            }
            // remove any other attributes
            $attrs = [];
            foreach ($n->attributes as $a) $attrs[] = $a->name;
            foreach ($attrs as $a) if (!in_array($a, ['href','rel','target'], true)) $n->removeAttribute($a);
        } else {
            // remove all attributes on other tags
            $attrs = [];
            foreach ($n->attributes as $a) $attrs[] = $a->name;
            foreach ($attrs as $a) $n->removeAttribute($a);
        }
    }

    $out = '';
    foreach ($container->childNodes as $child) $out .= $doc->saveHTML($child);
    return $out;
}

/**
 * Check if enrolling a student into a course causes any timetable clash
 * with any of the student's currently active enrolled courses.
 *
 * Returns an associative array of clash details if a conflict exists, or null otherwise.
 */
function student_enrolment_timetable_clash(int $studentId, int $newCourseId): ?array
{
    return row(
        "SELECT t_new.day_of_week,
                t_new.start_time AS new_start,
                t_new.end_time   AS new_end,
                c_new.name       AS new_course,
                c_new.code       AS new_code,
                d_new.name       AS new_dept,
                t_cur.start_time AS cur_start,
                t_cur.end_time   AS cur_end,
                c_cur.name       AS cur_course,
                c_cur.code       AS cur_code,
                d_cur.name       AS cur_dept
         FROM timetable t_new
         JOIN courses c_new ON c_new.id = t_new.course_id
         LEFT JOIN departments d_new ON d_new.id = c_new.department_id
         JOIN timetable t_cur ON t_cur.day_of_week = t_new.day_of_week
                              AND t_cur.start_time < t_new.end_time
                              AND t_cur.end_time   > t_new.start_time
         JOIN courses c_cur ON c_cur.id = t_cur.course_id
         LEFT JOIN departments d_cur ON d_cur.id = c_cur.department_id
         JOIN enrolments e ON e.course_id = t_cur.course_id
                           AND e.student_id = ?
                           AND e.status = 'active'
         WHERE t_new.course_id = ?
         LIMIT 1",
        [$studentId, $newCourseId]
    );
}

