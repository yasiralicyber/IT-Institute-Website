<?php
namespace App\Services;

use App\Core\Database;

/**
 * Creates encrypted-at-rest-optional backups of the database + uploaded media,
 * stored OUTSIDE the web root (storage/backups). Works on shared hosting with
 * no shell access: SQLite is copied; MySQL is dumped in pure PHP via PDO.
 */
class BackupService
{
    public static function dir(): string
    {
        $dir = BASE_PATH . '/storage/backups';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        return $dir;
    }

    /** Create a backup; returns [filename, size, type]. */
    public static function create(string $note = '', ?int $by = null): array
    {
        $dir = self::dir();
        $stamp = date('Ymd-His');
        $driver = Database::driver();

        // 1) Database dump file.
        $dbName = $driver === 'sqlite' ? "db-{$stamp}.sqlite" : "db-{$stamp}.sql";
        $dbPath = $dir . '/' . $dbName;
        if ($driver === 'sqlite') {
            @copy(config('db.sqlite'), $dbPath);
        } else {
            file_put_contents($dbPath, self::mysqlDump());
        }

        // 2) Zip database + media if ZipArchive is available.
        $type = 'full';
        if (class_exists('ZipArchive')) {
            $zipName = "backup-{$stamp}.zip";
            $zipPath = $dir . '/' . $zipName;
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $zip->addFile($dbPath, 'database/' . $dbName);
            self::addDir($zip, BASE_PATH . '/storage/uploads', 'media');
            $zip->close();
            @unlink($dbPath); // keep only the zip
            $filename = $zipName;
        } else {
            $filename = $dbName; // DB only (no zip support)
            $type = 'database';
        }

        $size = is_file($dir . '/' . $filename) ? filesize($dir . '/' . $filename) : 0;
        Database::run("INSERT INTO backups (filename,type,size,note,created_by) VALUES (?,?,?,?,?)",
            [$filename, $type, $size, $note, $by]);

        return [$filename, $size, $type];
    }

    private static function addDir(\ZipArchive $zip, string $path, string $prefix): void
    {
        if (!is_dir($path)) { return; }
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $local = $prefix . '/' . substr($file->getPathname(), strlen($path) + 1);
                $zip->addFile($file->getPathname(), str_replace('\\', '/', $local));
            }
        }
    }

    /** Pure-PHP MySQL dump (schema + data) via PDO - no shell needed. */
    private static function mysqlDump(): string
    {
        $pdo = Database::pdo();
        $out = "-- ITTI backup " . date('c') . "\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            $create = $pdo->query("SHOW CREATE TABLE `{$t}`")->fetch(\PDO::FETCH_ASSOC);
            $out .= "DROP TABLE IF EXISTS `{$t}`;\n" . ($create['Create Table'] ?? '') . ";\n\n";
            $rows = $pdo->query("SELECT * FROM `{$t}`");
            while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
                $cols = '`' . implode('`,`', array_keys($row)) . '`';
                $vals = implode(',', array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row)));
                $out .= "INSERT INTO `{$t}` ({$cols}) VALUES ({$vals});\n";
            }
            $out .= "\n";
        }
        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }
}
