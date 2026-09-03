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
// Admins/managers may edit or delete any plan (manage CRUD)
if (can('lessonplans.review')) {
  $page_actions = ($page_actions ?? '') . ' <a class="btn" href="' . e(url('lessonplans.form', ['id' => $id])) . '">' . icon('edit', 16) . ' Edit</a>'
          . ' <form method="post" action="' . e(url('lessonplans.act')) . '" style="display:inline;margin-left:8px">' . csrf_field()
          . '<input type="hidden" name="id" value="' . (int) $id . '"><button class="btn btn--danger" data-confirm="Delete this lesson plan?">' . icon('x', 12) . ' Delete</button></form>';
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
      <div class="tiny">
        <?php
          $obj = json_decode($p['objectives'] ?? '{}', true);
          if (!$obj) { echo nl2br(e($p['objectives'])); }
          else {
            echo '<strong>Knowledge</strong><br>' . nl2br(e($obj['knowledge'] ?? '')) . '<br><br>';
            echo '<strong>Skills</strong><br>' . nl2br(e($obj['skills'] ?? '')) . '<br><br>';
            echo '<strong>Heart</strong><br>' . nl2br(e($obj['heart'] ?? '')) . '<br><br>';
            echo '<strong>Practical</strong><br>' . nl2br(e($obj['practical'] ?? '')) . '<br>';
          }
        ?>
      </div>

      <div class="section-head"><span></span><h3>Activities planned</h3></div>
      <div class="tiny"><?= $p['activities'] ? sanitize_html($p['activities']) : '<span class="muted">Not stated.</span>' ?></div>

      <?php $res = json_decode($p['resources'] ?? '{}', true) ?: []; ?>
      <div class="section-head"><span></span><h3>Lesson details</h3></div>
      <div class="tiny">
        <?= e($res['classes_taught'] ?? '') ? '<strong>Classes taught:</strong> ' . e($res['classes_taught']) . '<br>' : '' ?>
        <?= e($res['lesson_name'] ?? '') ? '<strong>Lesson name:</strong> ' . e($res['lesson_name']) . '<br>' : '' ?>
        <?= e($res['duration_mins'] ?? '') ? '<strong>Duration:</strong> ' . e($res['duration_mins']) . ' mins<br>' : '' ?>
        <?= e($res['unit'] ?? '') ? '<strong>Unit:</strong> ' . e($res['unit']) . '<br>' : '' ?>
        <?= e($res['step'] ?? '') ? '<strong>Step:</strong> ' . e($res['step']) . '<br>' : '' ?>
        <?= e($res['leader_teacher'] ?? '') ? '<strong>Leader:</strong> ' . e($res['leader_teacher']) . '<br>' : '' ?>
        <?= e($res['assistants'] ?? '') ? '<strong>Assistants:</strong> ' . e($res['assistants']) . '<br>' : '' ?>
        <?= e($res['start_time'] ?? '') || e($res['end_time'] ?? '') ? '<strong>Time:</strong> ' . e($res['start_time'] ?? '') . ' — ' . e($res['end_time'] ?? '') . '<br>' : '' ?>
      </div>

      <div class="section-head"><span></span><h3>Attendance</h3></div>
      <div class="tiny">
        <?php if (!empty($res['registered']) || !empty($res['attended'])): ?>
          <?php $reg = $res['registered'] ?? []; $att = $res['attended'] ?? []; ?>
          <strong>Registered:</strong> Boys: <?= e($reg['boys'] ?? '') ?>, Girls: <?= e($reg['girls'] ?? '') ?>, Total: <?= e($reg['total'] ?? '') ?><br>
          <strong>Attended:</strong> Boys: <?= e($att['boys'] ?? '') ?>, Girls: <?= e($att['girls'] ?? '') ?>, Total: <?= e($att['total'] ?? '') ?><br>
        <?php else: ?>
          <span class="muted">No attendance recorded.</span>
        <?php endif; ?>
      </div>

      <div class="section-head"><span></span><h3>Teacher notes</h3></div>
      <div class="tiny">
        <?php $tn = $res['teacher_notes'] ?? []; ?>
        <?= $tn['comments'] ? nl2br(e($tn['comments'])) . '<br><br>' : '' ?>
        <?= $tn['challenges'] ? '<strong>Challenges:</strong><br>' . nl2br(e($tn['challenges'])) . '<br><br>' : '' ?>
        <?= $tn['suggested_changes'] ? '<strong>Suggested changes:</strong><br>' . nl2br(e($tn['suggested_changes'])) . '<br>' : '' ?>
      </div>

      <div class="section-head"><span></span><h3>Resources needed</h3></div>
      <div class="tiny">
        <?php
          $res = json_decode($p['resources'] ?? '{}', true);
          if (!$res) { echo nl2br(e($p['resources'])); }
          else {
            echo '<strong>Teaching methods</strong><br>' . nl2br(e($res['teaching_methods'] ?? '')) . '<br><br>';
            echo '<strong>References</strong><br>' . nl2br(e($res['references'] ?? '')) . '<br><br>';
            echo '<strong>Equipment & Materials</strong><br>' . nl2br(e($res['equipment'] ?? '')) . '<br>';
          }
        ?>
      </div>
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
