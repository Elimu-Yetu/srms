<?php
/** System settings. Every value here is used somewhere visible. */
require_once BASE_PATH . '/views/icons.php';

$groups = [
    'Organisation' => [
        'org_name'    => ['Name', 'text', 'Appears on every printed document.'],
        'org_motto'   => ['Motto', 'text', 'Printed under the name on documents.'],
        'org_phone'   => ['Phone', 'text', ''],
        'org_email'   => ['Email', 'text', ''],
        'org_address' => ['Address', 'text', ''],
    ],
    'Academic' => [
        'academic_year'        => ['Academic year', 'text', 'Used on registration slips and certificates.'],
        'attendance_threshold' => ['Attendance threshold (%)', 'number', 'Students below this are flagged on dashboards and reports.'],
    ],
    'Certificates and ID cards' => [
        'cert_signatory_1'        => ['First signatory', 'text', 'Printed on the left of certificates.'],
        'cert_signatory_1_title'  => ['Their title', 'text', ''],
        'cert_signatory_2'        => ['Second signatory', 'text', 'Printed on the right.'],
        'cert_signatory_2_title'  => ['Their title', 'text', ''],
        'id_card_validity_months' => ['ID card validity (months)', 'number', 'How long a new card is valid from the day it is issued.'],
    ],
    'Kitchen' => [
        'kitchen_tea_label'  => ['Morning service label', 'text', 'Shown on kitchen pages and reports.'],
        'kitchen_meal_label' => ['Afternoon service label', 'text', ''],
    ],
];

$errors = [];
if (is_post()) {
    csrf_check();
    $changed = 0;
    foreach ($groups as $fields) {
        foreach ($fields as $key => [$label, $kind]) {
            if (!array_key_exists($key, $_POST)) continue;
            $new = trim((string) $_POST[$key]);
            if ($kind === 'number') $new = (string) max(0, (int) $new);
            if ($key === 'attendance_threshold' && ((int) $new < 1 || (int) $new > 100)) {
                $errors[] = 'The attendance threshold must be between 1 and 100.';
                continue;
            }
            if ($key === 'id_card_validity_months' && ((int) $new < 1 || (int) $new > 120)) {
                $errors[] = 'ID card validity must be between 1 and 120 months.';
                continue;
            }
            if ($new !== setting($key)) { set_setting($key, $new); $changed++; }
        }
    }
    if (!$errors) {
        audit('settings', 'settings', '', $changed . ' value' . ($changed === 1 ? '' : 's') . ' changed');
        flash('ok', $changed ? 'Saved ' . $changed . ' change' . ($changed === 1 ? '' : 's') . '.' : 'Nothing was changed.');
        redirect('settings');
    }
}

$st = settings();
$page_sub = 'These values feed the printed documents and the attendance rules, so keep them accurate.';
?>
<?php foreach (array_unique($errors) as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= e($er) ?></div></div>
<?php endforeach; ?>

<form method="post">
  <?= csrf_field() ?>
  <div class="grid grid--2">
    <?php foreach ($groups as $group => $fields): ?>
      <div class="panel">
        <div class="panel__head"><h2><?= e($group) ?></h2></div>
        <div class="panel__body">
          <?php foreach ($fields as $key => [$label, $kind, $hint]): ?>
            <div class="field">
              <label for="s_<?= e($key) ?>"><?= e($label) ?></label>
              <input id="s_<?= e($key) ?>" name="<?= e($key) ?>"
                     type="<?= $kind === 'number' ? 'number' : 'text' ?>"
                     <?= $kind === 'number' ? 'min="1"' : '' ?>
                     value="<?= e($st[$key] ?? '') ?>">
              <?php if ($hint): ?><div class="hint"><?= e($hint) ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="panel" style="margin-top:16px">
    <div class="panel__foot" style="border-top:0">
      <button class="btn btn--primary"><?= icon('check', 16) ?> Save settings</button>
      <span class="tiny muted" style="margin-left:auto">
        System version <?= e(APP_VERSION) ?> · database <?= e(DB_DRIVER) ?> · PHP <?= e(PHP_VERSION) ?>
      </span>
    </div>
  </div>
</form>

<div class="grid grid--2" style="margin-top:16px">
  <div class="panel">
    <div class="panel__head"><h2>Public certificate check</h2></div>
    <div class="panel__body tiny" style="color:var(--ink-soft);line-height:1.7">
      Anyone can confirm a certificate without signing in, at
      <span class="mono"><?= e(url('verify')) ?></span>. They type the verification code printed on the
      certificate and see the holder's name, the course and the issue date — nothing else.
      Print that address on the certificate if you want employers to use it.
    </div>
  </div>
  <div class="panel">
    <div class="panel__head"><h2>Keeping the data safe</h2></div>
    <div class="panel__body tiny" style="color:var(--ink-soft);line-height:1.7">
      <?php if (can('backup.run')): ?>
        Take a backup every Friday from <a href="<?= e(url('backup')) ?>">the backup page</a> and copy the file
        to a flash disk kept outside the office. A backup you have never restored is only a hope, so test one
        each term.
      <?php else: ?>
        Backups are managed at the server level. Keep a copy off-site each week.
      <?php endif; ?>
    </div>
  </div>
</div>
