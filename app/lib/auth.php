<?php
/**
 * Authentication, roles and permissions.
 *
 * Six roles:
 *   superadmin  full access; INVISIBLE to every other role (never listed,
 *               never counted, its audit entries are hidden)
 *   admin       system management: departments, courses, registrations, staff,
 *               timetable, certificates, IDs, reports, settings
 *   manager     line manager — owns one department: its courses, facilitators,
 *               students, lesson-plan and monthly-report review
 *   facilitator teaching staff — attendance, marks, lesson plans, monthly report
 *   student     own courses, timetable, attendance, marks, certificate
 *   kitchen     kitchen admin — daily tea and meal counts only
 */

const ROLES = [
    'superadmin'  => 'Super Admin',
    'admin'       => 'Administrator',
    'manager'     => 'Line Manager',
    'facilitator' => 'Facilitator',
    'student'     => 'Student',
    'kitchen'     => 'Kitchen Admin',
];

/** Roles that hold a staff-side account (used for pickers and counts). */
const STAFF_ROLES = ['admin', 'manager', 'facilitator', 'kitchen'];

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_name('EY_SRMS');
    session_start();

    // Idle timeout
    if (!empty($_SESSION['uid'])) {
        $idle = time() - (int) ($_SESSION['seen'] ?? time());
        if ($idle > SESSION_IDLE_MINUTES * 60) {
            $_SESSION = [];
            session_regenerate_id(true);
            flash('info', 'You were signed out after ' . SESSION_IDLE_MINUTES . ' minutes of inactivity.');
        }
    }
    $_SESSION['seen'] = time();
}

function current_user(bool $reload = false): ?array
{
    static $cached = null;
    static $lookedUp = false;
    if ($reload) {
        $cached = null;
        $lookedUp = false;
    }
    if ($lookedUp) return $cached;
    $lookedUp = true;

    $uid = $_SESSION['uid'] ?? null;
    if (!$uid) return null;
    $cached = row('SELECT * FROM users WHERE id = ? AND status = ?', [$uid, 'active']);
    if (!$cached) { $_SESSION = []; return null; }
    return $cached;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function role(): string
{
    return current_user()['role'] ?? 'guest';
}

function user_id(): int
{
    return (int) (current_user()['id'] ?? 0);
}

/** Department a manager or facilitator belongs to (0 = none). */
function my_department(): int
{
    return (int) (current_user()['department_id'] ?? 0);
}

/** Student record linked to a student login (null for staff). */
function my_student(): ?array
{
    $u = current_user();
    if (!$u || $u['role'] !== 'student' || empty($u['student_id'])) return null;
    return row('SELECT * FROM students WHERE id = ?', [$u['student_id']]);
}

function is_role(string ...$roles): bool
{
    return in_array(role(), $roles, true);
}

/** superadmin + admin */
function is_admin(): bool
{
    return is_role('superadmin', 'admin');
}

// ── Login ───────────────────────────────────────────────────────────────────
function login_lock_remaining(string $email): int
{
    $r = row('SELECT tries, locked_till FROM login_attempts WHERE email = ?', [mb_strtolower($email)]);
    if (!$r || empty($r['locked_till'])) return 0;
    $left = strtotime($r['locked_till']) - time();
    return $left > 0 ? (int) ceil($left / 60) : 0;
}

function note_login_failure(string $email): void
{
    $email = mb_strtolower($email);
    $r = row('SELECT * FROM login_attempts WHERE email = ?', [$email]);
    if ($r) {
        $tries = (int) $r['tries'] + 1;
        $lock  = $tries >= MAX_LOGIN_TRIES ? date('Y-m-d H:i:s', time() + 600) : null;
        q('UPDATE login_attempts SET tries = ?, locked_till = ?, updated_at = ? WHERE email = ?',
          [$tries, $lock, now(), $email]);
    } else {
        insert('login_attempts', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-', 'tries' => 1, 'updated_at' => now()]);
    }
}

function clear_login_failures(string $email): void
{
    q('DELETE FROM login_attempts WHERE email = ?', [mb_strtolower($email)]);
}

/** Returns null on success, or an error message. */
function attempt_login(string $email, string $password): ?string
{
    $identity = trim($email);
    if ($identity === '') return 'Enter your email or registration number.';

    // Lock/attempt tracking keyed by the identity string
    if ($mins = login_lock_remaining($identity)) {
        return "Too many failed attempts. Try again in {$mins} minute" . ($mins === 1 ? '' : 's') . '.';
    }

    // If password is empty, allow student-only quick login using registration number
    if ($password === '') {
        // Try to find a student with that registration number
        $student = row('SELECT id FROM students WHERE student_no = ?', [$identity]);
        if (!$student) {
            note_login_failure($identity);
            return 'That registration number does not match an active student account.';
        }
        $u = row('SELECT * FROM users WHERE student_id = ? AND status = ?', [$student['id'], 'active']);
        if (!$u) {
            note_login_failure($identity);
            return 'Student account not available. Contact an administrator.';
        }

        // Successful student login
        clear_login_failures($identity);
        session_regenerate_id(true);
        $_SESSION['uid']     = (int) $u['id'];
        $_SESSION['seen']    = time();
        $_SESSION['started'] = time();
        current_user(true);
        q('UPDATE users SET last_login = ? WHERE id = ?', [now(), $u['id']]);
        audit('login', 'users', $u['id'], $u['role']);
        return null;
    }

    // Normal email + password login
    $email = mb_strtolower($identity);
    $u = row('SELECT * FROM users WHERE email = ?', [$email]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        note_login_failure($identity);
        return 'That email and password do not match an account.';
    }
    if ($u['status'] !== 'active') return 'This account is deactivated. Ask an administrator to re-enable it.';

    clear_login_failures($identity);
    session_regenerate_id(true);
    $_SESSION['uid']     = (int) $u['id'];
    $_SESSION['seen']    = time();
    $_SESSION['started'] = time();
    current_user(true);
    q('UPDATE users SET last_login = ? WHERE id = ?', [now(), $u['id']]);
    audit('login', 'users', $u['id'], $u['role']);
    return null;
}

function logout(): void
{
    if (is_logged_in()) audit('logout', 'users', user_id());
    $_SESSION = [];
    session_regenerate_id(true);
    current_user(true);
}

// ── Guards ──────────────────────────────────────────────────────────────────
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect('login');
    }
}

function deny(string $why = 'You do not have access to that page.'): never
{
    http_response_code(403);
    $u = current_user();
    audit('denied', 'route', getStr('r'), $why);
    include BASE_PATH . '/views/403.php';
    exit;
}

function require_role(string ...$roles): void
{
    require_login();
    if (!in_array(role(), $roles, true)) deny();
}

// ── Superadmin invisibility ─────────────────────────────────────────────────
/**
 * SQL fragment that removes the super admin from any user query, unless the
 * viewer IS the super admin. Every user listing, count, picker and audit view
 * runs through this.
 */
function hide_superadmin(string $alias = 'u'): string
{
    return role() === 'superadmin' ? '' : " AND {$alias}.role <> 'superadmin' ";
}

/**
 * SQL fragment that removes all superadmin activity, references, and actions
 * from audit log queries unless the viewer IS the super admin.
 */
function hide_superadmin_audit(string $alias = 'a'): string
{
    if (role() === 'superadmin') return '';
    return " AND ({$alias}.role IS NULL OR {$alias}.role <> 'superadmin') "
         . " AND ({$alias}.details NOT LIKE '%superadmin%') "
         . " AND ({$alias}.action <> 'backup') "
         . " AND ({$alias}.user_id NOT IN (SELECT id FROM users WHERE role = 'superadmin') OR {$alias}.user_id IS NULL) "
         . " AND NOT ({$alias}.entity = 'users' AND {$alias}.entity_id IN (SELECT id FROM users WHERE role = 'superadmin')) ";
}

/** Guard for opening a single user record. */
function can_see_user(array $target): bool
{
    if (role() === 'superadmin') return true;
    return $target['role'] !== 'superadmin';
}

/** Roles the current user is allowed to create or assign. */
function assignable_roles(): array
{
    $all = ROLES;
    if (role() === 'superadmin') return $all;
    unset($all['superadmin']);              // admins can never mint a super admin
    if (role() !== 'admin') return [];
    return $all;
}

// ── Capabilities ────────────────────────────────────────────────────────────
/**
 * One place that answers "may this role do this?". Pages call can('...')
 * instead of comparing role strings all over the codebase.
 */
function can(string $ability): bool
{
    $r = role();
    if ($r === 'superadmin') return true;   // full access, always

    $map = [
        // registration & students
        'students.view'      => ['admin', 'manager', 'facilitator'],
        'students.manage'    => ['admin', 'manager'],
        'students.delete'    => ['admin'],
        'students.enrol'     => ['admin', 'manager'],
        // structure
        'departments.view'   => ['admin', 'manager'],
        'departments.manage' => ['admin'],
        'courses.view'       => ['admin', 'manager', 'facilitator'],
        'courses.manage'     => ['admin', 'manager'],
        // teaching
        'attendance.mark'    => ['facilitator'],
        'attendance.view'    => ['admin', 'manager', 'facilitator'],
        'marks.manage'       => ['facilitator', 'admin', 'manager'],
        'timetable.manage'   => ['admin', 'manager'],
        'lessonplans.submit' => ['facilitator'],
        'lessonplans.review' => ['admin', 'manager'],
        'monthly.submit'     => ['facilitator'],
        'monthly.review'     => ['admin', 'manager'],
        'notices.manage'     => ['admin', 'manager', 'facilitator'],
        // documents
        'idcards.manage'     => ['admin', 'manager'],
        'certificates.manage' => ['admin'],
        // kitchen
        'kitchen.record'     => ['kitchen'],
        'kitchen.view'       => ['kitchen', 'admin'],
        // system
        'reports.view'       => ['admin', 'manager', 'facilitator'],
        'users.manage'       => ['admin'],
        'settings.manage'    => ['admin'],
        'audit.view'         => ['admin'],
        'backup.run'         => [],          // super admin only
    ];
    return in_array($r, $map[$ability] ?? [], true);
}

/**
 * Department scope. Managers and facilitators only ever see their own
 * department; admins see everything. Returns [sqlFragment, params].
 */
function dept_scope(string $column = 'department_id'): array
{
    if (is_admin()) return ['', []];
    $d = my_department();
    if (!$d) return [" AND 1 = 0 ", []];     // no department assigned = nothing to show
    return [" AND {$column} = ? ", [$d]];
}

/** Courses the current user is allowed to touch (facilitator = assigned only). */
function my_course_ids(): array
{
    $r = role();
    if (in_array($r, ['superadmin', 'admin'], true)) {
        return array_column(rows('SELECT id FROM courses'), 'id');
    }
    if ($r === 'manager') {
        return array_column(rows('SELECT id FROM courses WHERE department_id = ?', [my_department()]), 'id');
    }
    if ($r === 'facilitator') {
        $ids = array_column(rows('SELECT id FROM courses WHERE facilitator_id = ?', [user_id()]), 'id');
        $tt  = array_column(rows('SELECT DISTINCT course_id FROM timetable WHERE facilitator_id = ?', [user_id()]), 'course_id');
        return array_values(array_unique(array_merge($ids, $tt)));
    }
    if ($r === 'student') {
        $s = my_student();
        return $s ? array_column(rows('SELECT course_id FROM enrolments WHERE student_id = ?', [$s['id']]), 'course_id') : [];
    }
    return [];
}

/** Guard: does the current user own this course? */
function require_course_access(int $courseId): array
{
    $c = row('SELECT c.*, d.name AS dept_name FROM courses c
              LEFT JOIN departments d ON d.id = c.department_id WHERE c.id = ?', [$courseId]);
    if (!$c) deny('That course no longer exists.');
    if (!in_array((int) $c['id'], array_map('intval', my_course_ids()), true)) {
        deny('That course is not assigned to you.');
    }
    return $c;
}

/** Helper for building IN (...) lists from an id array. */
function in_list(array $ids): string
{
    $ids = array_map('intval', $ids);
    return $ids ? implode(',', $ids) : '0';
}
