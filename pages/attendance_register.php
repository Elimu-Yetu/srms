<?php
/** The register: every student against every session date in a month. */
require_once BASE_PATH . '/views/icons.php';

$mine    = my_course_ids();
$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list($mine) . ') ORDER BY name');
if (!$courses) {
    echo '<div class="panel"><div class="empty"><div class="empty__mark">' . icon('list', 22) . '</div>'
       . '<h3>No class in your scope</h3><p>The register opens once a course is assigned to you.</p></div></div>';
    return;
}
$courseId = getInt('course_id') ?: (int) $courses[0]['id'];
$month    = getStr('month') ?: date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
$course = require_course_access($courseId);

$from = $month . '-01';
$to   = date('Y-m-t', strtotime($from));

$dates = array_column(rows('SELECT DISTINCT session_date FROM attendance WHERE course_id = ? AND session_date BETWEEN ? AND ?
                            ORDER BY session_date', [$courseId, $from, $to]), 'session_date');

$class = rows("SELECT e.id AS enrolment_id, s.id, s.first_name, s.last_name, s.student_no
               FROM enrolments e JOIN students s ON s.id = e.student_id
               WHERE e.course_id = ? ORDER BY s.first_name, s.last_name", [$courseId]);

$marks = [];
foreach (rows('SELECT a.enrolment_id, a.session_date, a.status FROM attendance a
               WHERE a.course_id = ? AND a.session_date BETWEEN ? AND ?', [$courseId, $from, $to]) as $m) {
    $marks[(int) $m['enrolment_id']][$m['session_date']] = $m['status'];
}
$threshold = (int) setting('attendance_threshold', '80');
$page_sub  = e($course['code']) . ' · ' . e(month_label($month)) . ' · ' . count($dates) . ' session' . (count($dates) === 1 ? '' : 's');
$page_actions = '<a class="btn" target="_blank" href="' . e(url('reports.show', ['type' => 'register', 'course_id' => $courseId, 'month' => $month])) . '">' . icon('printer', 16) . ' Print</a>';
?>
<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="attendance.register">
    <div class="field grow">
      <label for="course_id">Class</label>
      <select id="course_id" name="course_id" data-autosubmit>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $courseId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="month">Month</label>
      <input id="month" name="month" type="month" value="<?= e($month) ?>" data-autosubmit>
    </div>
    <button class="btn"><?= icon('search', 16) ?> Show</button>
  </form>

  <?php if (!$dates): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('calendar', 22) ?></div>
      <h3>Nothing recorded in <?= e(month_label($month)) ?></h3>
      <p>Pick another month, or record today's session.</p>
      <?php if (can('attendance.mark')): ?>
        <a class="btn btn--primary" href="<?= e(url('attendance.mark', ['course_id' => $courseId])) ?>">Take attendance</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data compact">
        <thead>
          <tr>
            <th style="min-width:180px">Student</th>
            <?php foreach ($dates as $dt): ?>
              <th class="center mono" title="<?= e(d($dt)) ?>"><?= e(date('d', strtotime($dt))) ?></th>
            <?php endforeach; ?>
            <th class="right">Rate</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($class as $s):
          $eid = (int) $s['enrolment_id']; $ok = 0; $tot = 0; ?>
          <tr>
            <td><a href="<?= e(url('students.view', ['id' => $s['id']])) ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></a>
                <div class="tiny mono muted"><?= e($s['student_no']) ?></div></td>
            <?php foreach ($dates as $dt):
              $code = $marks[$eid][$dt] ?? null;
              if ($code) { $tot++; if (in_array($code, ['P', 'L'], true)) $ok++; }
              $tone = ['P' => 'green', 'L' => 'orange', 'E' => 'blue', 'A' => 'red'][$code] ?? 'neutral'; ?>
              <td class="center"><?= $code ? badge($code, $tone) : '<span class="muted tiny">·</span>' ?></td>
            <?php endforeach; ?>
            <?php $rate = pct($ok, $tot); ?>
            <td class="right"><?= $tot ? badge($rate . '%', $rate < $threshold ? 'red' : 'green') : '<span class="tiny muted">—</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="panel__foot">
      <span class="tiny muted">P present · L late · E excused · A absent · rate counts present and late as attended</span>
    </div>
  <?php endif; ?>
</div>
