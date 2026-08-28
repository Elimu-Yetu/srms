<?php
/** Issue a completion certificate against a real enrolment. */
require_once BASE_PATH . '/views/icons.php';

$studentId = getInt('student_id');
$errors = [];

if (is_post()) {
    csrf_check();
    $studentId = postInt('student_id');
    $courseId  = postInt('course_id');
    $grade     = post('grade');
    $issueDate = post('issue_date') ?: date('Y-m-d');
    $remarks   = post('remarks');

    $s = row('SELECT * FROM students WHERE id = ?', [$studentId]);
    $c = row('SELECT * FROM courses WHERE id = ?', [$courseId]);
    if (!$s) $errors[] = 'Choose a student.';
    if (!$c) $errors[] = 'Choose a course.';
    if ($s && $c && !val('SELECT 1 FROM enrolments WHERE student_id = ? AND course_id = ?', [$studentId, $courseId])) {
        $errors[] = 'That student is not enrolled in that course, so a certificate cannot be issued.';
    }
    if ($s && $c && val('SELECT 1 FROM certificates WHERE student_id = ? AND course_id = ? AND status = ?', [$studentId, $courseId, 'valid'])) {
        $errors[] = 'A valid certificate already exists for this student and course.';
    }
    if (strtotime($issueDate) > time() + 86400) $errors[] = 'The issue date cannot be in the future.';

    if (!$errors) {
        $id = insert('certificates', [
            'student_id' => $studentId, 'course_id' => $courseId,
            'serial_no' => next_serial('certificates', 'serial_no', 'EY-CERT-' . date('Y') . '-'),
            'verify_code' => verify_code(), 'grade' => $grade,
            'issue_date' => $issueDate, 'remarks' => $remarks, 'status' => 'valid',
            'issued_by' => user_id(), 'created_at' => now(),
        ]);
        // Completing a course closes the enrolment.
        q('UPDATE enrolments SET status = ? WHERE student_id = ? AND course_id = ?', ['completed', $studentId, $courseId]);
        audit('issue', 'certificates', $id, $s['student_no'] . ' ' . $c['code']);
        flash('ok', 'Certificate issued. Print it now or find it later in the registry.');
        redirect('certificates.print', ['id' => $id]);
    }
}

$students = rows("SELECT s.id, s.student_no, s.first_name, s.last_name FROM students s
                  WHERE s.status IN ('active','completed') ORDER BY s.first_name, s.last_name");
$enrolled = $studentId
    ? rows('SELECT c.id, c.code, c.name FROM enrolments e JOIN courses c ON c.id = e.course_id
            WHERE e.student_id = ? ORDER BY c.name', [$studentId])
    : [];
$page_sub = 'The serial number and verification code are generated automatically.';
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<div class="grid grid--sidebar">
  <div class="panel">
    <div class="panel__head"><h2>Certificate details</h2></div>
    <form method="post" class="panel__body">
      <?= csrf_field() ?>
      <div class="formgrid">
        <div class="field span2">
          <label for="student_id">Student <span class="req">*</span></label>
          <select id="student_id" name="student_id" required onchange="location.href='<?= e(url('certificates.issue')) ?>&student_id='+this.value">
            <option value="">Choose a student…</option>
            <?php foreach ($students as $s): ?>
              <option value="<?= (int) $s['id'] ?>" <?= $studentId === (int) $s['id'] ? 'selected' : '' ?>>
                <?= e($s['first_name'] . ' ' . $s['last_name'] . ' — ' . $s['student_no']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field span2">
          <label for="course_id">Completed course <span class="req">*</span></label>
          <select id="course_id" name="course_id" required <?= $enrolled ? '' : 'disabled' ?>>
            <?php if (!$studentId): ?><option value="">Pick the student first</option><?php endif; ?>
            <?php foreach ($enrolled as $c): ?>
              <option value="<?= (int) $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['name']) ?></option>
            <?php endforeach; ?>
            <?php if ($studentId && !$enrolled): ?><option value="">No enrolments on file</option><?php endif; ?>
          </select>
        </div>
        <div class="field">
          <label for="grade">Grade or classification</label>
          <select id="grade" name="grade">
            <?php foreach (['', 'Distinction', 'Credit', 'Pass', 'Completed'] as $g): ?>
              <option value="<?= e($g) ?>"><?= $g === '' ? '— none —' : e($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="issue_date">Issue date</label>
          <input id="issue_date" name="issue_date" type="date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
        </div>
        <div class="field span2">
          <label for="remarks">Remarks printed on the record</label>
          <input id="remarks" name="remarks" placeholder="e.g. Completed all practical assessments">
        </div>
      </div>
      <div class="btnrow">
        <button class="btn btn--green" <?= $enrolled ? '' : 'disabled' ?>><?= icon('award', 16) ?> Issue certificate</button>
        <a class="btn btn--ghost" href="<?= e(url('certificates.index')) ?>">Cancel</a>
      </div>
    </form>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>What issuing does</h2></div>
    <div class="panel__body tiny muted stack">
      <div>Creates a serial number in this year's sequence and a seven-character verification code.</div>
      <div>Marks the enrolment as <strong>completed</strong>.</div>
      <div>Records who issued it, in the audit log.</div>
      <div>The certificate can then be printed any number of times without a new number.</div>
    </div>
  </div>
</div>
