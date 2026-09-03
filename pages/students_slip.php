<?php
/** Registration confirmation slip — the paper the student walks out with. */
require_once BASE_PATH . '/views/icons.php';

$id = getInt('id');
$s  = row('SELECT s.*, d.name AS dept, u.name AS officer FROM students s
           LEFT JOIN departments d ON d.id = s.department_id
           LEFT JOIN users u ON u.id = s.registered_by WHERE s.id = ?', [$id]);
if (!$s) { flash('error', 'That student file was not found.'); redirect('students.index'); }
if (is_role('manager') && (int) $s['department_id'] !== my_department()) deny();

$enrols = rows('SELECT e.*, c.code, c.name AS course, c.duration_weeks, c.start_date, c.end_date
                FROM enrolments e JOIN courses c ON c.id = e.course_id
                WHERE e.student_id = ? ORDER BY c.name', [$id]);

$full        = trim($s['first_name'] . ' ' . ($s['middle_name'] ? $s['middle_name'] . ' ' : '') . $s['last_name']);
$page_title  = 'Registration slip · ' . $s['student_no'];
$print_page  = 'a4';
$print_back  = url('students.view', ['id' => $id]);
$print_hint  = 'Print two copies: one for the student, one for the file.';
$st          = settings();
?>
<div class="sheet sheet--a4">
  <div class="docribbon"><i></i><i></i><i></i></div>

  <div class="dochead">
    <div class="dochead__mark">EY</div>
    <div>
      <div class="dochead__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
      <div class="dochead__motto">"<?= e($st['org_motto'] ?? ORG_MOTTO) ?>"</div>
      <div class="dochead__meta"><?= e($st['org_address'] ?? '') ?> · <?= e($st['org_phone'] ?? '') ?> · <?= e($st['org_email'] ?? '') ?></div>
    </div>
    <div class="dochead__right">
      <div class="doctitle">Registration slip</div>
      <div class="docref"><?= e($s['student_no']) ?></div>
      <div class="dochead__meta">Issued <?= e(d(date('Y-m-d'))) ?></div>
    </div>
  </div>

  <div style="display:flex;gap:8mm;align-items:flex-start">
    <div style="flex:1">
      <div class="docsection">Student</div>
      <div class="pairs">
        <div class="pair"><span class="pair__k">Full name</span><span class="pair__v"><?= e($full) ?></span></div>
        <div class="pair"><span class="pair__k">Student number</span><span class="pair__v" style="font-family:var(--mono)"><?= e($s['student_no']) ?></span></div>
        <div class="pair"><span class="pair__k">Gender</span><span class="pair__v"><?= e($s['gender'] ?: '—') ?></span></div>
        <div class="pair"><span class="pair__k">Date of birth</span><span class="pair__v"><?= e(d($s['dob'])) ?></span></div>
        <div class="pair"><span class="pair__k">Phone</span><span class="pair__v"><?= e($s['phone'] ?: '—') ?></span></div>
        <div class="pair"><span class="pair__k">Email</span><span class="pair__v"><?= e($s['email'] ?: '—') ?></span></div>
        <div class="pair"><span class="pair__k">National ID</span><span class="pair__v"><?= e($s['national_id'] ?: '—') ?></span></div>
        <div class="pair pair--wide"><span class="pair__k">Address</span><span class="pair__v"><?= e($s['address'] ?: '—') ?></span></div>
        <div class="pair"><span class="pair__k">Department</span><span class="pair__v"><?= e($s['dept'] ?? '—') ?></span></div>
        <div class="pair"><span class="pair__k">Status</span><span class="pair__v"><?= e(ucfirst($s['status'])) ?></span></div>
      </div>
    </div>
    <?php if ($s['photo']): ?>
      <img src="<?= e(photo_url($s['photo'])) ?>" alt=""
           style="width:28mm;height:35mm;object-fit:cover;border:.3mm solid var(--line);border-radius:2mm">
    <?php endif; ?>
  </div>

  <div class="docsection">Guardian / emergency contact</div>
  <div class="pairs">
    <div class="pair"><span class="pair__k">Name</span><span class="pair__v"><?= e($s['guardian_name'] ?: '—') ?></span></div>
    <div class="pair"><span class="pair__k">Phone</span><span class="pair__v"><?= e($s['guardian_phone'] ?: '—') ?></span></div>
    <div class="pair"><span class="pair__k">Relationship</span><span class="pair__v"><?= e($s['guardian_relation'] ?: '—') ?></span></div>
  </div>

  <div class="docsection">Enrolment</div>
  <?php if (!$enrols): ?>
    <p style="font-size:12px;color:var(--ink-soft)">No course enrolment recorded at the time of printing.</p>
  <?php else: ?>
    <table class="doc">
      <thead>
        <tr><th>Code</th><th>Course</th><th class="num">Weeks</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php foreach ($enrols as $en): ?>
        <tr>
          <td style="font-family:var(--mono)"><?= e($en['code']) ?></td>
          <td><?= e($en['course']) ?></td>
          <td class="num"><?= (int) $en['duration_weeks'] ?></td>
          <td><?= e(ucfirst($en['status'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div class="docsection">Declaration</div>
  <p style="font-size:11.5px;line-height:1.6;color:var(--ink-soft)">
    I confirm that the details above are correct and that I have received the centre's rules on attendance and
    conduct. I understand that a minimum attendance of
    <?= e($st['attendance_threshold'] ?? '80') ?>% is required to sit assessments and receive a certificate.
  </p>

  <div class="signrow">
    <div><div class="signline">Student signature</div></div>
    <div><div class="signline">Guardian signature</div></div>
    <div><div class="signline">Registering officer — <?= e($s['officer'] ?? current_user()['name']) ?></div></div>
  </div>

  <div class="docfoot">
    <span><?= e($st['org_name'] ?? ORG_NAME) ?> · Registration slip · <?= e($s['student_no']) ?></span>
    <span>Printed <?= e(date('d M Y H:i')) ?> by <?= e(current_user()['name']) ?></span>
  </div>
</div>
