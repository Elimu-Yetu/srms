<?php
/** Small POST-only handler for enrolment and student-status changes. */
if (!is_post()) { redirect('students.index'); }
csrf_check();

$studentId = postInt('student_id');
$s = row('SELECT * FROM students WHERE id = ?', [$studentId]);
if (!$s) { flash('error', 'That student file was not found.'); redirect('students.index'); }
if (is_role('manager') && (int) $s['department_id'] !== my_department()) deny('That student is in another department.');

switch (post('do')) {

    case 'enrol':
        $courseId = postInt('course_id');
        $c = row('SELECT * FROM courses WHERE id = ?', [$courseId]);
        if (!$c) { flash('error', 'That course was not found.'); break; }
        if (val('SELECT 1 FROM enrolments WHERE student_id = ? AND course_id = ?', [$studentId, $courseId])) {
            flash('warn', 'Already enrolled in ' . e($c['code']) . '.');
            break;
        }
        $taken = (int) val('SELECT COUNT(*) FROM enrolments WHERE course_id = ? AND status = ?', [$courseId, 'active'], 0);
        if ($taken >= (int) $c['capacity']) {
            flash('warn', e($c['code']) . ' is full (' . $taken . ' of ' . (int) $c['capacity'] . '). Raise the capacity first.');
            break;
        }
        insert('enrolments', [
            'student_id' => $studentId, 'course_id' => $courseId,
            'enrolled_on' => date('Y-m-d'), 'status' => 'active', 'created_at' => now(),
        ]);
        audit('enrol', 'enrolments', $studentId, $c['code']);
        flash('ok', 'Enrolled in <strong>' . e($c['name']) . '</strong>.');
        break;

    case 'unenrol':
        $eid = postInt('enrolment_id');
        $en  = row('SELECT e.*, c.code FROM enrolments e JOIN courses c ON c.id = e.course_id WHERE e.id = ? AND e.student_id = ?',
                   [$eid, $studentId]);
        if (!$en) { flash('error', 'That enrolment was not found.'); break; }
        q('DELETE FROM enrolments WHERE id = ?', [$eid]);
        audit('unenrol', 'enrolments', $eid, $en['code']);
        flash('ok', 'Removed from ' . e($en['code']) . '. Recorded attendance is kept.');
        break;

    case 'status':
        $new = post('status');
        if (!in_array($new, ['pending', 'active', 'completed', 'deferred', 'withdrawn', 'suspended'], true)) {
            flash('error', 'That status is not recognised.');
            break;
        }
        q('UPDATE students SET status = ? WHERE id = ?', [$new, $studentId]);
        audit('status', 'students', $studentId, $s['status'] . ' → ' . $new);
        flash('ok', 'Status is now <strong>' . e($new) . '</strong>.');
        break;

    default:
        flash('error', 'Unknown action.');
}
redirect('students.view', ['id' => $studentId]);
