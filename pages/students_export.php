<?php
/** CSV export of the current register view — opens in Excel or LibreOffice. */
$search = getStr('q'); $status = getStr('status'); $dept = getInt('dept'); $course = getInt('course');

$where = ' WHERE 1=1 '; $args = [];
if (is_role('facilitator')) {
    $where .= ' AND s.id IN (SELECT student_id FROM enrolments WHERE course_id IN (' . in_list(my_course_ids()) . ')) ';
} else {
    [$scope, $scopeArgs] = dept_scope('s.department_id');
    $where .= $scope; $args = array_merge($args, $scopeArgs);
}
if ($search !== '') {
    $where .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.middle_name LIKE ? OR s.student_no LIKE ? OR s.phone LIKE ? OR s.national_id LIKE ? OR s.email LIKE ?) ';
    $like = '%' . $search . '%'; array_push($args, $like, $like, $like, $like, $like, $like, $like);
}
if ($status !== '') { $where .= ' AND s.status = ? '; $args[] = $status; }
if ($dept) {
    $where .= ' AND s.id IN (
        SELECT DISTINCT e.student_id
        FROM enrolments e
        JOIN courses c ON c.id = e.course_id
        WHERE c.department_id = ? AND e.status = "active"
    ) ';
    $args[] = $dept;
}
if ($course)       { $where .= ' AND s.id IN (SELECT student_id FROM enrolments WHERE course_id = ?) '; $args[] = $course; }

$list = rows("SELECT s.student_no, s.first_name, s.middle_name, s.last_name, s.gender, s.dob, s.phone,
                     s.email, s.national_id, s.address, s.education_level, s.guardian_name, s.guardian_phone,
                     s.guardian_relation,
                     COALESCE((SELECT GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ')
                               FROM enrolments e
                               JOIN courses c ON c.id = e.course_id
                               JOIN departments d ON d.id = c.department_id
                               WHERE e.student_id = s.id AND e.status = 'active'), 'No departments') AS departments_enrolled_in,
                     s.status, s.registered_at
              FROM students s
              $where ORDER BY s.student_no", $args);

audit('export', 'students', '', count($list) . ' rows');

$file = 'elimu-yetu-students-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $file . '"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // BOM so Excel reads UTF-8 names correctly
fputcsv($out, ['Student No', 'First name', 'Middle name', 'Last name', 'Gender', 'Date of birth', 'Phone',
               'Email', 'National ID', 'Address', 'Education level', 'Guardian', 'Guardian phone',
               'Relationship', 'Departments enrolled in', 'Status', 'Registered at']);
foreach ($list as $r) fputcsv($out, array_values($r));
fclose($out);
