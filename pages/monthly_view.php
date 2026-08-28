<?php
/** Read a monthly report; a line manager acknowledges or asks for changes. */
require_once BASE_PATH . '/views/icons.php';

$id = getInt('id');
$m  = row('SELECT mr.*, c.code, c.name AS course, u.name AS facilitator, r.name AS reviewer
           FROM monthly_reports mr JOIN courses c ON c.id = mr.course_id
           LEFT JOIN users u ON u.id = mr.facilitator_id
           LEFT JOIN users r ON r.id = mr.reviewed_by WHERE mr.id = ?', [$id]);
if (!$m) { flash('error', 'That report was not found.'); redirect('monthly.index'); }
if (!in_array((int) $m['course_id'], array_map('intval', my_course_ids()), true)) deny();
if (is_role('facilitator') && (int) $m['facilitator_id'] !== user_id()) deny('That report belongs to another facilitator.');

if (is_post() && can('monthly.review')) {
    csrf_check();
    $decision = post('decision');
    if (in_array($decision, ['acknowledged', 'changes_requested'], true)) {
        q('UPDATE monthly_reports SET status = ?, review_comment = ?, reviewed_by = ?, reviewed_at = ? WHERE id = ?',
          [$decision, post('review_comment'), user_id(), now(), $id]);
        audit('review', 'monthly_reports', $id, $decision);
        flash('ok', $decision === 'acknowledged' ? 'Report acknowledged.' : 'Changes requested.');
        redirect('monthly.view', ['id' => $id]);
    }
}

$page_title = month_label($m['month_year']) . ' · ' . $m['code'];
$page_sub   = e($m['course']) . ' · ' . e($m['facilitator'] ?? '') . ' · submitted ' . e(d($m['created_at']));
$page_actions = '<a class="btn" target="_blank" href="' . e(url('monthly.print', ['id' => $id])) . '">' . icon('printer', 16) . ' Print</a>';
if (is_role('facilitator') && $m['status'] !== 'acknowledged') {
    $page_actions .= '<a class="btn" href="' . e(url('monthly.form', ['id' => $id])) . '">' . icon('edit', 16) . ' Edit</a>';
}

$sections = [
    'Topics covered'              => $m['topics_covered'],
    'Attendance summary'          => $m['attendance_summary'],
    'Student challenges observed' => $m['challenges'],
    'Support needed'              => $m['support_needed'],
    'General comments'            => $m['comments'],
];
?>
<div class="grid grid--sidebar">
  <div class="panel">
    <div class="panel__head">
      <h2>Monthly report</h2>
      <?= badge(ucfirst(str_replace('_', ' ', $m['status'])), status_tone($m['status'])) ?>
      <span class="tiny muted"><?= (int) $m['sessions_held'] ?> sessions held</span>
    </div>
    <div class="panel__body">
      <?php foreach ($sections as $label => $body): ?>
        <div class="section-head"><span></span><h3><?= e($label) ?></h3></div>
        <div class="tiny"><?= $body !== '' && $body !== null ? nl2br(e($body)) : '<span class="muted">Not stated.</span>' ?></div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="stack">
    <?php if ($m['review_comment']): ?>
      <div class="panel">
        <div class="panel__head"><h2>Manager's response</h2></div>
        <div class="panel__body">
          <div class="tiny"><?= nl2br(e($m['review_comment'])) ?></div>
          <div class="tiny muted" style="margin-top:8px"><?= e($m['reviewer'] ?? '') ?> · <?= e(d($m['reviewed_at'], 'd M Y')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (can('monthly.review')): ?>
      <div class="panel">
        <div class="panel__head"><h2>Review</h2></div>
        <form method="post" class="panel__body">
          <?= csrf_field() ?>
          <div class="field">
            <label for="review_comment">Response to the facilitator</label>
            <textarea id="review_comment" name="review_comment" rows="4" placeholder="Acknowledge the report, or say what needs changing."><?= e($m['review_comment']) ?></textarea>
          </div>
          <div class="btnrow">
            <button class="btn btn--green" name="decision" value="acknowledged"><?= icon('check', 16) ?> Acknowledge</button>
            <button class="btn btn--orange" name="decision" value="changes_requested">Request changes</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel__head"><h2>Details</h2></div>
      <div class="panel__body">
        <dl class="dl">
          <dt>Month</dt><dd><?= e(month_label($m['month_year'])) ?></dd>
          <dt>Class</dt><dd><?= e($m['code']) ?></dd>
          <dt>Facilitator</dt><dd><?= e($m['facilitator'] ?? '—') ?></dd>
          <dt>Sessions held</dt><dd class="mono"><?= (int) $m['sessions_held'] ?></dd>
          <dt>Submitted</dt><dd><?= e(d($m['created_at'], 'd M Y H:i')) ?></dd>
        </dl>
      </div>
    </div>
  </div>
</div>
