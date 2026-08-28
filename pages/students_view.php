<?php
/** One student's complete file — the digital replacement for the paper folder. */
require_once BASE_PATH . '/views/icons.php';

$id = getInt('id');
$s  = row('SELECT s.*, d.name AS dept, u.name AS registered_by_name
           FROM students s LEFT JOIN departments d ON d.id = s.department_id
           LEFT JOIN users u ON u.id = s.registered_by WHERE s.id = ?', [$id]);
if (!$s) { flash('error', 'That student file was not found.'); redirect('students.index'); }

if (is_role('manager') && (int) $s['department_id'] !== my_department()) deny('That student is in another department.');
if (is_role('facilitator')) {
    $ok = val('SELECT 1 FROM enrolments WHERE student_id = ? AND course_id IN (' . in_list(my_course_ids()) . ')', [$id]);
    if (!$ok) deny('That student is not in one of your classes.');
}

$full = trim($s['first_name'] . ' ' . ($s['middle_name'] ? $s['middle_name'] . ' ' : '') . $s['last_name']);
$page_title = $full;
$page_sub   = '<span class="mono">' . e($s['student_no']) . '</span> · ' . e($s['dept'] ?? 'No department')
            . ' · ' . badge(ucfirst($s['status']), status_tone($s['status']));

$page_actions = '';
if (can('students.manage')) {
    $page_actions .= '<a class="btn" href="' . e(url('students.form', ['id' => $id])) . '">' . icon('edit', 16) . ' Edit</a>';
}
$page_actions .= '<a class="btn" target="_blank" href="' . e(url('students.slip', ['id' => $id])) . '">' . icon('printer', 16) . ' Slip</a>';

$enrols = rows('SELECT e.*, c.code, c.name AS course, c.end_date, u.name AS facilitator
                FROM enrolments e JOIN courses c ON c.id = e.course_id
                LEFT JOIN users u ON u.id = c.facilitator_id
                WHERE e.student_id = ? ORDER BY e.status, c.name', [$id]);

$attRows = rows("SELECT c.code, c.name AS course,
                   COUNT(a.id) AS total,
                   SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) AS p,
                   SUM(CASE WHEN a.status='L' THEN 1 ELSE 0 END) AS l,
                   SUM(CASE WHEN a.status='E' THEN 1 ELSE 0 END) AS x,
                   SUM(CASE WHEN a.status='A' THEN 1 ELSE 0 END) AS ab
                 FROM attendance a
                 JOIN enrolments e ON e.id = a.enrolment_id
                 JOIN courses c ON c.id = a.course_id
                 WHERE e.student_id = ? GROUP BY c.code, c.name", [$id]);

$marks = rows('SELECT m.score, m.feedback, a.name AS assessment, a.max_score, c.code
               FROM marks m JOIN assessments a ON a.id = m.assessment_id
               JOIN enrolments e ON e.id = m.enrolment_id
               JOIN courses c ON c.id = a.course_id
               WHERE e.student_id = ? ORDER BY a.due_date DESC', [$id]);

$certs = rows('SELECT ce.*, c.name AS course FROM certificates ce JOIN courses c ON c.id = ce.course_id
               WHERE ce.student_id = ? ORDER BY ce.issue_date DESC', [$id]);
$card  = row('SELECT * FROM id_cards WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$id]);
$login = row('SELECT id, email, status, last_login FROM users WHERE student_id = ?', [$id]);

$openCourses = can('students.enrol')
    ? rows('SELECT id, code, name FROM courses WHERE status = ? AND id NOT IN
            (SELECT course_id FROM enrolments WHERE student_id = ?) ORDER BY name', ['active', $id])
    : [];
$threshold = (int) setting('attendance_threshold', '80');
?>

<div class="grid grid--sidebar">
  <div class="stack">

    <div class="panel">
      <div class="panel__head"><h2>Registration details</h2>
        <span class="tiny muted">Registered <?= e(d($s['registered_at'], 'd M Y')) ?><?= $s['registered_by_name'] ? ' by ' . e($s['registered_by_name']) : '' ?></span>
      </div>
      <div class="panel__body">
        <div class="grid grid--2">
          <dl class="dl">
            <dt>Student number</dt><dd class="mono"><?= e($s['student_no']) ?></dd>
            <dt>Full name</dt><dd><?= e($full) ?></dd>
            <dt>Gender</dt><dd><?= e($s['gender'] ?: '—') ?></dd>
            <dt>Date of birth</dt><dd><?= e(d($s['dob'])) ?></dd>
            <dt>Education level</dt><dd><?= e($s['education_level'] ?: '—') ?></dd>
          </dl>
          <dl class="dl">
            <dt>Phone</dt><dd class="mono"><?= e($s['phone'] ?: '—') ?></dd>
            <dt>Email</dt><dd><?= e($s['email'] ?: '—') ?></dd>
            <dt>National ID</dt><dd class="mono"><?= e($s['national_id'] ?: '—') ?></dd>
            <dt>Address</dt><dd><?= e($s['address'] ?: '—') ?></dd>
            <dt>Department</dt><dd><?= e($s['dept'] ?? '—') ?></dd>
          </dl>
        </div>
        <div class="section-head"><span></span><h3>Guardian / emergency contact</h3></div>
        <dl class="dl" style="grid-template-columns:25% 75%">
          <dt>Name</dt><dd><?= e($s['guardian_name'] ?: '—') ?></dd>
          <dt>Phone</dt><dd class="mono"><?= e($s['guardian_phone'] ?: '—') ?></dd>
          <dt>Relationship</dt><dd><?= e($s['guardian_relation'] ?: '—') ?></dd>
        </dl>
        <?php if ($s['notes']): ?>
          <div class="section-head"><span></span><h3>Office notes</h3></div>
          <p class="tiny"><?= nl2br(e($s['notes'])) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Courses</h2><?= badge(count($enrols) . ' enrolment' . (count($enrols) === 1 ? '' : 's'), 'blue') ?></div>
      <?php if (!$enrols): ?>
        <div class="empty" style="padding:26px">
          <h3>Not enrolled in any course</h3>
          <p>Enrolment links this student to a class, its attendance register and its timetable.</p>
        </div>
      <?php else: ?>
        <div class="tablewrap">
          <table class="data">
            <thead><tr><th>Course</th><th>Facilitator</th><th>Enrolled</th><th>Status</th><?php if (can('students.enrol')): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($enrols as $en): ?>
              <tr>
                <td><a href="<?= e(url('courses.view', ['id' => $en['course_id']])) ?>"><strong><?= e($en['course']) ?></strong></a>
                    <div class="tiny mono muted"><?= e($en['code']) ?></div></td>
                <td class="tiny"><?= e($en['facilitator'] ?? 'To be assigned') ?></td>
                <td class="tiny mono"><?= e(d($en['enrolled_on'])) ?></td>
                <td><?= badge(ucfirst($en['status']), status_tone($en['status'])) ?></td>
                <?php if (can('students.enrol')): ?>
                  <td class="right">
                    <form method="post" action="<?= e(url('enrolments.act')) ?>" style="display:inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="do" value="unenrol">
                      <input type="hidden" name="enrolment_id" value="<?= (int) $en['id'] ?>">
                      <input type="hidden" name="student_id" value="<?= $id ?>">
                      <button class="btn btn--sm btn--ghost" data-confirm="Remove this enrolment? Attendance already recorded stays in the register."><?= icon('x', 15) ?></button>
                    </form>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <?php if (can('students.enrol') && $openCourses): ?>
        <form class="panel__foot" method="post" action="<?= e(url('enrolments.act')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="enrol">
          <input type="hidden" name="student_id" value="<?= $id ?>">
          <select name="course_id" required style="max-width:320px">
            <option value="">Add to a course…</option>
            <?php foreach ($openCourses as $c): ?>
              <option value="<?= (int) $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn--green"><?= icon('plus', 16) ?> Enrol</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Attendance</h2><span class="tiny muted">Threshold <?= $threshold ?>%</span></div>
      <?php if (!$attRows): ?>
        <div class="panel__body"><p class="muted tiny">No attendance recorded for this student yet.</p></div>
      <?php else: ?>
        <div class="panel__body" style="display:grid;gap:16px">
          <?php foreach ($attRows as $a):
            $t = (int) $a['total']; $rate = pct((int) $a['p'] + (int) $a['l'], $t); ?>
            <div>
              <div style="display:flex;gap:10px;align-items:baseline">
                <strong class="tiny"><?= e($a['code']) ?> · <?= e($a['course']) ?></strong>
                <span style="margin-left:auto"><?= badge($rate . '%', $rate < $threshold ? 'red' : 'green') ?></span>
              </div>
              <div class="segbar" style="margin-top:6px">
                <span class="seg-p" style="width:<?= pct((int) $a['p'], $t) ?>%"></span>
                <span class="seg-l" style="width:<?= pct((int) $a['l'], $t) ?>%"></span>
                <span class="seg-e" style="width:<?= pct((int) $a['x'], $t) ?>%"></span>
                <span class="seg-a" style="width:<?= pct((int) $a['ab'], $t) ?>%"></span>
              </div>
              <div class="seglegend">
                <span><i class="seg-p"></i>Present <?= (int) $a['p'] ?></span>
                <span><i class="seg-l"></i>Late <?= (int) $a['l'] ?></span>
                <span><i class="seg-e"></i>Excused <?= (int) $a['x'] ?></span>
                <span><i class="seg-a"></i>Absent <?= (int) $a['ab'] ?></span>
                <span class="mono"><?= $t ?> sessions</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($marks): ?>
      <div class="panel">
        <div class="panel__head"><h2>Assessment results</h2></div>
        <div class="tablewrap">
          <table class="data">
            <thead><tr><th>Assessment</th><th>Course</th><th class="right">Score</th><th>Feedback</th></tr></thead>
            <tbody>
            <?php foreach ($marks as $m): ?>
              <tr>
                <td><?= e($m['assessment']) ?></td>
                <td class="tiny mono"><?= e($m['code']) ?></td>
                <td class="right mono"><?= $m['score'] === null ? '—' : e(rtrim(rtrim(number_format((float) $m['score'], 1), '0'), '.')) . ' / ' . (int) $m['max_score'] ?></td>
                <td class="tiny muted"><?= e($m['feedback'] ?: '—') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__body center">
        <?php if ($s['photo']): ?>
          <img src="<?= e(photo_url($s['photo'])) ?>" alt="Photo of <?= e($full) ?>"
               style="width:130px;height:160px;object-fit:cover;border-radius:14px;border:1px solid var(--glass-edge)">
        <?php else: ?>
          <div style="width:130px;height:160px;margin:0 auto;border-radius:14px;background:rgba(255,255,255,.6);border:1px dashed var(--line);display:grid;place-items:center;color:var(--ink-faint)">
            <div><?= icon('users', 26) ?><div class="tiny">No photo</div></div>
          </div>
        <?php endif; ?>
        <div style="margin-top:10px"><strong><?= e($full) ?></strong></div>
        <div class="tiny mono muted"><?= e($s['student_no']) ?></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Documents</h2></div>
      <div class="panel__body" style="display:grid;gap:9px">
        <a class="btn" target="_blank" href="<?= e(url('students.slip', ['id' => $id])) ?>"><?= icon('printer', 16) ?> Registration slip</a>
        <?php if (can('idcards.manage')): ?>
          <a class="btn" target="_blank" href="<?= e(url('idcards.print', ['student_id' => $id])) ?>"><?= icon('card', 16) ?> <?= $card ? 'Reprint ID card' : 'Issue &amp; print ID card' ?></a>
          <?php if ($card): ?>
            <div class="tiny muted">Card <span class="mono"><?= e($card['card_no']) ?></span>, valid to <?= e(d($card['valid_until'])) ?></div>
          <?php endif; ?>
        <?php endif; ?>
        <?php foreach ($certs as $c): ?>
          <a class="btn btn--green" target="_blank" href="<?= e(url('certificates.print', ['id' => $c['id']])) ?>">
            <?= icon('award', 16) ?> Certificate · <?= e($c['course']) ?>
          </a>
          <div class="tiny muted">Serial <span class="mono"><?= e($c['serial_no']) ?></span> · code <span class="mono"><?= e($c['verify_code']) ?></span></div>
        <?php endforeach; ?>
        <?php if (can('certificates.manage') && !$certs): ?>
          <a class="btn btn--ghost" href="<?= e(url('certificates.issue', ['student_id' => $id])) ?>"><?= icon('award', 16) ?> Issue a certificate</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (can('students.manage')): ?>
      <div class="panel">
        <div class="panel__head"><h2>Status</h2></div>
        <form class="panel__body" method="post" action="<?= e(url('enrolments.act')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="status">
          <input type="hidden" name="student_id" value="<?= $id ?>">
          <div class="field">
            <label for="st">Registration status</label>
            <select id="st" name="status">
              <?php foreach (['pending', 'active', 'completed', 'deferred', 'withdrawn', 'suspended'] as $st): ?>
                <option value="<?= $st ?>" <?= $s['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn--primary" style="width:100%;justify-content:center"><?= icon('check', 16) ?> Update status</button>
        </form>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel__head"><h2>Portal login</h2></div>
      <div class="panel__body">
        <?php if ($login): ?>
          <dl class="dl" style="grid-template-columns:40% 60%">
            <dt>Email</dt><dd class="tiny mono"><?= e($login['email']) ?></dd>
            <dt>Status</dt><dd><?= badge(ucfirst($login['status']), status_tone($login['status'])) ?></dd>
            <dt>Last sign-in</dt><dd class="tiny"><?= e($login['last_login'] ? d($login['last_login'], 'd M Y H:i') : 'Never') ?></dd>
          </dl>
          <?php if (can('users.manage')): ?>
            <a class="btn btn--sm" style="margin-top:10px" href="<?= e(url('users.form', ['id' => $login['id']])) ?>">Manage account</a>
          <?php endif; ?>
        <?php else: ?>
          <p class="tiny muted">No portal account yet. A student needs one to see their courses, timetable and results.</p>
          <?php if (can('users.manage')): ?>
            <a class="btn btn--sm" href="<?= e(url('users.form', ['student_id' => $id, 'role' => 'student'])) ?>"><?= icon('plus', 15) ?> Create login</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
