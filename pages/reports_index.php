<?php
/** Report hub. Every report opens print-ready in a new tab. */
require_once BASE_PATH . '/views/icons.php';

$mine    = my_course_ids();
$courses = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list($mine) . ') ORDER BY name');
[$scope, $sArgs] = dept_scope('d.id');
$depts   = rows("SELECT d.id, d.name FROM departments d WHERE 1=1 $scope ORDER BY d.name", $sArgs);

$reports = [
    'enrolment'  => ['Enrolment summary', 'Students per department and course, with capacity and free places.', 'users', false],
    'attendance' => ['Attendance summary', 'Per-student attendance rate for a class over a month, flagging anyone below the threshold.', 'check', true],
    'register'   => ['Class register', 'The full grid of marks for every session in a month — the sheet you sign and file.', 'list', true],
    'marks'      => ['Marks sheet', 'Every assessment for a class with scores, weighted totals and the class average.', 'star', true],
    'monthly'    => ['Facilitator reporting status', 'Which facilitators have submitted their monthly report, and which have not.', 'calendar-check', false],
    'certificates' => ['Certificates issued', 'Every certificate with its serial, verification code and issue date.', 'award', false],
    'kitchen'    => ['Kitchen service', 'Tea and meals served, teachers against students, day by day.', 'kitchen', false],
];
if (!can('kitchen.view')) unset($reports['kitchen']);
if (!is_admin()) unset($reports['certificates']);

$page_sub = 'Each report opens as a print-ready sheet. Use your browser\'s print dialog and choose "Save as PDF" to keep a copy.';
?>
<div class="grid grid--2">
  <?php foreach ($reports as $key => [$title, $desc, $ic, $needsCourse]): ?>
    <div class="panel">
      <div class="panel__body">
        <div style="display:flex;gap:12px;align-items:flex-start">
          <span class="empty__mark" style="width:38px;height:38px;flex:0 0 auto"><?= icon($ic, 18) ?></span>
          <div style="min-width:0">
            <h2 style="margin:0"><?= e($title) ?></h2>
            <p class="tiny muted" style="margin:6px 0 0;line-height:1.6"><?= e($desc) ?></p>
          </div>
        </div>

        <form method="get" target="_blank" style="margin-top:14px">
          <input type="hidden" name="r" value="reports.show">
          <input type="hidden" name="type" value="<?= e($key) ?>">
          <div class="toolbar" style="padding:0;border:0;background:none;gap:8px">
            <?php if ($needsCourse): ?>
              <div class="field grow">
                <label>Class</label>
                <select name="course_id" required>
                  <?php foreach ($courses as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= e($c['code']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label>Month</label>
                <input name="month" type="month" value="<?= date('Y-m') ?>">
              </div>
            <?php elseif ($key === 'enrolment'): ?>
              <div class="field grow">
                <label>Department</label>
                <select name="department_id">
                  <option value="">All in scope</option>
                  <?php foreach ($depts as $d): ?>
                    <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php elseif ($key === 'monthly' || $key === 'kitchen'): ?>
              <div class="field grow">
                <label>Month</label>
                <input name="month" type="month" value="<?= date('Y-m') ?>">
              </div>
            <?php else: ?>
              <div class="field grow">
                <label>Year</label>
                <input name="year" type="number" min="2000" max="2100" value="<?= date('Y') ?>">
              </div>
            <?php endif; ?>
            <button class="btn btn--primary"><?= icon('printer', 16) ?> Open</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="panel" style="margin-top:16px">
  <div class="panel__head"><h2>Data exports</h2></div>
  <div class="panel__body" style="display:flex;gap:10px;flex-wrap:wrap">
    <?php if (can('students.view')): ?>
      <a class="btn" href="<?= e(url('students.export')) ?>"><?= icon('download', 16) ?> Students (CSV)</a>
    <?php endif; ?>
    <?php if (can('backup.run')): ?>
      <a class="btn" href="<?= e(url('backup')) ?>"><?= icon('database', 16) ?> Database backup</a>
    <?php endif; ?>
    <span class="tiny muted" style="align-self:center">CSV files open in Excel or LibreOffice Calc.</span>
  </div>
</div>
