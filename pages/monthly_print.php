<?php
/** Monthly report as a filing document. */
$id = getInt('id');
$m  = row('SELECT mr.*, c.code, c.name AS course, d.name AS dept, u.name AS facilitator, r.name AS reviewer
           FROM monthly_reports mr JOIN courses c ON c.id = mr.course_id
           LEFT JOIN departments d ON d.id = c.department_id
           LEFT JOIN users u ON u.id = mr.facilitator_id
           LEFT JOIN users r ON r.id = mr.reviewed_by WHERE mr.id = ?', [$id]);
if (!$m) { flash('error', 'That report was not found.'); redirect('monthly.index'); }
if (!in_array((int) $m['course_id'], array_map('intval', my_course_ids()), true)) deny();

$page_title = 'Monthly report · ' . $m['code'] . ' · ' . $m['month_year'];
$print_page = 'a4';
$print_back = url('monthly.view', ['id' => $id]);
$st = settings();
$sections = [
    'Topics covered'              => $m['topics_covered'],
    'Attendance summary'          => $m['attendance_summary'],
    'Student challenges observed' => $m['challenges'],
    'Support needed'              => $m['support_needed'],
    'General comments'            => $m['comments'],
];
?>
<div class="sheet sheet--a4">
  <div class="docribbon"><i></i><i></i><i></i></div>
  <div class="dochead">
    <div class="dochead__mark">EY</div>
    <div>
      <div class="dochead__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
      <div class="dochead__motto">"<?= e($st['org_motto'] ?? ORG_MOTTO) ?>"</div>
      <div class="dochead__meta"><?= e($st['org_phone'] ?? '') ?> · <?= e($st['org_email'] ?? '') ?></div>
    </div>
    <div class="dochead__right">
      <div class="doctitle">Facilitator monthly report</div>
      <div class="docref"><?= e($m['code']) ?> · <?= e(month_label($m['month_year'])) ?></div>
      <div class="dochead__meta"><?= e(ucfirst(str_replace('_', ' ', $m['status']))) ?></div>
    </div>
  </div>

  <div class="pairs">
    <div class="pair"><span class="pair__k">Course</span><span class="pair__v"><?= e($m['course']) ?></span></div>
    <div class="pair"><span class="pair__k">Department</span><span class="pair__v"><?= e($m['dept'] ?? '—') ?></span></div>
    <div class="pair"><span class="pair__k">Facilitator</span><span class="pair__v"><?= e($m['facilitator'] ?? '—') ?></span></div>
    <div class="pair"><span class="pair__k">Sessions held</span><span class="pair__v"><?= (int) $m['sessions_held'] ?></span></div>
    <div class="pair"><span class="pair__k">Month</span><span class="pair__v"><?= e(month_label($m['month_year'])) ?></span></div>
    <div class="pair"><span class="pair__k">Submitted</span><span class="pair__v"><?= e(d($m['created_at'], 'd M Y')) ?></span></div>
  </div>

  <?php foreach ($sections as $label => $body): ?>
    <div class="docsection"><?= e($label) ?></div>
    <div style="font-size:12.5px;white-space:pre-wrap"><?= $body !== '' && $body !== null ? e($body) : '—' ?></div>
  <?php endforeach; ?>

  <?php if ($m['review_comment']): ?>
    <div class="docsection">Line manager's response</div>
    <div style="font-size:12.5px;white-space:pre-wrap"><?= e($m['review_comment']) ?></div>
    <div style="font-size:11px;color:var(--ink-soft);margin-top:2mm">
      <?= e($m['reviewer'] ?? '') ?> · <?= e(d($m['reviewed_at'], 'd M Y')) ?>
    </div>
  <?php endif; ?>

  <div class="signrow">
    <div><div class="signline">Facilitator — <?= e($m['facilitator'] ?? '') ?></div></div>
    <div><div class="signline">Line manager</div></div>
  </div>

  <div class="docfoot">
    <span><?= e($st['org_name'] ?? ORG_NAME) ?> · Monthly report · <?= e($m['code']) ?> <?= e($m['month_year']) ?></span>
    <span>Printed <?= e(date('d M Y H:i')) ?></span>
  </div>
</div>
