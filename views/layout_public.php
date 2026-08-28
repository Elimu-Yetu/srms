<?php /** Public pages (certificate verification) — no session required */
require_once BASE_PATH . '/views/icons.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> · <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= APP_VERSION ?>">
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
</head>
<body>
<div class="authwrap" style="align-items:start;padding-top:8vh">
  <div style="width:100%;max-width:560px">
    <?= $content ?>
    <p class="tiny center muted" style="margin-top:16px">
      <?= e(setting('org_name', ORG_NAME)) ?> · <?= e(setting('org_phone', '')) ?>
    </p>
  </div>
</div>
</body>
</html>
