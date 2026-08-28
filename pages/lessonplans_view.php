<?php
/** Read a plan; a line manager reviews it here. */
require_once BASE_PATH . '/views/icons.php';

$id = getInt('id');
$p  = row('SELECT lp.*, c.code, c.name AS course, c.department_id, u.name AS facilitator, r.name AS reviewer
           FROM lesson_plans lp JOIN courses c ON c.id = lp.course_id
           LEFT JOIN users u ON u.id = lp.facilitator_id
           LEFT JOIN users r ON r.id = lp.reviewed_by WHERE lp.id = ?', [$id]);
if (!$p) { flash('error', 'That lesson plan was not found.'); redirect('lessonplans.index'); }
if (!in_array((int) $p['course_id'], array_map('intval', my_course_ids()), true)) deny();
if (is_role('facilitator') && (int) $p['facilitator_id'] !== user_id()) deny('That plan belongs to another facilitator.');

if (is_post() && can('lessonplans.review')) {
    csrf_check();
    $decision = post('decision');
    $comment  = post('review_comment');
    if (in_array($decision, ['reviewed', 'changes_requested'], true)) {
        q('UPDATE lesson_plans SET review_status = ?, review_comment = ?, reviewed_by = ?, reviewed_at = ? WHERE id = ?',
          [$decision, $comment, user_id(), now(), $id]);
        audit('review', 'lesson_plans', $id, $decision);
        flash('ok', $decision === 'reviewed' ? 'Marked as reviewed.' : 'Changes requested — the facilitator can see your comment.');
        redirect('lessonplans.view', ['id' => $id]);
    }
}

$page_title = $p['topic'];
$page_sub   = e($p['code']) . ' · ' . e(d($p['plan_date'], 'l d F Y')) . ' · ' . e($p['facilitator'] ?? '');
if (is_role('facilitator') && $p['review_status'] !== 'reviewed') {
    $page_actions = '<a class="btn" href="' . e(url('lessonplans.form', ['id' => $id])) . '">' . icon('edit', 16) . ' Edit</a>';
}
?>
<div class="grid grid--sidebar">
  <div class="panel">
    <div class="panel__head">
      <h2>Lesson plan</h2>
      <?= badge(ucfirst(str_replace('_', ' ', $p['review_status'])), status_tone($p['review_status'])) ?>
    </div>
    <div class="panel__body">
      <div class="section-head"><span></span><h3>Learning objectives</h3></div>
      <div class="tiny"><?= nl2br(e($p['objectives'])) ?></div>

      <div class="section-head"><span></span><h3>Activities planned</h3></div>
      <div class="tiny"><?= $p['activities'] ? nl2br(e($p['activities'])) : '<span class="muted">Not stated.</span>' ?></div>

      <div class="section-head"><span></span><h3>Resources needed</h3></div>
      <div class="tiny"><?= $p['resources'] ? nl2br(e($p['resources'])) : '<span class="muted">None listed.</span>' ?></div>
    </div>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Details</h2></div>
      <div class="panel__body">
        <dl class="dl">
          <dt>Course</dt><dd><?= e($p['course']) ?></dd>
          <dt>Lesson date</dt><dd><?= e(d($p['plan_date'])) ?></dd>
          <dt>Facilitator</dt><dd><?= e($p['facilitator'] ?? '—') ?></dd>
          <dt>Submitted</dt><dd><?= e(d($p['created_at'], 'd M Y H:i')) ?></dd>
          <?php if ($p['reviewed_at']): ?>
            <dt>Reviewed</dt><dd><?= e(d($p['reviewed_at'], 'd M Y')) ?><br><span class="tiny muted"><?= e($p['reviewer'] ?? '') ?></span></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <?php if ($p['review_comment']): ?>
      <div class="panel">
        <div class="panel__head"><h2>Manager's comment</h2></div>
        <div class="panel__body tiny"><?= nl2br(e($p['review_comment'])) ?></div>
      </div>
    <?php endif; ?>

    <?php if (can('lessonplans.review')): ?>
      <div class="panel">
        <div class="panel__head"><h2>Review</h2></div>
        <form method="post" class="panel__body">
          <?= csrf_field() ?>
          <div class="field">
            <label for="review_comment">Comment to the facilitator</label>
            <textarea id="review_comment" name="review_comment" rows="3"><?= e($p['review_comment']) ?></textarea>
          </div>
          <div class="btnrow">
            <button class="btn btn--green" name="decision" value="reviewed"><?= icon('check', 16) ?> Mark reviewed</button>
            <button class="btn btn--orange" name="decision" value="changes_requested">Request changes</button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
