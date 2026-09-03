<?php
/**
 * Kitchen dashboard. This is the landing page for the kitchen role.
 * Two services a day: morning tea (cups) and the afternoon meal (plates),
 * each counted separately for teachers and for students.
 */
require_once BASE_PATH . '/views/icons.php';

$st       = settings();
$teaLabel = $st['kitchen_tea_label'] ?? 'Morning tea (cups)';
$mealLabel = $st['kitchen_meal_label'] ?? 'Afternoon meal (plates)';

// Handle deletion of a kitchen record
if (post('do') === 'delete') {
  csrf_check();
  $kid = postInt('id');
  $r = row('SELECT * FROM kitchen_records WHERE id = ?', [$kid]);
  if (!$r) {
    flash('error', 'That kitchen record was not found.');
    redirect('kitchen.index');
  }
  if (!is_role('kitchen') && !is_role('admin')) {
    flash('error', 'Only kitchen staff or an administrator can delete kitchen records.');
    redirect('kitchen.index');
  }
  q('DELETE FROM kitchen_records WHERE id = ?', [$kid]);
  audit('delete', 'kitchen_records', $kid, $r['service_date']);
  flash('ok', 'Deleted the kitchen record for ' . e(d($r['service_date'])) . '.');
  redirect('kitchen.index');
}

$today  = date('Y-m-d');
$rec    = row('SELECT * FROM kitchen_records WHERE service_date = ?', [$today]);
$series = array_reverse(rows('SELECT * FROM kitchen_records ORDER BY service_date DESC LIMIT 14'));

$monthFrom = date('Y-m-01');
$mtd = row("SELECT COUNT(*) days,
              COALESCE(SUM(tea_teachers),0) tt, COALESCE(SUM(tea_students),0) ts,
              COALESCE(SUM(meals_teachers),0) mt, COALESCE(SUM(meals_students),0) ms
            FROM kitchen_records WHERE service_date >= ?", [$monthFrom]) ?: [];
$tt = (int) ($mtd['tt'] ?? 0); $ts = (int) ($mtd['ts'] ?? 0);
$mt = (int) ($mtd['mt'] ?? 0); $ms = (int) ($mtd['ms'] ?? 0);
$teaTotal  = $tt + $ts;
$mealTotal = $mt + $ms;
$days      = (int) ($mtd['days'] ?? 0);

// Expected heads today, so the kitchen knows roughly what to cook.
$expStudents = (int) val("SELECT COUNT(DISTINCT s.id) FROM students s
                          JOIN enrolments e ON e.student_id = s.id AND e.status = 'active'
                          WHERE s.status = 'active'", [], 0);
$expTeachers = (int) val("SELECT COUNT(*) FROM users WHERE role = 'facilitator' AND status = 'active'", [], 0);

$peak = 1;
foreach ($series as $s) {
    $peak = max($peak, (int) $s['tea_teachers'] + (int) $s['tea_students'],
                       (int) $s['meals_teachers'] + (int) $s['meals_students']);
}

$page_title   = 'Kitchen';
$page_sub     = 'Tea in the morning, meals in the afternoon — teachers and students counted separately.';
$page_actions = '<a class="btn btn--orange" href="' . e(url('kitchen.record')) . '">' . icon('kitchen', 16) . ' '
              . ($rec ? "Update today's record" : "Record today's service") . '</a>';
?>

<?php if (!$rec && can('kitchen.record')): ?>
  <div class="alert alert--warn">
    <?= icon('alert', 17) ?>
    <div><strong>Nothing recorded for <?= e(d($today, 'l d F')) ?> yet.</strong>
      Enter the morning tea after breakfast and come back for the afternoon meal —
      the form saves both, and you can update it during the day.</div>
  </div>
<?php endif; ?>

<div class="grid grid--4">
  <div class="stat stat--orange">
    <div class="stat__label">Today · tea served</div>
    <div class="stat__value"><?= $rec ? (int) $rec['tea_teachers'] + (int) $rec['tea_students'] : '—' ?></div>
    <div class="stat__note">
      <?php if ($rec): ?>
        <?= (int) $rec['tea_teachers'] ?> teachers · <?= (int) $rec['tea_students'] ?> students
      <?php else: ?>not recorded<?php endif; ?>
    </div>
  </div>
  <div class="stat stat--green">
    <div class="stat__label">Today · plates served</div>
    <div class="stat__value"><?= $rec ? (int) $rec['meals_teachers'] + (int) $rec['meals_students'] : '—' ?></div>
    <div class="stat__note">
      <?php if ($rec): ?>
        <?= (int) $rec['meals_teachers'] ?> teachers · <?= (int) $rec['meals_students'] ?> students
      <?php else: ?>not recorded<?php endif; ?>
    </div>
  </div>
  <div class="stat">
    <div class="stat__label">Expected today</div>
    <div class="stat__value"><?= $expTeachers + $expStudents ?></div>
    <div class="stat__note"><?= $expTeachers ?> teachers · <?= $expStudents ?> active students</div>
  </div>
  <div class="stat stat--ink">
    <div class="stat__label"><?= e(date('F')) ?> to date</div>
    <div class="stat__value"><?= number_format($teaTotal + $mealTotal) ?></div>
    <div class="stat__note">servings across <?= $days ?> day<?= $days === 1 ? '' : 's' ?></div>
  </div>
</div>

<div class="grid grid--sidebar" style="margin-top:16px">
  <div class="stack">
    <div class="panel">
      <div class="panel__head">
        <h2>Last <?= count($series) ?> days</h2>
        <span class="tiny muted">Total servings per day · <span class="mono">blue</span> teachers, <span class="mono">orange</span> students</span>
      </div>
      <div class="panel__body">
        <?php if (!$series): ?>
          <p class="tiny muted">No records yet. The chart fills in as you record each day.</p>
        <?php else: ?>
          <div class="rail__label" style="padding-left:0"><?= e($teaLabel) ?></div>
          <div class="spark">
            <?php foreach ($series as $s): ?>
              <div class="spark__col" title="<?= e(d($s['service_date'])) ?>: <?= (int) $s['tea_teachers'] ?> teachers, <?= (int) $s['tea_students'] ?> students">
                <div class="spark__bar spark__bar--s" style="height:<?= round((int) $s['tea_students'] / $peak * 62) ?>px"></div>
                <div class="spark__bar spark__bar--t" style="height:<?= round((int) $s['tea_teachers'] / $peak * 62) ?>px"></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="spark" style="height:auto">
            <?php foreach ($series as $s): ?>
              <div class="spark__col"><div class="spark__x"><?= e(date('d', strtotime($s['service_date']))) ?></div></div>
            <?php endforeach; ?>
          </div>

          <div class="rail__label" style="padding-left:0;margin-top:18px"><?= e($mealLabel) ?></div>
          <div class="spark">
            <?php foreach ($series as $s): ?>
              <div class="spark__col" title="<?= e(d($s['service_date'])) ?>: <?= (int) $s['meals_teachers'] ?> teachers, <?= (int) $s['meals_students'] ?> students">
                <div class="spark__bar spark__bar--s" style="height:<?= round((int) $s['meals_students'] / $peak * 62) ?>px"></div>
                <div class="spark__bar spark__bar--t" style="height:<?= round((int) $s['meals_teachers'] / $peak * 62) ?>px"></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="spark" style="height:auto">
            <?php foreach ($series as $s): ?>
              <div class="spark__col"><div class="spark__x"><?= e(date('d', strtotime($s['service_date']))) ?></div></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel__head">
        <h2>Recent records</h2>
        <a class="btn btn--sm" href="<?= e(url('kitchen.report')) ?>"><?= icon('chart', 15) ?> Full report</a>
      </div>
      <?php if (!$series): ?>
        <div class="empty" style="padding:28px">
          <div class="empty__mark"><?= icon('kitchen', 20) ?></div>
          <h3>No kitchen records yet</h3>
          <p>Record the first day and the totals, chart and monthly report all start working.</p>
          <?php if (can('kitchen.record')): ?>
            <a class="btn btn--orange" href="<?= e(url('kitchen.record')) ?>">Record today</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="tablewrap">
          <table class="data compact">
            <thead>
              <tr>
                <th>Date</th>
                <th class="center" colspan="3">Tea · cups</th>
                <th class="center" colspan="3">Meals · plates</th>
                <th></th>
              </tr>
              <tr>
                <th></th>
                <th class="right">Teach.</th><th class="right">Stud.</th><th class="right">All</th>
                <th class="right">Teach.</th><th class="right">Stud.</th><th class="right">All</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach (array_reverse($series) as $s): ?>
              <tr>
                <td class="nowrap"><?= e(d($s['service_date'], 'D d M')) ?>
                  <?php if ($s['service_date'] === $today): ?><?= badge('today', 'blue') ?><?php endif; ?>
                  <?php if ($s['notes']): ?><div class="tiny muted"><?= e($s['notes']) ?></div><?php endif; ?>
                </td>
                <td class="right mono"><?= (int) $s['tea_teachers'] ?></td>
                <td class="right mono"><?= (int) $s['tea_students'] ?></td>
                <td class="right mono"><strong><?= (int) $s['tea_teachers'] + (int) $s['tea_students'] ?></strong></td>
                <td class="right mono"><?= (int) $s['meals_teachers'] ?></td>
                <td class="right mono"><?= (int) $s['meals_students'] ?></td>
                <td class="right mono"><strong><?= (int) $s['meals_teachers'] + (int) $s['meals_students'] ?></strong></td>
                <td class="right">
                  <?php if (can('kitchen.record')): ?>
                    <a class="btn btn--sm btn--ghost" href="<?= e(url('kitchen.record', ['date' => $s['service_date']])) ?>"><?= icon('edit', 15) ?></a>
                  <?php endif; ?>
                  <?php if (is_role('kitchen') || is_role('admin')): ?>
                    <form method="post" style="display:inline"><?= csrf_field() ?>
                      <input type="hidden" name="do" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                      <button class="btn btn--sm btn--ghost" data-confirm="Delete record for <?= e(d($s['service_date'])) ?>?"><?= icon('x', 14) ?></button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2><?= e(date('F Y')) ?> so far</h2></div>
      <div class="panel__body">
        <div class="eyebrow"><?= e($teaLabel) ?></div>
        <div class="stat__value" style="font-size:26px"><?= number_format($teaTotal) ?></div>
        <div class="segbar" style="margin-top:8px">
          <span class="seg-teachers" style="width:<?= pct($tt, $teaTotal) ?>%"></span>
          <span class="seg-students" style="width:<?= pct($ts, $teaTotal) ?>%"></span>
        </div>
        <div class="seglegend">
          <span><i class="seg-teachers"></i>Teachers <?= number_format($tt) ?></span>
          <span><i class="seg-students"></i>Students <?= number_format($ts) ?></span>
        </div>

        <div class="eyebrow" style="margin-top:20px"><?= e($mealLabel) ?></div>
        <div class="stat__value" style="font-size:26px"><?= number_format($mealTotal) ?></div>
        <div class="segbar" style="margin-top:8px">
          <span class="seg-teachers" style="width:<?= pct($mt, $mealTotal) ?>%"></span>
          <span class="seg-students" style="width:<?= pct($ms, $mealTotal) ?>%"></span>
        </div>
        <div class="seglegend">
          <span><i class="seg-teachers"></i>Teachers <?= number_format($mt) ?></span>
          <span><i class="seg-students"></i>Students <?= number_format($ms) ?></span>
        </div>

        <?php if ($days): ?>
          <dl class="dl" style="margin-top:18px">
            <dt>Daily average · tea</dt><dd class="mono"><?= round($teaTotal / $days, 1) ?></dd>
            <dt>Daily average · plates</dt><dd class="mono"><?= round($mealTotal / $days, 1) ?></dd>
            <dt>Days recorded</dt><dd class="mono"><?= $days ?></dd>
          </dl>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Do this daily</h2></div>
      <div class="panel__body">
        <ol class="tiny" style="margin:0;padding-left:18px;display:grid;gap:7px;color:var(--ink-soft)">
          <li>After morning tea, enter cups for teachers and for students.</li>
          <li>After the afternoon meal, open the same day's record and enter plates.</li>
          <li>Note anything unusual — a visiting group, a shortage, a closed day.</li>
        </ol>
        <div class="btnrow" style="margin-top:14px">
          <?php if (can('kitchen.record')): ?>
            <a class="btn btn--orange" href="<?= e(url('kitchen.record')) ?>"><?= icon('kitchen', 16) ?> Record</a>
          <?php endif; ?>
          <a class="btn" href="<?= e(url('kitchen.report')) ?>"><?= icon('chart', 16) ?> Report</a>
        </div>
      </div>
    </div>
  </div>
</div>
