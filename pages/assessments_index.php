<?php
/** Set up assessments for a course, and see how much marking is left. */
require_once BASE_PATH . '/views/icons.php';

$mine    = my_course_ids();
$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list($mine) . ') ORDER BY name');
if (!$courses) {
    echo '<div class="panel"><div class="empty"><div class="empty__mark">' . icon('star', 22) . '</div>'
       . '<h3>No course in your scope</h3><p>Assessments belong to a course.</p></div></div>';
    return;
}
$courseId = getInt('course_id') ?: (int) $courses[0]['id'];
$course   = require_course_access($courseId);
$errors   = [];

if (is_post()) {
    csrf_check();
    $courseId = postInt('course_id');
    $course   = require_course_access($courseId);

    if (post('do') === 'delete') {
        $aid = postInt('id');
        $a = row('SELECT * FROM assessments WHERE id = ? AND course_id = ?', [$aid, $courseId]);
        if ($a) {
            q('DELETE FROM marks WHERE assessment_id = ?', [$aid]);
            q('DELETE FROM assessments WHERE id = ?', [$aid]);
            audit('delete', 'assessments', $aid, $a['name']);
            flash('ok', 'Assessment and its marks removed.');
        }
        redirect('assessments.index', ['course_id' => $courseId]);
    }

    $name   = post('name');
    $kind   = post('kind');
    $maxS   = postInt('max_score', 100);
    $weight = postInt('weight', 0);
    $due    = postNull('due_date');

    if ($name === '') $errors[] = 'Give the assessment a name.';
    if (!in_array($kind, ['theory', 'practical', 'project', 'exam'], true)) $errors[] = 'Choose the kind of assessment.';
    if ($maxS < 1 || $maxS > 1000) $errors[] = 'The maximum score must be between 1 and 1000.';
    if ($weight < 0 || $weight > 100) $errors[] = 'Weight is a percentage between 0 and 100.';

    $usedWeight = (int) val('SELECT COALESCE(SUM(weight),0) FROM assessments WHERE course_id = ?', [$courseId], 0);
    if (!$errors && $usedWeight + $weight > 100) {
        $errors[] = 'Weights for this course would total ' . ($usedWeight + $weight) . '%. Only ' . (100 - $usedWeight) . '% is left.';
    }

    if (!$errors) {
        $id = insert('assessments', [
            'course_id' => $courseId, 'name' => $name, 'kind' => $kind, 'max_score' => $maxS,
            'weight' => $weight, 'due_date' => $due, 'created_by' => user_id(), 'created_at' => now(),
        ]);
        audit('create', 'assessments', $id, $name);
        flash('ok', 'Assessment added. Enter marks when the work is done.');
        redirect('assessments.index', ['course_id' => $courseId]);
    }
}

$classSize = (int) val("SELECT COUNT(*) FROM enrolments WHERE course_id = ? AND status = 'active'", [$courseId], 0);
$list = rows('SELECT a.*, (SELECT COUNT(*) FROM marks m WHERE m.assessment_id = a.id) AS marked,
                (SELECT AVG(m.score) FROM marks m WHERE m.assessment_id = a.id) AS avg_score
              FROM assessments a WHERE a.course_id = ? ORDER BY a.due_date, a.id', [$courseId]);
$totalWeight = 0;
foreach ($list as $a) $totalWeight += (int) $a['weight'];

$page_sub = e($course['code']) . ' · ' . $classSize . ' active student' . ($classSize === 1 ? '' : 's')
          . ' · weights total ' . $totalWeight . '%';
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="assessments.index">
    <div class="field grow">
      <label for="pick">Course</label>
      <select id="pick" name="course_id" data-autosubmit>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $courseId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn"><?= icon('search', 16) ?> Show</button>
  </form>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('star', 22) ?></div>
      <h3>No assessments for <?= e($course['code']) ?></h3>
      <p>Add theory tests, practicals, projects and the final exam. Weights should add up to 100%.</p>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Assessment</th><th>Kind</th><th>Due</th><th class="right">Out of</th><th class="right">Weight</th><th class="right">Marked</th><th class="right">Average</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $a):
          $done = (int) $a['marked']; ?>
          <tr>
            <td><strong><?= e($a['name']) ?></strong></td>
            <td><?= badge(ucfirst($a['kind']), $a['kind'] === 'practical' ? 'orange' : ($a['kind'] === 'exam' ? 'blue' : 'neutral')) ?></td>
            <td class="tiny mono"><?= e(d($a['due_date'])) ?></td>
            <td class="right mono"><?= (int) $a['max_score'] ?></td>
            <td class="right mono"><?= (int) $a['weight'] ?>%</td>
            <td class="right" style="min-width:100px">
              <div class="mono tiny"><?= $done ?> / <?= $classSize ?></div>
              <div class="segbar" style="height:5px;margin-top:3px">
                <span class="seg-p" style="width:<?= pct($done, $classSize) ?>%"></span>
              </div>
            </td>
            <td class="right mono"><?= $a['avg_score'] !== null ? round((float) $a['avg_score'], 1) : '—' ?></td>
            <td class="right nowrap">
              <a class="btn btn--sm" href="<?= e(url('marks.enter', ['assessment_id' => $a['id']])) ?>">Enter marks</a>
              <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="do" value="delete">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button class="btn btn--sm btn--ghost" data-confirm="Delete this assessment and every mark recorded against it?"><?= icon('x', 14) ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="panel" style="margin-top:16px;max-width:820px">
  <div class="panel__head"><h2>Add an assessment</h2>
    <span class="tiny muted"><?= max(0, 100 - $totalWeight) ?>% of the weighting is unused</span></div>
  <form method="post" class="panel__body">
    <?= csrf_field() ?>
    <input type="hidden" name="course_id" value="<?= $courseId ?>">
    <div class="formgrid formgrid--3">
      <div class="field span2">
        <label for="name">Name <span class="req">*</span></label>
        <input id="name" name="name" required placeholder="Practical 2 — build a landing page">
      </div>
      <div class="field">
        <label for="kind">Kind</label>
        <select id="kind" name="kind">
          <option value="theory">Theory</option>
          <option value="practical">Practical</option>
          <option value="project">Project</option>
          <option value="exam">Exam</option>
        </select>
      </div>
      <div class="field">
        <label for="max_score">Out of</label>
        <input id="max_score" name="max_score" type="number" min="1" max="1000" value="100">
      </div>
      <div class="field">
        <label for="weight">Weight (%)</label>
        <input id="weight" name="weight" type="number" min="0" max="100" value="<?= max(0, 100 - $totalWeight) ?>">
      </div>
      <div class="field">
        <label for="due_date">Due date</label>
        <input id="due_date" name="due_date" type="date">
      </div>
    </div>
    <button class="btn btn--primary"><?= icon('plus', 16) ?> Add assessment</button>
  </form>
</div>
