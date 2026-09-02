<?php
/** Create or edit a staff account. */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
$rec = $id ? row('SELECT * FROM users WHERE id = ?', [$id]) : null;
if ($id && !$rec) { flash('error', 'That account was not found.'); redirect('users.index'); }
if ($rec && !can_see_user($rec)) deny('That account is not visible to you.');

$page_title = $rec ? 'Edit ' . $rec['name'] : 'New account';
$roles      = assignable_roles();
$depts      = rows("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name");
$errors     = [];

$studentId  = getInt('student_id') ?: postInt('student_id');
$studentRec = $studentId ? row('SELECT * FROM students WHERE id = ?', [$studentId]) : null;

$f = $rec ?: [
    'name'          => $studentRec ? trim($studentRec['first_name'] . ' ' . ($studentRec['middle_name'] ? $studentRec['middle_name'] . ' ' : '') . $studentRec['last_name']) : '',
    'email'         => $studentRec ? ($studentRec['email'] ?: (strtolower(str_replace(['-', ' '], '', $studentRec['student_no'])) . '@student.elimuyetu.org')) : '',
    'phone'         => $studentRec['phone'] ?? '',
    'role'          => getStr('role') ?: 'facilitator',
    'department_id' => $studentRec['department_id'] ?? '',
    'status'        => 'active'
];

if (is_post()) {
    csrf_check();
    foreach (['name', 'email', 'phone', 'role', 'status'] as $k) $f[$k] = post($k);
    $f['email']         = strtolower(trim($f['email']));
    $f['department_id'] = postInt('department_id') ?: null;
    $pw                 = post('password');
    $pw2                = post('password_confirm');

    if ($f['name'] === '') $errors[] = 'The name is required.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address — it is the username.';
    if (val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [$f['email'], $id])) $errors[] = 'Another account already uses that email.';
    if (!isset($roles[$f['role']])) $errors[] = 'Choose a role you are allowed to assign.';
    if (in_array($f['role'], ['manager', 'facilitator'], true) && !$f['department_id']) {
        $errors[] = ROLES[$f['role']] . ' accounts must belong to a department.';
    }
    if (!$rec && $pw === '') $errors[] = 'Set a first password for the account.';
    if ($pw !== '' && strlen($pw) < 8) $errors[] = 'The password must be at least 8 characters.';
    if ($pw !== '' && $pw !== $pw2) $errors[] = 'The two passwords do not match.';

    // Don't let the last active admin-level account be demoted or disabled.
    if ($rec && in_array($rec['role'], ['superadmin', 'admin'], true)) {
        // When the viewer is not superadmin, only count admins (superadmin is invisible to them).
        $adminRoles = is_role('superadmin') ? "role IN ('superadmin','admin')" : "role = 'admin'";
        $others = (int) val("SELECT COUNT(*) FROM users WHERE id <> ? AND status = 'active' AND $adminRoles", [$id], 0);
        if ($others === 0 && (!in_array($f['role'], ['superadmin', 'admin'], true) || $f['status'] !== 'active')) {
            $errors[] = 'This is the last active administrator. Create another one before changing this account.';
        }
    }

    if (!$errors) {
        $data = [
            'name' => $f['name'], 'email' => $f['email'], 'phone' => $f['phone'], 'role' => $f['role'],
            'department_id' => $f['department_id'], 'status' => $f['status'],
        ];
        if ($studentId) {
            $data['student_id'] = $studentId;
        }
        if ($pw !== '') {
            $data['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
            $data['must_reset']    = postInt('must_reset') ? 1 : 0;
        }
        if ($rec) {
            update('users', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'users', $id, $f['email'] . ' · ' . $f['role']);
            flash('ok', 'Account saved.');
        } else {
            $data['must_reset'] = postInt('must_reset') ? 1 : 0;
            $data['created_at'] = now();
            $id = insert('users', $data);
            audit('create', 'users', $id, $f['email'] . ' · ' . $f['role']);
            flash('ok', 'Account created. Give <strong>' . e($f['email']) . '</strong> the password you just set.');
        }
        if ($studentId) {
            redirect('students.view', ['id' => $studentId]);
        }
        redirect('users.index');
    }
}

$roleNotes = [
    'admin'       => 'Runs the system: departments, courses, registrations, staff, certificates, settings.',
    'manager'     => 'Owns one department — its courses, students, lesson plans and monthly reports.',
    'facilitator' => 'Teaches: takes attendance, enters marks, submits lesson plans and the monthly report.',
    'kitchen'     => 'Reaches the kitchen pages only: daily tea and meal counts, and the kitchen report.',
    'student'     => 'Portal access. Normally created from the student file, not here.',
];
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<div class="grid grid--sidebar">
  <div class="panel">
    <form method="post" class="panel__body">
      <?= csrf_field() ?>
      <?php if ($studentId): ?>
        <input type="hidden" name="student_id" value="<?= (int) $studentId ?>">
      <?php endif; ?>
      <div class="formgrid">
        <div class="field span2">
          <label for="name">Full name <span class="req">*</span></label>
          <input id="name" name="name" value="<?= e($f['name']) ?>" required>
        </div>
        <div class="field">
          <label for="email">Email (this is the username) <span class="req">*</span></label>
          <input id="email" name="email" type="email" value="<?= e($f['email']) ?>" required>
        </div>
        <div class="field">
          <label for="phone">Phone</label>
          <input id="phone" name="phone" value="<?= e($f['phone']) ?>" placeholder="+255…">
        </div>
        <div class="field">
          <label for="role">Role <span class="req">*</span></label>
          <select id="role" name="role" required>
            <?php foreach ($roles as $k => $lbl): ?>
              <option value="<?= $k ?>" <?= $f['role'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="department_id">Department</label>
          <select id="department_id" name="department_id">
            <option value="">Not tied to a department</option>
            <?php foreach ($depts as $d): ?>
              <option value="<?= (int) $d['id'] ?>" <?= (int) $f['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="hint">Required for line managers and facilitators.</div>
        </div>
        <div class="field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="active" <?= $f['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="disabled" <?= $f['status'] === 'disabled' ? 'selected' : '' ?>>Disabled — cannot sign in</option>
          </select>
        </div>
      </div>

      <div class="section-head"><span></span><h3><?= $rec ? 'Reset the password' : 'First password' ?></h3></div>
      <div class="formgrid">
        <div class="field">
          <label for="password"><?= $rec ? 'New password' : 'Password' ?> <?= $rec ? '' : '<span class="req">*</span>' ?></label>
          <input id="password" name="password" type="password" autocomplete="new-password" minlength="8"
                 <?= $rec ? 'placeholder="Leave empty to keep the current password"' : 'required' ?>>
        </div>
        <div class="field">
          <label for="password_confirm">Repeat it</label>
          <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password">
        </div>
      </div>
      <label class="check">
        <input type="checkbox" name="must_reset" value="1" <?= $rec ? '' : 'checked' ?>>
        <span>Ask them to choose their own password at first sign-in</span>
      </label>

      <div class="btnrow">
        <button class="btn btn--primary"><?= icon('check', 16) ?> Save account</button>
        <a class="btn btn--ghost" href="<?= e(url('users.index')) ?>">Cancel</a>
      </div>
    </form>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>What each role can do</h2></div>
      <div class="panel__body" style="display:grid;gap:12px">
        <?php foreach ($roles as $k => $lbl): ?>
          <div>
            <div style="font-weight:650;font-size:13px"><?= e($lbl) ?></div>
            <div class="tiny muted" style="line-height:1.6"><?= e($roleNotes[$k] ?? '') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($rec): ?>
      <div class="panel">
        <div class="panel__head"><h2>Account</h2></div>
        <div class="panel__body">
          <dl class="dl">
            <dt>Created</dt><dd><?= e(d($rec['created_at'], 'd M Y')) ?></dd>
            <dt>Last sign-in</dt><dd><?= $rec['last_login'] ? e(d($rec['last_login'], 'd M Y H:i')) : 'Never' ?></dd>
            <?php if ($rec['student_id']): ?>
              <dt>Linked student</dt>
              <dd><a href="<?= e(url('students.view', ['id' => $rec['student_id']])) ?>">Open the student file</a></dd>
            <?php endif; ?>
          </dl>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
