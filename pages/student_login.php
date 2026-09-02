<?php
/** Student portal sign-in — registration number only. */
$error = '';
if (is_post()) {
    csrf_check();
    $error = attempt_login(post('reg_no'), '');   // empty password = student quick-login
    if ($error === null) {
        $to = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        flash('ok', 'Welcome back, <strong>' . e(current_user()['name']) . '</strong>.');
        if ($to && str_contains($to, 'index.php')) { header('Location: ' . $to); exit; }
        redirect('dashboard');
    }
}
require_once BASE_PATH . '/views/icons.php';
?>
<div class="authcard">
  <div class="ribbon" style="border-radius:0"></div>
  <div class="authcard__body">
    <div class="authcard__mark">EY</div>
    <div class="eyebrow">Student Portal</div>
    <h1 style="margin:2px 0 3px"><?= e(setting('org_name', ORG_NAME)) ?></h1>
    <p class="muted tiny" style="font-style:italic"><?= e(setting('org_motto', ORG_MOTTO)) ?></p>

    <?php if ($error): ?>
      <div class="alert alert--error" style="margin-top:14px"><?= icon('alert', 17) ?><div><?= e($error) ?></div></div>
    <?php endif; ?>
    <?php foreach (take_flash() as $f): ?>
      <div class="alert alert--<?= e($f['type']) ?>" style="margin-top:14px"><div><?= $f['msg'] ?></div></div>
    <?php endforeach; ?>

    <form method="post" style="margin-top:20px">
      <?= csrf_field() ?>
      <div class="field">
        <label for="reg_no">Registration number</label>
        <input id="reg_no" name="reg_no" type="text"
               value="<?= e(post('reg_no')) ?>"
               placeholder="e.g. EY-01-2026-0001"
               required autofocus autocomplete="username"
               style="letter-spacing:.03em">
        <div class="hint">Enter the registration number on your ID card or slip.</div>
      </div>
      <button class="btn btn--primary" type="submit" style="width:100%;justify-content:center;margin-top:4px">
        <?= icon('login', 16) ?> Sign in
      </button>
    </form>

    <p class="tiny muted center" style="margin:16px 0 0">
      No password needed — just your registration number.<br>
      Forgot it? Ask an administrator.
    </p>

    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);text-align:center">
      <a class="tiny muted" href="<?= e(url('login')) ?>">← Back to staff login</a>
    </div>
  </div>
  <div class="authcard__foot" style="display:flex;gap:10px;align-items:center">
    <span>Certificate holder?</span>
    <a href="<?= e(url('verify')) ?>">Verify a certificate</a>
    <span style="margin-left:auto" class="mono">v<?= APP_VERSION ?></span>
  </div>
</div>
