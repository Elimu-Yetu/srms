<?php
/**
 * Database access. One thin layer over PDO so every query in the system
 * uses prepared statements — no string-built SQL anywhere.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        if (DB_DRIVER === 'sqlite') {
            $dir = dirname(DB_SQLITE_PATH);
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $opts);
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA journal_mode = WAL');   // survives concurrent readers
            $pdo->exec('PRAGMA busy_timeout = 5000');
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $hint = DB_DRIVER === 'mysql'
            ? 'Check DB_NAME / DB_USER / DB_PASS in app/config.php, and that MySQL is running.'
            : 'Check that the storage/database folder is writable by the web server.';
        echo '<h2 style="font:600 18px system-ui;color:#8a2b12">Cannot reach the database</h2>'
           . '<p style="font:14px system-ui">' . htmlspecialchars($hint) . '</p>'
           . (DEBUG ? '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>' : '');
        exit;
    }
    return $pdo;
}

/** Run a statement. Returns the PDOStatement. */
function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

/** All matching rows. */
function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/** First matching row, or null. */
function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

/** First column of the first row (counts, sums, single fields). */
function val(string $sql, array $params = [], $default = null)
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? $default : $v;
}

/** INSERT helper. Returns new id. */
function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql  = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES ('
          . implode(',', array_map(fn($c) => ':' . $c, $cols)) . ')';
    q($sql, $data);
    return (int) db()->lastInsertId();
}

/** UPDATE helper: update('students', ['phone'=>'07..'], 'id = :id', ['id'=>4]) */
function update(string $table, array $data, string $where, array $whereParams = []): int
{
    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    return q("UPDATE $table SET $set WHERE $where", $data + $whereParams)->rowCount();
}

/** True when the table already exists (used by install.php). */
function table_exists(string $table): bool
{
    try {
        if (DB_DRIVER === 'sqlite') {
            return (bool) val("SELECT 1 FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
        }
        return (bool) val('SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?', [DB_NAME, $table]);
    } catch (Throwable $e) {
        return false;
    }
}
