<?php
/** The certificate itself — A4 landscape, branded, verifiable. */
$id = getInt('id');
$c  = row('SELECT ce.*, s.first_name, s.middle_name, s.last_name, s.student_no,
                  co.name AS course, co.code, co.duration_weeks, d.name AS dept
           FROM certificates ce
           JOIN students s ON s.id = ce.student_id
           JOIN courses co ON co.id = ce.course_id
           LEFT JOIN departments d ON d.id = co.department_id
           WHERE ce.id = ?', [$id]);
if (!$c) { flash('error', 'That certificate was not found.'); redirect('certificates.index'); }

// A student may only print their own.
if (is_role('student')) {
    $me = my_student();
    if (!$me || (int) $me['id'] !== (int) $c['student_id']) deny('That certificate belongs to another student.');
}

$full = trim($c['first_name'] . ' ' . ($c['middle_name'] ? $c['middle_name'] . ' ' : '') . $c['last_name']);
$page_title = 'Certificate · ' . $c['serial_no'];
$print_page = 'a4l';
$print_back = is_role('student') ? url('certificates.index') : url('students.view', ['id' => $c['student_id']]);
$print_hint = 'Set paper to A4 landscape and scale to 100%. Print on 160gsm or heavier for a proper finish.';
$st = settings();
?>
<div class="cert">
  <div class="cert__wash"></div>
  <div class="cert__frame"></div>
  <div class="cert__inner">
    <div class="cert__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
    <div class="cert__motto">"<?= e($st['org_motto'] ?? ORG_MOTTO) ?>"</div>

    <div class="cert__kicker">Certificate of completion</div>
    <div class="cert__title">Vocational Training</div>

    <div class="cert__lead">This is to certify that</div>
    <div class="cert__name"><?= e($full) ?></div>
    <div class="cert__detail">Student number <?= e($c['student_no']) ?></div>

    <div class="cert__course">has successfully completed <?= e($c['course']) ?></div>
    <div class="cert__detail">
      <?= (int) $c['duration_weeks'] ?>-week programme<?= $c['dept'] ? ' · ' . e($c['dept']) : '' ?>
      <?= $c['grade'] ? ' · Awarded with ' . e($c['grade']) : '' ?>
    </div>
    <div class="cert__detail">Issued on <?= e(d($c['issue_date'], 'd F Y')) ?></div>

    <div class="cert__signs">
      <div class="cert__sign">
        <b><?= e($st['cert_signatory_1'] ?? '') ?></b>
        <span><?= e($st['cert_signatory_1_title'] ?? '') ?></span>
      </div>
      <div class="cert__sign">
        <b><?= e($st['cert_signatory_2'] ?? '') ?></b>
        <span><?= e($st['cert_signatory_2_title'] ?? '') ?></span>
      </div>
    </div>
  </div>

  <div class="cert__seal"><div>Elimu<br>Yetu<br>Seal</div></div>
  <div class="cert__verify">
    Serial <b><?= e($c['serial_no']) ?></b><br>
    Verify with code <b><?= e($c['verify_code']) ?></b> · <?= e($st['org_email'] ?? '') ?>
  </div>
</div>
