<?php
/**
 * The monthly report, in the fixed format from the proposal:
 * month, class, sessions held, topics covered, attendance summary, challenges,
 * support needed, comments. The attendance figure is calculated for the
 * facilitator so the numbers in the report match the register.
 */
require_once BASE_PATH . '/views/icons.php';

$id  = getInt('id');
$rec = $id ? row('SELECT * FROM monthly_reports WHERE id = ? AND facilitator_id = ?', [$id, user_id()]) : null;
if ($id && !$rec) deny('You can only edit your own reports.');
if ($rec && $rec['status'] === 'acknowledged') {
    flash('warn', 'That report has been acknowledged, so it is now read-only.');
    redirect('monthly.view', ['id' => $id]);
}
$page_title = $rec ? 'Edit monthly report' : 'Monthly report';

$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list(my_course_ids()) . ') ORDER BY name');
$errors  = [];
$f = $rec ?: [
    'course_id' => getInt('course_id') ?: (int) ($courses[0]['id'] ?? 0),
    'month_year' => getStr('month') ?: date('Y-m'),
    'sessions_held' => 0, 'topics_covered' => '', 'attendance_summary' => '',
    'challenges' => '', 'support_needed' => '', 'comments' => '',
];

/** Attendance facts for the chosen class and month — offered, not invented. */
function month_attendance(int $courseId, string $ym): array
{
    $from = $ym . '-01';
    $to   = date('Y-m-t', strtotime($from));
    $r = row("SELECT COUNT(DISTINCT session_date) sessions, COUNT(*) marks,
                SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) present
              FROM attendance WHERE course_id = ? AND session_date BETWEEN ? AND ?", [$courseId, $from, $to]) ?: [];
    return [
        'sessions' => (int) ($r['sessions'] ?? 0),
        'marks'    => (int) ($r['marks'] ?? 0),
        'rate'     => pct((int) ($r['present'] ?? 0), (int) ($r['marks'] ?? 0)),
    ];
}

if (is_post()) {
    csrf_check();
    foreach (['topics_covered', 'attendance_summary', 'challenges', 'support_needed', 'comments'] as $k) $f[$k] = post($k);
    $f['course_id']     = postInt('course_id');
    $f['month_year']    = post('month_year');
    $f['sessions_held'] = max(0, postInt('sessions_held'));

    if (!in_array($f['course_id'], array_map('intval', my_course_ids()), true)) $errors[] = 'Choose one of your classes.';
    if (!preg_match('/^\d{4}-\d{2}$/', (string) $f['month_year'])) $errors[] = 'Choose the month being reported.';
    elseif ($f['month_year'] > date('Y-m')) $errors[] = 'You cannot report on a month that has not started.';
    if ($f['topics_covered'] === '') $errors[] = 'List the topics covered this month.';

    $clash = row('SELECT id FROM monthly_reports WHERE facilitator_id = ? AND course_id = ? AND month_year = ? AND id <> ?',
                 [user_id(), $f['course_id'], $f['month_year'], $id]);
    if ($clash) $errors[] = 'You already submitted a report for that class and month. <a href="'
                          . e(url('monthly.view', ['id' => $clash['id']])) . '">Open it</a> to edit instead.';

    if (!$errors) {
        $data = [
            'course_id' => $f['course_id'], 'month_year' => $f['month_year'],
            'sessions_held' => $f['sessions_held'], 'topics_covered' => $f['topics_covered'],
            'attendance_summary' => $f['attendance_summary'], 'challenges' => $f['challenges'],
            'support_needed' => $f['support_needed'], 'comments' => $f['comments'], 'status' => 'submitted',
        ];
        if ($rec) {
            update('monthly_reports', $data, 'id = :wid', ['wid' => $id]);
            audit('update', 'monthly_reports', $id, $f['month_year']);
            flash('ok', 'Report updated and sent for review again.');
        } else {
            $data['facilitator_id'] = user_id();
            $data['created_at'] = now();
            $id = insert('monthly_reports', $data);
            audit('create', 'monthly_reports', $id, $f['month_year']);
            flash('ok', 'Monthly report submitted.');
        }
        redirect('monthly.view', ['id' => $id]);
    }
}

$facts = $f['course_id'] ? month_attendance((int) $f['course_id'], (string) $f['month_year']) : ['sessions' => 0, 'marks' => 0, 'rate' => 0];
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<?php if (!$courses): ?>
  <div class="panel"><div class="empty"><h3>No class assigned to you</h3>
    <p>Ask the office to set you as the facilitator of a course.</p></div></div>
<?php else: ?>
<div class="grid grid--sidebar">
  <div class="panel">
    <div class="panel__head"><h2>Report</h2><span class="tiny muted">All fields fit on one screen, by design</span></div>
    <form method="post" class="panel__body">
      <?= csrf_field() ?>
      <div class="formgrid formgrid--3">
        <div class="field">
          <label for="course_id">Class <span class="req">*</span></label>
          <select id="course_id" name="course_id" required onchange="this.form.submit()">
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= (int) $f['course_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="month_year">Month <span class="req">*</span></label>
          <input id="month_year" name="month_year" type="month" value="<?= e($f['month_year']) ?>" max="<?= date('Y-m') ?>" required>
        </div>
        <div class="field">
          <label for="sessions_held">Sessions held</label>
          <input id="sessions_held" name="sessions_held" type="number" min="0" max="200" value="<?= (int) $f['sessions_held'] ?>">
          <div class="hint">Register shows <?= $facts['sessions'] ?>.</div>
        </div>
      </div>

      <div class="field">
        <label for="topics_covered">Topics covered <span class="req">*</span></label>
        <input type="hidden" id="topics_covered" name="topics_covered" value="<?= e($f['topics_covered']) ?>">
        <link href="assets/css/quill.snow.css" rel="stylesheet">
        <div id="quill-topics" style="height:260px;background:#fff;border:1px solid var(--line);border-radius:6px;overflow:auto"></div>
      </div>
      <div class="field">
        <label for="attendance_summary">Attendance summary</label>
        <textarea id="attendance_summary" name="attendance_summary" rows="2" placeholder="e.g. Average attendance 86%. Two students below threshold."><?= e($f['attendance_summary']) ?></textarea>
        <div class="hint">Register for this month: <?= $facts['rate'] ?>% across <?= $facts['marks'] ?> marks.</div>
      </div>
      <div class="field">
        <label for="challenges">Student challenges observed</label>
        <textarea id="challenges" name="challenges" rows="3"><?= e($f['challenges']) ?></textarea>
      </div>
      <div class="field">
        <label for="support_needed">Support needed</label>
        <textarea id="support_needed" name="support_needed" rows="3" placeholder="Equipment, materials, staffing — what management can act on."><?= e($f['support_needed']) ?></textarea>
      </div>
      <div class="field">
        <label for="comments">General comments</label>
        <textarea id="comments" name="comments" rows="2"><?= e($f['comments']) ?></textarea>
      </div>

      <div class="btnrow">
        <button class="btn btn--primary"><?= icon('check', 16) ?> <?= $rec ? 'Resubmit report' : 'Submit report' ?></button>
        <?php if (is_role('facilitator')): ?>
          <button type="button" id="load-template" class="btn btn--ghost"><?= icon('file', 14) ?> Load template</button>
        <?php endif; ?>
        <a class="btn btn--ghost" href="<?= e(url('monthly.index')) ?>">Cancel</a>
      </div>
    </form>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>From the register</h2></div>
    <div class="panel__body">
      <p class="tiny muted">These come from attendance already recorded for this class in <?= e(month_label((string) $f['month_year'])) ?>.
         Use them so the report and the register agree.</p>
      <div class="grid" style="gap:10px;margin-top:10px">
        <div class="stat" style="box-shadow:none">
          <div class="stat__label">Session dates</div><div class="stat__value"><?= $facts['sessions'] ?></div>
        </div>
        <div class="stat stat--green" style="box-shadow:none">
          <div class="stat__label">Attendance rate</div><div class="stat__value"><?= $facts['rate'] ?><small>%</small></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php if (is_role('facilitator')): ?>
<script>
// Populate the monthly report form with an example template.
// Load Quill editor
var quill;
(function(){
  var s1 = document.createElement('script');
  s1.src = 'assets/js/quill.min.js';
  s1.onload = function(){
    quill = new Quill('#quill-topics', { theme: 'snow', modules: { toolbar: [ [{ 'header': [1,2,3,false] } ], ['bold','italic','underline','strike'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote','code-block'], ['link','image'] ] } });
    // Populate editor with existing content (safe HTML stored as value)
    var existing = document.getElementById('topics_covered').value || '';
    try { quill.root.innerHTML = existing; } catch(e) { quill.setText(existing); }
    // Ensure form submits HTML
    document.querySelector('form').addEventListener('submit', function(){
      document.getElementById('topics_covered').value = quill.root.innerHTML;
    });
  };
  document.head.appendChild(s1);
})();

document.getElementById('load-template')?.addEventListener('click', function(){
  if (!confirm('Load the example monthly report template? Existing content will be replaced.')) return;
  var tpl = `
<p>Week 1: Introduction and safety briefing</p>
<p>Week 2: Practical skills - equipment handling</p>
<p>Week 3: Theory - food hygiene</p>
<p>Week 4: Assessment and revision</p>`;
  if (quill) quill.root.innerHTML = tpl; else document.getElementById('topics_covered').value = tpl;
  document.getElementById('attendance_summary').value = `Average attendance: 88%\nNotable absentees: Student A (illness), Student B (late)`;
  document.getElementById('challenges').value = `Shortage of consumables (spices and packaging). One practical session interrupted by power outage.`;
  document.getElementById('support_needed').value = `Request replacement gas cylinder and 10 sets of protective aprons. Consider backup power for practical sessions.`;
  document.getElementById('comments').value = `Learners showed good engagement; two students require remedial support in knife skills.`;
  // scroll to top of form
  window.scrollTo({top: document.querySelector('form').offsetTop - 20, behavior: 'smooth'});
});
</script>
<?php endif; ?>
