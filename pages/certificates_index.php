<?php
/** Certificate registry. Students see only their own. */
require_once BASE_PATH . '/views/icons.php';

$isStudent = is_role('student');
$me = $isStudent ? my_student() : null;

$where = ' WHERE 1=1 '; $args = [];
if ($isStudent) {
    if (!$me) { echo '<div class="alert alert--warn"><div>Your login is not linked to a student file yet.</div></div>'; return; }
    $where .= ' AND ce.student_id = ? '; $args[] = $me['id'];
}
$search = getStr('q');
if ($search !== '') {
    $where .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_no LIKE ? OR ce.serial_no LIKE ? OR ce.verify_code LIKE ?) ';
    $like = '%' . $search . '%'; array_push($args, $like, $like, $like, $like, $like);
}

$list = rows("SELECT ce.*, s.first_name, s.last_name, s.student_no, c.name AS course, c.code, u.name AS issuer
              FROM certificates ce
              JOIN students s ON s.id = ce.student_id
              JOIN courses c ON c.id = ce.course_id
              LEFT JOIN users u ON u.id = ce.issued_by
              $where ORDER BY ce.issue_date DESC, ce.id DESC", $args);

$page_title = $isStudent ? 'My certificates' : 'Certificates';
$page_sub   = $isStudent ? 'Every certificate carries a code anyone can verify.' : count($list) . ' issued';
if (can('certificates.manage')) {
    $page_actions = '<a class="btn btn--primary" href="' . e(url('certificates.issue')) . '">' . icon('award', 16) . ' Issue a certificate</a>';
}
?>
<div class="panel">
  <?php if (!$isStudent): ?>
    <form class="toolbar" method="get">
      <input type="hidden" name="r" value="certificates.index">
      <div class="field grow">
        <label for="q">Search by student, serial or verification code</label>
        <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="EY-CERT-… or a name">
      </div>
      <button class="btn"><?= icon('search', 16) ?> Search</button>
    </form>
  <?php endif; ?>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('award', 22) ?></div>
      <h3><?= $isStudent ? 'No certificate yet' : 'Nothing issued yet' ?></h3>
      <p><?= $isStudent
          ? 'A certificate appears here once you complete your course and the office issues it.'
          : 'Issue a certificate once a student has completed a course. Each one gets a serial number and a verification code.' ?></p>
      <?php if (can('certificates.manage')): ?>
        <a class="btn btn--primary" href="<?= e(url('certificates.issue')) ?>">Issue a certificate</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Serial</th><?php if (!$isStudent): ?><th>Student</th><?php endif; ?><th>Course</th><th>Grade</th><th>Issued</th><th>Code</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $c): ?>
          <tr>
            <td class="mono tiny"><?= e($c['serial_no']) ?></td>
            <?php if (!$isStudent): ?>
              <td><a href="<?= e(url('students.view', ['id' => $c['student_id']])) ?>"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></a>
                  <div class="tiny mono muted"><?= e($c['student_no']) ?></div></td>
            <?php endif; ?>
            <td class="tiny"><?= e($c['course']) ?></td>
            <td class="tiny"><?= e($c['grade'] ?: '—') ?></td>
            <td class="tiny mono"><?= e(d($c['issue_date'])) ?></td>
            <td class="mono tiny"><?= e($c['verify_code']) ?></td>
            <td><?= badge(ucfirst($c['status']), status_tone($c['status'])) ?></td>
            <td class="right nowrap">
              <a class="btn btn--sm" target="_blank" href="<?= e(url('certificates.print', ['id' => $c['id']])) ?>"><?= icon('printer', 15) ?> Print</a>
              <?php if (can('certificates.manage')): ?>
                <form method="post" action="<?= e(url('certificates.act')) ?>" style="display:inline;margin-left:6px">
                  <?= csrf_field() ?>
                  <input type="hidden" name="certificate_id" value="<?= (int) $c['id'] ?>">
                  <button class="btn btn--sm btn--danger" data-confirm="Unissue this certificate? This cannot be undone."><?= icon('x', 12) ?> Unissue</button>
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

<div class="panel" style="margin-top:16px">
  <div class="panel__body tiny muted">
    Anyone — an employer, another college — can confirm a certificate at
    <a href="<?= e(url('verify')) ?>" target="_blank">the verification page</a> using the printed code. No login needed.
  </div>
</div>
