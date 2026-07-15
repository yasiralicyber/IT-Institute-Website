<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Services\BackupService;

class SystemController extends Controller
{
    /* ---------------- Audit log ---------------- */

    public function audit(): void
    {
        Auth::requireAdmin();
        $action = trim((string) input('action', ''));
        $q = trim((string) input('q', ''));
        $where = '1=1'; $params = [];
        if ($action !== '') { $where .= ' AND action = ?'; $params[] = $action; }
        if ($q !== '') { $where .= ' AND (summary LIKE ? OR user_name LIKE ? OR entity LIKE ?)'; $like = "%$q%"; array_push($params, $like, $like, $like); }
        $rows = Database::all("SELECT * FROM audit_log WHERE {$where} ORDER BY id DESC LIMIT 300", $params);
        $actions = Database::all("SELECT DISTINCT action FROM audit_log ORDER BY action");
        $this->view('admin/audit', [
            'title' => 'Audit Log', 'heading' => 'Audit Log', 'rows' => $rows, 'actions' => $actions, 'action' => $action, 'q' => $q,
        ], 'admin/layouts/admin');
    }

    /* ---------------- Recycle bin ---------------- */

    public function recycle(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM trash ORDER BY id DESC LIMIT 300");
        $this->view('admin/recycle', ['title' => 'Recycle Bin', 'heading' => 'Recycle Bin', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function restore(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/recycle'); }
        $t = Database::first("SELECT * FROM trash WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if ($t) {
            self::reinsert($t['table_name'], json_decode($t['data'], true) ?: []);
            foreach (json_decode($t['children'] ?? '[]', true) ?: [] as $tbl => $rows) {
                foreach ($rows as $r) { self::reinsert($tbl, $r); }
            }
            Database::run("DELETE FROM trash WHERE id = ?", [$t['id']]);
            audit('restore', $t['table_name'], (int) $t['record_id'], 'Restored ' . $t['label']);
            flash('success', 'Record restored: ' . $t['label']);
        }
        redirect('/recycle');
    }

    public function purge(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/recycle'); }
        // Permanent deletion requires the admin password (owner confirmation).
        $admin = Auth::user();
        if (!$admin || !password_verify((string) input('password', ''), $admin['password'])) {
            flash('error', 'Permanent deletion requires your admin password.');
            redirect('/recycle');
        }
        $t = Database::first("SELECT * FROM trash WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if ($t) {
            Database::run("DELETE FROM trash WHERE id = ?", [$t['id']]);
            audit('purge', $t['table_name'], (int) $t['record_id'], 'Permanently deleted ' . $t['label']);
            flash('success', 'Permanently deleted.');
        }
        redirect('/recycle');
    }

    private static function reinsert(string $table, array $row): void
    {
        if (!$row) { return; }
        $cols = array_keys($row);
        $place = implode(',', array_fill(0, count($cols), '?'));
        $colList = '"' . implode('","', $cols) . '"';
        $verb = Database::driver() === 'sqlite' ? 'INSERT OR REPLACE' : 'REPLACE';
        try {
            Database::run("{$verb} INTO {$table} ({$colList}) VALUES ({$place})", array_values($row));
        } catch (\Throwable $e) { /* table/columns may have changed; skip */ }
    }

    /* ---------------- Backups ---------------- */

    public function backups(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM backups ORDER BY id DESC");
        $this->view('admin/backups', ['title' => 'Backups', 'heading' => 'Backups', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function createBackup(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/backups'); }
        try {
            [$file, $size] = BackupService::create((string) input('note', ''), Auth::id());
            audit('backup', 'backups', null, 'Created backup ' . $file);
            flash('success', 'Backup created: ' . $file . ' (' . self::human($size) . ').');
        } catch (\Throwable $e) {
            flash('error', 'Backup failed: ' . $e->getMessage());
        }
        redirect('/backups');
    }

    public function downloadBackup(array $params): void
    {
        Auth::requireAdmin();
        $b = Database::first("SELECT * FROM backups WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        $path = $b ? BackupService::dir() . '/' . $b['filename'] : '';
        if (!$b || !is_file($path)) { http_response_code(404); exit('Backup not found.'); }
        audit('backup_download', 'backups', (int) $b['id'], 'Downloaded ' . $b['filename']);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $b['filename'] . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function deleteBackup(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/backups'); }
        $b = Database::first("SELECT * FROM backups WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if ($b) {
            @unlink(BackupService::dir() . '/' . $b['filename']);
            Database::run("DELETE FROM backups WHERE id = ?", [$b['id']]);
            audit('backup_delete', 'backups', (int) $b['id'], 'Deleted backup ' . $b['filename']);
            flash('success', 'Backup deleted.');
        }
        redirect('/backups');
    }

    private static function human(int $bytes): string
    {
        $u = ['B', 'KB', 'MB', 'GB']; $i = 0;
        while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
        return round($bytes, 1) . ' ' . $u[$i];
    }
}
