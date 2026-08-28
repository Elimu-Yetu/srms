<?php
/** A student's own marks, per course, with the weighted running total. */
require_once BASE_PATH . '/views/icons.php';

$me = my_student();
if (!$me) { echo '<div class="alert alert--warn"><div>Your login is not linked to a student file yet.</div></div>'; return; }

$courses = rows('SELECT c.id, c.code, c.name, e.id AS enrolment_id
                 FROM enrolments e JOIN courses c ON c.id = e.course_id
                 WHERE e.student_id = ? ORDER BY c.name', [$me['id']]);
$page_sub = 'Marks are published as your facilitator enters them.';
?>
<?php if (!$courses): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('star', 22) ?></div>
    <h3>No enrolment yet</h3>
    <p>Once you are enrolled, your assessments and marks appear here.</p>
  </div></div>
<?php else: ?>
  <?php foreach ($courses as $c):
    $items = rows('SELECT a.name, a.kind, a.max_score, a.weight, a.due_date, m.score, m.feedback
                   FROM assessments a
                   LEFT JOIN marks m ON m.assessment_id = a.id AND m.enrolment_id = ?
                   WHERE a.course_id = ? ORDER BY a.due_date, a.id', [$c['enrolment_id'], $c['id']]);
    $earned = 0; $possible = 0;
    foreach ($items as $it) {
      if ($it['score'] === null || (int) $it['weight'] === 0) continue;
      $earned   += ((float) $it['score'] / max(1, (float) $it['max_score'])) * (int) $it['weight'];
      $possible += (int) $it['weight'];
    } ?>
    <div class="panel" style="margin-bottom:16px">
      <div class="panel__head">
        <div>
          <div class="eyebrow mono"><?= e($c['code']) ?></div>
          <h2><?= e($c['name']) ?></h2>
        </div>
        <?php if ($possible): ?>
          <div style="margin-left:auto;text-align:right">
            <div class="stat__value" style="font-size:24px"><?= round($earned, 1) ?><small> / <?= $possible ?></small></div>
            <span class="tiny muted">weighted so far</span>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!$items): ?>
        <div class="panel__body"><p class="tiny muted">No assessments set for this course yet.</p></div>
      <?php else: ?>
        <div class="tablewrap">
          <table class="data compact">
            <thead><tr><th>Assessment</th><th>Kind</th><th>Due</th><th class="right">Score</th><th class="right">%</th><th>Feedback</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it):
              $p = $it['score'] !== null ? pct((float) $it['score'], (float) $it['max_score']) : null; ?>
              <tr>
                <td><?= e($it['name']) ?><div class="tiny muted">weight <?= (int) $it['weight'] ?>%</div></td>
                <td class="tiny"><?= e(ucfirst($it['kind'])) ?></td>
                <td class="tiny mono"><?= e(d($it['due_date'])) ?></td>
                <td class="right mono"><?= $it['score'] !== null ? round((float) $it['score'], 1) . ' / ' . (int) $it['max_score'] : '<span class="muted tiny">not marked</span>' ?></td>
                <td class="right"><?= $p !== null ? badge($p . '%', $p >= 75 ? 'green' : ($p >= 50 ? 'blue' : ($p >= 40 ? 'orange' : 'red'))) : '—' ?></td>
                <td class="tiny muted"><?= e($it['feedback'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
