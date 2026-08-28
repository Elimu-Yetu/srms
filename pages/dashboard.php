<?php
/** Dashboard — each role lands on the numbers and the queue it owns. */
require_once BASE_PATH . '/views/icons.php';

$u    = current_user();
$r    = role();
$mine = my_course_ids();
$threshold = (int) setting('attendance_threshold', '80');
$page_sub  = 'Signed in as ' . e(ROLES[$r]) . ' · ' . e(d(date('Y-m-d'), 'l, d F Y'));

/* ── shared numbers ───────────────────────────────────────────────────────── */
$courseFilter = $mine ? ' AND e.course_id IN (' . in_list($mine) . ') ' : ' AND 1=0 ';

if (in_array($r, ['superadmin', 'admin', 'manager'], true)) {
    [$dScope, $dArgs] = dept_scope('s.department_id');
    $totStudents = (int) val("SELECT COUNT(*) FROM students s WHERE s.status = 'active' $dScope", $dArgs, 0);
    $totPending  = (int) val("SELECT COUNT(*) FROM students s WHERE s.status = 'pending' $dScope", $dArgs, 0);
    $newThisMonth = (int) val("SELECT COUNT(*) FROM students s WHERE s.registered_at >= ? $dScope",
        array_merge([date('Y-m-01 00:00:00')], $dArgs), 0);
    $totCourses  = count($mine);
    $totStaff    = (int) val('SELECT COUNT(*) FROM users u WHERE u.status = ? AND u.role <> ? ' . hide_superadmin('u'),
        ['active', 'student'], 0);

    // attendance this month across scope
    $att = row("SELECT
            SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) p,
            SUM(CASE WHEN a.status='L' THEN 1 ELSE 0 END) l,
            SUM(CASE WHEN a.status='E' THEN 1 ELSE 0 END) x,
            SUM(CASE WHEN a.status='A' THEN 1 ELSE 0 END) ab,
            COUNT(*) t
        FROM attendance a WHERE a.session_date >= ? AND a.course_id IN (" . in_list($mine) . ")",
        [date('Y-m-01')]) ?: [];
    $attTotal = (int) ($att['t'] ?? 0);
    $attRate  = pct((int) ($att['p'] ?? 0) + (int) ($att['l'] ?? 0), $attTotal);

    $pendingPlans   = (int) val('SELECT COUNT(*) FROM lesson_plans WHERE review_status = ? AND course_id IN (' . in_list($mine) . ')', ['submitted'], 0);
    $pendingReports = (int) val('SELECT COUNT(*) FROM monthly_reports WHERE status = ? AND course_id IN (' . in_list($mine) . ')', ['submitted'], 0);
}
?>

<?php if (in_array($r, ['superadmin', 'admin', 'manager'], true)): ?>

  <div class="grid grid--4">
    <a class="stat" href="<?= e(url('students.index', ['status' => 'active'])) ?>">
      <div class="stat__label">Active students</div>
      <div class="stat__value"><?= $totStudents ?></div>
      <div class="stat__note"><?= $newThisMonth ?> registered this month</div>
    </a>
    <a class="stat stat--orange" href="<?= e(url('students.index', ['status' => 'pending'])) ?>">
      <div class="stat__label">Awaiting approval</div>
      <div class="stat__value"><?= $totPending ?></div>
      <div class="stat__note"><?= $totPending ? 'Confirm or reject these' : 'Nothing waiting' ?></div>
    </a>
    <a class="stat stat--green" href="<?= e(url('attendance.register')) ?>">
      <div class="stat__label">Attendance this month</div>
      <div class="stat__value"><?= $attRate ?><small>%</small></div>
      <div class="stat__note"><?= number_format($attTotal) ?> marks recorded</div>
    </a>
    <a class="stat stat--ink" href="<?= e(url('courses.index')) ?>">
      <div class="stat__label">Courses running</div>
      <div class="stat__value"><?= $totCourses ?></div>
      <div class="stat__note"><?= $totStaff ?> staff accounts</div>
    </a>
  </div>

  <div class="grid grid--sidebar" style="margin-top:16px">
    <div class="stack">

      <?php if ($pendingPlans || $pendingReports): ?>
        <div class="panel">
          <div class="panel__head">
            <h2>Waiting for your review</h2>
            <?= badge(($pendingPlans + $pendingReports) . ' item' . ($pendingPlans + $pendingReports === 1 ? '' : 's'), 'orange') ?>
          </div>
          <div class="panel__body" style="display:flex;gap:12px;flex-wrap:wrap">
            <?php if ($pendingReports): ?>
              <a class="btn btn--orange" href="<?= e(url('monthly.index', ['status' => 'submitted'])) ?>">
                <?= icon('inbox', 16) ?> <?= $pendingReports ?> monthly report<?= $pendingReports === 1 ? '' : 's' ?>
              </a>
            <?php endif; ?>
            <?php if ($pendingPlans): ?>
              <a class="btn" href="<?= e(url('lessonplans.index', ['status' => 'submitted'])) ?>">
                <?= icon('file', 16) ?> <?= $pendingPlans ?> lesson plan<?= $pendingPlans === 1 ? '' : 's' ?>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php
      $recent = rows("SELECT s.*, d.name AS dept FROM students s
                      LEFT JOIN departments d ON d.id = s.department_id
                      WHERE 1=1 " . dept_scope('s.department_id')[0] . "
                      ORDER BY s.registered_at DESC LIMIT 8", dept_scope('s.department_id')[1]);
      ?>
      <div class="panel">
        <div class="panel__head">
          <h2>Latest registrations</h2>
          <a class="btn btn--sm" href="<?= e(url('students.index')) ?>">All students</a>
        </div>
        <?php if (!$recent): ?>
          <div class="empty">
            <div class="empty__mark"><?= icon('users', 22) ?></div>
            <h3>No students yet</h3>
            <p>Register the first student and the file, ID card and certificate all follow from that one record.</p>
            <a class="btn btn--primary" href="<?= e(url('students.form')) ?>"><?= icon('plus', 16) ?> Register a student</a>
          </div>
        <?php else: ?>
          <div class="tablewrap">
            <table class="data">
              <thead><tr><th>Student</th><th>Department</th><th>Status</th><th class="right">Registered</th></tr></thead>
              <tbody>
              <?php foreach ($recent as $s): ?>
                <tr>
                  <td>
                    <a class="person" href="<?= e(url('students.view', ['id' => $s['id']])) ?>">
                      <?php if ($s['photo']): ?>
                        <img class="avatar" src="<?= e(photo_url($s['photo'])) ?>" alt="">
                      <?php else: ?>
                        <span class="avatar"><?= e(initials($s['first_name'] . ' ' . $s['last_name'])) ?></span>
                      <?php endif; ?>
                      <span>
                        <span class="person__name"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></span><br>
                        <span class="person__meta"><?= e($s['student_no']) ?></span>
                      </span>
                    </a>
                  </td>
                  <td class="tiny"><?= e($s['dept'] ?? '—') ?></td>
                  <td><?= badge(ucfirst($s['status']), status_tone($s['status'])) ?></td>
                  <td class="right tiny mono"><?= e(d($s['registered_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="stack">
      <div class="panel">
        <div class="panel__head"><h2>Attendance mix</h2><span class="tiny muted"><?= e(date('F')) ?></span></div>
        <div class="panel__body">
          <?php if ($attTotal): ?>
            <?php $P = (int) $att['p']; $L = (int) $att['l']; $E = (int) $att['x']; $A = (int) $att['ab']; ?>
            <div class="segbar">
              <span class="seg-p" style="width:<?= pct($P, $attTotal) ?>%"></span>
              <span class="seg-l" style="width:<?= pct($L, $attTotal) ?>%"></span>
              <span class="seg-e" style="width:<?= pct($E, $attTotal) ?>%"></span>
              <span class="seg-a" style="width:<?= pct($A, $attTotal) ?>%"></span>
            </div>
            <div class="seglegend">
              <span><i class="seg-p"></i>Present <?= $P ?></span>
              <span><i class="seg-l"></i>Late <?= $L ?></span>
              <span><i class="seg-e"></i>Excused <?= $E ?></span>
              <span><i class="seg-a"></i>Absent <?= $A ?></span>
            </div>
          <?php else: ?>
            <p class="muted tiny">No attendance recorded this month yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php
      // students below the attendance threshold
      $atRisk = rows("SELECT s.id, s.first_name, s.last_name, s.student_no, c.name AS course,
                             SUM(CASE WHEN a.status IN ('P','L') THEN 1 ELSE 0 END) AS ok, COUNT(a.id) AS tot
                      FROM attendance a
                      JOIN enrolments e ON e.id = a.enrolment_id
                      JOIN students s   ON s.id = e.student_id
                      JOIN courses c    ON c.id = a.course_id
                      WHERE a.course_id IN (" . in_list($mine) . ")
                      GROUP BY s.id, s.first_name, s.last_name, s.student_no, c.name
                      HAVING COUNT(a.id) >= 5 AND (SUM(CASE WHEN a.status IN ('P','L') THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)) < ?
                      ORDER BY (SUM(CASE WHEN a.status IN ('P','L') THEN 1 ELSE 0 END) * 1.0 / COUNT(a.id)) ASC LIMIT 6", [$threshold]);
      ?>
      <div class="panel">
        <div class="panel__head"><h2>Below <?= $threshold ?>%</h2></div>
        <?php if (!$atRisk): ?>
          <div class="panel__body"><p class="muted tiny">Every student is above the attendance threshold.</p></div>
        <?php else: ?>
          <table class="data compact">
            <tbody>
            <?php foreach ($atRisk as $s): $rate = pct((int) $s['ok'], (int) $s['tot']); ?>
              <tr>
                <td>
                  <a href="<?= e(url('students.view', ['id' => $s['id']])) ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></a>
                  <div class="tiny muted"><?= e($s['course']) ?></div>
                </td>
                <td class="right mono"><?= badge($rate . '%', $rate < $threshold - 15 ? 'red' : 'orange') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="panel">
        <div class="panel__head"><h2>Quick actions</h2></div>
        <div class="panel__body" style="display:grid;gap:8px">
          <?php if (can('students.manage')): ?>
            <a class="btn" href="<?= e(url('students.form')) ?>"><?= icon('plus', 16) ?> Register a student</a>
          <?php endif; ?>
          <?php if (can('idcards.manage')): ?>
            <a class="btn" href="<?= e(url('idcards.index')) ?>"><?= icon('card', 16) ?> Print ID cards</a>
          <?php endif; ?>
          <?php if (can('certificates.manage')): ?>
            <a class="btn" href="<?= e(url('certificates.issue')) ?>"><?= icon('award', 16) ?> Issue a certificate</a>
          <?php endif; ?>
          <a class="btn" href="<?= e(url('reports.index')) ?>"><?= icon('printer', 16) ?> Generate a report</a>
        </div>
      </div>
    </div>
  </div>

<?php elseif ($r === 'facilitator'): ?>
  <?php
  $today   = (int) date('N');
  $lessons = $mine ? rows('SELECT t.*, c.name AS course, c.code FROM timetable t
                           JOIN courses c ON c.id = t.course_id
                           WHERE t.day_of_week = ? AND t.course_id IN (' . in_list($mine) . ')
                           ORDER BY t.start_time', [$today]) : [];
  $myStudents = $mine ? (int) val('SELECT COUNT(DISTINCT e.student_id) FROM enrolments e
                                   WHERE e.status = ? AND e.course_id IN (' . in_list($mine) . ')', ['active'], 0) : 0;
  $markedToday = $mine ? (int) val('SELECT COUNT(*) FROM attendance WHERE session_date = ? AND course_id IN (' . in_list($mine) . ')',
                                   [date('Y-m-d')], 0) : 0;
  $thisMonth = date('Y-m');
  $missing   = [];
  foreach ($mine as $cid) {
      $has = val('SELECT 1 FROM monthly_reports WHERE facilitator_id = ? AND course_id = ? AND month_year = ?',
                 [user_id(), $cid, $thisMonth]);
      if (!$has) $missing[] = row('SELECT id, code, name FROM courses WHERE id = ?', [$cid]);
  }
  $plans = rows('SELECT lp.*, c.name AS course FROM lesson_plans lp JOIN courses c ON c.id = lp.course_id
                 WHERE lp.facilitator_id = ? ORDER BY lp.plan_date DESC LIMIT 5', [user_id()]);
  ?>

  <div class="grid grid--4">
    <a class="stat stat--green" href="<?= e(url('attendance.mark')) ?>">
      <div class="stat__label">Marks entered today</div>
      <div class="stat__value"><?= $markedToday ?></div>
      <div class="stat__note"><?= $markedToday ? 'Attendance recorded' : 'Attendance not taken yet' ?></div>
    </a>
    <a class="stat" href="<?= e(url('students.index')) ?>">
      <div class="stat__label">My students</div>
      <div class="stat__value"><?= $myStudents ?></div>
      <div class="stat__note">Across <?= count($mine) ?> course<?= count($mine) === 1 ? '' : 's' ?></div>
    </a>
    <a class="stat stat--orange" href="<?= e(url('monthly.index')) ?>">
      <div class="stat__label">Monthly report</div>
      <div class="stat__value"><?= count($missing) ?><small> due</small></div>
      <div class="stat__note"><?= e(month_label($thisMonth)) ?></div>
    </a>
    <a class="stat stat--ink" href="<?= e(url('lessonplans.index')) ?>">
      <div class="stat__label">Lesson plans</div>
      <div class="stat__value"><?= (int) val('SELECT COUNT(*) FROM lesson_plans WHERE facilitator_id = ?', [user_id()], 0) ?></div>
      <div class="stat__note">Submitted so far</div>
    </a>
  </div>

  <?php if ($missing): ?>
    <div class="alert alert--warn" style="margin-top:16px">
      <?= icon('alert', 17) ?>
      <div>
        <strong>Monthly report for <?= e(month_label($thisMonth)) ?> is not submitted</strong> for
        <?= e(implode(', ', array_column($missing, 'code'))) ?>.
        <?php foreach ($missing as $m): ?>
          <a href="<?= e(url('monthly.form', ['course_id' => $m['id'], 'month' => $thisMonth])) ?>">Submit <?= e($m['code']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="grid grid--sidebar" style="margin-top:16px">
    <div class="panel">
      <div class="panel__head">
        <h2>Today — <?= e(day_name($today)) ?></h2>
        <a class="btn btn--sm btn--green" href="<?= e(url('attendance.mark')) ?>"><?= icon('check', 16) ?> Take attendance</a>
      </div>
      <?php if (!$lessons): ?>
        <div class="empty">
          <div class="empty__mark"><?= icon('calendar', 22) ?></div>
          <h3>No sessions timetabled today</h3>
          <p>You can still record attendance for any course assigned to you.</p>
        </div>
      <?php else: ?>
        <div class="panel__body" style="display:grid;gap:10px">
          <?php foreach ($lessons as $l): ?>
            <div class="ttcard" style="margin:0">
              <div class="ttcard__time"><?= e($l['start_time']) ?>–<?= e($l['end_time']) ?> · <?= e($l['room'] ?: 'Room TBA') ?></div>
              <div class="ttcard__subject"><?= e($l['subject']) ?></div>
              <div class="ttcard__meta"><?= e($l['code']) ?> · <?= e($l['course']) ?></div>
              <div class="btnrow" style="margin-top:8px">
                <a class="btn btn--sm" href="<?= e(url('attendance.mark', ['course_id' => $l['course_id']])) ?>">Attendance</a>
                <a class="btn btn--sm btn--ghost" href="<?= e(url('courses.view', ['id' => $l['course_id']])) ?>">Class list</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="panel__head">
        <h2>My recent lesson plans</h2>
        <a class="btn btn--sm" href="<?= e(url('lessonplans.form')) ?>"><?= icon('plus', 15) ?></a>
      </div>
      <?php if (!$plans): ?>
        <div class="empty" style="padding:26px 18px">
          <h3>Nothing submitted yet</h3>
          <p>Submit a plan and your line manager sees it immediately.</p>
          <a class="btn btn--primary" href="<?= e(url('lessonplans.form')) ?>">Write a lesson plan</a>
        </div>
      <?php else: ?>
        <table class="data compact">
          <tbody>
          <?php foreach ($plans as $p): ?>
            <tr>
              <td>
                <a href="<?= e(url('lessonplans.view', ['id' => $p['id']])) ?>"><?= e($p['topic']) ?></a>
                <div class="tiny muted"><?= e(d($p['plan_date'])) ?> · <?= e($p['course']) ?></div>
              </td>
              <td class="right"><?= badge(str_replace('_', ' ', $p['review_status']), status_tone($p['review_status'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

<?php elseif ($r === 'student'): ?>
  <?php
  $s = my_student();
  if (!$s) {
      echo '<div class="alert alert--warn"><div>Your login is not linked to a student file yet. Ask the office to link it.</div></div>';
  } else {
      $enrols = rows('SELECT e.*, c.name AS course, c.code, c.end_date, u.name AS facilitator
                      FROM enrolments e JOIN courses c ON c.id = e.course_id
                      LEFT JOIN users u ON u.id = c.facilitator_id
                      WHERE e.student_id = ? ORDER BY e.status, c.name', [$s['id']]);
      $att = row("SELECT COUNT(*) t, SUM(CASE WHEN a.status IN ('P','L') THEN 1 ELSE 0 END) ok
                  FROM attendance a JOIN enrolments e ON e.id = a.enrolment_id WHERE e.student_id = ?", [$s['id']]);
      $rate  = pct((int) ($att['ok'] ?? 0), (int) ($att['t'] ?? 0));
      $today = (int) date('N');
      $next  = rows('SELECT t.*, c.name AS course FROM timetable t JOIN courses c ON c.id = t.course_id
                     WHERE t.day_of_week = ? AND t.course_id IN (SELECT course_id FROM enrolments WHERE student_id = ?)
                     ORDER BY t.start_time', [$today, $s['id']]);
      $certs = (int) val('SELECT COUNT(*) FROM certificates WHERE student_id = ? AND status = ?', [$s['id'], 'valid'], 0);
      $notices = rows('SELECT n.*, u.name AS author FROM notices n LEFT JOIN users u ON u.id = n.posted_by
                       WHERE n.course_id IS NULL OR n.course_id IN (SELECT course_id FROM enrolments WHERE student_id = ?)
                       ORDER BY n.created_at DESC LIMIT 4', [$s['id']]);
  ?>
    <div class="grid grid--4">
      <div class="stat">
        <div class="stat__label">My number</div>
        <div class="stat__value" style="font-size:20px"><?= e($s['student_no']) ?></div>
        <div class="stat__note"><?= badge(ucfirst($s['status']), status_tone($s['status'])) ?></div>
      </div>
      <a class="stat stat--green" href="<?= e(url('attendance.mine')) ?>">
        <div class="stat__label">My attendance</div>
        <div class="stat__value"><?= $rate ?><small>%</small></div>
        <div class="stat__note"><?= (int) ($att['t'] ?? 0) ?> sessions recorded</div>
      </a>
      <a class="stat stat--orange" href="<?= e(url('courses.index')) ?>">
        <div class="stat__label">Enrolled courses</div>
        <div class="stat__value"><?= count($enrols) ?></div>
        <div class="stat__note">See topics and facilitators</div>
      </a>
      <a class="stat stat--ink" href="<?= e(url('certificates.index')) ?>">
        <div class="stat__label">Certificates</div>
        <div class="stat__value"><?= $certs ?></div>
        <div class="stat__note"><?= $certs ? 'Ready to download' : 'None issued yet' ?></div>
      </a>
    </div>

    <div class="grid grid--sidebar" style="margin-top:16px">
      <div class="stack">
        <div class="panel">
          <div class="panel__head"><h2>My courses</h2>
            <a class="btn btn--sm" href="<?= e(url('timetable.mine')) ?>"><?= icon('calendar', 15) ?> Timetable</a></div>
          <?php if (!$enrols): ?>
            <div class="empty"><h3>You are not enrolled yet</h3><p>The office will enrol you once your registration is confirmed.</p></div>
          <?php else: ?>
            <div class="tablewrap">
              <table class="data">
                <thead><tr><th>Course</th><th>Facilitator</th><th>Ends</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($enrols as $en): ?>
                  <tr>
                    <td><strong><?= e($en['course']) ?></strong><div class="tiny mono muted"><?= e($en['code']) ?></div></td>
                    <td class="tiny"><?= e($en['facilitator'] ?? 'To be assigned') ?></td>
                    <td class="tiny mono"><?= e(d($en['end_date'])) ?></td>
                    <td><?= badge(ucfirst($en['status']), status_tone($en['status'])) ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div class="panel">
          <div class="panel__head"><h2>Today — <?= e(day_name($today)) ?></h2></div>
          <?php if (!$next): ?>
            <div class="panel__body"><p class="muted tiny">No lessons on your timetable today.</p></div>
          <?php else: ?>
            <div class="panel__body" style="display:grid;gap:8px">
              <?php foreach ($next as $l): ?>
                <div class="ttcard ttcard--blue" style="margin:0">
                  <div class="ttcard__time"><?= e($l['start_time']) ?>–<?= e($l['end_time']) ?> · <?= e($l['room'] ?: 'Room TBA') ?></div>
                  <div class="ttcard__subject"><?= e($l['subject']) ?></div>
                  <div class="ttcard__meta"><?= e($l['course']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel">
        <div class="panel__head"><h2>Notices</h2></div>
        <?php if (!$notices): ?>
          <div class="panel__body"><p class="muted tiny">No notices right now.</p></div>
        <?php else: ?>
          <div class="panel__body" style="display:grid;gap:14px">
            <?php foreach ($notices as $n): ?>
              <div>
                <strong><?= e($n['title']) ?></strong>
                <div class="tiny muted"><?= e(d($n['created_at'], 'd M')) ?> · <?= e($n['author'] ?? 'Office') ?></div>
                <div class="tiny" style="margin-top:3px"><?= nl2br(e(mb_substr((string) $n['body'], 0, 170))) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php } ?>
<?php endif; ?>
