<?php
/** Enter or change marks for one assessment, whole class on one screen. */
require_once BASE_PATH . '/views/icons.php';

$aid = getInt('assessment_id') ?: postInt('assessment_id');
$a = row('SELECT a.*, c.code, c.name AS course, c.id AS cid FROM assessments a
          JOIN courses c ON c.id = a.course_id WHERE a.id = ?', [$aid]);
if (!$a) { flash('error', 'That assessment was not found.'); redirect('assessments.index'); }
require_course_access((int) $a['cid']);

if (is_post()) {
    csrf_check();
    $scores   = $_POST['score'] ?? [];
    $feedback = $_POST['feedback'] ?? [];
    $saved = 0; $cleared = 0;

    foreach ($scores as $enrolmentId => $raw) {
        $enrolmentId = (int) $enrolmentId;
        if (!val('SELECT 1 FROM enrolments WHERE id = ? AND course_id = ?', [$enrolmentId, $a['cid']])) continue;

        $raw = trim((string) $raw);
        $fb  = mb_substr(trim((string) ($feedback[$enrolmentId] ?? '')), 0, 400);
        $existing = val('SELECT id FROM marks WHERE assessment_id = ? AND enrolment_id = ?', [$aid, $enrolmentId]);

        if ($raw === '') {
            if ($existing && $fb === '') { q('DELETE FROM marks WHERE id = ?', [$existing]); $cleared++; }
            elseif ($existing) { q('UPDATE marks SET score = NULL, feedback = ? WHERE id = ?', [$fb, $existing]); }
            continue;
        }

        $score = min((float) $a['max_score'], max(0, (float) $raw));
        if ($existing) {
            q('UPDATE marks SET score = ?, feedback = ?, recorded_by = ? WHERE id = ?', [$score, $fb, user_id(), $existing]);
        } else {
            insert('marks', ['assessment_id' => $aid, 'enrolment_id' => $enrolmentId, 'score' => $score,
                             'feedback' => $fb, 'recorded_by' => user_id(), 'created_at' => now()]);
        }
        $saved++;
    }
    audit('marks', 'assessments', $aid, $a['name'] . ' · ' . $saved . ' marks');
    flash('ok', 'Saved <strong>' . $saved . '</strong> mark' . ($saved === 1 ? '' : 's')
               . ($cleared ? ', cleared ' . $cleared : '') . ' for ' . e($a['name']) . '.');
    redirect('marks.enter', ['assessment_id' => $aid]);
}

$class = rows("SELECT e.id AS enrolment_id, s.id, s.first_name, s.last_name, s.student_no,
                 m.score, m.feedback
               FROM enrolments e JOIN students s ON s.id = e.student_id
               LEFT JOIN marks m ON m.enrolment_id = e.id AND m.assessment_id = ?
               WHERE e.course_id = ? AND e.status = 'active'
               ORDER BY s.first_name, s.last_name", [$aid, $a['cid']]);

$scored = array_filter($class, fn($r) => $r['score'] !== null);
$avg = $scored ? array_sum(array_map(fn($r) => (float) $r['score'], $scored)) / count($scored) : null;

$page_title   = $a['name'];
$page_sub     = e($a['code']) . ' · ' . e(ucfirst($a['kind'])) . ' · out of ' . (int) $a['max_score']
              . ' · weight ' . (int) $a['weight'] . '%'
              . ($avg !== null ? ' · class average ' . round($avg, 1) : '');
$page_actions = '<a class="btn" href="' . e(url('assessments.index', ['course_id' => $a['cid']])) . '">' . icon('back', 16) . ' Assessments</a>';
?>
<div class="panel">
  <?php if (!$class): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('users', 22) ?></div>
      <h3>No active students in <?= e($a['code']) ?></h3>
      <p>Enrol students first and they appear here for marking.</p>
    </div>
  <?php else: ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="assessment_id" value="<?= $aid ?>">
      <div class="toolbar">
        <span class="tiny muted" style="margin-right:auto">
          <?= count($scored) ?> of <?= count($class) ?> marked. Leave a box empty for work not submitted.
        </span>
        <input type="search" data-filter="#marktable" placeholder="Find a student…" style="max-width:180px">
      </div>
      <div class="tablewrap">
        <table class="data" id="marktable">
          <thead><tr><th>Student</th><th style="width:120px">Score</th><th style="width:90px">%</th><th>Feedback</th></tr></thead>
          <tbody>
          <?php foreach ($class as $s):
            $eid = (int) $s['enrolment_id'];
            $p   = $s['score'] !== null ? pct((float) $s['score'], (float) $a['max_score']) : null; ?>
            <tr>
              <td>
                <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                <div class="tiny mono muted"><?= e($s['student_no']) ?></div>
              </td>
              <td>
                <input name="score[<?= $eid ?>]" type="number" step="0.5" min="0" max="<?= (int) $a['max_score'] ?>"
                       value="<?= $s['score'] !== null ? e(rtrim(rtrim(number_format((float) $s['score'], 2, '.', ''), '0'), '.')) : '' ?>"
                       class="mono" style="text-align:center" inputmode="decimal">
              </td>
              <td>
                <?php if ($p !== null): ?>
                  <?= badge($p . '%', $p >= 75 ? 'green' : ($p >= 50 ? 'blue' : ($p >= 40 ? 'orange' : 'red'))) ?>
                <?php else: ?><span class="tiny muted">—</span><?php endif; ?>
              </td>
              <td><input name="feedback[<?= $eid ?>]" value="<?= e($s['feedback'] ?? '') ?>" placeholder="Optional comment for the student"></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="panel__foot">
        <button class="btn btn--green"><?= icon('check', 16) ?> Save marks</button>
        <a class="btn btn--ghost" target="_blank" href="<?= e(url('reports.show', ['type' => 'marks', 'course_id' => $a['cid']])) ?>">Print the marks sheet</a>
      </div>
    </form>
  <?php endif; ?>
</div>
