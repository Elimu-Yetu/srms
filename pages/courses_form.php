<?php
/** Create or edit a course (an intake of a programme). */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
$rec = $id ? row('SELECT * FROM courses WHERE id = ?', [$id]) : null;
if ($id && !$rec) { flash('error', 'That course was not found.'); redirect('courses.index'); }
if ($rec && is_role('manager') && (int) $rec['department_id'] !== my_department()) deny('That course belongs to another department.');
$page_title = $rec ? 'Edit ' . $rec['code'] : 'New course';

[$scope, $sArgs] = dept_scope('d.id');
$depts = rows("SELECT d.id, d.name FROM departments d WHERE d.status = 'active' $scope ORDER BY d.name", $sArgs);
$facs  = rows("SELECT u.id, u.name, u.department_id FROM users u WHERE u.role = 'facilitator' AND u.status = 'active' ORDER BY u.name");

$errors = [];
$f = $rec ?: [
    'department_id' => getInt('department_id') ?: (is_admin() ? '' : my_department()),
    'code' => '', 'name' => '', 'description' => '', 'duration_weeks' => 12, 'fee_amount' => 0,
    'capacity' => 25, 'facilitator_id' => '', 'start_date' => '', 'end_date' => '', 'status' => 'active',
];

if (is_post()) {
    csrf_check();
    foreach (['code', 'name', 'description', 'status'] as $k) $f[$k] = post($k);
    $f['code']           = strtoupper($f['code']);
    $f['department_id']  = postInt('department_id');
    $f['duration_weeks'] = max(1, postInt('duration_weeks', 12));
    $f['capacity']       = max(1, postInt('capacity', 25));
    $f['facilitator_id'] = postInt('facilitator_id') ?: null;
    $f['start_date']     = postNull('start_date');
    $f['end_date']       = postNull('end_date');

    if ($f['code'] === '') $errors[] = 'A course code is required, e.g. WEB-101.';
    if ($f['name'] === '') $errors[] = 'The course name is required.';
    if (!$f['department_id']) $errors[] = 'Choose a department.';
    if (val('SELECT 1 FROM courses WHERE code = ? AND id <> ?', [$f['code'], $id])) $errors[] = 'Another course already uses the code ' . e($f['code']) . '.';
    if ($f['start_date'] && $f['end_date'] && strtotime($f['end_date']) < strtotime($f['start_date'])) {
        $errors[] = 'The end date is before the start date.';
    }
    if ($rec) {
        $enrolled = (int) val('SELECT COUNT(*) FROM enrolments WHERE course_id = ? AND status = ?', [$id, 'active'], 0);
        if ($f['capacity'] < $enrolled) $errors[] = "Capacity cannot be below the $enrolled students already enrolled.";
    }

    if (!$errors) {
        $data = [
            'department_id' => $f['department_id'], 'code' => $f['code'], 'name' => $f['name'],
            'description' => $f['description'], 'duration_weeks' => $f['duration_weeks'],
            'fee_amount' => 0, 'capacity' => $f['capacity'],
            'facilitator_id' => $f['facilitator_id'], 'start_date' => $f['start_date'],
            'end_date' => $f['end_date'], 'status' => $f['status'],
        ];
        if ($rec) {
            update('courses', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'courses', $id, $f['code']);
            flash('ok', 'Course saved.');
        } else {
            $data['created_at'] = now();
            $id = insert('courses', $data);
            audit('create', 'courses', $id, $f['code']);
            flash('ok', 'Course created. Add timetable slots next so students know when to come.');
        }
        redirect('courses.view', ['id' => $id]);
    }
}
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<div class="panel" style="max-width:820px">
  <form method="post" class="panel__body">
    <?= csrf_field() ?>
    <div class="formgrid">
      <div class="field">
        <label for="code">Course code <span class="req">*</span></label>
        <input id="code" name="code" value="<?= e($f['code']) ?>" required placeholder="WEB-101" style="text-transform:uppercase">
      </div>
      <div class="field">
        <label for="department_id">Department <span class="req">*</span></label>
        <select id="department_id" name="department_id" required>
          <option value="">Choose…</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= (int) $d['id'] ?>" <?= (int) $f['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field span2">
        <label for="name">Course name <span class="req">*</span></label>
        <input id="name" name="name" value="<?= e($f['name']) ?>" required placeholder="Web Development Level 1">
      </div>
      <div class="field span2">
        <label for="description">What students will learn</label>
        <textarea id="description" name="description" rows="3"><?= e($f['description']) ?></textarea>
      </div>
      <div class="field">
        <label for="duration_weeks">Duration (weeks)</label>
        <input id="duration_weeks" name="duration_weeks" type="number" min="1" max="200" value="<?= (int) $f['duration_weeks'] ?>">
      </div>
      <div class="field">
        <label for="capacity">Capacity</label>
        <input id="capacity" name="capacity" type="number" min="1" max="500" value="<?= (int) $f['capacity'] ?>">
      </div>

      <div class="field">
        <label for="facilitator_id">Facilitator</label>
        <select id="facilitator_id" name="facilitator_id">
          <option value="">To be assigned</option>
          <?php foreach ($facs as $u): ?>
            <option value="<?= (int) $u['id'] ?>" <?= (int) $f['facilitator_id'] === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="start_date">Starts</label>
        <input id="start_date" name="start_date" type="date" value="<?= e($f['start_date']) ?>">
      </div>
      <div class="field">
        <label for="end_date">Ends</label>
        <input id="end_date" name="end_date" type="date" value="<?= e($f['end_date']) ?>">
      </div>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <?php foreach (['active' => 'Active', 'planned' => 'Planned', 'closed' => 'Closed'] as $k => $lbl): ?>
            <option value="<?= $k ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="btnrow">
      <button class="btn btn--primary"><?= icon('check', 16) ?> Save course</button>
      <a class="btn btn--ghost" href="<?= e($rec ? url('courses.view', ['id' => $id]) : url('courses.index')) ?>">Cancel</a>
    </div>
  </form>
</div>
