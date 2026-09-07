<?php
// Reset student records: delete all students and student users, insert one sample student.
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lib/db.php';

try {
    db()->beginTransaction();

    // remove attendance and enrolments linked to any student
    q('DELETE a FROM attendance a JOIN enrolments e ON a.enrolment_id = e.id JOIN students s ON e.student_id = s.id');
    q('DELETE e FROM enrolments e JOIN students s ON e.student_id = s.id');

    // remove student users and student records
    q("DELETE FROM users WHERE role = 'student'");
    q('DELETE FROM students');

    // insert one sample student 
    /*
    $studentNo = 'SAMPLE-001';
    $now = date('Y-m-d H:i:s');
    $sid = insert('students', [
        'student_no' => $studentNo,
        'first_name' => 'Test',
        'middle_name' => '',
        'last_name'  => 'Student',
        'gender'     => 'other',
        'dob'        => null,
        'phone'      => '',
        'email'      => 'sample@student.local',
        'national_id'=> '',
        'address'    => '',
        'education_level' => '',
        'guardian_name' => '',
        'guardian_phone' => '',
        'guardian_relation' => '',
        'department_id' => null,
        'photo' => '',
        'status' => 'active',
        'notes' => 'Sample student created by reset script',
        'registered_by' => null,
        'registered_at' => $now,
    ]);
*/

    // create a linked user account (empty password_hash)
    q('INSERT INTO users (name, email, phone, role, password_hash, department_id, student_id, status, must_reset, last_login, created_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
       ['Test Student', 'sample@student.local', '', 'student', '', null, $sid, 'active', 0, null, $now]);

    db()->commit();
    echo "Reset complete. Inserted student id: {$sid}\n";
} catch (Throwable $e) {
    db()->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
