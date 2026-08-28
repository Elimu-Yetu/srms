<?php
/** Notice board. Staff post; students and facilitators read. */
require_once BASE_PATH . '/views/icons.php';

$mine   = my_course_ids();
$errors = [];

if (is_post() && can('notices.manage')) {
    csrf_check();
    if (post('do') === 'delete') {
        $nid = postInt('id');
        $n = row('SELECT * FROM notices WHERE id = ?', [$nid]);
        if ($n && ((int) $n['posted_by'] === user_id() || is_admin())) {
            q('DELETE FROM notices WHERE id = ?', [$nid]);
            audit('delete', 'notices', $nid, $n['title']);
            flash('ok', 'Notice removed.');
        }
        redirect('notices.index');
    }

    $title = post('title');
    $body  = post('body');
    $cid   = postInt('course_id') ?: null;
    $until = postNull('show_until');

    if ($title === '') $errors[] = 'Give the notice a title.';
    if ($body === '')  $errors[] = 'Write the notice.';
    if ($cid && !in_array($cid, array_map('intval', $mine), true)) $errors[] = 'Choose one of your courses, or post to everyone.';

    if (!$errors) {
        $id = insert('notices', [
            'course_id' => $cid, 'title' => $title, 'body' => $body, 'show_until' => $until,
            'posted_by' => user_id(), 'created_at' => now(),
        ]);
        audit('create', 'notices', $id, $title);
        flash('ok', 'Notice posted.');
        redirect('notices.index');
    }
}

$today = date('Y-m-d');
if (is_role('student')) {
    $list = rows('SELECT n.*, u.name AS author, c.code FROM notices n
                  LEFT JOIN users u ON u.id = n.posted_by
                  LEFT JOIN courses c ON c.id = n.course_id
                  WHERE (n.course_id IS NULL OR n.course_id IN (' . in_list($mine) . '))
                    AND (n.show_until IS NULL OR n.show_until >= ?)
                  ORDER BY n.created_at DESC LIMIT 60', [$today]);
} else {
    $list = rows('SELECT n.*, u.name AS author, c.code FROM notices n
                  LEFT JOIN users u ON u.id = n.posted_by
                  LEFT JOIN courses c ON c.id = n.course_id
                  ORDER BY n.created_at DESC LIMIT 80');
}
$courses = can('notices.manage')
    ? rows('SELECT id, code, name FROM courses WHERE id IN (' . in_list($mine) . ') ORDER BY name')
    : [];
$page_sub = is_role('student') ? 'Announcements for you and your classes.' : 'Posted notices, newest first.';
?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert--error"><?= icon('alert', 17) ?><div><?= $er ?></div></div>
<?php endforeach; ?>

<div class="grid <?= can('notices.manage') ? 'grid--sidebar' : '' ?>">
  <div class="stack">
    <?php if (!$list): ?>
      <div class="panel"><div class="empty">
        <div class="empty__mark"><?= icon('bell', 22) ?></div>
        <h3>Nothing on the board</h3>
        <p><?= can('notices.manage') ? 'Post the first notice — it shows on every dashboard straight away.' : 'Check back later.' ?></p>
      </div></div>
    <?php else: ?>
      <?php foreach ($list as $n):
        $expired = $n['show_until'] && $n['show_until'] < $today; ?>
        <div class="panel" style="<?= $expired ? 'opacity:.62' : '' ?>">
          <div class="panel__body">
            <div style="display:flex;gap:10px;align-items:baseline;flex-wrap:wrap">
              <h2 style="margin:0"><?= e($n['title']) ?></h2>
              <?php if ($n['code']): ?><?= badge($n['code'], 'blue') ?><?php else: ?><?= badge('Everyone', 'neutral') ?><?php endif; ?>
              <?php if ($expired): ?><?= badge('Expired', 'red') ?><?php endif; ?>
              <span class="tiny muted" style="margin-left:auto"><?= e(d($n['created_at'], 'd M Y')) ?></span>
            </div>
            <div class="tiny" style="margin-top:8px;line-height:1.7"><?= nl2br(e($n['body'])) ?></div>
            <div class="tiny muted" style="margin-top:10px;display:flex;align-items:center;gap:10px">
              <span><?= e($n['author'] ?? 'Office') ?></span>
              <?php if ($n['show_until']): ?><span>· shows until <?= e(d($n['show_until'])) ?></span><?php endif; ?>
              <?php if (can('notices.manage') && ((int) $n['posted_by'] === user_id() || is_admin())): ?>
                <form method="post" style="margin-left:auto">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                  <button class="btn btn--sm btn--ghost" data-confirm="Remove this notice?"><?= icon('x', 14) ?> Remove</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if (can('notices.manage')): ?>
    <div class="panel">
      <div class="panel__head"><h2>Post a notice</h2></div>
      <form method="post" class="panel__body">
        <?= csrf_field() ?>
        <div class="field">
          <label for="title">Title <span class="req">*</span></label>
          <input id="title" name="title" required placeholder="Graduation rehearsal on Friday">
        </div>
        <div class="field">
          <label for="body">Notice <span class="req">*</span></label>
          <textarea id="body" name="body" rows="6" required></textarea>
        </div>
        <div class="field">
          <label for="course_id">Who sees it</label>
          <select id="course_id" name="course_id">
            <option value="">Everyone</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int) $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="show_until">Hide after</label>
          <input id="show_until" name="show_until" type="date">
          <div class="hint">Leave empty to keep it on the board.</div>
        </div>
        <button class="btn btn--primary"><?= icon('bell', 16) ?> Post notice</button>
      </form>
    </div>
  <?php endif; ?>
</div>
