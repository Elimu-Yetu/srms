<?php require_once BASE_PATH . '/views/icons.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>No access · <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= APP_VERSION ?>">
</head>
<body>
<div class="authwrap">
  <div class="authcard">
    <div class="ribbon" style="border-radius:0"></div>
    <div class="authcard__body">
      <h2>This page is closed to your role</h2>
      <p class="muted">You are signed in as <strong><?= e(ROLES[role()] ?? role()) ?></strong>.
         Ask an administrator if you need access to <span class="mono"><?= e(getStr('r')) ?></span>.</p>
      <div class="btnrow"><a class="btn btn--primary" href="<?= e(url('dashboard')) ?>">Back to dashboard</a></div>
    </div>
  </div>
</div>
</body>
</html>
