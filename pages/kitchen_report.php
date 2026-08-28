<?php
/**
 * Kitchen monthly report. Two modes: on-screen (default) and print=1, which
 * renders a signed sheet management can file. Kitchen staff, managers and
 * admins all reach the same page.
 */
require_once BASE_PATH . '/views/icons.php';

$month = getStr('month');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
$from = $month . '-01';
$to   = date('Y-m-t', strtotime($from));
$print = getInt('print') === 1;

$st      = settings();
$teaLbl  = $st['kitchen_tea_label']  ?? 'Morning tea (cups)';
$mealLbl = $st['kitchen_meal_label'] ?? 'Afternoon meal (plates)';

$days = rows('SELECT k.*, u.name AS recorded_by_name FROM kitchen_records k
              LEFT JOIN users u ON u.id = k.recorded_by
              WHERE k.service_date BETWEEN ? AND ? ORDER BY k.service_date', [$from, $to]);

$sum = ['tt' => 0, 'ts' => 0, 'mt' => 0, 'ms' => 0];
$peak = 1;
foreach ($days as $k) {
    $sum['tt'] += (int) $k['tea_teachers'];
    $sum['ts'] += (int) $k['tea_students'];
    $sum['mt'] += (int) $k['meals_teachers'];
    $sum['ms'] += (int) $k['meals_students'];
    $peak = max($peak, (int) $k['tea_teachers'] + (int) $k['tea_students']
                     + (int) $k['meals_teachers'] + (int) $k['meals_students']);
}
$tea      = $sum['tt'] + $sum['ts'];
$meal     = $sum['mt'] + $sum['ms'];
$teachers = $sum['tt'] + $sum['mt'];
$students = $sum['ts'] + $sum['ms'];
$all      = $tea + $meal;
$recorded = count($days);

// Working days in the month that have no record at all (Mon–Sat).
$missing = [];
$cursor  = strtotime($from);
$endTs   = min(strtotime($to), strtotime(date('Y-m-d')));
$have    = array_column($days, 'service_date');
while ($cursor <= $endTs) {
    $ds = date('Y-m-d', $cursor);
    if ((int) date('N', $cursor) <= 6 && !in_array($ds, $have, true)) $missing[] = $ds;
    $cursor = strtotime('+1 day', $cursor);
}

if ($print) {
    $page_title = 'Kitchen report · ' . month_label($month);
    $print_page = 'a4';
    $print_back = url('kitchen.report', ['month' => $month]);
    ?>
    <div class="sheet sheet--a4">
      <div class="docribbon"><i></i><i></i><i></i></div>
      <div class="dochead">
        <div class="dochead__mark">EY</div>
        <div>
          <div class="dochead__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
          <div class="dochead__motto">"<?= e($st['org_motto'] ?? ORG_MOTTO) ?>"</div>
          <div class="dochead__meta"><?= e($st['org_phone'] ?? '') ?> · <?= e($st['org_email'] ?? '') ?></div>
        </div>
        <div class="dochead__right">
          <div class="doctitle">Kitchen service report</div>
          <div class="docref"><?= e(month_label($month)) ?></div>
          <div class="dochead__meta"><?= $recorded ?> day<?= $recorded === 1 ? '' : 's' ?> recorded</div>
        </div>
      </div>

      <div class="pairs">
        <div class="pair"><span class="pair__k">Tea served</span><span class="pair__v"><?= $tea ?> cups</span></div>
        <div class="pair"><span class="pair__k">Meals served</span><span class="pair__v"><?= $meal ?> plates</span></div>
        <div class="pair"><span class="pair__k">To teachers</span><span class="pair__v"><?= $teachers ?></span></div>
        <div class="pair"><span class="pair__k">To students</span><span class="pair__v"><?= $students ?></span></div>
        <div class="pair"><span class="pair__k">Total servings</span><span class="pair__v"><?= $all ?></span></div>
        <div class="pair"><span class="pair__k">Daily average</span><span class="pair__v"><?= $recorded ? round($all / $recorded, 1) : 0 ?></span></div>
      </div>

      <div class="docsection">Day by day</div>
      <table class="doc">
        <thead>
          <tr>
            <th>Date</th>
            <th class="center">Tea — teachers</th><th class="center">Tea — students</th>
            <th class="center">Meals — teachers</th><th class="center">Meals — students</th>
            <th class="center">Total</th><th>Note</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($days as $k):
          $t = (int) $k['tea_teachers'] + (int) $k['tea_students'] + (int) $k['meals_teachers'] + (int) $k['meals_students']; ?>
          <tr>
            <td><?= e(d($k['service_date'], 'D d M')) ?></td>
            <td class="center"><?= (int) $k['tea_teachers'] ?></td>
            <td class="center"><?= (int) $k['tea_students'] ?></td>
            <td class="center"><?= (int) $k['meals_teachers'] ?></td>
            <td class="center"><?= (int) $k['meals_students'] ?></td>
            <td class="center"><strong><?= $t ?></strong></td>
            <td><?= e($k['notes'] ?: '') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$days): ?>
          <tr><td colspan="7">No days recorded in this month.</td></tr>
        <?php endif; ?>
        </tbody>
        <?php if ($days): ?>
        <tfoot>
          <tr>
            <th>Total</th>
            <th class="center"><?= $sum['tt'] ?></th><th class="center"><?= $sum['ts'] ?></th>
            <th class="center"><?= $sum['mt'] ?></th><th class="center"><?= $sum['ms'] ?></th>
            <th class="center"><?= $all ?></th><th></th>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>

      <div class="signrow">
        <div><div class="signline">Kitchen in charge</div></div>
        <div><div class="signline">Approved by</div></div>
      </div>

      <div class="docfoot">
        <span><?= e($st['org_name'] ?? ORG_NAME) ?> · Kitchen report · <?= e(month_label($month)) ?></span>
        <span>Printed <?= e(date('d M Y H:i')) ?></span>
      </div>
    </div>
    <?php
    return;
}

$page_title   = 'Kitchen report';
$page_sub     = e(month_label($month)) . ' · ' . $recorded . ' day' . ($recorded === 1 ? '' : 's') . ' recorded';
$page_actions = '<a class="btn" target="_blank" href="' . e(url('kitchen.report', ['month' => $month, 'print' => 1])) . '">'
              . icon('printer', 16) . ' Print</a>';
?>
<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="kitchen.report">
    <div class="field">
      <label for="month">Month</label>
      <input id="month" name="month" type="month" value="<?= e($month) ?>" max="<?= date('Y-m') ?>" data-autosubmit>
    </div>
    <button class="btn"><?= icon('search', 16) ?> Show</button>
  </form>
</div>

<div class="grid grid--4" style="margin-top:16px">
  <div class="stat stat--orange">
    <div class="stat__label"><?= e($teaLbl) ?></div>
    <div class="stat__value"><?= $tea ?></div>
    <div class="stat__note"><?= $sum['tt'] ?> teachers · <?= $sum['ts'] ?> students</div>
  </div>
  <div class="stat stat--green">
    <div class="stat__label"><?= e($mealLbl) ?></div>
    <div class="stat__value"><?= $meal ?></div>
    <div class="stat__note"><?= $sum['mt'] ?> teachers · <?= $sum['ms'] ?> students</div>
  </div>
  <div class="stat">
    <div class="stat__label">Total servings</div>
    <div class="stat__value"><?= $all ?></div>
    <div class="stat__note">Average <?= $recorded ? round($all / $recorded, 1) : 0 ?> per day</div>
  </div>
  <div class="stat stat--ink">
    <div class="stat__label">Days recorded</div>
    <div class="stat__value"><?= $recorded ?><small> / <?= (int) date('t', strtotime($from)) ?></small></div>
    <div class="stat__note"><?= count($missing) ?> working day<?= count($missing) === 1 ? '' : 's' ?> missing</div>
  </div>
</div>

<?php if ($missing && can('kitchen.record')): ?>
  <div class="alert alert--warn" style="margin-top:16px">
    <?= icon('alert', 17) ?>
    <div><strong><?= count($missing) ?> working day<?= count($missing) === 1 ? '' : 's' ?> without a record:</strong>
      <?php foreach (array_slice($missing, 0, 12) as $i => $ds): ?>
        <a href="<?= e(url('kitchen.record', ['date' => $ds])) ?>"><?= e(d($ds, 'd M')) ?></a><?= $i < min(11, count($missing) - 1) ? ', ' : '' ?>
      <?php endforeach; ?><?= count($missing) > 12 ? ' and ' . (count($missing) - 12) . ' more' : '' ?>.
    </div>
  </div>
<?php endif; ?>

<div class="panel" style="margin-top:16px">
  <div class="panel__head">
    <h2>Day by day</h2>
    <span class="seglegend" style="margin:0">
      <span><i class="seg-teachers"></i>Teachers</span>
      <span><i class="seg-students"></i>Students</span>
    </span>
  </div>
  <?php if (!$days): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('kitchen', 22) ?></div>
      <h3>Nothing recorded in <?= e(month_label($month)) ?></h3>
      <p>Pick another month, or record a day.</p>
      <?php if (can('kitchen.record')): ?>
        <a class="btn btn--primary" href="<?= e(url('kitchen.record')) ?>">Record a day</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data compact">
        <thead>
          <tr>
            <th>Date</th>
            <th class="center" colspan="2">Tea</th>
            <th class="center" colspan="2">Meals</th>
            <th class="right">Total</th>
            <th style="min-width:120px">Split</th>
            <th>Note</th>
          </tr>
          <tr class="subhead">
            <th></th>
            <th class="center">Teach.</th><th class="center">Stud.</th>
            <th class="center">Teach.</th><th class="center">Stud.</th>
            <th></th><th></th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($days as $k):
          $dt = (int) $k['tea_teachers'] + (int) $k['meals_teachers'];
          $ds = (int) $k['tea_students'] + (int) $k['meals_students'];
          $t  = $dt + $ds; ?>
          <tr>
            <td class="nowrap"><?= e(d($k['service_date'], 'D d M')) ?></td>
            <td class="center mono"><?= (int) $k['tea_teachers'] ?></td>
            <td class="center mono"><?= (int) $k['tea_students'] ?></td>
            <td class="center mono"><?= (int) $k['meals_teachers'] ?></td>
            <td class="center mono"><?= (int) $k['meals_students'] ?></td>
            <td class="right mono"><strong><?= $t ?></strong></td>
            <td>
              <div class="segbar" style="height:6px">
                <span class="seg-teachers" style="width:<?= pct($dt, $t) ?>%"></span>
                <span class="seg-students" style="width:<?= pct($ds, $t) ?>%"></span>
              </div>
            </td>
            <td class="tiny muted"><?= e($k['notes'] ?: '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th>Total</th>
            <th class="center mono"><?= $sum['tt'] ?></th>
            <th class="center mono"><?= $sum['ts'] ?></th>
            <th class="center mono"><?= $sum['mt'] ?></th>
            <th class="center mono"><?= $sum['ms'] ?></th>
            <th class="right mono"><?= $all ?></th>
            <th colspan="2" class="tiny muted"><?= $teachers ?> to teachers · <?= $students ?> to students</th>
          </tr>
        </tfoot>
      </table>
    </div>
  <?php endif; ?>
</div>
