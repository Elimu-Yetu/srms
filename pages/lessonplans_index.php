<?php
/** Lesson plans: facilitators see their own, managers see their department's inbox. */
require_once BASE_PATH . '/views/icons.php';

$status = getStr('status');
$mine   = my_course_ids();
$where  = ' WHERE lp.course_id IN (' . in_list($mine) . ') ';
$args   = [];
if (is_role('facilitator')) { $where .= ' AND lp.facilitator_id = ? '; $args[] = user_id(); }
if ($status !== '') { $where .= ' AND lp.review_status = ? '; $args[] = $status; }

$list = rows("SELECT lp.*, c.code, c.name AS course, u.name AS facilitator, r.name AS reviewer
              FROM lesson_plans lp
              JOIN courses c ON c.id = lp.course_id
              LEFT JOIN users u ON u.id = lp.facilitator_id
              LEFT JOIN users r ON r.id = lp.reviewed_by
              $where ORDER BY lp.plan_date DESC, lp.id DESC", $args);

$pending = (int) val('SELECT COUNT(*) FROM lesson_plans WHERE review_status = ? AND course_id IN (' . in_list($mine) . ')', ['submitted'], 0);
$page_sub = can('lessonplans.review')
    ? $pending . ' waiting for review'
    : 'Your submitted plans and the feedback on them.';
if (can('lessonplans.submit') || can('lessonplans.review')) {
  $page_actions = '<a class="btn btn--primary" href="' . e(url('lessonplans.form')) . '">' . icon('plus', 16) . ' New lesson plan</a>';
}
?>
<div class="panel">
  <div class="tabs">
    <a class="<?= $status === '' ? 'is-active' : '' ?>" href="<?= e(url('lessonplans.index')) ?>">All</a>
    <a class="<?= $status === 'submitted' ? 'is-active' : '' ?>" href="<?= e(url('lessonplans.index', ['status' => 'submitted'])) ?>">Submitted<?= $pending ? ' (' . $pending . ')' : '' ?></a>
    <a class="<?= $status === 'reviewed' ? 'is-active' : '' ?>" href="<?= e(url('lessonplans.index', ['status' => 'reviewed'])) ?>">Reviewed</a>
    <a class="<?= $status === 'changes_requested' ? 'is-active' : '' ?>" href="<?= e(url('lessonplans.index', ['status' => 'changes_requested'])) ?>">Changes requested</a>
  </div>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('file', 22) ?></div>
      <h3>No lesson plans here</h3>
    <p><?= can('lessonplans.submit') || can('lessonplans.review')
      ? 'Write a plan before the lesson: date, topic, objectives, activities and resources. Your line manager sees it at once.'
      : 'Facilitators submit plans from their own dashboard. Nothing has arrived yet.' ?></p>
    <?php if (can('lessonplans.submit') || can('lessonplans.review')): ?>
      <a class="btn btn--primary" href="<?= e(url('lessonplans.form')) ?>">Write a lesson plan</a>
    <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Date</th><th>Topic</th><th>Course</th><?php if (!is_role('facilitator')): ?><th>Facilitator</th><?php endif; ?><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $p): ?>
          <tr>
            <td class="tiny mono nowrap"><?= e(d($p['plan_date'])) ?></td>
            <td><a href="<?= e(url('lessonplans.view', ['id' => $p['id']])) ?>"><strong><?= e($p['topic']) ?></strong></a></td>
            <td class="tiny mono"><?= e($p['code']) ?></td>
            <?php if (!is_role('facilitator')): ?><td class="tiny"><?= e($p['facilitator'] ?? '—') ?></td><?php endif; ?>
            <td><?= badge(ucfirst(str_replace('_', ' ', $p['review_status'])), status_tone($p['review_status'])) ?></td>
            <td class="right">
              <a class="btn btn--sm btn--ghost" href="<?= e(url('lessonplans.view', ['id' => $p['id']])) ?>"><?= icon('eye', 15) ?></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
