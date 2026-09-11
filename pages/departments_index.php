<?php
/** Departments — the top level of the centre's structure. */
require_once BASE_PATH . '/views/icons.php';

if (is_post() && can('departments.manage')) {
  csrf_check();
  if (post('do') === 'delete') {
    $did = postInt('id');
    $d = row('SELECT * FROM departments WHERE id = ?', [$did]);
    if ($d) {
      $courses = (int) val('SELECT COUNT(*) FROM courses WHERE department_id = ?', [$did], 0);
      $students = (int) val("SELECT COUNT(DISTINCT e.student_id)
                            FROM enrolments e
                            JOIN courses c ON c.id = e.course_id
                            WHERE c.department_id = ? AND e.status = 'active'", [$did], 0);
      $users = (int) val('SELECT COUNT(*) FROM users WHERE department_id = ?', [$did], 0);
      if ($courses || $students || $users) {
        flash('error', 'Cannot delete department while it has ' . ($courses ? $courses . ' course(s) ' : '') . ($students ? $students . ' student(s) ' : '') . ($users ? $users . ' account(s)' : ''));
      } else {
        q('DELETE FROM departments WHERE id = ?', [$did]);
        audit('delete', 'departments', $did, $d['name']);
        flash('ok', 'Department removed.');
      }
    }
    redirect('departments.index');
  }
}

[$scope, $args] = dept_scope('d.id');
$list = rows("SELECT d.*, u.name AS manager,
                (SELECT COUNT(*) FROM courses c WHERE c.department_id = d.id AND c.status = 'active') AS courses,
                (SELECT COUNT(DISTINCT e.student_id)
                 FROM enrolments e
                 JOIN courses c ON c.id = e.course_id
                 WHERE c.department_id = d.id AND e.status = 'active') AS students
              FROM departments d LEFT JOIN users u ON u.id = d.manager_id
              WHERE 1=1 $scope ORDER BY d.name", $args);

if (can('departments.manage')) {
    $page_actions = '<a class="btn btn--primary" href="' . e(url('departments.form')) . '">' . icon('plus', 16) . ' New department</a>';
}
$page_sub = 'Each department has one line manager, its own courses and its own students.';
?>
<?php if (!$list): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('layers', 22) ?></div>
    <h3>No departments yet</h3>
    <p>Departments come first: courses live inside them, and a line manager is responsible for each one.</p>
    <?php if (can('departments.manage')): ?>
      <a class="btn btn--primary" href="<?= e(url('departments.form')) ?>">Create the first department</a>
    <?php endif; ?>
  </div></div>
<?php else: ?>
  <div class="grid grid--3">
    <?php foreach ($list as $d): ?>
      <div class="panel">
        <div class="panel__body">
          <div style="display:flex;align-items:flex-start;gap:10px">
            <div>
              <div class="eyebrow mono"><?= e($d['code']) ?></div>
              <h2 style="margin-top:2px"><a href="<?= e(url('departments.view', ['id' => $d['id']])) ?>"><?= e($d['name']) ?></a></h2>
            </div>
            <span style="margin-left:auto"><?= badge(ucfirst($d['status']), status_tone($d['status'])) ?></span>
          </div>
          <p class="tiny muted" style="margin-top:8px"><?= e($d['description'] ?: 'No description.') ?></p>
          <div class="ribbon" style="margin:12px 0 10px"></div>
          <div style="display:flex;gap:18px">
            <div><div class="eyebrow">Students</div><div class="mono" style="font-size:19px"><?= (int) $d['students'] ?></div></div>
            <div><div class="eyebrow">Courses</div><div class="mono" style="font-size:19px"><?= (int) $d['courses'] ?></div></div>
          </div>
          <div class="tiny muted" style="margin-top:10px">
            Line manager: <strong><?= e($d['manager'] ?? 'not assigned') ?></strong>
          </div>
        </div>
        <div class="panel__foot">
          <a class="btn btn--sm" href="<?= e(url('departments.view', ['id' => $d['id']])) ?>">Open</a>
          <?php if (can('departments.manage')): ?>
            <a class="btn btn--sm btn--ghost" href="<?= e(url('departments.form', ['id' => $d['id']])) ?>"><?= icon('edit', 15) ?> Edit</a>
            <form method="post" style="display:inline-block;margin-left:6px">
              <?= csrf_field() ?>
              <input type="hidden" name="do" value="delete">
              <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
              <button class="btn btn--sm btn--ghost" data-confirm="Delete this department?"><?= icon('x', 14) ?></button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
