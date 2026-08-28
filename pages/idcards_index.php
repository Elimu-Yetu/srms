<?php
/** Student ID cards — issue one, or print a whole class in a batch. */
require_once BASE_PATH . '/views/icons.php';

$courseId = getInt('course_id');
$courses  = rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list(my_course_ids()) . ') ORDER BY name');

$where = ' WHERE s.status IN (\'active\',\'completed\') ';
$args  = [];
[$scope, $scopeArgs] = dept_scope('s.department_id');
$where .= $scope; $args = array_merge($args, $scopeArgs);
if ($courseId) { $where .= ' AND s.id IN (SELECT student_id FROM enrolments WHERE course_id = ?) '; $args[] = $courseId; }

$list = rows("SELECT s.*, d.name AS dept, ic.card_no, ic.valid_until, ic.issued_on
              FROM students s
              LEFT JOIN departments d ON d.id = s.department_id
              LEFT JOIN id_cards ic ON ic.id = (SELECT MAX(id) FROM id_cards WHERE student_id = s.id)
              $where ORDER BY s.student_no", $args);

$page_sub = 'Cards print at 85.6 × 54 mm — the same size as a bank card.';
$page_actions = $courseId
    ? '<a class="btn btn--primary" target="_blank" href="' . e(url('idcards.print', ['course_id' => $courseId])) . '">' . icon('printer', 16) . ' Print this class</a>'
    : '';
?>
<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="idcards.index">
    <div class="field grow">
      <label for="course_id">Class</label>
      <select id="course_id" name="course_id" data-autosubmit>
        <option value="">All students in scope</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $courseId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['code'] . ' — ' . $c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field grow">
      <label for="filter">Find a student</label>
      <input id="filter" type="search" data-filter="#idtable" placeholder="Type a name or number…">
    </div>
  </form>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('card', 22) ?></div>
      <h3>No students to card yet</h3>
      <p>Only active or completed students appear here. Register and activate a student first.</p>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data" id="idtable">
        <thead><tr><th>Student</th><th>Department</th><th>Card</th><th>Valid until</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $s): $full = $s['first_name'] . ' ' . $s['last_name']; ?>
          <tr>
            <td>
              <a class="person" href="<?= e(url('students.view', ['id' => $s['id']])) ?>">
                <?php if ($s['photo']): ?><img class="avatar" src="<?= e(photo_url($s['photo'])) ?>" alt="">
                <?php else: ?><span class="avatar"><?= e(initials($full)) ?></span><?php endif; ?>
                <span><span class="person__name"><?= e($full) ?></span><br>
                  <span class="person__meta"><?= e($s['student_no']) ?></span></span>
              </a>
            </td>
            <td class="tiny"><?= e($s['dept'] ?? '—') ?></td>
            <td class="tiny mono"><?= $s['card_no'] ? e($s['card_no']) : '<span class="muted">Not issued</span>' ?></td>
            <td class="tiny mono"><?= $s['valid_until'] ? e(d($s['valid_until'])) : '—' ?></td>
            <td class="right">
              <a class="btn btn--sm" target="_blank" href="<?= e(url('idcards.print', ['student_id' => $s['id']])) ?>">
                <?= icon('printer', 15) ?> <?= $s['card_no'] ? 'Reprint' : 'Issue' ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="panel" style="margin-top:16px">
  <div class="panel__body tiny muted">
    A card is created the first time you print it, with a card number and an expiry
    <?= e(setting('id_card_validity_months', '12')) ?> months out. Reprinting keeps the same number.
    Students without a photo still get a card — the photo box prints empty so you can attach one.
  </div>
</div>
