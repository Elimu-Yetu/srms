<?php
/** Build the weekly timetable, one slot at a time, with clash detection. */
require_once BASE_PATH . '/views/icons.php';

$mine    = my_course_ids();
$courses = rows('SELECT c.id, c.code, c.name FROM courses c WHERE c.id IN (' . in_list($mine) . ") AND c.status <> 'closed' ORDER BY c.name");
$facs    = rows("SELECT id, name FROM users WHERE role = 'facilitator' AND status = 'active' ORDER BY name");
$filter  = getInt('course_id');
$errors  = [];

if (is_post()) {
    csrf_check();
    if (post('do') === 'delete') {
    $idsRaw = post('id');
    $ids = array_filter(array_map('intval', explode(',', $idsRaw)));
    if ($ids) {
      foreach ($ids as $sid) {
        $slot = row('SELECT t.*, c.code FROM timetable t JOIN courses c ON c.id = t.course_id WHERE t.id = ?', [$sid]);
        if ($slot && in_array((int) $slot['course_id'], array_map('intval', $mine), true)) {
          q('DELETE FROM timetable WHERE id = ?', [$sid]);
          audit('delete', 'timetable', $sid, $slot['code'] . ' ' . $slot['subject']);
        }
      }
      flash('ok', 'Slot removed.');
    }
    redirect('timetable.index', $filter ? ['course_id' => $filter] : []);
    }

    $courseId = postInt('course_id');
    $dow      = postInt('day_of_week');
    $start    = post('start_time');
    $end      = post('end_time');
    $subject  = post('subject');
    $room     = post('room');
    $facId    = postInt('facilitator_id') ?: null;

    if (!in_array($courseId, array_map('intval', $mine), true)) $errors[] = 'Choose a course in your scope.';
    if ($dow < 1 || $dow > 7)  $errors[] = 'Choose a day of the week.';
    if ($subject === '')       $errors[] = 'Say what is taught in this slot.';
    if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) $errors[] = 'Enter both times.';
    elseif ($end <= $start)    $errors[] = 'The end time must be after the start time.';

    if (!$errors) {
        // Clash detection: same room or same facilitator at an overlapping time.
        $clash = row('SELECT t.*, c.code FROM timetable t JOIN courses c ON c.id = t.course_id
                      WHERE t.day_of_week = ? AND t.start_time < ? AND t.end_time > ?
                        AND ((? <> \'\' AND t.room = ?) OR (? IS NOT NULL AND t.facilitator_id = ?))',
                     [$dow, $end, $start, $room, $room, $facId, $facId]);
        if ($clash) {
            $errors[] = 'That clashes with <strong>' . e($clash['code'] . ' — ' . $clash['subject']) . '</strong> ('
                      . e($clash['start_time']) . '–' . e($clash['end_time']) . ($clash['room'] ? ', ' . e($clash['room']) : '') . ').';
        }
    }

    if (!$errors) {
        $id = insert('timetable', [
            'course_id' => $courseId, 'day_of_week' => $dow, 'start_time' => $start, 'end_time' => $end,
            'subject' => $subject, 'room' => $room, 'facilitator_id' => $facId, 'created_at' => now(),
        ]);
        audit('create', 'timetable', $id, $subject);
        flash('ok', 'Slot added to the timetable.');
        redirect('timetable.index', $filter ? ['course_id' => $filter] : []);
    }
}

$where = $filter ? ' AND t.course_id = ? ' : '';
$args  = $filter ? [$filter] : [];
$slots = rows("SELECT t.*, c.code, c.name AS course, u.name AS facilitator
               FROM timetable t JOIN courses c ON c.id = t.course_id
               LEFT JOIN users u ON u.id = t.facilitator_id
               WHERE t.course_id IN (" . in_list($mine) . ") $where
               ORDER BY t.day_of_week, t.start_time", $args);

// Group slots per day
$grid = [];
foreach ($slots as $s) $grid[(int) $s['day_of_week']][] = $s;

// Merge adjacent consecutive slots for the same subject/course/room/facilitator on the same day
$mergedSlots = [];
for ($d = 1; $d <= 5; $d++) {
  $daySlots = $grid[$d] ?? [];
  usort($daySlots, fn($a,$b) => strcmp($a['start_time'], $b['start_time']));
  $out = [];
  foreach ($daySlots as $s) {
    $last = end($out);
    if ($last && $last['subject'] === $s['subject'] && $last['course_id'] == $s['course_id']
      && ($last['room'] ?? '') === ($s['room'] ?? '') && ($last['facilitator_id'] ?? '') == ($s['facilitator_id'] ?? '')
      && $last['end_time'] === $s['start_time']) {
      // extend last slot
      $out[key($out)]['end_time'] = $s['end_time'];
      $out[key($out)]['ids'][] = (int)$s['id'];
    } else {
      $s['ids'] = [(int)$s['id']];
      $out[] = $s;
    }
  }
  $mergedSlots[$d] = $out;
}

// Build time rows from merged slots
$timeRows = [];
foreach ($mergedSlots as $day => $slArr) {
  foreach ($slArr as $s) {
    $k = $s['start_time'] . '|' . $s['end_time'];
    $timeRows[$k] = ['start' => $s['start_time'], 'end' => $s['end_time']];
  }
}
// Sort time rows
usort($timeRows, function($a, $b) { return strcmp($a['start'], $b['start']); });

// Map merged slots by day and time key for quick lookup
$slotMap = [];
foreach ($mergedSlots as $day => $arr) {
  foreach ($arr as $s) {
    $k = $s['start_time'] . '|' . $s['end_time'];
    $slotMap[(int)$day][$k] = $s;
  }
}
$page_sub = 'Slots repeat every week. Clashes on room or facilitator are blocked when you save.';
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<?php if (!$courses): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('calendar', 22) ?></div>
    <h3>Create a course first</h3>
    <p>A timetable slot always belongs to a course.</p>
    <?php if (can('courses.manage')): ?><a class="btn btn--primary" href="<?= e(url('courses.form')) ?>">New course</a><?php endif; ?>
  </div></div>
<?php else: ?>
<div class="panel">
  <div class="panel__head"><h2>Add a slot</h2></div>
  <form method="post" class="panel__body">
    <?= csrf_field() ?>
    <div class="formgrid formgrid--3">
      <div class="field">
        <label for="course_id">Course</label>
        <select id="course_id" name="course_id" required>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $filter === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="day_of_week">Day</label>
        <select id="day_of_week" name="day_of_week" required>
          <?php for ($i = 1; $i <= 6; $i++): ?>
            <option value="<?= $i ?>"><?= e(day_name($i)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="field">
        <label for="subject">Subject or topic</label>
        <input id="subject" name="subject" required placeholder="HTML &amp; CSS foundations">
      </div>
      <div class="field">
        <label for="start_time">From</label>
        <input id="start_time" name="start_time" type="time" value="09:00" required>
      </div>
      <div class="field">
        <label for="end_time">To</label>
        <input id="end_time" name="end_time" type="time" value="11:00" required>
      </div>
      <div class="field">
        <label for="room">Room</label>
        <input id="room" name="room" placeholder="Lab 1">
      </div>
      <div class="field span2">
        <label for="facilitator_id">Facilitator for this slot</label>
        <select id="facilitator_id" name="facilitator_id">
          <option value="">Course facilitator</option>
          <?php foreach ($facs as $u): ?>
            <option value="<?= (int) $u['id'] ?>"><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <button class="btn btn--primary"><?= icon('plus', 16) ?> Add slot</button>
  </form>
</div>

<div class="panel" style="margin-top:16px">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="timetable.index">
    <div class="field grow">
      <label for="filterc">Show</label>
      <select id="filterc" name="course_id" data-autosubmit>
        <option value="">All courses in scope</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $filter === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <?php if (!$slots): ?>
    <div class="empty"><h3>The timetable is empty</h3><p>Add the first slot above and students will see it in their portal.</p></div>
  <?php else: ?>
    <div class="panel__body">
      <div class="timetable-adv-wrap">
        <table class="timetable-adv">
          <thead>
            <tr>
              <th class="time-head">TIME</th>
              <?php for ($d = 1; $d <= 5; $d++): ?>
                <th class="<?= $d === 5 ? 'day-friday' : '' ?>"><?= e(day_name($d)) ?></th>
              <?php endfor; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (!$timeRows): ?>
              <tr><td colspan="6" class="empty-cell">No timetable slots to show.</td></tr>
            <?php else: ?>
              <?php $fridayEmpty = empty($mergedSlots[5]); ?>
              <?php
                // Prepare indexed timeRows and maps for rowspan rendering
                $timeRows = array_values($timeRows);
                $numRows = count($timeRows);
                $occupied = [];
                $slotStartMap = [];
                foreach ($mergedSlots as $day => $arr) {
                  foreach ($arr as $s) {
                    // find starting row index for this merged slot
                    for ($ri = 0; $ri < $numRows; $ri++) {
                      if ($timeRows[$ri]['start'] === $s['start_time'] && $timeRows[$ri]['end'] === $s['end_time']) {
                        // compute rowspan as number of consecutive rows covered by this slot
                        $rowspan = 0;
                        for ($rj = $ri; $rj < $numRows; $rj++) {
                          if ($timeRows[$rj]['start'] >= $s['start_time'] && $timeRows[$rj]['end'] <= $s['end_time']) $rowspan++; else break;
                        }
                        $slotStartMap[(int)$day][$ri] = ['slot' => $s, 'rowspan' => $rowspan];
                        for ($rj = $ri; $rj < $ri + $rowspan; $rj++) $occupied[(int)$day][$rj] = true;
                        break;
                      }
                    }
                  }
                }

                for ($ri = 0; $ri < $numRows; $ri++): $tr = $timeRows[$ri];
              ?>
                <tr>
                  <td class="time-col"><?= e($tr['start']) ?> – <?= e($tr['end']) ?></td>
                  <?php for ($d = 1; $d <= 5; $d++): ?>
                      <?php if ($d === 5 && $fridayEmpty): ?>
                        <td class="ttcell"></td>
                        <?php continue; ?>
                      <?php endif; ?>
                      <?php if (!empty($occupied[$d][$ri]) && empty($slotStartMap[$d][$ri])) { continue; } ?>
                      <?php if (!empty($slotStartMap[$d][$ri])):
                          $info = $slotStartMap[$d][$ri]; $s = $info['slot']; $rs = (int) $info['rowspan']; ?>
                      <td class="ttcell" rowspan="<?= $rs ?>">
                        <div class="tt-entry">
                          <div class="tt-entry__subject"><?= e($s['subject']) ?></div>
                            <div class="tt-entry__meta"><?= e($s['code']) ?><?= $s['room'] ? ' · ' . e($s['room']) : '' ?></div>
                            <div class="tt-entry__meta tiny muted"><?= e($s['facilitator'] ?? '') ?></div>
                            <form method="post" style="margin-top:6px">
                              <?= csrf_field() ?>
                              <input type="hidden" name="do" value="delete">
                              <input type="hidden" name="id" value="<?= e(implode(',', array_map('intval', $s['ids']))) ?>">
                              <button class="btn btn--sm btn--ghost" data-confirm="Remove this slot from the timetable?"><?= icon('x', 14) ?> Remove</button>
                            </form>
                        </div>
                      </td>
                    <?php else: ?>
                      <td class="ttcell"></td>
                    <?php endif; ?>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="friday-vertical">TALENT SHOW</div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
