<?php
/** A student's own attendance history. */
require_once BASE_PATH . '/views/icons.php';

$me = my_student();
if (!$me) { echo '<div class="alert alert--warn"><div>Your login is not linked to a student file yet.</div></div>'; return; }

$byCourse = rows("SELECT c.code, c.name,
                    COUNT(a.id) total,
                    SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) p,
                    SUM(CASE WHEN a.status='E' THEN 1 ELSE 0 END) x,
                    SUM(CASE WHEN a.status='A' THEN 1 ELSE 0 END) ab
                  FROM attendance a
                  JOIN enrolments e ON e.id = a.enrolment_id
                  JOIN courses c ON c.id = a.course_id
                  WHERE e.student_id = ? GROUP BY c.code, c.name ORDER BY c.name", [$me['id']]);

$recent = rows('SELECT a.session_date, a.status, a.remarks, c.code, c.name
                FROM attendance a JOIN enrolments e ON e.id = a.enrolment_id
                JOIN courses c ON c.id = a.course_id
                WHERE e.student_id = ? ORDER BY a.session_date DESC LIMIT 40', [$me['id']]);

$threshold = (int) setting('attendance_threshold', '80');
$page_sub = 'You need at least ' . $threshold . '% attendance to sit assessments.';
?>
<?php if (!$byCourse): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('check', 22) ?></div>
    <h3>No attendance recorded yet</h3>
    <p>Your facilitator records attendance in class. It shows up here the same day.</p>
  </div></div>
<?php else: ?>
  <div class="grid grid--2">
    <?php foreach ($byCourse as $a):
      $t = (int) $a['total']; $rate = pct((int) $a['p'], $t); ?>
      <div class="panel">
        <div class="panel__body">
          <div style="display:flex;align-items:baseline;gap:10px">
            <div>
              <div class="eyebrow mono"><?= e($a['code']) ?></div>
              <h2><?= e($a['name']) ?></h2>
            </div>
            <div style="margin-left:auto;text-align:right">
              <div class="stat__value" style="font-size:26px"><?= $rate ?><small>%</small></div>
              <?= badge($rate < $threshold ? 'Below threshold' : 'On track', $rate < $threshold ? 'red' : 'green') ?>
            </div>
          </div>
          <div class="segbar" style="margin-top:12px">
            <span class="seg-p" style="width:<?= pct((int) $a['p'], $t) ?>%"></span>
            <span class="seg-e" style="width:<?= pct((int) $a['x'], $t) ?>%"></span>
            <span class="seg-a" style="width:<?= pct((int) $a['ab'], $t) ?>%"></span>
          </div>
          <div class="seglegend">
            <span><i class="seg-p"></i>Present <?= (int) $a['p'] ?></span>
            <span><i class="seg-e"></i>Excused <?= (int) $a['x'] ?></span>
            <span><i class="seg-a"></i>Absent <?= (int) $a['ab'] ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="panel" style="margin-top:16px">
    <div class="panel__head"><h2>Recent sessions</h2></div>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Date</th><th>Course</th><th>Mark</th><th>Remark</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $r):
          $tone = ['P' => 'green', 'E' => 'blue', 'A' => 'red'][$r['status']] ?? 'neutral'; ?>
          <tr>
            <td class="mono tiny"><?= e(d($r['session_date'], 'D d M Y')) ?></td>
            <td class="tiny"><?= e($r['name']) ?></td>
            <td><?= badge(attendance_label($r['status']), $tone) ?></td>
            <td class="tiny muted"><?= e($r['remarks'] ?: '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
