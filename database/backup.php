<?php
/**
 * Scheduled backup runner - point a Hostinger cron job at this file:
 *   php /home/uXXXX/itti/database/backup.php
 * It creates a full DB + media backup in storage/backups and keeps the
 * 14 most recent (older ones are pruned).
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Services\BackupService;

[$file, $size] = BackupService::create('Scheduled backup', null);
echo "Created backup: {$file} ({$size} bytes)\n";

// Retention: keep the newest 14.
$old = Database::all("SELECT * FROM backups ORDER BY id DESC LIMIT 100 OFFSET 14");
foreach ($old as $b) {
    @unlink(BackupService::dir() . '/' . $b['filename']);
    Database::run("DELETE FROM backups WHERE id = ?", [$b['id']]);
}
echo "Pruned " . count($old) . " old backups.\n";
