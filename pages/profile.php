<?php
/** Your own account: details, password, and what the system knows about you. */
require_once BASE_PATH . '/views/icons.php';

$me     = current_user();
$errors = [];
$ok     = [];

if (is_post()) {
    csrf_check();
    $what = post('do');

    if ($what === 'details') {
        $name  = post('name');
        $phone = post('phone');
        if ($name === '') {
            $errors[] = 'Your name cannot be empty.';
        } else {
            update('users', ['name' => $name, 'phone' => $phone], 'id = :wid', ['wid' => user_id()]);
            audit('update', 'users', user_id(), 'own details');
            flash('ok', 'Your details are saved.');
            redirect('profile');
        }
    }

    if ($what === 'password') {
        $cur  = post('current_password');
        $new  = post('new_password');
        $new2 = post('new_password_confirm');
        $rec  = row('SELECT password_hash FROM users WHERE id = ?', [user_id()]);

        if (!$rec || !password_verify($cur, $rec['password_hash'])) $errors[] = 'Your current password is not right.';
        if (strlen($new) < 8)  $errors[] = 'The new password must be at least 8 characters.';
        if ($new !== $new2)    $errors[] = 'The two new passwords do not match.';
        if ($new !== '' && $new === $cur) $errors[] = 'The new password must be different from the old one.';

        if (!$errors) {
            update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT), 'must_reset' => 0],
                   'id = :wid', ['wid' => user_id()]);
            audit('password', 'users', user_id(), 'changed own password');
            flash('ok', 'Password changed. Use the new one next time you sign in.');
            redirect('profile');
        }
    }
}

$me = current_user();   // reload after any save
$student = $me['student_id'] ? row('SELECT * FROM students WHERE id = ?', [$me['student_id']]) : null;
$dept    = $me['department_id'] ? row('SELECT name FROM departments WHERE id = ?', [$me['department_id']]) : null;
$courses = count(my_course_ids());
$recent  = rows('SELECT action, entity, details, created_at FROM audit_logs WHERE user_id = ? ORDER BY id DESC LIMIT 8', [user_id()]);

$page_title = 'My account';
$page_sub   = e(ROLES[$me['role']] ?? $me['role']) . ($dept ? ' · ' . e($dept['name']) : '');
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= e($er) ?></div></div>
<?php endforeach; ?>

<?php if (!empty($me['must_reset'])): ?>
  <div class="alert alert--warn">
    <?= icon('alert', 17) ?>
    <div><strong>Please set your own password.</strong> The one you have was chosen by someone else, so only you should know the next one.</div>
  </div>
<?php endif; ?>

<div class="grid grid--sidebar">
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Your details</h2></div>
      <form method="post" class="panel__body">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="details">
        <div class="formgrid">
          <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" value="<?= e($me['name']) ?>" required>
          </div>
          <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="<?= e($me['phone'] ?? '') ?>" placeholder="+255…">
          </div>
          <div class="field span2">
            <label>Email — this is your username</label>
            <input value="<?= e($me['email']) ?>" disabled>
            <div class="hint">Only an administrator can change the email on an account.</div>
          </div>
        </div>
        <button class="btn btn--primary"><?= icon('check', 16) ?> Save details</button>
      </form>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Change your password</h2></div>
      <form method="post" class="panel__body">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="password">
        <div class="formgrid formgrid--3">
          <div class="field">
            <label for="current_password">Current password</label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
          </div>
          <div class="field">
            <label for="new_password">New password</label>
            <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8" required>
          </div>
          <div class="field">
            <label for="new_password_confirm">Repeat it</label>
            <input id="new_password_confirm" name="new_password_confirm" type="password" autocomplete="new-password" required>
          </div>
        </div>
        <p class="tiny muted" style="margin:0 0 12px">
          At least 8 characters. A short phrase you will remember beats a clever word you will forget.
        </p>
        <button class="btn btn--green"><?= icon('shield', 16) ?> Change password</button>
      </form>
    </div>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Account</h2></div>
      <div class="panel__body">
        <dl class="dl">
          <dt>Role</dt><dd><?= badge(ROLES[$me['role']] ?? $me['role'], $me['role'] === 'superadmin' ? 'red' : 'neutral') ?></dd>
          <?php if ($dept): ?><dt>Department</dt><dd><?= e($dept['name']) ?></dd><?php endif; ?>
          <?php if (!is_role('kitchen')): ?>
            <dt>Courses in scope</dt><dd class="mono"><?= $courses ?></dd>
          <?php endif; ?>
          <?php if ($student): ?>
            <dt>Student number</dt><dd class="mono"><?= e($student['student_no']) ?></dd>
          <?php endif; ?>
          <dt>Signed in since</dt><dd><?= e(d(date('Y-m-d H:i:s', $_SESSION['started'] ?? time()), 'd M Y H:i')) ?></dd>
          <dt>Last sign-in</dt><dd><?= $me['last_login'] ? e(d($me['last_login'], 'd M Y H:i')) : 'This is your first' ?></dd>
        </dl>
        <p class="tiny muted" style="margin:12px 0 0">
          You are signed out automatically after <?= (int) SESSION_IDLE_MINUTES ?> minutes of no activity —
          useful on a shared office computer.
        </p>
      </div>
    </div>

    <?php if ($recent): ?>
      <div class="panel">
        <div class="panel__head"><h2>Your recent activity</h2></div>
        <table class="data compact">
          <tbody>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td class="tiny mono nowrap" style="color:var(--ink-faint)"><?= e(d($r['created_at'], 'd M H:i')) ?></td>
              <td class="tiny"><?= e(ucfirst(str_replace('_', ' ', $r['action']))) ?>
                <?php if ($r['details']): ?><div class="tiny muted"><?= e($r['details']) ?></div><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
