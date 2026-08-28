<?php
/** Read-only weekly timetable for the signed-in student or facilitator. */
require_once BASE_PATH . '/views/icons.php';

$mine = my_course_ids();
if (is_role('facilitator')) {
    $slots = rows('SELECT t.*, c.code, c.name AS course FROM timetable t JOIN courses c ON c.id = t.course_id
                   WHERE t.facilitator_id = ? OR (t.facilitator_id IS NULL AND c.facilitator_id = ?)
                   ORDER BY t.day_of_week, t.start_time', [user_id(), user_id()]);
    $page_sub = 'Every slot assigned to you this week.';
} else {
    $slots = $mine ? rows('SELECT t.*, c.code, c.name AS course, u.name AS facilitator
                           FROM timetable t JOIN courses c ON c.id = t.course_id
                           LEFT JOIN users u ON u.id = COALESCE(t.facilitator_id, c.facilitator_id)
                           WHERE t.course_id IN (' . in_list($mine) . ')
                           ORDER BY t.day_of_week, t.start_time') : [];
    $page_sub = 'Your lessons, every week.';
}
$grid = [];
foreach ($slots as $s) $grid[(int) $s['day_of_week']][] = $s;
$today = (int) date('N');
?>
<?php if (!$slots): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('calendar', 22) ?></div>
    <h3>No timetable yet</h3>
    <p>The office builds the timetable per course. It appears here as soon as slots are entered.</p>
  </div></div>
<?php else: ?>
  <div class="panel">
    <div class="panel__body">
      <table class="tt">
        <thead><tr><?php for ($i = 1; $i <= 6; $i++): ?><th><?= e(day_name($i)) ?></th><?php endfor; ?></tr></thead>
        <tbody><tr>
        <?php for ($i = 1; $i <= 6; $i++): ?>
          <td>
            <div class="rail__label" style="padding-left:0">
              <?= e(day_name($i)) ?><?= $i === $today ? ' · today' : '' ?>
            </div>
            <?php if (empty($grid[$i])): ?>
              <div class="tiny muted" style="padding:6px 2px">—</div>
            <?php endif; ?>
            <?php foreach ($grid[$i] ?? [] as $s): ?>
              <div class="ttcard <?= $i === $today ? '' : 'ttcard--blue' ?>">
                <div class="ttcard__time"><?= e($s['start_time']) ?>–<?= e($s['end_time']) ?></div>
                <div class="ttcard__subject"><?= e($s['subject']) ?></div>
                <div class="ttcard__meta"><?= e($s['code']) ?><?= $s['room'] ? ' · ' . e($s['room']) : '' ?></div>
                <?php if (!empty($s['facilitator'])): ?>
                  <div class="ttcard__meta"><?= e($s['facilitator']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </td>
        <?php endfor; ?>
        </tr></tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
