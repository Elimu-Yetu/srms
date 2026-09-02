<?php
/**
 * Staff accounts. The super admin never appears in this list for anyone but
 * another super admin — hide_superadmin() does that in SQL, not in the view.
 */
require_once BASE_PATH . '/views/icons.php';

$roleFilter = getStr('role');
$search     = getStr('q');
$where      = ' WHERE 1=1 ' . hide_superadmin('u');
$args       = [];

if ($roleFilter !== '' && isset(ROLES[$roleFilter]) && ($roleFilter !== 'superadmin' || is_role('superadmin'))) { $where .= ' AND u.role = ? '; $args[] = $roleFilter; }
if ($search !== '') {
    $where .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?) ';
    $like = '%' . $search . '%';
    array_push($args, $like, $like, $like);
}

$list = rows("SELECT u.*, d.name AS dept, s.student_no
              FROM users u
              LEFT JOIN departments d ON d.id = u.department_id
              LEFT JOIN students s ON s.id = u.student_id
              $where ORDER BY
                CASE u.role WHEN 'superadmin' THEN 0 WHEN 'admin' THEN 1 WHEN 'manager' THEN 2
                            WHEN 'facilitator' THEN 3 WHEN 'kitchen' THEN 4 ELSE 5 END, u.name", $args);

$counts = [];
foreach (rows('SELECT u.role, COUNT(*) n FROM users u WHERE 1=1 ' . hide_superadmin('u') . ' GROUP BY u.role') as $c) {
    $counts[$c['role']] = (int) $c['n'];
}

$page_sub     = count($list) . ' account' . (count($list) === 1 ? '' : 's');
$page_actions = '<a class="btn btn--primary" href="' . e(url('users.form')) . '">' . icon('plus', 16) . ' New account</a>';
?>
<div class="panel">
  <form class="toolbar" method="get">
    <input type="hidden" name="r" value="users.index">
    <div class="field grow">
      <label for="q">Search</label>
      <input id="q" name="q" value="<?= e($search) ?>" placeholder="Name, email or phone">
    </div>
    <div class="field">
      <label for="role">Role</label>
      <select id="role" name="role" data-autosubmit>
        <option value="">All roles</option>
        <?php foreach (ROLES as $k => $lbl): ?>
          <?php if ($k === 'superadmin' && !is_role('superadmin')) continue; ?>
          <option value="<?= $k ?>" <?= $roleFilter === $k ? 'selected' : '' ?>>
            <?= e($lbl) ?><?= isset($counts[$k]) ? ' (' . $counts[$k] . ')' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn"><?= icon('search', 16) ?> Search</button>
    <?php if ($search !== '' || $roleFilter !== ''): ?>
      <a class="btn btn--ghost" href="<?= e(url('users.index')) ?>">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (!$list): ?>
    <div class="empty">
      <div class="empty__mark"><?= icon('shield', 22) ?></div>
      <h3>No accounts match</h3>
      <p>Clear the filters, or create the account you need.</p>
    </div>
  <?php else: ?>
    <div class="tablewrap">
      <table class="data">
        <thead><tr><th>Name</th><th>Role</th><th>Department</th><th>Contact</th><th>Last signed in</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $u): ?>
          <tr>
            <td>
              <div class="person">
                <span class="avatar"><?= e(initials($u['name'])) ?></span>
                <span><span class="person__name"><?= e($u['name']) ?></span>
                  <?php if ((int) $u['id'] === user_id()): ?> <?= badge('You', 'blue') ?><?php endif; ?>
                  <?php if ($u['must_reset']): ?> <?= badge('Must reset password', 'orange') ?><?php endif; ?>
                  <br><span class="person__meta mono"><?= e($u['email']) ?></span></span>
              </div>
            </td>
            <td><?= badge(ROLES[$u['role']] ?? $u['role'], $u['role'] === 'superadmin' ? 'red' : ($u['role'] === 'kitchen' ? 'orange' : 'neutral')) ?></td>
            <td class="tiny"><?= e($u['dept'] ?? ($u['student_no'] ? 'Student ' . $u['student_no'] : '—')) ?></td>
            <td class="tiny mono"><?= e($u['phone'] ?: '—') ?></td>
            <td class="tiny mono"><?= $u['last_login'] ? e(d($u['last_login'], 'd M Y H:i')) : '<span class="muted">never</span>' ?></td>
            <td><?= badge(ucfirst($u['status']), status_tone($u['status'])) ?></td>
            <td class="right">
              <a class="btn btn--sm btn--ghost" href="<?= e(url('users.form', ['id' => $u['id']])) ?>"><?= icon('edit', 15) ?></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="panel" style="margin-top:16px">
  <div class="panel__body tiny" style="color:var(--ink-soft);line-height:1.7">
    <strong>How accounts work.</strong>
    Line managers see only their own department. Facilitators see only the classes assigned to them.
    Students get a portal login from their student file, not from here.
    The kitchen account reaches nothing but the kitchen pages.
  </div>
</div>
