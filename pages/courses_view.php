<?php
/** One course: who is in it, when it meets, how attendance is going. */
require_once BASE_PATH . '/views/icons.php';

$id = getInt('id');
$c  = row('SELECT c.*, d.name AS dept, u.name AS facilitator
           FROM courses c LEFT JOIN departments d ON d.id = c.department_id
           LEFT JOIN users u ON u.id = c.facilitator_id WHERE c.id = ?', [$id]);
if (!$c) { flash('error', 'That course was not found.'); redirect('courses.index'); }
if (!in_array($id, array_map('intval', my_course_ids()), true)) deny('That course is not in your scope.');

$page_title = $c['name'];
$page_sub   = '<span class="mono">' . e($c['code']) . '</span> · ' . e($c['dept'] ?? '—')
            . ' · ' . e($c['facilitator'] ?? 'no facilitator assigned');
$page_actions = '';
if (can('courses.manage')) {
    $page_actions .= '<a class="btn" href="' . e(url('courses.form', ['id' => $id])) . '">' . icon('edit', 16) . ' Edit</a>';
}
if (can('attendance.mark')) {
    $page_actions .= '<a class="btn btn--green" href="' . e(url('attendance.mark', ['course_id' => $id])) . '">' . icon('check', 16) . ' Attendance</a>';
}

$threshold = (int) setting('attendance_threshold', '80');
$class = rows("SELECT e.id AS enrolment_id, e.status AS enrol_status, s.*,
                 (SELECT COUNT(*) FROM attendance a WHERE a.enrolment_id = e.id) AS sessions,
                 (SELECT COUNT(*) FROM attendance a WHERE a.enrolment_id = e.id AND a.status = 'P') AS attended
               FROM enrolments e JOIN students s ON s.id = e.student_id
               WHERE e.course_id = ? ORDER BY s.first_name, s.last_name", [$id]);

$slots = rows('SELECT t.*, u.name AS facilitator FROM timetable t LEFT JOIN users u ON u.id = t.facilitator_id
               WHERE t.course_id = ? ORDER BY t.day_of_week, t.start_time', [$id]);
$assessments = rows('SELECT a.*, (SELECT COUNT(*) FROM marks m WHERE m.assessment_id = a.id) AS marked
                     FROM assessments a WHERE a.course_id = ? ORDER BY a.due_date DESC, a.id DESC', [$id]);
$sessionCount = (int) val('SELECT COUNT(DISTINCT session_date) FROM attendance WHERE course_id = ?', [$id], 0);
?>

<div class="grid grid--4">
  <div class="stat">
    <div class="stat__label">Enrolled</div>
    <div class="stat__value"><?= count($class) ?><small> / <?= (int) $c['capacity'] ?></small></div>
    <div class="stat__note"><?= max(0, (int) $c['capacity'] - count($class)) ?> places left</div>
  </div>
  <div class="stat stat--green">
    <div class="stat__label">Sessions recorded</div>
    <div class="stat__value"><?= $sessionCount ?></div>
    <div class="stat__note">Distinct dates in the register</div>
  </div>
  <div class="stat stat--orange">
    <div class="stat__label">Duration</div>
    <div class="stat__value"><?= (int) $c['duration_weeks'] ?><small> wks</small></div>
    <div class="stat__note"><?= (int) $c['duration_weeks'] ?> weeks training</div>
  </div>
  <!-- start/end dates removed from course view stats -->
</div>

<?php if ($c['description']): ?>
  <div class="panel" style="margin-top:16px"><div class="panel__body tiny"><?= nl2br(e($c['description'])) ?></div></div>
<?php endif; ?>

<div class="grid grid--sidebar" style="margin-top:16px">
  <div class="stack">
    <div class="panel">
      <div class="panel__head">
        <h2>Class list</h2>
        <input type="search" data-filter="#classtable" placeholder="Filter…" style="max-width:180px">
      </div>
      <?php if (!$class): ?>
        <div class="empty" style="padding:28px">
          <div class="empty__mark"><?= icon('users', 20) ?></div>
          <h3>Nobody enrolled yet</h3>
          <p>Enrol students from their file, or during registration.</p>
          <?php if (can('students.manage')): ?>
            <a class="btn btn--primary" href="<?= e(url('students.form')) ?>">Register a student</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="tablewrap">
          <table class="data" id="classtable">
            <thead><tr><th>Student</th><th>Phone</th><th class="right">Attendance</th><th>Enrolment</th></tr></thead>
            <tbody>
            <?php foreach ($class as $s):
              $full = $s['first_name'] . ' ' . $s['last_name'];
              $rate = pct((int) $s['attended'], (int) $s['sessions']); ?>
              <tr>
                <td>
                  <a class="person" href="<?= e(url('students.view', ['id' => $s['id']])) ?>">
                    <?php if ($s['photo']): ?><img class="avatar" src="<?= e(photo_url($s['photo'])) ?>" alt="">
                    <?php else: ?><span class="avatar"><?= e(initials($full)) ?></span><?php endif; ?>
                    <span><span class="person__name"><?= e($full) ?></span><br>
                      <span class="person__meta"><?= e($s['student_no']) ?></span></span>
                  </a>
                </td>
                <td class="tiny mono"><?= e($s['phone'] ?: '—') ?></td>
                <td class="right">
                  <?php if ((int) $s['sessions'] === 0): ?>
                    <span class="tiny muted">no data</span>
                  <?php else: ?>
                    <?= badge($rate . '%', $rate < $threshold ? 'red' : 'green') ?>
                    <div class="tiny muted mono"><?= (int) $s['attended'] ?>/<?= (int) $s['sessions'] ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?= badge(ucfirst($s['enrol_status']), status_tone($s['enrol_status'])) ?>
                  <?php if (can('students.enrol')): ?>
                    <form method="post" action="<?= e(url('enrolments.act')) ?>" style="display:inline;margin-left:6px">
                      <?= csrf_field() ?>
                      <input type="hidden" name="do" value="unenrol">
                      <input type="hidden" name="enrolment_id" value="<?= (int) $s['enrolment_id'] ?>">
                      <input type="hidden" name="student_id" value="<?= (int) $s['id'] ?>">
                      <button class="btn btn--sm btn--ghost" data-confirm="Remove this student from the course?" title="Unenrol"><?= icon('x', 12) ?></button>
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

    <?php if (can('marks.manage') || is_role('superadmin', 'admin')): ?>
      <div class="panel">
        <div class="panel__head"><h2>Assessments</h2>
          <?php if (can('marks.manage')): ?>
            <a class="btn btn--sm" href="<?= e(url('assessments.index', ['course_id' => $id])) ?>"><?= icon('plus', 15) ?> Manage</a>
          <?php endif; ?>
        </div>
        <?php if (!$assessments): ?>
          <div class="panel__body"><p class="tiny muted">No assessments set for this course yet.</p></div>
        <?php else: ?>
          <table class="data compact">
            <thead><tr><th>Assessment</th><th>Due</th><th class="right">Marked</th></tr></thead>
            <tbody>
            <?php foreach ($assessments as $a): ?>
              <tr>
                <td><?= e($a['name']) ?><div class="tiny muted"><?= e(ucfirst($a['kind'])) ?> · out of <?= (int) $a['max_score'] ?> · weight <?= (int) $a['weight'] ?>%</div></td>
                <td class="tiny mono"><?= e(d($a['due_date'])) ?></td>
                <td class="right">
                  <?php if (can('marks.manage')): ?>
                    <a class="btn btn--sm" href="<?= e(url('marks.enter', ['assessment_id' => $a['id']])) ?>"><?= (int) $a['marked'] ?>/<?= count($class) ?> · enter</a>
                  <?php else: ?>
                    <span class="mono tiny"><?= (int) $a['marked'] ?>/<?= count($class) ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Weekly timetable</h2>
        <?php if (can('timetable.manage')): ?>
          <a class="btn btn--sm" href="<?= e(url('timetable.index', ['course_id' => $id])) ?>"><?= icon('edit', 15) ?></a>
        <?php endif; ?>
      </div>
      <?php if (!$slots): ?>
        <div class="panel__body"><p class="tiny muted">No timetable slots yet.
          <?php if (can('timetable.manage')): ?><a href="<?= e(url('timetable.index', ['course_id' => $id])) ?>">Add one</a>.<?php endif; ?></p></div>
      <?php else: ?>
        <div class="panel__body" style="display:grid;gap:8px">
          <?php foreach ($slots as $t): ?>
            <div class="ttcard" style="margin:0">
              <div class="ttcard__time"><?= e(day_name((int) $t['day_of_week'])) ?> · <?= e($t['start_time']) ?>–<?= e($t['end_time']) ?></div>
              <div class="ttcard__subject"><?= e($t['subject']) ?></div>
              <div class="ttcard__meta"><?= e($t['room'] ?: 'Room TBA') ?><?= $t['facilitator'] ? ' · ' . e($t['facilitator']) : '' ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Reports</h2></div>
      <div class="panel__body" style="display:grid;gap:8px">
        <a class="btn" target="_blank" href="<?= e(url('reports.show', ['type' => 'attendance', 'course_id' => $id])) ?>"><?= icon('printer', 16) ?> Attendance summary</a>
        <a class="btn" target="_blank" href="<?= e(url('reports.show', ['type' => 'register', 'course_id' => $id])) ?>"><?= icon('printer', 16) ?> Class register</a>
        <a class="btn" target="_blank" href="<?= e(url('reports.show', ['type' => 'marks', 'course_id' => $id])) ?>"><?= icon('printer', 16) ?> Marks sheet</a>
        <?php if (can('idcards.manage')): ?>
          <a class="btn" target="_blank" href="<?= e(url('idcards.print', ['course_id' => $id])) ?>"><?= icon('card', 16) ?> ID cards for this class</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
