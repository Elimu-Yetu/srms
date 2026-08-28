<?php
/**
 * Route table. Every reachable page is declared here with the capability or
 * roles required to open it — access is decided before the page file is
 * included, never inside the page markup.
 *
 *   cap    => capability from can()
 *   roles  => explicit role list
 *   public => reachable without signing in
 *   layout => app (default) | auth | print | public
 */
function routes(): array
{
    return [
        // ── access ──────────────────────────────────────────────────────────
        'login'   => ['file' => 'login.php',  'public' => true, 'layout' => 'auth',   'title' => 'Sign in'],
        'logout'  => ['file' => 'logout.php', 'public' => true, 'layout' => 'auth',   'title' => 'Sign out'],
        'verify'  => ['file' => 'verify.php', 'public' => true, 'layout' => 'public', 'title' => 'Verify a certificate'],

        // ── everyone signed in ──────────────────────────────────────────────
        'dashboard'     => ['file' => 'dashboard.php', 'roles' => array_keys(ROLES), 'title' => 'Dashboard'],
        'profile'       => ['file' => 'profile.php',   'roles' => array_keys(ROLES), 'title' => 'My account'],
        'media.photo'   => ['file' => 'media.php',     'roles' => array_keys(ROLES), 'layout' => 'raw', 'title' => 'Photo'],

        // ── registration & students ─────────────────────────────────────────
        'students.index'  => ['file' => 'students_index.php', 'cap' => 'students.view',   'title' => 'Students'],
        'students.form'   => ['file' => 'students_form.php',  'cap' => 'students.manage', 'title' => 'Register a student'],
        'students.view'   => ['file' => 'students_view.php',  'cap' => 'students.view',   'title' => 'Student file'],
        'students.slip'   => ['file' => 'students_slip.php',  'cap' => 'students.view',   'layout' => 'print', 'title' => 'Registration slip'],
        'students.export' => ['file' => 'students_export.php', 'cap' => 'students.view',  'layout' => 'raw',   'title' => 'Export students'],
        'enrolments.act'  => ['file' => 'enrolments_act.php', 'cap' => 'students.enrol',  'layout' => 'raw',   'title' => 'Enrolment'],

        'departments.index' => ['file' => 'departments_index.php', 'cap' => 'departments.view',   'title' => 'Departments'],
        'departments.form'  => ['file' => 'departments_form.php',  'cap' => 'departments.manage', 'title' => 'Department'],
        'departments.view'  => ['file' => 'departments_view.php',  'cap' => 'departments.view',   'title' => 'Department'],

        // Students see this same page as "My courses", so it is role-based, not cap-based.
        'courses.index' => ['file' => 'courses_index.php', 'roles' => ['superadmin', 'admin', 'manager', 'facilitator', 'student'], 'title' => 'Courses'],
        'courses.form'  => ['file' => 'courses_form.php',  'cap' => 'courses.manage', 'title' => 'Course'],
        'courses.view'  => ['file' => 'courses_view.php',  'cap' => 'courses.view',   'title' => 'Course'],

        // ── teaching ────────────────────────────────────────────────────────
        'attendance.mark'     => ['file' => 'attendance_mark.php',     'cap' => 'attendance.mark', 'title' => 'Take attendance'],
        'attendance.register' => ['file' => 'attendance_register.php', 'cap' => 'attendance.view', 'title' => 'Attendance register'],
        'attendance.mine'     => ['file' => 'attendance_mine.php',     'roles' => ['student'],     'title' => 'My attendance'],

        'timetable.index' => ['file' => 'timetable_index.php', 'cap' => 'timetable.manage', 'title' => 'Timetable'],
        'timetable.mine'  => ['file' => 'timetable_mine.php',  'roles' => ['student', 'facilitator', 'manager', 'admin', 'superadmin'], 'title' => 'Timetable'],

        'lessonplans.index' => ['file' => 'lessonplans_index.php', 'roles' => ['superadmin', 'admin', 'manager', 'facilitator'], 'title' => 'Lesson plans'],
        'lessonplans.form'  => ['file' => 'lessonplans_form.php',  'cap' => 'lessonplans.submit', 'title' => 'Lesson plan'],
        'lessonplans.view'  => ['file' => 'lessonplans_view.php',  'roles' => ['superadmin', 'admin', 'manager', 'facilitator'], 'title' => 'Lesson plan'],

        'monthly.index' => ['file' => 'monthly_index.php', 'roles' => ['superadmin', 'admin', 'manager', 'facilitator'], 'title' => 'Monthly reports'],
        'monthly.form'  => ['file' => 'monthly_form.php',  'cap' => 'monthly.submit', 'title' => 'Monthly report'],
        'monthly.view'  => ['file' => 'monthly_view.php',  'roles' => ['superadmin', 'admin', 'manager', 'facilitator'], 'layout' => 'app', 'title' => 'Monthly report'],
        'monthly.print' => ['file' => 'monthly_print.php', 'roles' => ['superadmin', 'admin', 'manager', 'facilitator'], 'layout' => 'print', 'title' => 'Monthly report'],

        'assessments.index' => ['file' => 'assessments_index.php', 'cap' => 'marks.manage', 'title' => 'Assessments'],
        'marks.enter'       => ['file' => 'marks_enter.php',       'cap' => 'marks.manage', 'title' => 'Enter marks'],
        'marks.mine'        => ['file' => 'marks_mine.php',        'roles' => ['student'],  'title' => 'My results'],

        'notices.index' => ['file' => 'notices_index.php', 'roles' => array_keys(ROLES), 'title' => 'Notices'],

        // ── documents ───────────────────────────────────────────────────────
        'idcards.index' => ['file' => 'idcards_index.php', 'cap' => 'idcards.manage', 'title' => 'Student ID cards'],
        'idcards.print' => ['file' => 'idcards_print.php', 'cap' => 'idcards.manage', 'layout' => 'print', 'title' => 'Print ID cards'],

        'certificates.index' => ['file' => 'certificates_index.php', 'roles' => ['superadmin', 'admin', 'student'], 'title' => 'Certificates'],
        'certificates.issue' => ['file' => 'certificates_issue.php', 'cap' => 'certificates.manage', 'title' => 'Issue a certificate'],
        'certificates.print' => ['file' => 'certificates_print.php', 'roles' => ['superadmin', 'admin', 'student'], 'layout' => 'print', 'title' => 'Certificate'],

        // ── reports ─────────────────────────────────────────────────────────
        'reports.index' => ['file' => 'reports_index.php', 'cap' => 'reports.view', 'title' => 'Reports'],
        'reports.show'  => ['file' => 'reports_show.php',  'cap' => 'reports.view', 'title' => 'Report'],

        // ── kitchen (kept at the bottom of the sidebar) ──────────────────────
        'kitchen.index'  => ['file' => 'kitchen_index.php',  'cap' => 'kitchen.view',   'title' => 'Kitchen dashboard'],
        'kitchen.record' => ['file' => 'kitchen_record.php', 'cap' => 'kitchen.record', 'title' => 'Record service'],
        'kitchen.report' => ['file' => 'kitchen_report.php', 'cap' => 'kitchen.view',   'title' => 'Kitchen report'],

        // ── system ──────────────────────────────────────────────────────────
        'users.index' => ['file' => 'users_index.php', 'cap' => 'users.manage',    'title' => 'Staff accounts'],
        'users.form'  => ['file' => 'users_form.php',  'cap' => 'users.manage',    'title' => 'Account'],
        'settings'    => ['file' => 'settings.php',    'cap' => 'settings.manage', 'title' => 'Settings'],
        'audit'       => ['file' => 'audit.php',       'cap' => 'audit.view',      'title' => 'Audit log'],
        'backup'      => ['file' => 'backup.php',      'roles' => ['superadmin'],  'title' => 'Backup'],
    ];
}

/**
 * Sidebar. Sections are ordered by how often staff use them; the kitchen
 * section is always last, as requested. Items are filtered by capability so a
 * role never sees a door it cannot open.
 */
function nav_sections(): array
{
    $s = [];

    $s[] = ['label' => '', 'items' => array_filter([
        nav('dashboard', 'Dashboard', 'grid'),
    ])];

    // Student-facing
    if (is_role('student')) {
        $s[] = ['label' => 'My learning', 'items' => array_filter([
            nav('courses.index',       'My courses',    'book'),
            nav('timetable.mine',      'My timetable',  'calendar'),
            nav('attendance.mine',     'My attendance', 'check'),
            nav('marks.mine',          'My results',    'chart'),
            nav('certificates.index',  'My certificate', 'award'),
            nav('notices.index',       'Notices',       'bell'),
        ])];
    }

    // Registration
    $reg = array_filter([
        can('students.view')     ? nav('students.index', 'Students', 'users') : null,
        can('students.manage')   ? nav('students.form',  'Register a student', 'plus') : null,
        can('departments.view')  ? nav('departments.index', 'Departments', 'layers') : null,
        can('courses.view') && !is_role('student') ? nav('courses.index', 'Courses', 'book') : null,
    ]);
    if ($reg) $s[] = ['label' => 'Registration', 'items' => $reg];

    // Teaching
    $teach = array_filter([
        can('attendance.mark') ? nav('attendance.mark', 'Take attendance', 'check') : null,
        can('attendance.view') ? nav('attendance.register', 'Attendance register', 'list') : null,
        can('timetable.manage') ? nav('timetable.index', 'Timetable', 'calendar')
                                : (is_role('facilitator') ? nav('timetable.mine', 'My timetable', 'calendar') : null),
        can('marks.manage') ? nav('assessments.index', 'Assessments & marks', 'chart') : null,
        is_role('superadmin', 'admin', 'manager', 'facilitator') ? nav('lessonplans.index', 'Lesson plans', 'file') : null,
        is_role('superadmin', 'admin', 'manager', 'facilitator') ? nav('monthly.index', 'Monthly reports', 'calendar-check') : null,
        can('notices.manage') ? nav('notices.index', 'Notices', 'bell') : null,
    ]);
    if ($teach) $s[] = ['label' => 'Teaching', 'items' => $teach];

    // Documents
    $docs = array_filter([
        can('idcards.manage')      ? nav('idcards.index', 'Student ID cards', 'card') : null,
        can('certificates.manage') ? nav('certificates.index', 'Certificates', 'award') : null,
        can('reports.view')        ? nav('reports.index', 'Reports', 'printer') : null,
    ]);
    if ($docs) $s[] = ['label' => 'Documents & reports', 'items' => $docs];

    // System
    $sys = array_filter([
        can('users.manage')    ? nav('users.index', 'Staff accounts', 'shield') : null,
        can('settings.manage') ? nav('settings', 'Settings', 'cog') : null,
        can('audit.view')      ? nav('audit', 'Audit log', 'eye') : null,
        is_role('superadmin')  ? nav('backup', 'Backup & restore', 'database') : null,
    ]);
    if ($sys) $s[] = ['label' => 'System', 'items' => $sys];

    // Kitchen — always the lowest section in the sidebar
    $kit = array_filter([
        can('kitchen.view')   ? nav('kitchen.index', 'Kitchen dashboard', 'kitchen') : null,
        can('kitchen.record') ? nav('kitchen.record', 'Record service', 'plus') : null,
        can('kitchen.view')   ? nav('kitchen.report', 'Kitchen report', 'printer') : null,
    ]);
    if ($kit) $s[] = ['label' => 'Kitchen', 'items' => $kit, 'accent' => 'orange'];

    return $s;
}

function nav(string $route, string $label, string $icon, array $params = []): array
{
    return ['route' => $route, 'label' => $label, 'icon' => $icon, 'params' => $params];
}

/** Highlight the sidebar item matching the current route. */
function nav_active(string $route, string $current): bool
{
    if ($route === $current) return true;
    $group = explode('.', $route)[0];
    // A section's index highlights for its own detail pages.
    return str_ends_with($route, '.index') && str_starts_with($current, $group . '.');
}
