<?php
/** Issues (if needed) and renders ID cards at CR80 size, one or a whole class. */
require_once BASE_PATH . '/views/icons.php';

$studentId = getInt('student_id');
$courseId  = getInt('course_id');

$where = ' WHERE 1=1 '; $args = [];
[$scope, $scopeArgs] = dept_scope('s.department_id');
$where .= $scope; $args = array_merge($args, $scopeArgs);
if ($studentId) { $where .= ' AND s.id = ? '; $args[] = $studentId; }
elseif ($courseId) { $where .= " AND s.status IN ('active','completed') AND s.id IN (SELECT student_id FROM enrolments WHERE course_id = ?) "; $args[] = $courseId; }
else { flash('warn', 'Choose a student or a class to print.'); redirect('idcards.index'); }

$list = rows("SELECT s.*, d.name AS dept FROM students s
              LEFT JOIN departments d ON d.id = s.department_id
              $where ORDER BY s.student_no", $args);
if (!$list) { flash('error', 'Nothing to print for that selection.'); redirect('idcards.index'); }

$months = max(1, (int) setting('id_card_validity_months', '12'));
$cards  = [];
foreach ($list as $s) {
    $card = row('SELECT * FROM id_cards WHERE student_id = ? ORDER BY id DESC LIMIT 1', [$s['id']]);
    if (!$card) {
        $no = next_serial('id_cards', 'card_no', 'EYID-' . date('Y') . '-');
        $cid = insert('id_cards', [
            'student_id' => $s['id'], 'card_no' => $no, 'issued_on' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime("+$months months")), 'status' => 'active',
            'issued_by' => user_id(), 'created_at' => now(),
        ]);
        audit('issue', 'id_cards', $cid, $s['student_no'] . ' ' . $no);
        $card = row('SELECT * FROM id_cards WHERE id = ?', [$cid]);
    }
    $cards[] = [$s, $card];
}

$page_title = count($cards) === 1 ? 'ID card · ' . $list[0]['student_no'] : 'ID cards · ' . count($cards);
$print_page = 'card';
$print_back = $studentId ? url('students.view', ['id' => $studentId]) : url('idcards.index');
$print_hint = 'Print on card stock, then cut along the dashed edge. Scale must stay at 100%.';
$st = settings();

/** Deterministic bar pattern from the student number — a scannable-looking mark
 *  that encodes nothing secret and needs no barcode library. */
function bar_pattern(string $seed): array {
    $out = []; $h = md5($seed);
    for ($i = 0; $i < 30; $i++) $out[] = (hexdec($h[$i % 32]) % 3) + 1;
    return $out;
}
?>
<div class="cardsheet">
<?php foreach ($cards as [$s, $card]): $full = trim($s['first_name'] . ' ' . $s['last_name']); ?>
  <div class="idcard">
    <div class="idcard__top">
      <div class="idcard__mark">EY</div>
      <div>
        <div class="idcard__org"><?= e($st['org_name'] ?? ORG_NAME) ?></div>
        <div class="idcard__kind">Student identity card</div>
      </div>
    </div>
    <div class="idcard__body">
      <?php if ($s['photo']): ?>
        <img class="idcard__photo" src="<?= e(photo_url($s['photo'])) ?>" alt="">
      <?php else: ?>
        <div class="idcard__photo"><span>Attach<br>photo</span></div>
      <?php endif; ?>
      <div class="idcard__fields">
        <div class="idcard__name"><?= e($full) ?></div>
        <div class="idcard__no"><?= e($s['student_no']) ?></div>
        <div class="idcard__row"><b>Dept</b> <?= e($s['dept'] ?? '—') ?></div>
        <div class="idcard__row"><b>Valid to</b> <?= e(d($card['valid_until'], 'M Y')) ?> &nbsp; <b>Card</b> <?= e($card['card_no']) ?></div>
      </div>
    </div>
    <div class="idcard__foot">
      <span>If found, return to <?= e($st['org_phone'] ?? '') ?></span>
      <span class="idcard__bars">
        <?php foreach (bar_pattern($s['student_no']) as $h): ?><i style="height:<?= 2 + $h ?>mm"></i><?php endforeach; ?>
      </span>
    </div>
    <div class="idcard__stripe"><i></i><i></i><i></i></div>
  </div>
<?php endforeach; ?>
</div>
