<?php
/** Courses. Students see this as "My courses". */
require_once BASE_PATH . '/views/icons.php';

if (is_post() && can('courses.manage')) {
  csrf_check();
  if (post('do') === 'delete') {
    $cid = postInt('id');
    $c = row('SELECT * FROM courses WHERE id = ?', [$cid]);
    if ($c) {
      $enrolled = (int) val('SELECT COUNT(*) FROM enrolments WHERE course_id = ?', [$cid], 0);
      if ($enrolled) {
        flash('error', 'Cannot delete a course with enrolled students.');
      } else {
        q('DELETE FROM courses WHERE id = ?', [$cid]);
        audit('delete', 'courses', $cid, $c['code'] . ' ' . $c['name']);
        flash('ok', 'Course removed.');
      }
    }
    redirect('courses.index');
  }
}

$isStudent = is_role('student');
$me = $isStudent ? my_student() : null;
$mine = my_course_ids();
$search = getStr('q');

$where = ' WHERE c.id IN (' . in_list($mine) . ') ';
$args = [];

if ($search !== '') {
    $where .= ' AND (c.name LIKE ? OR c.code LIKE ? OR c.description LIKE ?) ';
    $like = '%' . $search . '%';
    array_push($args, $like, $like, $like);
}

$list = rows("SELECT c.*, d.name AS dept, u.name AS facilitator,
                (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = 'active') AS enrolled
              FROM courses c
              LEFT JOIN departments d ON d.id = c.department_id
              LEFT JOIN users u ON u.id = c.facilitator_id
              $where ORDER BY c.status, c.name", $args);

$page_title = $isStudent ? 'My courses' : 'Courses';
$page_sub   = count($list) . ' course' . (count($list) === 1 ? '' : 's') . ($isStudent ? ' you are enrolled in' : ' in your scope');
if (can('courses.manage')) {
    $page_actions = '<a class="btn btn--primary" href="' . e(url('courses.form')) . '">' . icon('plus', 16) . ' New course</a>';
}
?>
<?php if (!$list): ?>
  <div class="panel">
    <form class="toolbar" method="get">
      <input type="hidden" name="r" value="courses.index">
      <div class="field grow">
        <label for="q">Search by name or code</label>
        <input id="q" type="search" name="q" value="<?= e($search) ?>" placeholder="e.g. Mathematics, CS-101">
      </div>
      <button class="btn" type="submit"><?= icon('search', 16) ?> Search</button>
      <?php if ($search): ?>
        <a class="btn btn--ghost" href="<?= e(url('courses.index')) ?>">Clear</a>
      <?php endif; ?>
    </form>
    <div class="empty">
      <div class="empty__mark"><?= icon('book', 22) ?></div>
      <h3><?= $isStudent ? 'You are not enrolled in a course yet' : 'No courses yet' ?></h3>
      <p><?= $isStudent ? 'Once the office enrols you, your course, timetable and attendance appear here.'
                        : 'A course belongs to a department, has a facilitator, a capacity and a timetable.' ?></p>
      <?php if (can('courses.manage')): ?><a class="btn btn--primary" href="<?= e(url('courses.form')) ?>">Create a course</a><?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="panel">
    <form class="toolbar" method="get">
      <input type="hidden" name="r" value="courses.index">
      <div class="field grow">
        <label for="q">Search by name or code</label>
        <input id="q" type="search" name="q" value="<?= e($search) ?>" placeholder="e.g. Mathematics, CS-101">
      </div>
      <button class="btn" type="submit"><?= icon('search', 16) ?> Search</button>
      <?php if ($search): ?>
        <a class="btn btn--ghost" href="<?= e(url('courses.index')) ?>">Clear</a>
      <?php endif; ?>
    </form>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Course</th><th>Department</th><th>Facilitator</th><th class="right">Enrolled</th><th>Status</th><th></th></tr></thead>
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
            <!-- start/end dates removed from listing -->
            <td class="right" style="min-width:110px">
              <div class="mono tiny"><?= (int) $c['enrolled'] ?> / <?= (int) $c['capacity'] ?></div>
              <div class="segbar" style="height:5px;margin-top:3px"><span class="<?= $fill >= 100 ? 'seg-l' : 'seg-p' ?>" style="width:<?= min(100, $fill) ?>%"></span></div>
            </td>
            <td><?= badge(ucfirst($c['status']), status_tone($c['status'])) ?></td>
            <td class="right nowrap">
              <a class="btn btn--sm btn--ghost" href="<?= e(url('courses.view', ['id' => $c['id']])) ?>"><?= icon('eye', 15) ?></a>
              <?php if (can('courses.manage')): ?>
                <a class="btn btn--sm btn--ghost" href="<?= e(url('courses.form', ['id' => $c['id']])) ?>"><?= icon('edit', 15) ?></a>
                <form method="post" style="display:inline-block;margin-left:6px">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <button class="btn btn--sm btn--ghost" data-confirm="Delete this course?"><?= icon('x', 14) ?></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
