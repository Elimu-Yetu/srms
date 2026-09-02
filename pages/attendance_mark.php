<?php
/** Take attendance for one class on one date. Works on a phone in class. */
require_once BASE_PATH . '/views/icons.php';

$mine    = my_course_ids();
$courses = rows('SELECT c.id, c.code, c.name FROM courses c WHERE c.id IN (' . in_list($mine) . ") AND c.status = 'active' ORDER BY c.name");

$courseId = getInt('course_id') ?: (int) ($courses[0]['id'] ?? 0);
$date     = getStr('date') ?: date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

if (is_post()) {
    csrf_check();
    $courseId = postInt('course_id');
    $date     = post('date');
    $c = require_course_access($courseId);

    if (strtotime($date) > time()) {
        flash('error', 'You cannot record attendance for a future date.');
        redirect('attendance.mark', ['course_id' => $courseId]);
    }

    $marks  = $_POST['status'] ?? [];
    $notes  = $_POST['remark'] ?? [];
    $saved  = 0;
    foreach ($marks as $enrolmentId => $status) {
        $enrolmentId = (int) $enrolmentId;
        if (!in_array($status, ['P', 'A', 'E'], true)) continue;
        $belongs = val('SELECT 1 FROM enrolments WHERE id = ? AND course_id = ?', [$enrolmentId, $courseId]);
        if (!$belongs) continue;

        $remark   = mb_substr(trim((string) ($notes[$enrolmentId] ?? '')), 0, 160);
        $existing = val('SELECT id FROM attendance WHERE enrolment_id = ? AND session_date = ?', [$enrolmentId, $date]);
        if ($existing) {
            q('UPDATE attendance SET status = ?, remarks = ?, recorded_by = ? WHERE id = ?',
              [$status, $remark, user_id(), $existing]);
        } else {
            insert('attendance', [
                'enrolment_id' => $enrolmentId, 'course_id' => $courseId, 'session_date' => $date,
                'status' => $status, 'remarks' => $remark, 'recorded_by' => user_id(), 'created_at' => now(),
            ]);
        }
        $saved++;
    }
    audit('attendance', 'attendance', $courseId, $c['code'] . ' ' . $date . ' · ' . $saved . ' marks');
    flash('ok', 'Saved <strong>' . $saved . '</strong> mark' . ($saved === 1 ? '' : 's') . ' for ' . e(d($date)) . '.');
    redirect('attendance.mark', ['course_id' => $courseId, 'date' => $date]);
}

if (!$courses) {
    echo '<div class="panel"><div class="empty"><div class="empty__mark">' . icon('check', 22) . '</div>'
       . '<h3>No class is assigned to you</h3><p>Attendance opens once you are set as the facilitator of a course, '
       . 'or listed on its timetable.</p></div></div>';
    return;
}

$course = require_course_access($courseId);
$class  = rows("SELECT e.id AS enrolment_id, s.id, s.first_name, s.last_name, s.student_no, s.photo,
                  a.status AS marked, a.remarks
                FROM enrolments e
                JOIN students s ON s.id = e.student_id
                LEFT JOIN attendance a ON a.enrolment_id = e.id AND a.session_date = ?
                WHERE e.course_id = ? AND e.status = 'active'
                ORDER BY s.first_name, s.last_name", [$date, $courseId]);

$already = count(array_filter($class, fn($r) => $r['marked'] !== null));
$page_sub = e($course['code']) . ' · ' . e($course['name']) . ' · ' . e(d($date, 'l d F Y'))
          . ($already ? ' · <strong>already recorded</strong>' : '');
?>

<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="attendance.mark">
    <div class="field grow">
      <label for="course_id">Class</label>
      <select id="course_id" name="course_id" data-autosubmit>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $courseId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="date">Session date</label>
      <input id="date" name="date" type="date" value="<?= e($date) ?>" max="<?= date('Y-m-d') ?>" data-autosubmit>
    </div>
    <button class="btn"><?= icon('search', 16) ?> Load</button>
  </form>

  <?php if ($already): ?>
    <div style="padding:12px 18px 0">
      <div class="alert alert--info" style="margin:0">
        <?= icon('clock', 17) ?>
        <div>Attendance for this date is already recorded. Changing a mark and saving overwrites it, and the change is logged.</div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!$class): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('users', 22) ?></div>
      <h3>No active students in this class</h3>
      <p>Enrol students into <?= e($course['code']) ?> and they appear here automatically.</p>
    </div>
  <?php else: ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="course_id" value="<?= $courseId ?>">
      <input type="hidden" name="date" value="<?= e($date) ?>">

      <div class="toolbar" style="border-top:1px solid var(--line)">
        <span class="tiny muted" style="margin-right:auto">
          <?= count($class) ?> student<?= count($class) === 1 ? '' : 's' ?> · P present · E excused · A absent
        </span>
        <button type="button" class="btn btn--sm btn--green" data-markall="P">Mark all present</button>
        <button type="button" class="btn btn--sm btn--ghost" data-markall="A">Mark all absent</button>
      </div>

      <div class="tablewrap">
        <table class="data">
          <thead><tr><th>Student</th><th style="width:170px">Mark</th><th>Remark</th></tr></thead>
          <tbody>
          <?php foreach ($class as $s): $full = $s['first_name'] . ' ' . $s['last_name']; $eid = (int) $s['enrolment_id']; ?>
            <tr>
              <td>
                <div class="person">
                  <?php if ($s['photo']): ?><img class="avatar" src="<?= e(photo_url($s['photo'])) ?>" alt="">
                  <?php else: ?><span class="avatar"><?= e(initials($full)) ?></span><?php endif; ?>
                  <span><span class="person__name"><?= e($full) ?></span><br>
                    <span class="person__meta"><?= e($s['student_no']) ?></span></span>
                </div>
              </td>
              <td>
                <div class="att">
                  <?php foreach (['P', 'E', 'A'] as $code):
                    $checked = ($s['marked'] ?? 'P') === $code; ?>
                    <input type="radio" id="a<?= $eid . $code ?>" name="status[<?= $eid ?>]" value="<?= $code ?>" <?= $checked ? 'checked' : '' ?>>
                    <label for="a<?= $eid . $code ?>" title="<?= e(attendance_label($code)) ?>"><?= $code ?></label>
                  <?php endforeach; ?>
                </div>
              </td>
              <td><input name="remark[<?= $eid ?>]" value="<?= e($s['remarks'] ?? '') ?>" placeholder="Optional — reason for absence"></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="panel__foot">
        <button class="btn btn--green"><?= icon('check', 16) ?> Save attendance</button>
        <a class="btn btn--ghost" href="<?= e(url('attendance.register', ['course_id' => $courseId])) ?>">View the register</a>
      </div>
    </form>
  <?php endif; ?>
</div>
