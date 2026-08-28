<?php
/**
 * One page renders every report, because they all share the same letterhead,
 * the same signature block and the same print rules. $type picks the body.
 */
$type  = getStr('type', 'enrolment');
$month = getStr('month');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
$year     = getInt('year') ?: (int) date('Y');
$courseId = getInt('course_id');
$deptId   = getInt('department_id');

$from = $month . '-01';
$to   = date('Y-m-t', strtotime($from));

$st         = settings();
$threshold  = (int) ($st['attendance_threshold'] ?? 80);
$print_page = in_array($type, ['register', 'marks'], true) ? 'a4l' : 'a4';
$print_back = url('reports.index');

$titles = [
    'enrolment'    => 'Enrolment summary',
    'attendance'   => 'Attendance summary',
    'register'     => 'Class register',
    'marks'        => 'Marks sheet',
    'monthly'      => 'Facilitator reporting status',
    'certificates' => 'Certificates issued',
    'kitchen'      => 'Kitchen service report',
];
if (!isset($titles[$type])) { flash('error', 'Unknown report.'); redirect('reports.index'); }
if ($type === 'kitchen' && !can('kitchen.view')) deny();
if ($type === 'certificates' && !is_admin()) deny();

$course = null;
if (in_array($type, ['attendance', 'register', 'marks'], true)) {
    if (!$courseId) { flash('error', 'Choose a class for that report.'); redirect('reports.index'); }
    $course = require_course_access($courseId);
}

$page_title = $titles[$type];
$subtitle   = match ($type) {
    'attendance', 'register' => $course['code'] . ' · ' . month_label($month),
    'marks'        => $course['code'] . ' · ' . date('Y'),
    'monthly', 'kitchen' => month_label($month),
    'certificates' => 'Year ' . $year,
    default        => 'As at ' . date('d M Y'),
};

/** Shared letterhead. */
function report_head(array $st, string $title, string $subtitle, string $note = ''): void
{ ?>
  <div class="docribbon"><i></i><i></i><i></i></div>
  <div class="dochead">
    <div class="dochead__mark">EY</div>
    <div>
      <div class="dochead__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
      <div class="dochead__motto">"<?= e($st['org_motto'] ?? ORG_MOTTO) ?>"</div>
      <div class="dochead__meta"><?= e($st['org_address'] ?? '') ?></div>
    </div>
    <div class="dochead__right">
      <div class="doctitle"><?= e($title) ?></div>
      <div class="docref"><?= e($subtitle) ?></div>
      <?php if ($note): ?><div class="dochead__meta"><?= e($note) ?></div><?php endif; ?>
    </div>
  </div>
<?php }
?>
<div class="sheet sheet--<?= $print_page === 'a4l' ? 'a4l' : 'a4' ?>">
<?php

// ─────────────────────────────────────────────────────────── enrolment summary
if ($type === 'enrolment') {
    [$scope, $sArgs] = dept_scope('d.id');
    $args = $sArgs;
    $extra = '';
    if ($deptId) { $extra = ' AND d.id = ? '; $args[] = $deptId; }

    $list = rows("SELECT d.name AS dept, c.code, c.name AS course, c.capacity, c.status,
                    (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = 'active') act,
                    (SELECT COUNT(*) FROM enrolments e WHERE e.course_id = c.id AND e.status = 'completed') comp
                  FROM courses c LEFT JOIN departments d ON d.id = c.department_id
                  WHERE 1=1 $scope $extra ORDER BY d.name, c.code", $args);

    $tAct = 0; $tComp = 0; $tCap = 0;
    foreach ($list as $r) { $tAct += (int) $r['act']; $tComp += (int) $r['comp']; $tCap += (int) $r['capacity']; }

    $byStatus = rows("SELECT s.status, COUNT(*) n FROM students s
                      LEFT JOIN departments d ON d.id = s.department_id
                      WHERE 1=1 " . str_replace('d.id', 'd.id', $scope) . " GROUP BY s.status", $sArgs);

    report_head($st, $titles[$type], $subtitle, count($list) . ' courses');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Active enrolments</span><span class="pair__v"><?= $tAct ?></span></div>
      <div class="pair"><span class="pair__k">Completed</span><span class="pair__v"><?= $tComp ?></span></div>
      <div class="pair"><span class="pair__k">Total capacity</span><span class="pair__v"><?= $tCap ?></span></div>
      <div class="pair"><span class="pair__k">Free places</span><span class="pair__v"><?= max(0, $tCap - $tAct) ?></span></div>
    </div>

    <?php if ($byStatus): ?>
      <div class="docsection">Students by status</div>
      <div class="pairs">
        <?php foreach ($byStatus as $b): ?>
          <div class="pair"><span class="pair__k"><?= e(ucfirst($b['status'])) ?></span><span class="pair__v"><?= (int) $b['n'] ?></span></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="docsection">Courses</div>
    <table class="doc">
      <thead><tr><th>Department</th><th>Code</th><th>Course</th><th class="center">Active</th><th class="center">Completed</th><th class="center">Capacity</th><th class="center">Free</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($list as $r): ?>
        <tr>
          <td><?= e($r['dept'] ?? '—') ?></td>
          <td><?= e($r['code']) ?></td>
          <td><?= e($r['course']) ?></td>
          <td class="center"><?= (int) $r['act'] ?></td>
          <td class="center"><?= (int) $r['comp'] ?></td>
          <td class="center"><?= (int) $r['capacity'] ?></td>
          <td class="center"><?= max(0, (int) $r['capacity'] - (int) $r['act']) ?></td>
          <td><?= e(ucfirst($r['status'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$list): ?><tr><td colspan="8">No courses in scope.</td></tr><?php endif; ?>
      </tbody>
      <tfoot><tr><th colspan="3">Total</th><th class="center"><?= $tAct ?></th><th class="center"><?= $tComp ?></th><th class="center"><?= $tCap ?></th><th class="center"><?= max(0, $tCap - $tAct) ?></th><th></th></tr></tfoot>
    </table>
    <?php
}

// ────────────────────────────────────────────────────────── attendance summary
elseif ($type === 'attendance') {
    $list = rows("SELECT s.student_no, s.first_name, s.last_name,
                    COUNT(a.id) total,
                    SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) p,
                    SUM(CASE WHEN a.status='L' THEN 1 ELSE 0 END) l,
                    SUM(CASE WHEN a.status='E' THEN 1 ELSE 0 END) x,
                    SUM(CASE WHEN a.status='A' THEN 1 ELSE 0 END) ab
                  FROM enrolments e JOIN students s ON s.id = e.student_id
                  LEFT JOIN attendance a ON a.enrolment_id = e.id AND a.session_date BETWEEN ? AND ?
                  WHERE e.course_id = ?
                  GROUP BY s.id, s.student_no, s.first_name, s.last_name
                  ORDER BY s.first_name, s.last_name", [$from, $to, $courseId]);
    $sessions = (int) val('SELECT COUNT(DISTINCT session_date) FROM attendance WHERE course_id = ? AND session_date BETWEEN ? AND ?',
                          [$courseId, $from, $to], 0);
    $below = 0;
    foreach ($list as $r) if ((int) $r['total'] && pct((int) $r['p'] + (int) $r['l'], (int) $r['total']) < $threshold) $below++;

    report_head($st, $titles[$type], $subtitle, $sessions . ' sessions · threshold ' . $threshold . '%');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Course</span><span class="pair__v"><?= e($course['name']) ?></span></div>
      <div class="pair"><span class="pair__k">Department</span><span class="pair__v"><?= e($course['dept_name'] ?? '—') ?></span></div>
      <div class="pair"><span class="pair__k">Students</span><span class="pair__v"><?= count($list) ?></span></div>
      <div class="pair"><span class="pair__k">Below threshold</span><span class="pair__v"><?= $below ?></span></div>
    </div>

    <div class="docsection">Per student</div>
    <table class="doc">
      <thead><tr><th>Student no.</th><th>Name</th><th class="center">Present</th><th class="center">Late</th><th class="center">Excused</th><th class="center">Absent</th><th class="center">Sessions</th><th class="center">Rate</th><th>Flag</th></tr></thead>
      <tbody>
      <?php foreach ($list as $r):
        $tot = (int) $r['total'];
        $rate = pct((int) $r['p'] + (int) $r['l'], $tot); ?>
        <tr>
          <td><?= e($r['student_no']) ?></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
          <td class="center"><?= (int) $r['p'] ?></td>
          <td class="center"><?= (int) $r['l'] ?></td>
          <td class="center"><?= (int) $r['x'] ?></td>
          <td class="center"><?= (int) $r['ab'] ?></td>
          <td class="center"><?= $tot ?></td>
          <td class="center"><?= $tot ? $rate . '%' : '—' ?></td>
          <td><?= $tot && $rate < $threshold ? 'Below threshold' : '' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$list): ?><tr><td colspan="9">Nobody enrolled in this class.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <?php
}

// ───────────────────────────────────────────────────────────── class register
elseif ($type === 'register') {
    $dates = array_column(rows('SELECT DISTINCT session_date FROM attendance WHERE course_id = ? AND session_date BETWEEN ? AND ? ORDER BY session_date',
                               [$courseId, $from, $to]), 'session_date');
    $class = rows("SELECT e.id AS enrolment_id, s.student_no, s.first_name, s.last_name
                   FROM enrolments e JOIN students s ON s.id = e.student_id
                   WHERE e.course_id = ? ORDER BY s.first_name, s.last_name", [$courseId]);
    $marks = [];
    foreach (rows('SELECT enrolment_id, session_date, status FROM attendance WHERE course_id = ? AND session_date BETWEEN ? AND ?',
                  [$courseId, $from, $to]) as $m) {
        $marks[(int) $m['enrolment_id']][$m['session_date']] = $m['status'];
    }

    report_head($st, $titles[$type], $subtitle, count($dates) . ' sessions · ' . count($class) . ' students');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Course</span><span class="pair__v"><?= e($course['code'] . ' — ' . $course['name']) ?></span></div>
      <div class="pair"><span class="pair__k">Key</span><span class="pair__v">P present · L late · E excused · A absent</span></div>
    </div>
    <div class="docsection">Register for <?= e(month_label($month)) ?></div>
    <table class="doc">
      <thead>
        <tr><th>Student</th>
        <?php foreach ($dates as $dt): ?><th class="center"><?= e(date('d', strtotime($dt))) ?></th><?php endforeach; ?>
        <th class="center">Rate</th></tr>
      </thead>
      <tbody>
      <?php foreach ($class as $s):
        $eid = (int) $s['enrolment_id']; $ok = 0; $tot = 0; ?>
        <tr>
          <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?><br><span style="font-size:10px;color:#6C7B75"><?= e($s['student_no']) ?></span></td>
          <?php foreach ($dates as $dt):
            $code = $marks[$eid][$dt] ?? '';
            if ($code) { $tot++; if ($code === 'P' || $code === 'L') $ok++; } ?>
            <td class="center"><?= e($code) ?></td>
          <?php endforeach; ?>
          <td class="center"><?= $tot ? pct($ok, $tot) . '%' : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$dates): ?><tr><td colspan="2">No sessions recorded in this month.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <?php
}

// ─────────────────────────────────────────────────────────────── marks sheet
elseif ($type === 'marks') {
    $items = rows('SELECT id, name, kind, max_score, weight FROM assessments WHERE course_id = ? ORDER BY due_date, id', [$courseId]);
    $class = rows("SELECT e.id AS enrolment_id, s.student_no, s.first_name, s.last_name
                   FROM enrolments e JOIN students s ON s.id = e.student_id
                   WHERE e.course_id = ? ORDER BY s.first_name, s.last_name", [$courseId]);
    $scores = [];
    foreach (rows('SELECT m.assessment_id, m.enrolment_id, m.score FROM marks m
                   JOIN assessments a ON a.id = m.assessment_id WHERE a.course_id = ?', [$courseId]) as $m) {
        $scores[(int) $m['enrolment_id']][(int) $m['assessment_id']] = $m['score'];
    }
    $totalWeight = 0;
    foreach ($items as $it) $totalWeight += (int) $it['weight'];

    report_head($st, $titles[$type], $subtitle, count($items) . ' assessments · weights ' . $totalWeight . '%');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Course</span><span class="pair__v"><?= e($course['code'] . ' — ' . $course['name']) ?></span></div>
      <div class="pair"><span class="pair__k">Students</span><span class="pair__v"><?= count($class) ?></span></div>
    </div>
    <div class="docsection">Marks</div>
    <table class="doc">
      <thead>
        <tr><th>Student</th>
        <?php foreach ($items as $it): ?>
          <th class="center"><?= e($it['name']) ?><br><span style="font-size:9px;font-weight:400">/<?= (int) $it['max_score'] ?> · <?= (int) $it['weight'] ?>%</span></th>
        <?php endforeach; ?>
        <th class="center">Weighted</th></tr>
      </thead>
      <tbody>
      <?php foreach ($class as $s):
        $eid = (int) $s['enrolment_id']; $earned = 0; $poss = 0; ?>
        <tr>
          <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?><br><span style="font-size:10px;color:#6C7B75"><?= e($s['student_no']) ?></span></td>
          <?php foreach ($items as $it):
            $sc = $scores[$eid][(int) $it['id']] ?? null;
            if ($sc !== null && (int) $it['weight'] > 0) {
              $earned += ((float) $sc / max(1, (float) $it['max_score'])) * (int) $it['weight'];
              $poss   += (int) $it['weight'];
            } ?>
            <td class="center"><?= $sc !== null ? round((float) $sc, 1) : '' ?></td>
          <?php endforeach; ?>
          <td class="center"><strong><?= $poss ? round($earned, 1) . ' / ' . $poss : '—' ?></strong></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="2">No assessments set for this course.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <?php
}

// ───────────────────────────────────────────── facilitator reporting status
elseif ($type === 'monthly') {
    $mine = my_course_ids();
    $list = rows("SELECT c.code, c.name AS course, d.name AS dept, u.name AS facilitator,
                    mr.status, mr.sessions_held, mr.created_at,
                    (SELECT COUNT(DISTINCT session_date) FROM attendance a
                      WHERE a.course_id = c.id AND a.session_date BETWEEN ? AND ?) AS actual_sessions
                  FROM courses c
                  LEFT JOIN departments d ON d.id = c.department_id
                  LEFT JOIN users u ON u.id = c.facilitator_id
                  LEFT JOIN monthly_reports mr ON mr.course_id = c.id AND mr.month_year = ?
                  WHERE c.id IN (" . in_list($mine) . ") AND c.status = 'active'
                  ORDER BY d.name, c.code", [$from, $to, $month]);
    $done = 0;
    foreach ($list as $r) if ($r['status'] !== null) $done++;

    report_head($st, $titles[$type], $subtitle, $done . ' of ' . count($list) . ' submitted');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Month</span><span class="pair__v"><?= e(month_label($month)) ?></span></div>
      <div class="pair"><span class="pair__k">Submitted</span><span class="pair__v"><?= $done ?> of <?= count($list) ?></span></div>
    </div>
    <div class="docsection">By class</div>
    <table class="doc">
      <thead><tr><th>Department</th><th>Code</th><th>Facilitator</th><th class="center">Sessions in register</th><th class="center">Sessions reported</th><th>Report status</th><th>Submitted</th></tr></thead>
      <tbody>
      <?php foreach ($list as $r): ?>
        <tr>
          <td><?= e($r['dept'] ?? '—') ?></td>
          <td><?= e($r['code']) ?></td>
          <td><?= e($r['facilitator'] ?? 'unassigned') ?></td>
          <td class="center"><?= (int) $r['actual_sessions'] ?></td>
          <td class="center"><?= $r['status'] !== null ? (int) $r['sessions_held'] : '—' ?></td>
          <td><?= $r['status'] !== null ? e(ucfirst(str_replace('_', ' ', $r['status']))) : 'NOT SUBMITTED' ?></td>
          <td><?= $r['created_at'] ? e(d($r['created_at'])) : '' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$list): ?><tr><td colspan="7">No active courses in scope.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <?php
}

// ───────────────────────────────────────────────────────── certificates issued
elseif ($type === 'certificates') {
    $list = rows("SELECT ct.*, s.student_no, s.first_name, s.last_name, c.code, c.name AS course, u.name AS issuer
                  FROM certificates ct
                  JOIN students s ON s.id = ct.student_id
                  JOIN courses c ON c.id = ct.course_id
                  LEFT JOIN users u ON u.id = ct.issued_by
                  WHERE ct.issue_date BETWEEN ? AND ?
                  ORDER BY ct.issue_date DESC, ct.serial_no", [$year . '-01-01', $year . '-12-31']);
    $revoked = 0;
    foreach ($list as $r) if ($r['status'] !== 'valid') $revoked++;

    report_head($st, $titles[$type], $subtitle, count($list) . ' certificates');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Year</span><span class="pair__v"><?= $year ?></span></div>
      <div class="pair"><span class="pair__k">Issued</span><span class="pair__v"><?= count($list) ?></span></div>
      <div class="pair"><span class="pair__k">Revoked</span><span class="pair__v"><?= $revoked ?></span></div>
      <div class="pair"><span class="pair__k">Verify at</span><span class="pair__v">index.php?r=verify</span></div>
    </div>
    <div class="docsection">Register of certificates</div>
    <table class="doc">
      <thead><tr><th>Serial</th><th>Verify code</th><th>Student no.</th><th>Name</th><th>Course</th><th>Issued</th><th>Status</th><th>Issued by</th></tr></thead>
      <tbody>
      <?php foreach ($list as $r): ?>
        <tr>
          <td><?= e($r['serial_no']) ?></td>
          <td><?= e($r['verify_code']) ?></td>
          <td><?= e($r['student_no']) ?></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
          <td><?= e($r['code']) ?></td>
          <td><?= e(d($r['issue_date'])) ?></td>
          <td><?= e(ucfirst($r['status'])) ?></td>
          <td><?= e($r['issuer'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$list): ?><tr><td colspan="8">No certificates issued in <?= $year ?>.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <?php
}

// ───────────────────────────────────────────────────────────── kitchen service
elseif ($type === 'kitchen') {
    $days = rows('SELECT * FROM kitchen_records WHERE service_date BETWEEN ? AND ? ORDER BY service_date', [$from, $to]);
    $sum = ['tt' => 0, 'ts' => 0, 'mt' => 0, 'ms' => 0];
    foreach ($days as $k) {
        $sum['tt'] += (int) $k['tea_teachers'];  $sum['ts'] += (int) $k['tea_students'];
        $sum['mt'] += (int) $k['meals_teachers']; $sum['ms'] += (int) $k['meals_students'];
    }
    $tea = $sum['tt'] + $sum['ts']; $meal = $sum['mt'] + $sum['ms'];
    $all = $tea + $meal;

    report_head($st, $titles[$type], $subtitle, count($days) . ' days recorded');
    ?>
    <div class="pairs">
      <div class="pair"><span class="pair__k">Tea served</span><span class="pair__v"><?= $tea ?> cups</span></div>
      <div class="pair"><span class="pair__k">Meals served</span><span class="pair__v"><?= $meal ?> plates</span></div>
      <div class="pair"><span class="pair__k">To teachers</span><span class="pair__v"><?= $sum['tt'] + $sum['mt'] ?></span></div>
      <div class="pair"><span class="pair__k">To students</span><span class="pair__v"><?= $sum['ts'] + $sum['ms'] ?></span></div>
      <div class="pair"><span class="pair__k">Total servings</span><span class="pair__v"><?= $all ?></span></div>
      <div class="pair"><span class="pair__k">Daily average</span><span class="pair__v"><?= count($days) ? round($all / count($days), 1) : 0 ?></span></div>
    </div>
    <div class="docsection">Day by day</div>
    <table class="doc">
      <thead><tr><th>Date</th><th class="center">Tea teachers</th><th class="center">Tea students</th><th class="center">Meals teachers</th><th class="center">Meals students</th><th class="center">Total</th><th>Note</th></tr></thead>
      <tbody>
      <?php foreach ($days as $k):
        $t = (int) $k['tea_teachers'] + (int) $k['tea_students'] + (int) $k['meals_teachers'] + (int) $k['meals_students']; ?>
        <tr>
          <td><?= e(d($k['service_date'], 'D d M')) ?></td>
          <td class="center"><?= (int) $k['tea_teachers'] ?></td>
          <td class="center"><?= (int) $k['tea_students'] ?></td>
          <td class="center"><?= (int) $k['meals_teachers'] ?></td>
          <td class="center"><?= (int) $k['meals_students'] ?></td>
          <td class="center"><strong><?= $t ?></strong></td>
          <td><?= e($k['notes'] ?: '') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$days): ?><tr><td colspan="7">No kitchen records in this month.</td></tr><?php endif; ?>
      </tbody>
      <?php if ($days): ?>
      <tfoot><tr><th>Total</th><th class="center"><?= $sum['tt'] ?></th><th class="center"><?= $sum['ts'] ?></th><th class="center"><?= $sum['mt'] ?></th><th class="center"><?= $sum['ms'] ?></th><th class="center"><?= $all ?></th><th></th></tr></tfoot>
      <?php endif; ?>
    </table>
    <?php
}
?>

  <div class="signrow">
    <div><div class="signline">Prepared by — <?= e(current_user()['name'] ?? '') ?></div></div>
    <div><div class="signline">Approved by</div></div>
  </div>

  <div class="docfoot">
    <span><?= e($st['org_name'] ?? ORG_NAME) ?> · <?= e($titles[$type]) ?> · <?= e($subtitle) ?></span>
    <span>Printed <?= e(date('d M Y H:i')) ?> by <?= e(current_user()['name'] ?? '') ?></span>
  </div>
</div>
