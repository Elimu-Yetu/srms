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
    $where .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_no LIKE ? OR s.phone LIKE ?) ';
    $like = '%' . $search . '%'; array_push($args, $like, $like, $like, $like);
}
if ($status !== '') { $where .= ' AND s.status = ? '; $args[] = $status; }
if ($dept)         { $where .= ' AND s.department_id = ? '; $args[] = $dept; }
if ($course)       { $where .= ' AND s.id IN (SELECT student_id FROM enrolments WHERE course_id = ?) '; $args[] = $course; }

$list = rows("SELECT s.student_no, s.first_name, s.middle_name, s.last_name, s.gender, s.dob, s.phone,
                     s.email, s.national_id, s.address, s.education_level, s.guardian_name, s.guardian_phone,
                     s.guardian_relation, d.name AS department, s.status, s.registered_at
              FROM students s LEFT JOIN departments d ON d.id = s.department_id
              $where ORDER BY s.student_no", $args);

audit('export', 'students', '', count($list) . ' rows');

$file = 'elimu-yetu-students-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $file . '"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // BOM so Excel reads UTF-8 names correctly
fputcsv($out, ['Student No', 'First name', 'Middle name', 'Last name', 'Gender', 'Date of birth', 'Phone',
               'Email', 'National ID', 'Address', 'Education level', 'Guardian', 'Guardian phone',
               'Relationship', 'Department', 'Status', 'Registered at']);
foreach ($list as $r) fputcsv($out, array_values($r));
fclose($out);
