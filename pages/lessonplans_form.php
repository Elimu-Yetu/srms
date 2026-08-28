<?php
/** The lesson plan form — deliberately short, the same five fields every time. */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
$rec = $id ? row('SELECT * FROM lesson_plans WHERE id = ? AND facilitator_id = ?', [$id, user_id()]) : null;
if ($id && !$rec) deny('You can only edit your own lesson plans.');
if ($rec && $rec['review_status'] === 'reviewed') {
    flash('warn', 'That plan has been reviewed already, so it is now read-only.');
    redirect('lessonplans.view', ['id' => $id]);
}
$page_title = $rec ? 'Edit lesson plan' : 'New lesson plan';

$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list(my_course_ids()) . ') ORDER BY name');
$errors  = [];
$f = $rec ?: ['course_id' => getInt('course_id'), 'plan_date' => date('Y-m-d'), 'topic' => '',
              'objectives' => '', 'activities' => '', 'resources' => ''];

if (is_post()) {
    csrf_check();
    foreach (['topic', 'objectives', 'activities', 'resources'] as $k) $f[$k] = post($k);
    $f['course_id'] = postInt('course_id');
    $f['plan_date'] = post('plan_date');

    if (!in_array($f['course_id'], array_map('intval', my_course_ids()), true)) $errors[] = 'Choose one of your courses.';
    if (!strtotime((string) $f['plan_date'])) $errors[] = 'Enter the date of the lesson.';
    if ($f['topic'] === '') $errors[] = 'Give the lesson a topic.';
    if ($f['objectives'] === '') $errors[] = 'List at least one learning objective.';

    if (!$errors) {
        $data = [
            'course_id' => $f['course_id'], 'plan_date' => $f['plan_date'], 'topic' => $f['topic'],
            'objectives' => $f['objectives'], 'activities' => $f['activities'], 'resources' => $f['resources'],
        ];
        if ($rec) {
            $data['review_status'] = 'submitted';
            update('lesson_plans', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'lesson_plans', $id, $f['topic']);
            flash('ok', 'Lesson plan updated and sent for review again.');
        } else {
            $data['facilitator_id'] = user_id();
            $data['review_status']  = 'submitted';
            $data['created_at']     = now();
            $id = insert('lesson_plans', $data);
            audit('create', 'lesson_plans', $id, $f['topic']);
            flash('ok', 'Lesson plan submitted. Your line manager can see it now.');
        }
        redirect('lessonplans.view', ['id' => $id]);
    }
}
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<?php if (!$courses): ?>
  <div class="panel"><div class="empty"><h3>No course assigned to you</h3>
    <p>Ask the office to set you as the facilitator of a course.</p></div></div>
<?php else: ?>
<div class="panel" style="max-width:860px">
  <form method="post" class="panel__body">
    <?= csrf_field() ?>
    <div class="formgrid">
      <div class="field">
        <label for="course_id">Class <span class="req">*</span></label>
        <select id="course_id" name="course_id" required>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $f['course_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="plan_date">Date of the lesson <span class="req">*</span></label>
        <input id="plan_date" name="plan_date" type="date" value="<?= e($f['plan_date']) ?>" required>
      </div>
      <div class="field span2">
        <label for="topic">Topic <span class="req">*</span></label>
        <input id="topic" name="topic" value="<?= e($f['topic']) ?>" required placeholder="Forms and server-side validation">
      </div>
      <div class="field span2">
        <label for="objectives">Learning objectives <span class="req">*</span></label>
        <textarea id="objectives" name="objectives" rows="4" required placeholder="One per line — what students will be able to do by the end."><?= e($f['objectives']) ?></textarea>
        <div class="hint">One per line.</div>
      </div>
      <div class="field span2">
        <label for="activities">Activities planned</label>
        <textarea id="activities" name="activities" rows="4" placeholder="Demonstration, paired practical, group review…"><?= e($f['activities']) ?></textarea>
      </div>
      <div class="field span2">
        <label for="resources">Resources needed</label>
        <textarea id="resources" name="resources" rows="2" placeholder="Projector, 12 workstations, printed handout, flour 10kg…"><?= e($f['resources']) ?></textarea>
      </div>
    </div>
    <div class="btnrow">
      <button class="btn btn--primary"><?= icon('check', 16) ?> <?= $rec ? 'Resubmit plan' : 'Submit plan' ?></button>
      <a class="btn btn--ghost" href="<?= e(url('lessonplans.index')) ?>">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>
