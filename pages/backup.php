<?php
/**
 * One-click backup. SQLite: copy the file (after a WAL checkpoint) and stream it.
 * MySQL: build a portable .sql dump in PHP, because mysqldump may not be on PATH
 * and we cannot rely on shell_exec being enabled on the machine.
 */
require_once BASE_PATH . '/views/icons.php';

$dir = BASE_PATH . '/storage/backups';
if (!is_dir($dir)) @mkdir($dir, 0775, true);

/** Quote a value for a SQL dump. */
function dump_value(?string $v): string
{
    if ($v === null) return 'NULL';
    return "'" . str_replace(
        ["\\", "'", "\n", "\r", "\0"],
        ["\\\\", "''", "\\n", "\\r", ""],
        $v
    ) . "'";
}

if (is_post()) {
    csrf_check();
    $stamp = date('Ymd-His');

    if (DB_DRIVER === 'sqlite') {
        $src = BASE_PATH . '/' . ltrim(DB_SQLITE_PATH, '/');
        if (!is_file($src)) $src = DB_SQLITE_PATH;
        try { db()->exec('PRAGMA wal_checkpoint(TRUNCATE)'); } catch (Throwable $e) { /* not fatal */ }

        $name = 'elimu-srms-' . $stamp . '.sqlite';
        $path = $dir . '/' . $name;
        if (!@copy($src, $path)) {
            flash('error', 'Could not write the backup. Check that <span class="mono">storage/backups</span> is writable.');
            redirect('backup');
        }
    } else {
        $name = 'elimu-srms-' . $stamp . '.sql';
        $path = $dir . '/' . $name;
        $fh = @fopen($path, 'w');
        if (!$fh) {
            flash('error', 'Could not write the backup. Check that <span class="mono">storage/backups</span> is writable.');
            redirect('backup');
        }
        fwrite($fh, "-- Elimu Yetu SRMS backup\n-- Taken " . date('Y-m-d H:i:s') . "\n"
                  . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        foreach (array_keys(schema_sql()) as $table) {
            if (!table_exists($table)) continue;
            $createRow = row("SHOW CREATE TABLE `$table`");
            $ddl = $createRow['Create Table'] ?? ($createRow['Create View'] ?? null);
            fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n");
            if ($ddl) fwrite($fh, $ddl . ";\n");
            foreach (rows("SELECT * FROM `$table`") as $r) {
                $cols = '`' . implode('`,`', array_keys($r)) . '`';
                $vals = implode(',', array_map(fn($v) => dump_value($v === null ? null : (string) $v), array_values($r)));
                fwrite($fh, "INSERT INTO `$table` ($cols) VALUES ($vals);\n");
            }
            fwrite($fh, "\n");
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }

    audit('backup', 'database', '', $name . ' · ' . number_format(filesize($path) / 1024, 1) . ' KB');
    flash('ok', 'Backup written: <span class="mono">' . e($name) . '</span>. Download it and copy it off this machine.');
    redirect('backup');
}

// Streaming a previously made backup.
$get = getStr('file');
if ($get !== '') {
    $safe = basename($get);
    $path = $dir . '/' . $safe;
    if (!preg_match('/^elimu-srms-[\d-]+\.(sqlite|sql)$/', $safe) || !is_file($path)) {
        flash('error', 'That backup file was not found.');
        redirect('backup');
    }
    audit('backup', 'download', '', $safe);
    while (ob_get_level()) ob_end_clean();   // we are inside the layout buffer
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $safe . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

$files = [];
foreach (glob($dir . '/elimu-srms-*') ?: [] as $f) {
    $files[] = ['name' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
}
usort($files, fn($a, $b) => $b['time'] <=> $a['time']);

$counts = [];
foreach (['students', 'users', 'attendance', 'marks', 'certificates', 'kitchen_records'] as $t) {
    $counts[$t] = table_exists($t) ? (int) val("SELECT COUNT(*) FROM $t", [], 0) : 0;
}
$page_sub = 'Database is ' . DB_DRIVER . '. A backup is one file — copy it to a flash disk kept outside the office.';
?>
<div class="grid grid--sidebar">
  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Take a backup now</h2></div>
      <form method="post" class="panel__body">
        <?= csrf_field() ?>
        <p class="tiny" style="color:var(--ink-soft);line-height:1.7;margin:0 0 14px">
          This writes a single file into <span class="mono">storage/backups</span> containing every record:
          <?= number_format($counts['students']) ?> students,
          <?= number_format($counts['attendance']) ?> attendance marks,
          <?= number_format($counts['marks']) ?> marks,
          <?= number_format($counts['certificates']) ?> certificates and
          <?= number_format($counts['kitchen_records']) ?> kitchen days.
          Uploaded photos live in <span class="mono">storage/uploads</span> and are not in the file, so copy that
          folder too when you archive a term.
        </p>
        <button class="btn btn--primary"><?= icon('database', 16) ?> Create backup</button>
      </form>
    </div>

    <div class="panel">
      <div class="panel__head"><h2>Backups on this machine</h2></div>
      <?php if (!$files): ?>
        <div class="empty" style="padding:28px">
          <div class="empty__mark"><?= icon('database', 20) ?></div>
          <h3>No backup taken yet</h3>
          <p>Take one now, then set a weekly habit — Friday before you go home.</p>
        </div>
      <?php else: ?>
        <div class="tablewrap">
          <table class="data">
            <thead><tr><th>File</th><th>Taken</th><th class="right">Size</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($files as $f): ?>
              <tr>
                <td class="mono tiny"><?= e($f['name']) ?></td>
                <td class="tiny"><?= e(date('d M Y H:i', $f['time'])) ?></td>
                <td class="right mono tiny"><?= number_format($f['size'] / 1024, 1) ?> KB</td>
                <td class="right">
                  <a class="btn btn--sm" href="<?= e(url('backup', ['file' => $f['name']])) ?>"><?= icon('download', 15) ?> Download</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stack">
    <div class="panel">
      <div class="panel__head"><h2>Restoring</h2></div>
      <div class="panel__body tiny" style="color:var(--ink-soft);line-height:1.75">
        <?php if (DB_DRIVER === 'sqlite'): ?>
          Stop using the system, then replace
          <span class="mono">storage/database/elimu_srms.sqlite</span> with the backup file, renaming it back to
          that exact name. Nothing else is needed.
        <?php else: ?>
          Create an empty database, then load the file:<br>
          <span class="mono">mysql -u root -p elimu_srms &lt; backup.sql</span>
        <?php endif; ?>
        <div style="margin-top:12px">
          Test a restore once a term on a spare copy. That is the only way to know the backup works.
        </div>
      </div>
    </div>
    <div class="panel">
      <div class="panel__head"><h2>A weekly routine</h2></div>
      <div class="panel__body tiny" style="color:var(--ink-soft);line-height:1.75">
        Friday afternoon: take a backup, download it, copy it to a flash disk, and keep that disk somewhere other
        than the room holding this computer. Three copies in two places beats one perfect copy in one place.
      </div>
    </div>
  </div>
</div>
