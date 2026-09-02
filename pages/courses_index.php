<?php
/** Courses. Students see this as "My courses". */
require_once BASE_PATH . '/views/icons.php';

$isStudent = is_role('student');
$me = $isStudent ? my_student() : null;
$mine = my_course_ids();

$list = rows("SELECT c.*, d.name AS dept, u.name AS facilitator,
                (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = 'active') AS enrolled
              FROM courses c
              LEFT JOIN departments d ON d.id = c.department_id
              LEFT JOIN users u ON u.id = c.facilitator_id
              WHERE c.id IN (" . in_list($mine) . ") ORDER BY c.status, c.name");

$page_title = $isStudent ? 'My courses' : 'Courses';
$page_sub   = count($list) . ' course' . (count($list) === 1 ? '' : 's') . ($isStudent ? ' you are enrolled in' : ' in your scope');
if (can('courses.manage')) {
    $page_actions = '<a class="btn btn--primary" href="' . e(url('courses.form')) . '">' . icon('plus', 16) . ' New course</a>';
}
?>
<?php if (!$list): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('book', 22) ?></div>
    <h3><?= $isStudent ? 'You are not enrolled in a course yet' : 'No courses yet' ?></h3>
    <p><?= $isStudent ? 'Once the office enrols you, your course, timetable and attendance appear here.'
                      : 'A course belongs to a department, has a facilitator, a capacity and a timetable.' ?></p>
    <?php if (can('courses.manage')): ?><a class="btn btn--primary" href="<?= e(url('courses.form')) ?>">Create a course</a><?php endif; ?>
  </div></div>
<?php else: ?>
  <div class="panel">
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Course</th><th>Department</th><th>Facilitator</th><th>Runs</th><th class="right">Enrolled</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $c):
          $fill = (int) $c['capacity'] > 0 ? pct((int) $c['enrolled'], (int) $c['capacity']) : 0; ?>
          <tr>
            <td>
              <a href="<?= e(url('courses.view', ['id' => $c['id']])) ?>"><strong><?= e($c['name']) ?></strong></a>
              <div class="tiny mono muted"><?= e($c['code']) ?> · <?= (int) $c['duration_weeks'] ?> weeks</div>
            </td>
            <td class="tiny"><?= e($c['dept'] ?? '—') ?></td>
            <td class="tiny"><?= e($c['facilitator'] ?? 'To be assigned') ?></td>
            <td class="tiny mono"><?= e(d($c['start_date'], 'd M y')) ?> → <?= e(d($c['end_date'], 'd M y')) ?></td>
            <td class="right" style="min-width:110px">
              <div class="mono tiny"><?= (int) $c['enrolled'] ?> / <?= (int) $c['capacity'] ?></div>
              <div class="segbar" style="height:5px;margin-top:3px"><span class="<?= $fill >= 100 ? 'seg-l' : 'seg-p' ?>" style="width:<?= min(100, $fill) ?>%"></span></div>
            </td>
            <td><?= badge(ucfirst($c['status']), status_tone($c['status'])) ?></td>
            <td class="right nowrap">
              <a class="btn btn--sm btn--ghost" href="<?= e(url('courses.view', ['id' => $c['id']])) ?>"><?= icon('eye', 15) ?></a>
              <?php if (can('courses.manage')): ?>
                <a class="btn btn--sm btn--ghost" href="<?= e(url('courses.form', ['id' => $c['id']])) ?>"><?= icon('edit', 15) ?></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
