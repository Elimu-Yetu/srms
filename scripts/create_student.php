<?php
// Create one student with a valid registration number and linked user account.
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lib/db.php';
require_once __DIR__ . '/../app/lib/helpers.php';

try {
    db()->beginTransaction();

    $studentNo = next_student_no();
    $now = date('Y-m-d H:i:s');

    $sid = insert('students', [
        'student_no' => $studentNo,
        'first_name' => 'Demo',
        'middle_name' => '',
        'last_name'  => 'Learner',
        'gender'     => 'other',
        'dob'        => null,
        'phone'      => '',
        'email'      => 'demo+' . strtolower(str_replace(['-', ' '], '', $studentNo)) . '@example.local',
        'national_id'=> '',
        'address'    => '',
        'education_level' => '',
        'guardian_name' => '',
        'guardian_phone' => '',
        'guardian_relation' => '',
        'department_id' => null,
        'photo' => '',
        'status' => 'active',
        'notes' => 'Auto-created sample student',
        'registered_by' => null,
        'registered_at' => $now,
    ]);

    // create linked user with empty password_hash to allow reg-no login
    q('INSERT INTO users (name, email, phone, role, password_hash, department_id, student_id, status, must_reset, last_login, created_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
       [
           'Demo Learner',
           'demo+' . strtolower(str_replace(['-', ' '], '', $studentNo)) . '@example.local',
           '', 'student', '', null, $sid, 'active', 0, null, $now
       ]);

    db()->commit();
    echo "Created student {$studentNo} (id={$sid}).\n";
} catch (Throwable $e) {
    db()->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
