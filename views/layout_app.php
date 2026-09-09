<?php
/** @var string $content @var string $current @var string $page_title */
require_once BASE_PATH . '/views/icons.php';
$u        = current_user();
$sections = nav_sections();
$flashes  = take_flash();
$isKitchen = is_role('kitchen');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#F4F6F2">
<title><?= e($page_title) ?> · <?= e(setting('org_name', ORG_NAME)) ?></title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= APP_VERSION ?>">
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
</head>
<body>
<div class="shell">

  <aside class="rail" id="rail">
    <div class="ribbon" style="border-radius:0"></div>
    <div class="rail__brand">
      <div class="rail__mark">EY</div>
      <div>
        <div class="rail__name">Elimu Yetu Development Organization</div>
        <div class="rail__motto">Ninaweza, nitafanya </div>
      </div>
    </div>

    <nav class="rail__nav" aria-label="Main navigation">
      <?php foreach ($sections as $sec):
        $accent = $sec['accent'] ?? (($sec['label'] === 'Teaching' || $sec['label'] === 'My learning') ? 'green' : '');
      ?>
        <div class="rail__section <?= $accent ? 'rail__section--' . e($accent) : '' ?>">
          <?php if (!empty($sec['label'])): ?>
            <div class="rail__label"><?= e($sec['label']) ?></div>
          <?php endif; ?>
          <?php foreach ($sec['items'] as $item):
            $active = nav_active($item['route'], $current); ?>
            <a class="rail__link<?= $active ? ' is-active' : '' ?>"
               href="<?= e(url($item['route'], $item['params'])) ?>"
               <?= $active ? 'aria-current="page"' : '' ?>>
              <?= icon($item['icon']) ?><span><?= e($item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>

    <div class="rail__foot">
     
      <p style="font-family:verdana;font-size:11px;color:var(--ink-soft);margin-top:4px">
        &copy; <?= e(setting('org_name', ORG_NAME)) ?> <br> <!--<p style="font-size:0.5rem;">Made with ❤️ by jimmy</p></p>-->
    </div>
  </aside>

  <button class="scrim" id="scrim" aria-label="Close navigation"></button>

  <div class="main">
    <header class="topbar">
      <button class="burger" id="burger" aria-label="Open navigation" aria-expanded="false" aria-controls="rail">
        <?= icon('menu', 20) ?>
      </button>
      <div class="topbar__title">
        <h1><?= e($page_title) ?></h1>
        <?php if ($page_sub): ?><div class="tiny"><?= $page_sub ?></div><?php endif; ?>
      </div>
      <?php if ($page_actions): ?><div class="btnrow no-print"><?= $page_actions ?></div><?php endif; ?>
      <a class="userchip no-print" href="<?= e(url('profile')) ?>" title="My account">
        <span class="avatar <?= $isKitchen ? 'avatar--orange' : '' ?>"><?= e(initials($u['name'])) ?></span>
        <span>
          <span class="userchip__name"><?= e($u['name']) ?></span><br>
          <span class="userchip__role"><?= e(ROLES[$u['role']] ?? $u['role']) ?></span>
        </span>
      </a>
      <a class="btn btn--sm btn--ghost no-print" href="<?= e(url('logout')) ?>" title="Sign out"><?= icon('logout', 16) ?></a>
    </header>

    <main class="content">
      <?php foreach ($flashes as $f):
        $map = ['ok' => 'ok', 'error' => 'error', 'warn' => 'warn', 'info' => 'info']; ?>
        <div class="alert alert--<?= e($map[$f['type']] ?? 'info') ?>">
          <?= icon($f['type'] === 'ok' ? 'check' : 'alert', 17) ?>
          <div><?= $f['msg'] ?></div>
        </div>
      <?php endforeach; ?>

      <?= $content ?>
    </main>
  </div>
</div>

<script src="assets/js/app.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>
