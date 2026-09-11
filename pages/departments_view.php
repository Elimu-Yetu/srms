<?php
/** One department: its courses, staff and students. */
require_once BASE_PATH . '/views/icons.php';

$id = getInt('id');
$d  = row('SELECT d.*, u.name AS manager FROM departments d LEFT JOIN users u ON u.id = d.manager_id WHERE d.id = ?', [$id]);
if (!$d) { flash('error', 'That department was not found.'); redirect('departments.index'); }
if (is_role('manager') && $id !== my_department()) deny('You manage a different department.');

$page_title = $d['name'];
$page_sub   = '<span class="mono">' . e($d['code']) . '</span> · Line manager: ' . e($d['manager'] ?? 'not assigned');
if (can('departments.manage')) {
    $page_actions = '<a class="btn" href="' . e(url('departments.form', ['id' => $id])) . '">' . icon('edit', 16) . ' Edit</a>';
}

$courses = rows("SELECT c.*, u.name AS facilitator,
                   (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = 'active') AS enrolled
                 FROM courses c LEFT JOIN users u ON u.id = c.facilitator_id
                 WHERE c.department_id = ? ORDER BY c.status, c.name", [$id]);
$staff = rows("SELECT u.* FROM users u WHERE u.department_id = ? AND u.role <> 'student'" . hide_superadmin('u') . ' ORDER BY u.role, u.name', [$id]);
$students = (int) val("SELECT COUNT(DISTINCT e.student_id)
                      FROM enrolments e
                      JOIN courses c ON c.id = e.course_id
                      WHERE c.department_id = ? AND e.status = 'active'", [$id], 0);
?>
<div class="grid grid--4">
  <div class="stat"><div class="stat__label">Active students</div><div class="stat__value"><?= $students ?></div></div>
  <div class="stat stat--green"><div class="stat__label">Courses</div><div class="stat__value"><?= count($courses) ?></div></div>
  <div class="stat stat--orange"><div class="stat__label">Staff</div><div class="stat__value"><?= count($staff) ?></div></div>
  <div class="stat stat--ink"><div class="stat__label">Status</div><div class="stat__value" style="font-size:18px"><?= e(ucfirst($d['status'])) ?></div></div>
</div>

<div class="grid grid--sidebar" style="margin-top:16px">
  <div class="panel">
    <div class="panel__head"><h2>Courses</h2>
      <?php if (can('courses.manage')): ?>
        <a class="btn btn--sm btn--primary" href="<?= e(url('courses.form', ['department_id' => $id])) ?>"><?= icon('plus', 15) ?> Add course</a>
      <?php endif; ?>
    </div>
    <?php if (!$courses): ?>
      <div class="empty" style="padding:28px"><h3>No courses in this department</h3>
        <p>Add a course so students can be enrolled and timetabled.</p></div>
    <?php else: ?>
      <div class="tablewrap">
        <table class="data">
          <thead><tr><th>Course</th><th>Facilitator</th><th class="right">Enrolled</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($courses as $c): ?>
            <tr>
              <td><a href="<?= e(url('courses.view', ['id' => $c['id']])) ?>"><strong><?= e($c['name']) ?></strong></a>
                  <div class="tiny mono muted"><?= e($c['code']) ?> · <?= (int) $c['duration_weeks'] ?> weeks</div></td>
              <td class="tiny"><?= e($c['facilitator'] ?? 'To be assigned') ?></td>
              <td class="right mono"><?= (int) $c['enrolled'] ?> / <?= (int) $c['capacity'] ?></td>
              <td><?= badge(ucfirst($c['status']), status_tone($c['status'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>Staff</h2></div>
    <?php if (!$staff): ?>
      <div class="panel__body"><p class="tiny muted">No staff assigned to this department yet.</p></div>
    <?php else: ?>
      <table class="data compact">
        <tbody>
        <?php foreach ($staff as $u): ?>
          <tr>
            <td>
              <div class="person">
                <span class="avatar"><?= e(initials($u['name'])) ?></span>
                <span><span class="person__name"><?= e($u['name']) ?></span><br>
                  <span class="tiny muted"><?= e(ROLES[$u['role']] ?? $u['role']) ?></span></span>
              </div>
            </td>
            <td class="right"><?= badge(ucfirst($u['status']), status_tone($u['status'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
