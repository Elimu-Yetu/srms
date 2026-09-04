<?php
/** Read-only weekly timetable for the signed-in student or facilitator. */
require_once BASE_PATH . '/views/icons.php';

$mine = my_course_ids();
$filter = getInt('course_id');
$where = '';
$args = [];

if (is_role('facilitator')) {
    $courses = rows('SELECT DISTINCT c.id, c.code, c.name
                     FROM courses c
                     LEFT JOIN timetable t ON t.course_id = c.id
                     WHERE c.facilitator_id = ? OR t.facilitator_id = ?
                     ORDER BY c.name', [user_id(), user_id()]);
    if ($filter) {
        $where = ' AND t.course_id = ?';
        $args[] = $filter;
    }
    $slots = rows('SELECT t.*, c.code, c.name AS course FROM timetable t JOIN courses c ON c.id = t.course_id
                   WHERE (t.facilitator_id = ? OR (t.facilitator_id IS NULL AND c.facilitator_id = ?))' . $where . '
                   ORDER BY t.day_of_week, t.start_time', array_merge([user_id(), user_id()], $args));
    $page_sub = 'Every slot assigned to you this week.';
} else {
    $courses = $mine ? rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list($mine) . ') ORDER BY name') : [];
    if ($filter) {
        $where = ' AND t.course_id = ?';
        $args[] = $filter;
    }
    $slots = $mine ? rows('SELECT t.*, c.code, c.name AS course
                           FROM timetable t JOIN courses c ON c.id = t.course_id
                           WHERE t.course_id IN (' . in_list($mine) . ')' . $where . '
                           ORDER BY t.day_of_week, t.start_time', $args) : [];
    $page_sub = 'Your lessons, every week.';
}

// Group slots per day
$grid = [];
foreach ($slots as $s) $grid[(int) $s['day_of_week']][] = $s;

// Merge adjacent consecutive slots for the same course on the same day
$mergedSlots = [];
for ($d = 1; $d <= 5; $d++) {
    $daySlots = $grid[$d] ?? [];
    usort($daySlots, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
    $out = [];
    foreach ($daySlots as $s) {
        $last = end($out);
        if ($last && $last['course_id'] == $s['course_id'] && $last['end_time'] === $s['start_time']) {
            $out[key($out)]['end_time'] = $s['end_time'];
            $out[key($out)]['ids'][] = (int) $s['id'];
        } else {
            $s['ids'] = [(int) $s['id']];
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
usort($timeRows, fn($a, $b) => strcmp($a['start'], $b['start']));
?>

<?php if (!$slots && !$filter): ?>
  <div class="panel"><div class="empty">
    <div class="empty__mark"><?= icon('calendar', 22) ?></div>
    <h3>No timetable yet</h3>
    <p>The office builds the timetable per course. It appears here as soon as slots are entered.</p>
  </div></div>
<?php else: ?>
  <div class="panel">
    <?php if (count($courses) > 1): ?>
      <form class="toolbar" method="get">
        <input type="hidden" name="r" value="timetable.mine">
        <div class="field grow">
          <label for="filterc">Course</label>
          <select id="filterc" name="course_id" data-autosubmit>
            <option value="">All courses</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= $filter === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    <?php endif; ?>

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
                $timeRows = array_values($timeRows);
                $numRows = count($timeRows);
                $occupied = [];
                $slotStartMap = [];
                foreach ($mergedSlots as $day => $arr) {
                    foreach ($arr as $s) {
                        for ($ri = 0; $ri < $numRows; $ri++) {
                            if ($timeRows[$ri]['start'] === $s['start_time'] && $timeRows[$ri]['end'] === $s['end_time']) {
                                $rowspan = 0;
                                for ($rj = $ri; $rj < $numRows; $rj++) {
                                    if ($timeRows[$rj]['start'] >= $s['start_time'] && $timeRows[$rj]['end'] <= $s['end_time']) {
                                        $rowspan++;
                                    } else {
                                        break;
                                    }
                                }
                                $slotStartMap[(int) $day][$ri] = ['slot' => $s, 'rowspan' => $rowspan];
                                for ($rj = $ri; $rj < $ri + $rowspan; $rj++) {
                                    $occupied[(int) $day][$rj] = true;
                                }
                                break;
                            }
                        }
                    }
                }

                for ($ri = 0; $ri < $numRows; $ri++):
                    $tr = $timeRows[$ri];
              ?>
                <tr>
                  <td class="time-col"><?= e($tr['start']) ?> – <?= e($tr['end']) ?></td>
                  <?php for ($d = 1; $d <= 5; $d++): ?>
                    <?php if ($d === 5 && $fridayEmpty): ?>
                      <?php if ($ri === 0): ?>
                        <td class="ttcell day-friday-cell" rowspan="<?= $numRows ?>">
                          <div class="talent-show-vertical">TALENT SHOW</div>
                        </td>
                      <?php endif; ?>
                      <?php continue; ?>
                    <?php endif; ?>
                    <?php if (!empty($occupied[$d][$ri]) && empty($slotStartMap[$d][$ri])) { continue; } ?>
                    <?php if (!empty($slotStartMap[$d][$ri])):
                        $info = $slotStartMap[$d][$ri];
                        $s = $info['slot'];
                        $rs = (int) $info['rowspan'];
                    ?>
                      <td class="ttcell" rowspan="<?= $rs ?>">
                        <div class="tt-entry">
                          <div class="tt-entry__subject"><?= e($s['course']) ?></div>
                          <div class="tt-entry__meta"><?= e($s['start_time']) ?> – <?= e($s['end_time']) ?></div>
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
      </div>
    </div>
  </div>
<?php endif; ?>
