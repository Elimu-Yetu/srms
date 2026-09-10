<?php
/** Student register — searchable, filtered, role-scoped. */
require_once BASE_PATH . '/views/icons.php';

// Allow deletion from the index for admins or users with students.manage
if (is_post() && (can('students.manage') || is_role('admin'))) {
  csrf_check();
  if (post('do') === 'delete') {
    $sid = postInt('id');
    $srec = row('SELECT * FROM students WHERE id = ?', [$sid]);
    if (!$srec) {
      flash('error', 'That student file was not found.');
      redirect('students.index');
    }
    $enrols = (int) val('SELECT COUNT(*) FROM enrolments WHERE student_id = ?', [$sid], 0);
    $certs  = (int) val('SELECT COUNT(*) FROM certificates WHERE student_id = ?', [$sid], 0);
    if ($enrols > 0) {
      flash('error', 'Cannot delete a student with active enrolments. Unenrol the student first.');
      redirect('students.index');
    }
    if ($certs > 0) {
      flash('error', 'That student has certificates issued. Remove certificates before deleting the student.');
      redirect('students.index');
    }
    if (!empty($srec['photo'])) {
      delete_student_photo($srec['photo']);
    }
    q('DELETE FROM users WHERE student_id = ?', [$sid]);
    q('DELETE FROM id_cards WHERE student_id = ?', [$sid]);
    q('DELETE FROM students WHERE id = ?', [$sid]);
    audit('delete', 'students', $sid, $srec['student_no'] . ' ' . trim($srec['first_name'] . ' ' . $srec['last_name']));
    flash('ok', 'Deleted student file for ' . e($srec['first_name'] . ' ' . $srec['last_name']) . '.');
    redirect('students.index');
  }
}

$search = getStr('q');
$status = getStr('status');
$dept   = getInt('dept');
$course = getInt('course');
[$limit, $offset, $page] = paging(20);

$where = ' WHERE 1=1 ';
$args  = [];

// Facilitators only see students in the courses assigned to them.
if (is_role('facilitator')) {
    $ids = my_course_ids();
    $where .= ' AND s.id IN (SELECT student_id FROM enrolments WHERE course_id IN (' . in_list($ids) . ')) ';
} else {
    [$scope, $scopeArgs] = dept_scope('s.department_id');
    $where .= $scope;
    $args = array_merge($args, $scopeArgs);
}

if ($search !== '') {
    $where .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.middle_name LIKE ? OR s.student_no LIKE ? OR s.phone LIKE ? OR s.national_id LIKE ? OR s.email LIKE ?) ';
    $like  = '%' . $search . '%';
    array_push($args, $like, $like, $like, $like, $like, $like, $like);
}
if ($status !== '') { $where .= ' AND s.status = ? '; $args[] = $status; }
if ($dept)         { $where .= ' AND s.department_id = ? '; $args[] = $dept; }
if ($course)       { $where .= ' AND s.id IN (SELECT student_id FROM enrolments WHERE course_id = ?) '; $args[] = $course; }

$total = (int) val("SELECT COUNT(*) FROM students s $where", $args, 0);
$list  = rows("SELECT s.*, d.name AS dept,
                 (SELECT COUNT(*) FROM enrolments e WHERE e.student_id = s.id AND e.status = 'active') AS courses
               FROM students s LEFT JOIN departments d ON d.id = s.department_id
               $where ORDER BY s.registered_at DESC, s.id DESC LIMIT $limit OFFSET $offset", $args);

$depts   = rows('SELECT id, name FROM departments ORDER BY name');
$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list(my_course_ids()) . ') ORDER BY name');

$page_sub = number_format($total) . ' student' . ($total === 1 ? '' : 's') . ' in view';
$page_actions = '';
if (can('students.manage')) {
    $page_actions .= '<a class="btn btn--primary" href="' . e(url('students.form')) . '">' . icon('plus', 16) . ' Register a student</a>';
}
$page_actions .= '<a class="btn" href="' . e(url('students.export', ['q' => $search, 'status' => $status, 'dept' => $dept, 'course' => $course])) . '">' . icon('download', 16) . ' CSV</a>';
$qs = ['q' => $search, 'status' => $status, 'dept' => $dept, 'course' => $course];
?>

<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="students.index">
    <div class="field grow">
      <label for="q">Search by name, number, phone or national ID</label>
      <input id="q" type="search" name="q" value="<?= e($search) ?>" placeholder="e.g. Amina, EY-<?= date('Y') ?>-0003, 0763…">
    </div>
    <div class="field">
      <label for="status">Status</label>
      <select id="status" name="status" data-autosubmit>
        <option value="">Any</option>
        <?php foreach (['pending', 'active', 'completed', 'deferred', 'withdrawn', 'suspended'] as $st): ?>
          <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (is_admin()): ?>
      <div class="field">
        <label for="dept">Department</label>
        <select id="dept" name="dept" data-autosubmit>
          <option value="">All</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= (int) $d['id'] ?>" <?= $dept === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>
    <div class="field">
      <label for="course">Course</label>
      <select id="course" name="course" data-autosubmit>
        <option value="">All</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $course === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn" type="submit"><?= icon('search', 16) ?> Search</button>
    <?php if ($search || $status || $dept || $course): ?>
      <a class="btn btn--ghost" href="<?= e(url('students.index')) ?>">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('users', 22) ?></div>
      <h3><?= $search || $status || $dept || $course ? 'Nothing matches those filters' : 'The register is empty' ?></h3>
      <p><?= $search || $status || $dept || $course
            ? 'Clear the filters, or check the spelling of the name or student number.'
            : 'Register the first student to open a file, then enrol them into a course.' ?></p>
      <?php if (can('students.manage')): ?>
        <a class="btn btn--primary" href="<?= e(url('students.form')) ?>"><?= icon('plus', 16) ?> Register a student</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data">
        <thead>
          <tr>
            <th>Student</th><th>Department</th><th>Courses</th><th>Phone</th>
            <th>Status</th><th>Registered</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $s): $full = trim($s['first_name'] . ' ' . $s['last_name']); ?>
          <tr>
            <td>
              <a class="person" href="<?= e(url('students.view', ['id' => $s['id']])) ?>">
                <?php if ($s['photo']): ?>
                  <img class="avatar" src="<?= e(photo_url($s['photo'])) ?>" alt="">
                <?php else: ?>
                  <span class="avatar"><?= e(initials($full)) ?></span>
                <?php endif; ?>
                <span>
                  <span class="person__name"><?= e($full) ?></span><br>
                  <span class="person__meta"><?= e($s['student_no']) ?></span>
                </span>
              </a>
            </td>
            <td class="tiny"><?= e($s['dept'] ?? '—') ?></td>
            <td class="mono"><?= (int) $s['courses'] ?></td>
            <td class="tiny mono"><?= e($s['phone'] ?: '—') ?></td>
            <td><?= badge(ucfirst($s['status']), status_tone($s['status'])) ?></td>
            <td class="tiny mono"><?= e(d($s['registered_at'])) ?></td>
            <td class="right nowrap">
              <a class="btn btn--sm btn--ghost" href="<?= e(url('students.view', ['id' => $s['id']])) ?>" title="Open file"><?= icon('eye', 15) ?></a>
              <?php if (can('students.manage')): ?>
                <a class="btn btn--sm btn--ghost" href="<?= e(url('students.form', ['id' => $s['id']])) ?>" title="Edit"><?= icon('edit', 15) ?></a>
              <?php endif; ?>
              <?php if (can('students.manage') || is_role('admin')): ?>
                <form method="post" style="display:inline-block;margin-left:6px">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                  <button class="btn btn--sm btn--ghost" data-confirm="Delete this student file?"><?= icon('x', 14) ?></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= pager($total, $limit, $page, 'students.index', $qs) ?>
  <?php endif; ?>
</div>
