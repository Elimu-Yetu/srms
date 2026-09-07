<?php
/** Sign in */
$error = '';
if (is_post()) {
    csrf_check();
    $error = attempt_login(post('email'), $_POST['password'] ?? '');
    if ($error === null) {
        $to = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        flash('ok', 'Welcome back, <strong>' . e(current_user()['name']) . '</strong>.');
        if ($to && str_contains($to, 'index.php')) { header('Location: ' . $to); exit; }
        redirect('dashboard');
    }
}
require_once BASE_PATH . '/views/icons.php';
$hasUsers = (int) val('SELECT COUNT(*) FROM users', [], 0);
?>
<div class="authcard">
  <div class="ribbon" style="border-radius:0"></div>
  <div class="authcard__body">
    <!-- <div class="authcard__mark">EYDO</div> -->
    <div class="eyebrow">Student Registration &amp; Management</div>
    <h1 style="margin:2px 0 3px"><?= e(setting('org_name', ORG_NAME)) ?></h1>
    <p class="muted tiny" style="font-style:italic"><?= e(setting('org_motto', ORG_MOTTO)) ?></p>

    <?php if ($error): ?>
      <div class="alert alert--error" style="margin-top:14px"><?= icon('alert', 17) ?><div><?= e($error) ?></div></div>
    <?php endif; ?>
    <?php foreach (take_flash() as $f): ?>
      <div class="alert alert--<?= e($f['type']) ?>" style="margin-top:14px"><div><?= $f['msg'] ?></div></div>
    <?php endforeach; ?>

    <form method="post" style="margin-top:16px">
      <?= csrf_field() ?>
        <div class="field">
          <label for="email">Email or registration number</label>
          <input id="email" name="email" type="text" value="<?= e(post('email')) ?>" required autofocus autocomplete="username">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" autocomplete="current-password">
        </div>
        <p class="tiny" style="margin:4px 0 12px;text-align:center">
          A student? <a href="<?= e(url('student.login')) ?>">Log in with your registration number &rarr;</a>
        </p>
      <button class="btn btn--primary" type="submit" style="width:100%;justify-content:center">Sign in</button>
    </form>
    <p class="tiny muted center" style="margin:14px 0 0">
      Forgot your password? Ask an administrator.
    </p>
  </div>
  <div class="authcard__foot" style="display:flex;gap:10px;align-items:center">
    <span>Certificate holder?</span>
    <a href="<?= e(url('verify')) ?>">Verify a certificate</a>
    <span style="margin-left:auto" class="mono">v<?= APP_VERSION ?></span>
  </div>
</div>
