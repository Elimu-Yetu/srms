<?php
/** Monthly report inbox / my submissions. */
require_once BASE_PATH . '/views/icons.php';

$status = getStr('status');
$month  = getStr('month');
$mine   = my_course_ids();
$where  = ' WHERE mr.course_id IN (' . in_list($mine) . ') ';
$args   = [];
if (is_role('facilitator')) { $where .= ' AND mr.facilitator_id = ? '; $args[] = user_id(); }
if ($status !== '') { $where .= ' AND mr.status = ? '; $args[] = $status; }
if (preg_match('/^\d{4}-\d{2}$/', $month)) { $where .= ' AND mr.month_year = ? '; $args[] = $month; }

$list = rows("SELECT mr.*, c.code, c.name AS course, u.name AS facilitator, r.name AS reviewer
              FROM monthly_reports mr
              JOIN courses c ON c.id = mr.course_id
              LEFT JOIN users u ON u.id = mr.facilitator_id
              LEFT JOIN users r ON r.id = mr.reviewed_by
              $where ORDER BY mr.month_year DESC, c.code", $args);

$pending = (int) val('SELECT COUNT(*) FROM monthly_reports WHERE status = ? AND course_id IN (' . in_list($mine) . ')', ['submitted'], 0);

// Which of my courses still owe a report for the current month?
$due = [];
if (is_role('facilitator')) {
    foreach ($mine as $cid) {
        if (!val('SELECT 1 FROM monthly_reports WHERE facilitator_id = ? AND course_id = ? AND month_year = ?',
                 [user_id(), $cid, date('Y-m')])) {
            $due[] = row('SELECT id, code, name FROM courses WHERE id = ?', [$cid]);
        }
    }
}
$page_sub = can('monthly.review') ? $pending . ' waiting for acknowledgement' : 'One report per class per month.';
if (can('monthly.submit')) {
    $page_actions = '<a class="btn btn--primary" href="' . e(url('monthly.form')) . '">' . icon('plus', 16) . ' New monthly report</a>';
}
?>
<?php if ($due): ?>
  <div class="alert alert--warn">
    <?= icon('alert', 17) ?>
    <div><strong><?= e(month_label(date('Y-m'))) ?> is not submitted</strong> for
      <?php foreach ($due as $i => $c): ?>
        <a href="<?= e(url('monthly.form', ['course_id' => $c['id'], 'month' => date('Y-m')])) ?>"><?= e($c['code']) ?></a><?= $i < count($due) - 1 ? ', ' : '' ?>
      <?php endforeach; ?>.
    </div>
  </div>
<?php endif; ?>

<div class="panel">
  <div class="tabs">
    <a class="<?= $status === '' ? 'is-active' : '' ?>" href="<?= e(url('monthly.index')) ?>">All</a>
    <a class="<?= $status === 'submitted' ? 'is-active' : '' ?>" href="<?= e(url('monthly.index', ['status' => 'submitted'])) ?>">Submitted<?= $pending ? ' (' . $pending . ')' : '' ?></a>
    <a class="<?= $status === 'acknowledged' ? 'is-active' : '' ?>" href="<?= e(url('monthly.index', ['status' => 'acknowledged'])) ?>">Acknowledged</a>
    <a class="<?= $status === 'changes_requested' ? 'is-active' : '' ?>" href="<?= e(url('monthly.index', ['status' => 'changes_requested'])) ?>">Changes requested</a>
  </div>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('calendar-check', 22) ?></div>
      <h3>No monthly reports here</h3>
      <p><?= can('monthly.submit')
            ? 'The form is short: sessions held, topics covered, attendance, challenges, support needed, comments.'
            : 'Facilitators submit one report per class each month. None have arrived for this filter.' ?></p>
      <?php if (can('monthly.submit')): ?>
        <a class="btn btn--primary" href="<?= e(url('monthly.form')) ?>">Write this month's report</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Month</th><th>Class</th><?php if (!is_role('facilitator')): ?><th>Facilitator</th><?php endif; ?><th class="right">Sessions</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $m): ?>
          <tr>
            <td class="nowrap"><a href="<?= e(url('monthly.view', ['id' => $m['id']])) ?>"><strong><?= e(month_label($m['month_year'])) ?></strong></a></td>
            <td class="tiny"><?= e($m['code']) ?><div class="tiny muted"><?= e($m['course']) ?></div></td>
            <?php if (!is_role('facilitator')): ?><td class="tiny"><?= e($m['facilitator'] ?? '—') ?></td><?php endif; ?>
            <td class="right mono"><?= (int) $m['sessions_held'] ?></td>
            <td><?= badge(ucfirst(str_replace('_', ' ', $m['status'])), status_tone($m['status'])) ?></td>
            <td class="right nowrap">
              <a class="btn btn--sm btn--ghost" href="<?= e(url('monthly.view', ['id' => $m['id']])) ?>"><?= icon('eye', 15) ?></a>
              <a class="btn btn--sm btn--ghost" target="_blank" href="<?= e(url('monthly.print', ['id' => $m['id']])) ?>"><?= icon('printer', 15) ?></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
