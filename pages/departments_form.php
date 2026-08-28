<?php
/** Create or edit a department. */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
$rec = $id ? row('SELECT * FROM departments WHERE id = ?', [$id]) : null;
if ($id && !$rec) { flash('error', 'That department was not found.'); redirect('departments.index'); }
$page_title = $rec ? 'Edit ' . $rec['name'] : 'New department';

$managers = rows("SELECT id, name FROM users u WHERE u.role IN ('manager','admin') AND u.status = 'active'"
                 . hide_superadmin('u') . ' ORDER BY name');
$errors = [];
$f = $rec ?: ['code' => '', 'name' => '', 'description' => '', 'manager_id' => '', 'status' => 'active'];

if (is_post()) {
    csrf_check();
    foreach (['code', 'name', 'description', 'status'] as $k) $f[$k] = post($k);
    $f['manager_id'] = postInt('manager_id') ?: null;
    $f['code'] = strtoupper($f['code']);

    if ($f['code'] === '') $errors[] = 'A short code is required, e.g. ICT.';
    if ($f['name'] === '') $errors[] = 'The department name is required.';
    $clash = row('SELECT id FROM departments WHERE code = ? AND id <> ?', [$f['code'], $id]);
    if ($clash) $errors[] = 'Another department already uses the code ' . e($f['code']) . '.';

    if (!$errors) {
        $data = ['code' => $f['code'], 'name' => $f['name'], 'description' => $f['description'],
                 'manager_id' => $f['manager_id'], 'status' => $f['status']];
        if ($rec) {
            update('departments', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'departments', $id, $f['code']);
            flash('ok', 'Department saved.');
        } else {
            $data['created_at'] = now();
            $id = insert('departments', $data);
            audit('create', 'departments', $id, $f['code']);
            flash('ok', 'Department created.');
        }
        // Keep the manager's own department in step so scoping works immediately.
        if ($f['manager_id']) q('UPDATE users SET department_id = ? WHERE id = ? AND role = ?', [$id, $f['manager_id'], 'manager']);
        redirect('departments.view', ['id' => $id]);
    }
}
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<div class="panel" style="max-width:640px">
  <form method="post" class="panel__body">
    <?= csrf_field() ?>
    <div class="formgrid">
      <div class="field">
        <label for="code">Short code <span class="req">*</span></label>
        <input id="code" name="code" value="<?= e($f['code']) ?>" maxlength="20" required placeholder="ICT" style="text-transform:uppercase">
        <div class="hint">Used in course codes and reports.</div>
      </div>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="active" <?= $f['status'] === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="closed" <?= $f['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
      </div>
      <div class="field span2">
        <label for="name">Department name <span class="req">*</span></label>
        <input id="name" name="name" value="<?= e($f['name']) ?>" required placeholder="ICT / TEHAMA">
      </div>
      <div class="field span2">
        <label for="manager_id">Line manager</label>
        <select id="manager_id" name="manager_id">
          <option value="">Not assigned yet</option>
          <?php foreach ($managers as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= (int) $f['manager_id'] === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="hint">The manager sees only this department's courses, students and reports.</div>
      </div>
      <div class="field span2">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?= e($f['description']) ?></textarea>
      </div>
    </div>
    <div class="btnrow">
      <button class="btn btn--primary"><?= icon('check', 16) ?> Save department</button>
      <a class="btn btn--ghost" href="<?= e(url('departments.index')) ?>">Cancel</a>
    </div>
  </form>
</div>
