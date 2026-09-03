<?php
/** Admin global view: show all timetable slots grouped by department. */
require_once BASE_PATH . '/views/icons.php';

// Only admins and superadmins may view this page per routes.php
$slots = rows("SELECT t.*, c.code, c.name AS course, d.name AS department, u.name AS facilitator
               FROM timetable t
               JOIN courses c ON c.id = t.course_id
               LEFT JOIN departments d ON d.id = c.department_id
               LEFT JOIN users u ON u.id = t.facilitator_id
               ORDER BY d.name, t.day_of_week, t.start_time");

$grouped = [];
foreach ($slots as $s) {
    $dept = $s['department'] ?? '—';
    $grouped[$dept][] = $s;
}

$page_title = 'All timetables';
?>
<div class="panel">
  <?php if (!$slots): ?>
    <div class="empty"><div class="empty__mark"><?= icon('calendar',22) ?></div><h3>No timetable slots</h3><p>No slots are defined yet.</p></div>
  <?php else: ?>
    <?php foreach ($grouped as $dept => $arr): ?>
      <h3 style="margin-top:12px"><?= e($dept) ?></h3>
      <div class="tablewrap">
        <table class="data compact">
          <thead><tr><th>Day</th><th>Time</th><th>Course</th><th>Subject</th><th>Room</th><th>Facilitator</th></tr></thead>
          <tbody>
          <?php foreach ($arr as $r): ?>
            <tr>
              <td class="tiny mono"><?= e(day_name((int) $r['day_of_week'])) ?></td>
              <td class="tiny mono"><?= e($r['start_time']) ?>–<?= e($r['end_time']) ?></td>
              <td class="tiny"><?= e($r['code'] . ' — ' . $r['course']) ?></td>
              <td><?= e($r['subject']) ?></td>
              <td class="tiny"><?= e($r['room'] ?: '—') ?></td>
              <td class="tiny"><?= e($r['facilitator'] ?: '—') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
