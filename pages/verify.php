<?php
/** Public certificate verification — no account, no data leakage beyond the fact. */
require_once BASE_PATH . '/views/icons.php';

$code   = strtoupper(trim(getStr('code') ?: post('code')));
$result = null;
$checked = false;

if ($code !== '') {
    $checked = true;
    $result = row('SELECT ce.serial_no, ce.verify_code, ce.grade, ce.issue_date, ce.status,
                          s.first_name, s.middle_name, s.last_name, c.name AS course, c.duration_weeks
                   FROM certificates ce
                   JOIN students s ON s.id = ce.student_id
                   JOIN courses c ON c.id = ce.course_id
                   WHERE ce.verify_code = ? OR ce.serial_no = ?', [$code, $code]);
}
$page_title = 'Verify a certificate';
?>
<div class="panel">
  <div class="ribbon" style="border-radius:0"></div>
  <div class="panel__body">
    <div class="eyebrow">Certificate check</div>
    <h1 style="margin:2px 0 6px"><?= e(setting('org_name', ORG_NAME)) ?></h1>
    <p class="muted tiny">Enter the code printed at the bottom left of the certificate. It looks like
      <span class="mono">ABC-1234</span>.</p>

    <form method="get" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-top:14px">
      <input type="hidden" name="r" value="verify">
      <div class="field grow" style="flex:1;min-width:200px;margin:0">
        <label for="code">Verification code or serial number</label>
        <input id="code" name="code" value="<?= e($code) ?>" required autofocus
               style="font-family:var(--mono);letter-spacing:.08em;text-transform:uppercase">
      </div>
      <button class="btn btn--primary"><?= icon('search', 16) ?> Check</button>
    </form>

    <?php if ($checked && !$result): ?>
      <div class="alert alert--error" style="margin-top:16px">
        <?= icon('alert', 17) ?>
        <div><strong>No certificate matches that code.</strong> Check for a typo — the letters O/0 and I/1 are never
        used in our codes. If it still fails, contact the centre on <?= e(setting('org_phone', '')) ?>.</div>
      </div>
    <?php elseif ($result && $result['status'] !== 'valid'): ?>
      <div class="alert alert--warn" style="margin-top:16px">
        <?= icon('alert', 17) ?>
        <div><strong>This certificate has been <?= e($result['status']) ?>.</strong> Contact the centre before relying on it.</div>
      </div>
    <?php elseif ($result): ?>
      <div class="alert alert--ok" style="margin-top:16px">
        <?= icon('check', 17) ?><div><strong>Genuine certificate.</strong> Issued by <?= e(setting('org_name', ORG_NAME)) ?>.</div>
      </div>
      <dl class="dl">
        <dt>Holder</dt>
        <dd><?= e(trim($result['first_name'] . ' ' . ($result['middle_name'] ? $result['middle_name'] . ' ' : '') . $result['last_name'])) ?></dd>
        <dt>Course</dt><dd><?= e($result['course']) ?> (<?= (int) $result['duration_weeks'] ?> weeks)</dd>
        <dt>Grade</dt><dd><?= e($result['grade'] ?: 'Completed') ?></dd>
        <dt>Issued</dt><dd><?= e(d($result['issue_date'], 'd F Y')) ?></dd>
        <dt>Serial</dt><dd class="mono"><?= e($result['serial_no']) ?></dd>
      </dl>
    <?php endif; ?>
  </div>
  <div class="panel__foot">
    <a class="tiny" href="<?= e(url('login')) ?>">Staff sign-in</a>
  </div>
</div>
