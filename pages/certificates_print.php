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
    <div class="cert__header">
      <div class="cert__brand">
        <img src="assets/img/logo.svg" alt="logo" class="cert__logo">
        <div class="cert__brandtext">
          <div class="cert__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
          <div class="cert__contact">P.O.BOX <?= e($st['org_address'] ?? '837') ?> · TEL: <?= e($st['org_phone'] ?? '') ?> · <?= e($st['org_email'] ?? '') ?></div>
        </div>
      </div>
      <div class="cert__topright">&nbsp;</div>
    </div>

    <div class="cert__kicker">CERTIFICATE</div>
    <div class="cert__subtitle">OF COMPLETION</div>

    <div class="cert__present">this certificate presented For :</div>
    <div class="cert__name"><?= strtoupper(e($full)) ?></div>

    <div class="cert__desc">Has successfully completed the Basic <span class="cert__course-link"><?= e($c['course']) ?></span>, covering essential topics including HTML, CSS, JavaScript, Responsive Design, and Basic Server-Side Programming.</div>

    <div class="cert__daterange"><?= strtoupper(e(d($c['issue_date'], 'F Y'))) ?><?= $c['duration_weeks'] ? ' TO ' . strtoupper(e(date('F Y', strtotime('+'.((int)$c['duration_weeks']*7).' days', strtotime($c['issue_date'])))) ) : '' ?></div>

    <div class="cert__seal-holder">
      <div class="cert__seal"><div>Elimu<br>Yetu<br>Seal</div></div>
    </div>

    <div class="cert__signs">
      <div class="cert__sign">
        <b>&nbsp;</b>
        <span>AMOS KASARAMBA<br><small>(Director)</small></span>
      </div>
      <div class="cert__sign">
        <b>&nbsp;</b>
        <span>HEAD OF DEPARTMENT</span>
      </div>
    </div>
  </div>

  <div class="cert__seal"><div>Elimu<br>Yetu<br>Seal</div></div>
  <div class="cert__verify">
    Serial <b><?= e($c['serial_no']) ?></b><br>
    Verify with code <b><?= e($c['verify_code']) ?></b> · <?= e($st['org_email'] ?? '') ?>
  </div>
</div>
