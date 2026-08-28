<?php
/** Bare document layout. $print_page: a4 | a4l | card */
require_once BASE_PATH . '/views/icons.php';
$size = match ($print_page ?? 'a4') {
    'a4l'  => 'A4 landscape',
    'card' => 'A4 portrait',
    default => 'A4 portrait',
};
$margin = ($print_page ?? 'a4') === 'card' ? '8mm' : '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> · <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="assets/css/print.css?v=<?= APP_VERSION ?>">
<style>@page { size: <?= $size ?>; margin: <?= $margin ?>; }</style>
</head>
<body>
<div class="printbar no-print">
  <a class="pbtn" href="<?= e($print_back ?? url('dashboard')) ?>"><?= icon('back', 16) ?> Back</a>
  <span class="printbar__hint"><?= e($print_hint ?? 'Use your browser print dialog, then choose "Save as PDF" or send it to the printer.') ?></span>
  <button class="pbtn pbtn--primary" onclick="window.print()"><?= icon('printer', 16) ?> Print</button>
</div>
<?= $content ?>
</body>
</html>
