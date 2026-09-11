<?php
/** Certificate registry. Students see only their own. */
require_once BASE_PATH . '/views/icons.php';

$isStudent = is_role('student');
$me = $isStudent ? my_student() : null;

if ($isStudent) {
    if (!$me) { echo '<div class="alert alert--warn"><div>Your login is not linked to a student file yet.</div></div>'; return; }
    $where = ' WHERE ce.student_id = ? ';
    $args = [$me['id']];
    $list = rows("SELECT ce.*, s.first_name, s.last_name, s.student_no, c.name AS course, c.code, u.name AS issuer
                  FROM certificates ce
                  JOIN students s ON s.id = ce.student_id
                  JOIN courses c ON c.id = ce.course_id
                  LEFT JOIN users u ON u.id = ce.issued_by
                  $where ORDER BY ce.issue_date DESC, ce.id DESC", $args);

    $page_title = 'My certificates';
    $page_sub = 'Every certificate carries a code anyone can verify.';
    ?>
    <div class="panel">
      <div class="tablewrap">
        <table class="data">
          <thead><tr><th>Serial</th><th>Course</th><th>Grade</th><th>Issued</th><th>Code</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($list as $c): ?>
            <tr>
              <td class="mono tiny"><?= e($c['serial_no']) ?></td>
              <td class="tiny"><?= e($c['course']) ?></td>
              <td class="tiny"><?= e($c['grade'] ?: '—') ?></td>
              <td class="tiny mono"><?= e(d($c['issue_date'])) ?></td>
              <td class="mono tiny"><?= e($c['verify_code']) ?></td>
              <td><?= badge(ucfirst($c['status']), status_tone($c['status'])) ?></td>
              <td class="right nowrap">
                <a class="btn btn--sm" target="_blank" href="<?= e(url('certificates.print', ['id' => $c['id']])) ?>"><?= icon('printer', 15) ?> Print</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php return; }

$search = getStr('q');
$where = ' WHERE 1=1 '; $args = [];
if ($search !== '') {
    $where .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_no LIKE ? OR s.phone LIKE ? OR s.email LIKE ?) ';
    $like = '%' . $search . '%'; array_push($args, $like, $like, $like, $like, $like);
}

$list = rows("SELECT s.id, s.student_no, s.first_name, s.last_name, s.status, d.name AS dept,
                    (SELECT COUNT(*) FROM certificates ce WHERE ce.student_id = s.id AND ce.status = 'valid') AS valid_count,
                    (SELECT MAX(issue_date) FROM certificates ce WHERE ce.student_id = s.id AND ce.status = 'valid') AS last_issued
              FROM students s
              LEFT JOIN departments d ON d.id = s.department_id
              $where ORDER BY s.first_name, s.last_name", $args);

$studentCerts = [];
if ($list) {
    $studentIds = array_map('intval', array_column($list, 'id'));
    if ($studentIds) {
        $studentCerts = rows('SELECT ce.id, ce.student_id, ce.serial_no, ce.verify_code, ce.issue_date, ce.status,
                                    c.code, c.name AS course
                             FROM certificates ce
                             JOIN courses c ON c.id = ce.course_id
                             WHERE ce.student_id IN (' . in_list($studentIds) . ')
                             ORDER BY ce.student_id, ce.issue_date DESC, ce.id DESC');
    }
}
$byStudent = [];
foreach ($studentCerts as $cert) { $byStudent[(int) $cert['student_id']][] = $cert; }

$page_title = 'Certificates';
$page_sub = count($list) . ' student' . (count($list) === 1 ? '' : 's') . ' in scope';
if (can('certificates.manage')) {
    $page_actions = '<a class="btn btn--primary" href="' . e(url('certificates.issue')) . '">' . icon('award', 16) . ' Issue a certificate</a>';
}
?>
<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="certificates.index">
    <div class="field grow">
      <label for="q">Search by student name, number or contact</label>
      <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Amina, EY-2026-… or a phone number">
    </div>
    <button class="btn"><?= icon('search', 16) ?> Search</button>
  </form>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('award', 22) ?></div>
      <h3>No students in view</h3>
      <p>Issue a certificate once a student completes a course. Use the student directory to find the right learner.</p>
      <?php if (can('certificates.manage')): ?>
        <a class="btn btn--primary" href="<?= e(url('certificates.issue')) ?>">Issue a certificate</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data" id="cert-student-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Department</th>
            <th class="center">Valid certificates</th>
            <th>Last issued</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $s): $full = trim($s['first_name'] . ' ' . $s['last_name']); $certs = $byStudent[(int) $s['id']] ?? []; ?>
          <tr>
            <td>
              <a class="person" href="<?= e(url('students.view', ['id' => $s['id']])) ?>">
                <span>
                  <span class="person__name"><?= e($full) ?></span><br>
                  <span class="person__meta"><?= e($s['student_no']) ?></span>
                </span>
              </a>
            </td>
            <td class="tiny"><?= e($s['dept'] ?? '—') ?></td>
            <td class="center"><?= (int) $s['valid_count'] ?></td>
            <td class="tiny mono"><?= $s['last_issued'] ? e(d($s['last_issued'])) : '—' ?></td>
            <td class="right nowrap">
              <?php if (can('certificates.manage')): ?>
                <a class="btn btn--sm" href="<?= e(url('certificates.issue', ['student_id' => $s['id']])) ?>"><?= icon('award', 15) ?> Issue</a>
                <?php if ($certs): foreach ($certs as $cert): ?>
                  <form method="post" action="<?= e(url('certificates.act')) ?>" style="display:inline;margin-left:6px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="certificate_id" value="<?= (int) $cert['id'] ?>">
                    <button class="btn btn--sm btn--danger" data-confirm="Unissue certificate <?= e($cert['serial_no']) ?>?"><?= icon('x', 12) ?> Unissue</button>
                  </form>
                <?php endforeach; endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="panel" style="margin-top:16px">
  <div class="panel__head"><h2>Issued certificates</h2></div>
  <div class="panel__body">
    <?php
    $recent = rows("SELECT ce.*, s.first_name, s.last_name, s.student_no, c.name AS course, c.code, u.name AS issuer
                   FROM certificates ce
                   JOIN students s ON s.id = ce.student_id
                   JOIN courses c ON c.id = ce.course_id
                   LEFT JOIN users u ON u.id = ce.issued_by
                   ORDER BY ce.issue_date DESC, ce.id DESC LIMIT 12");
    ?>
    <?php if (!$recent): ?>
      <div class="tiny muted">No certificates issued yet.</div>
    <?php else: ?>
      <div class="tablewrap">
        <table class="data">
          <thead><tr><th>Student</th><th>Serial</th><th>Course</th><th>Issued</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($recent as $c): ?>
            <tr>
              <td><a href="<?= e(url('students.view', ['id' => $c['student_id']])) ?>"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></a></td>
              <td class="mono tiny"><?= e($c['serial_no']) ?></td>
              <td class="tiny"><?= e($c['code']) ?></td>
              <td class="tiny mono"><?= e(d($c['issue_date'])) ?></td>
              <td><?= badge(ucfirst($c['status']), status_tone($c['status'])) ?></td>
              <td class="right nowrap">
                <a class="btn btn--sm" target="_blank" href="<?= e(url('certificates.print', ['id' => $c['id']])) ?>"><?= icon('printer', 15) ?> Print</a>
                <?php if (can('certificates.manage')): ?>
                  <form method="post" action="<?= e(url('certificates.act')) ?>" style="display:inline;margin-left:6px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="certificate_id" value="<?= (int) $c['id'] ?>">
                    <button class="btn btn--sm btn--danger" data-confirm="Unissue this certificate?"><?= icon('x', 12) ?> Unissue</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="panel" style="margin-top:16px">
  <div class="panel__body tiny muted">
    Anyone — an employer, another college — can confirm a certificate at
    <a href="<?= e(url('verify')) ?>" target="_blank">the verification page</a> using the printed code. No login needed.
  </div>
</div>
