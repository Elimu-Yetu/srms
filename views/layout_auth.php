<?php /** Sign-in / sign-out screen */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> · <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= APP_VERSION ?>">
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
<link rel="icon" href="assets/img/image.png" type="image/png">
</head>
<body>
<div class="authwrap"><?= $content ?></div>
</body>
</html>
