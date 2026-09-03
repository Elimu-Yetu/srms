<?php
/** The lesson plan form — deliberately short, the same five fields every time. */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
// Facilitators may only edit their own plans; admins/managers may edit any plan.
if ($id) {
  if (is_role('facilitator')) {
    $rec = row('SELECT * FROM lesson_plans WHERE id = ? AND facilitator_id = ?', [$id, user_id()]);
    if (!$rec) deny('You can only edit your own lesson plans.');
    if ($rec['review_status'] === 'reviewed') {
      flash('warn', 'That plan has been reviewed already, so it is now read-only.');
      redirect('lessonplans.view', ['id' => $id]);
    }
  } else {
    $rec = row('SELECT * FROM lesson_plans WHERE id = ?', [$id]);
    if (!$rec) { flash('error', 'That lesson plan was not found.'); redirect('lessonplans.index'); }
  }
} else {
  $rec = null;
}
$page_title = $rec ? 'Edit lesson plan' : 'New lesson plan';

$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list(my_course_ids()) . ') ORDER BY name');
$errors  = [];
$f = $rec ?: ['course_id' => getInt('course_id'), 'plan_date' => date('Y-m-d'), 'topic' => '',
              'lesson_name' => '', 'duration_mins' => '', 'unit' => '', 'step' => '', 'classes_taught' => '',
              'leader_teacher' => '', 'assistants' => '', 'start_time' => '', 'end_time' => '',
              'registered_boys' => '', 'registered_girls' => '', 'registered_total' => '',
              'attended_boys' => '', 'attended_girls' => '', 'attended_total' => '',
              'knowledge' => '', 'skills' => '', 'heart' => '', 'practical' => '',
              'activities' => '', 'teaching_methods' => '', 'references' => '', 'equipment' => '',
              'comments' => '', 'challenges' => '', 'suggested_changes' => ''];

if (is_post()) {
    csrf_check();
    // collect simple fields
    foreach (['topic','lesson_name','duration_mins','unit','step','classes_taught','leader_teacher','assistants','start_time','end_time','teaching_methods','references','equipment','comments','challenges','suggested_changes'] as $k) $f[$k] = post($k);
    // activities come as rich HTML
    $f['activities'] = post('activities');
    // learning goals
    $f['knowledge'] = post('knowledge');
    $f['skills'] = post('skills');
    $f['heart'] = post('heart');
    $f['practical'] = post('practical');
    $f['course_id'] = postInt('course_id');
    $f['plan_date'] = post('plan_date');

    if (!in_array($f['course_id'], array_map('intval', my_course_ids()), true)) $errors[] = 'Choose one of your courses.';
    if (!strtotime((string) $f['plan_date'])) $errors[] = 'Enter the date of the lesson.';
    if ($f['topic'] === '') $errors[] = 'Give the lesson a topic.';
    if ($f['knowledge'] === '' && $f['skills'] === '' && $f['heart'] === '' && $f['practical'] === '') $errors[] = 'List at least one learning objective.';

    if (!$errors) {
        // store learning goals as JSON in objectives, activities as HTML, resources as JSON
        $objectives = json_encode([
          'knowledge' => $f['knowledge'], 'skills' => $f['skills'], 'heart' => $f['heart'], 'practical' => $f['practical']
        ]);
        $resources = json_encode([
          'teaching_methods' => $f['teaching_methods'], 'references' => $f['references'], 'equipment' => $f['equipment'],
          'lesson_name' => $f['lesson_name'], 'duration_mins' => $f['duration_mins'], 'unit' => $f['unit'], 'step' => $f['step'], 'classes_taught' => $f['classes_taught'],
          'leader_teacher' => $f['leader_teacher'], 'assistants' => $f['assistants'], 'start_time' => $f['start_time'], 'end_time' => $f['end_time'],
          'registered' => ['boys' => $f['registered_boys'], 'girls' => $f['registered_girls'], 'total' => $f['registered_total']],
          'attended'  => ['boys' => $f['attended_boys'], 'girls' => $f['attended_girls'], 'total' => $f['attended_total']],
          'teacher_notes' => ['comments' => $f['comments'], 'challenges' => $f['challenges'], 'suggested_changes' => $f['suggested_changes']]
        ]);

        $data = [
          'course_id' => $f['course_id'], 'plan_date' => $f['plan_date'], 'topic' => $f['topic'],
          'objectives' => $objectives, 'activities' => $f['activities'], 'resources' => $resources,
        ];
        if ($rec) {
          // facilitators re-submit for review; admins/managers save as reviewed
          $data['review_status'] = is_role('facilitator') ? 'submitted' : 'reviewed';
          update('lesson_plans', $data, 'id = :wid', ['wid' => $id]);
          audit('update', 'lesson_plans', $id, $f['topic']);
          flash('ok', 'Lesson plan updated.' . (is_role('facilitator') ? ' Sent for review.' : ''));
        } else {
          // facilitator becomes owner; admins create without being the facilitator and mark reviewed
          $data['facilitator_id'] = is_role('facilitator') ? user_id() : null;
          $data['review_status']  = is_role('facilitator') ? 'submitted' : 'reviewed';
          $data['created_at']     = now();
          $id = insert('lesson_plans', $data);
          audit('create', 'lesson_plans', $id, $f['topic']);
          flash('ok', 'Lesson plan ' . (is_role('facilitator') ? 'submitted. Your line manager can see it now.' : 'created.'));
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
<?php if (is_role('facilitator')): ?>
<script>
// Load Quill for activities editor and ensure submit stores HTML
var qActivities;
(function(){
  var s1 = document.createElement('script'); s1.src = 'assets/js/quill.min.js';
  s1.onload = function(){
    qActivities = new Quill('#quill-activities', { theme: 'snow', modules: { toolbar: [ ['bold','italic','underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote','code-block'], ['link'] ] } });
    // populate
    try { qActivities.root.innerHTML = document.getElementById('activities').value || ''; } catch(e){}
    document.querySelector('form').addEventListener('submit', function(){ document.getElementById('activities').value = qActivities.root.innerHTML; });
  };
  document.head.appendChild(s1);
})();

// Load template into new fields if needed
document.getElementById('load-template')?.addEventListener('click', function(){
  if (!confirm('Load example lesson plan template? This will replace current content.')) return;
  document.getElementById('lesson_name').value = 'Sample lesson';
  document.getElementById('topic').value = 'Sample topic';
  if (qActivities) qActivities.root.innerHTML = '<p>Activity 1: Demonstration</p><p>Activity 2: Practice</p>';
  document.getElementById('knowledge').value = 'Identify key concepts';
  document.getElementById('skills').value = 'Perform task X';
  document.getElementById('heart').value = 'Show respect for safety';
  document.getElementById('practical').value = 'Complete hands-on exercise';
});
</script>
<?php endif; ?>
      </div>
      <div class="field span2">
        <label for="classes_taught">Classes taught (using this lesson plan)</label>
        <input id="classes_taught" name="classes_taught" value="<?= e($f['classes_taught']) ?>" placeholder="e.g. Form 2A, 2B">
      </div>
      <div class="field">
        <label for="leader_teacher">Leader teacher</label>
        <input id="leader_teacher" name="leader_teacher" value="<?= e($f['leader_teacher']) ?>">
      </div>
      <div class="field">
        <label for="assistants">Assistants</label>
        <input id="assistants" name="assistants" value="<?= e($f['assistants']) ?>" placeholder="Comma-separated">
      </div>
      <div class="field">
        <label for="start_time">Start time</label>
        <input id="start_time" name="start_time" type="time" value="<?= e($f['start_time']) ?>">
      </div>
      <div class="field">
        <label for="end_time">End time</label>
        <input id="end_time" name="end_time" type="time" value="<?= e($f['end_time']) ?>">
      </div>

      <div class="field">
        <label for="duration_mins">Duration (mins)</label>
        <input id="duration_mins" name="duration_mins" type="number" min="0" value="<?= e($f['duration_mins']) ?>">
      </div>
      <div class="field">
        <label for="unit">Unit</label>
        <input id="unit" name="unit" value="<?= e($f['unit']) ?>">
      </div>
      <div class="field">
        <label for="step">Step</label>
        <input id="step" name="step" value="<?= e($f['step']) ?>">
      </div>

      <div class="field span2">
        <label>Activities planned</label>
        <input type="hidden" id="activities" name="activities" value="<?= e($f['activities']) ?>">
        <link href="assets/css/quill.snow.css" rel="stylesheet">
        <div id="quill-activities" style="height:160px;background:#fff;border:1px solid var(--line);border-radius:6px;overflow:auto"></div>
      </div>

      <div class="field span2">
        <label>Learning goals</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label for="knowledge">Knowledge</label>
            <textarea id="knowledge" name="knowledge" rows="3"><?= e($f['knowledge']) ?></textarea>
          </div>
          <div>
            <label for="skills">Skills</label>
            <textarea id="skills" name="skills" rows="3"><?= e($f['skills']) ?></textarea>
          </div>
          <div>
            <label for="heart">Heart (attitudes)</label>
            <textarea id="heart" name="heart" rows="3"><?= e($f['heart']) ?></textarea>
          </div>
          <div>
            <label for="practical">Practical</label>
            <textarea id="practical" name="practical" rows="3"><?= e($f['practical']) ?></textarea>
          </div>
        </div>
      </div>

      <!-- Attendance inputs moved to end of form -->


      <div class="field span2">
        <label for="teaching_methods">Teaching methods</label>
        <textarea id="teaching_methods" name="teaching_methods" rows="2"><?= e($f['teaching_methods']) ?></textarea>
      </div>
      <div class="field">
        <label for="references">References</label>
        <input id="references" name="references" value="<?= e($f['references']) ?>">
      </div>
      <div class="field">
        <label for="equipment">Equipment & Materials</label>
        <input id="equipment" name="equipment" value="<?= e($f['equipment']) ?>">
      </div>
      <div class="field span2">
        <label for="comments">Teacher notes — Comments</label>
        <textarea id="comments" name="comments" rows="3"><?= e($f['comments']) ?></textarea>
      </div>
      <div class="field span2">
        <label for="challenges">Teacher notes — Challenges</label>
        <textarea id="challenges" name="challenges" rows="3"><?= e($f['challenges']) ?></textarea>
      </div>
      <div class="field span2">
        <label for="suggested_changes">Teacher notes — Suggested changes</label>
        <textarea id="suggested_changes" name="suggested_changes" rows="3"><?= e($f['suggested_changes']) ?></textarea>
      </div>
      <div class="field span2">
        <label>Attendance — Registered</label>
        <div style="display:flex;gap:8px">
          <input id="registered_boys" name="registered_boys" placeholder="Boys" value="<?= e($f['registered_boys']) ?>">
          <input id="registered_girls" name="registered_girls" placeholder="Girls" value="<?= e($f['registered_girls']) ?>">
          <input id="registered_total" name="registered_total" placeholder="Total" value="<?= e($f['registered_total']) ?>">
        </div>
      </div>
      <div class="field span2">
        <label>Attendance — Attended</label>
        <div style="display:flex;gap:8px">
          <input id="attended_boys" name="attended_boys" placeholder="Boys" value="<?= e($f['attended_boys']) ?>">
          <input id="attended_girls" name="attended_girls" placeholder="Girls" value="<?= e($f['attended_girls']) ?>">
          <input id="attended_total" name="attended_total" placeholder="Total" value="<?= e($f['attended_total']) ?>">
        </div>
      </div>
    </div>
    <div class="btnrow">
      <button class="btn btn--primary"><?= icon('check', 16) ?> <?= $rec ? 'Resubmit plan' : 'Submit plan' ?></button>
      <a class="btn btn--ghost" href="<?= e(url('lessonplans.index')) ?>">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>
