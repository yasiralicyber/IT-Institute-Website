<?php
/**
 * Non-destructive: adds the Website-Content CMS tables only.
 * Uses CREATE TABLE IF NOT EXISTS — never DROPs, never touches existing
 * tables or data. Safe to run repeatedly, safe on live production data.
 *
 * Run once on the live site after deploying this feature's code:
 *   php database/upgrade_website_content.php
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$driver   = Database::driver();
$isSqlite = $driver === 'sqlite';
$PK   = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
$INT  = $isSqlite ? 'INTEGER' : 'INT';
$BOOL = $isSqlite ? 'INTEGER' : 'TINYINT(1)';
$TS   = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
$SUF  = $isSqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

$tables = require __DIR__ . '/schema/website_content.php';

$pdo = Database::pdo();
foreach ($tables as $name => $cols) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$name} ({$cols}){$SUF}");
    echo "  ✓ ensured table {$name}\n";
}
echo "Website-content upgrade complete ({$driver}). No existing tables were touched.\n";
