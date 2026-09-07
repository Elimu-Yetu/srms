<?php

require_once BASE_PATH . '/views/icons.php';

$action = getStr('action');
$search = getStr('q');
[$page, $perPage, $offset] = paging(40);

$where = " WHERE 1=1 " . hide_superadmin_audit('a');
$args  = [];
if ($action !== '') { $where .= ' AND a.action = ? '; $args[] = $action; }
if ($search !== '') {
    $where .= ' AND (a.user_name LIKE ? OR a.details LIKE ? OR a.entity LIKE ?) ';
    $like = '%' . $search . '%';
    array_push($args, $like, $like, $like);
}

$total = (int) val("SELECT COUNT(*) FROM audit_logs a $where", $args, 0);
$list  = rows("SELECT a.* FROM audit_logs a $where ORDER BY a.created_at DESC, a.id DESC LIMIT $perPage OFFSET $offset", $args);

$actions = array_column(rows("SELECT DISTINCT a.action FROM audit_logs a $where ORDER BY a.action", $args), 'action');

$tone = [
    'login' => 'green', 'logout' => 'neutral', 'login_failed' => 'red',
    'create' => 'blue', 'update' => 'orange', 'delete' => 'red',
    'attendance' => 'green', 'marks' => 'blue', 'review' => 'orange',
    'certificate' => 'green', 'idcard' => 'blue', 'settings' => 'orange', 'backup' => 'blue',
];
$page_sub = number_format($total) . ' entr' . ($total === 1 ? 'y' : 'ies');
?>
<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="audit">
    <div class="field grow">
      <label for="q">Search</label>
      <input id="q" name="q" value="<?= e($search) ?>" placeholder="Person, record or detail">
    </div>
    <div class="field">
      <label for="action">Action</label>
      <select id="action" name="action" data-autosubmit>
        <option value="">Everything</option>
        <?php foreach ($actions as $a): ?>
          <option value="<?= e($a) ?>" <?= $action === $a ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $a))) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn"><?= icon('search', 16) ?> Filter</button>
    <?php if ($search !== '' || $action !== ''): ?>
      <a class="btn btn--ghost" href="<?= e(url('audit')) ?>">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('shield', 22) ?></div>
      <h3>Nothing logged for that filter</h3>
      <p>The log records sign-ins, registrations, attendance, marks, reviews, certificates and settings changes.</p>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data compact">
        <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Record</th><th>Detail</th><th>From</th></tr></thead>
        <tbody>
        <?php foreach ($list as $l): ?>
          <tr>
            <td class="tiny mono nowrap"><?= e(d($l['created_at'], 'd M Y H:i')) ?></td>
            <td class="tiny">
              <?= e($l['user_name'] ?: 'anonymous') ?>
              <?php if ($l['role']): ?><div class="tiny muted"><?= e(ROLES[$l['role']] ?? $l['role']) ?></div><?php endif; ?>
            </td>
            <td><?= badge(ucfirst(str_replace('_', ' ', $l['action'])), $tone[$l['action']] ?? 'neutral') ?></td>
            <td class="tiny mono"><?= e($l['entity'] ?: '—') ?><?= $l['entity_id'] ? ' #' . e($l['entity_id']) : '' ?></td>
            <td class="tiny"><?= e($l['details'] ?: '') ?></td>
            <td class="tiny mono muted"><?= e($l['ip'] ?: '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= pager($total, $perPage, $page, 'audit', ['q' => $search, 'action' => $action]) ?>
  <?php endif; ?>
</div>
