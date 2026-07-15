<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Imports\Registry;
use App\Imports\Reader;
use App\Imports\ImportService;

class ImportController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $recent = Database::all("SELECT * FROM import_sessions ORDER BY id DESC LIMIT 8");
        $this->view('admin/import-index', [
            'title' => 'Imports', 'heading' => 'Bulk Import', 'sections' => Registry::all(), 'recent' => $recent,
        ], 'admin/layouts/admin');
    }

    public function history(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM import_sessions ORDER BY id DESC LIMIT 200");
        $this->view('admin/import-history', ['title' => 'Import History', 'heading' => 'Import History', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function template(array $params): void
    {
        Auth::requireAdmin();
        $def = Registry::get((string) ($params['section'] ?? ''));
        if (!$def) { redirect('/imports'); }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $params['section'] . '-template.csv"');
        echo ImportService::template($def);
        exit;
    }

    public function export(array $params): void
    {
        Auth::requireAdmin();
        $section = (string) ($params['section'] ?? '');
        $def = Registry::get($section);
        if (!$def) { redirect('/imports'); }
        $cols = array_merge(['id'], array_keys($def['fields']));
        $where = isset($def['fixed']['role']) ? " WHERE role = " . Database::pdo()->quote($def['fixed']['role']) : '';
        $rows = Database::all("SELECT * FROM {$def['table']}{$where} ORDER BY id");
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $section . '-export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $cols);
        foreach ($rows as $r) {
            $line = [];
            foreach ($cols as $c) { $line[] = self::antiInjection((string) ($r[$c] ?? '')); }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    public function start(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/imports'); }
        $section = (string) input('section', '');
        $def = Registry::get($section);
        if (!$def) { flash('error', 'Unknown section.'); redirect('/imports'); }

        $dir = BASE_PATH . '/storage/imports';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $token = bin2hex(random_bytes(8));
        $source = (string) input('source', 'file');
        $storagePath = ''; $filename = '';

        try {
            if ($source === 'paste') {
                $parsed = Reader::fromText((string) input('paste', ''));
                $storagePath = self::writeCsv($dir . "/{$token}.csv", $parsed);
                $filename = 'pasted-data.csv';
            } elseif ($source === 'google') {
                $parsed = Reader::fromGoogleSheet(trim((string) input('sheet_url', '')));
                $storagePath = self::writeCsv($dir . "/{$token}.csv", $parsed);
                $filename = 'google-sheet.csv';
            } else {
                if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('Please choose a CSV or Excel file.');
                }
                $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['csv', 'xlsx'], true)) { throw new \RuntimeException('Only .csv or .xlsx files are supported.'); }
                $storagePath = $dir . "/{$token}.{$ext}";
                move_uploaded_file($_FILES['file']['tmp_name'], $storagePath) || rename($_FILES['file']['tmp_name'], $storagePath);
                $filename = $_FILES['file']['name'];
            }
            $data = Reader::read($storagePath, pathinfo($storagePath, PATHINFO_EXTENSION));
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            redirect('/imports');
        }

        if (empty($data['headers'])) { flash('error', 'No columns detected in the file.'); redirect('/imports'); }

        $id = Database::run(
            "INSERT INTO import_sessions (section,source,filename,storage_path,columns,total_rows,status,created_by,created_by_name)
             VALUES (?,?,?,?,?,?,'mapping',?,?)",
            [$section, $source, $filename, $storagePath, json_encode($data['headers']), count($data['rows']),
             Auth::id(), Auth::user()['name'] ?? '']
        );
        redirect('/imports/' . $id);
    }

    public function map(array $params): void
    {
        Auth::requireAdmin();
        $session = $this->session((int) ($params['id'] ?? 0));
        $def = Registry::get($session['section']);
        $headers = json_decode($session['columns'], true) ?: [];
        $data = Reader::read($session['storage_path'], pathinfo($session['storage_path'], PATHINFO_EXTENSION));

        $this->view('admin/import-map', [
            'title' => 'Map Columns', 'heading' => 'Import: ' . $def['label'],
            'session' => $session, 'def' => $def, 'headers' => $headers,
            'preview' => array_slice($data['rows'], 0, 8),
            'suggest' => ImportService::suggestMapping($def, $headers),
        ], 'admin/layouts/admin');
    }

    public function validateStep(array $params): void
    {
        Auth::requireAdmin();
        $session = $this->session((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/imports/' . $session['id']); }
        $def = Registry::get($session['section']);

        $mapping = [];
        foreach ($def['fields'] as $field => $cfg) {
            $col = input('map_' . $field);
            $mapping[$field] = ($col === '' || $col === null) ? null : (int) $col;
        }
        $strategy = in_array(input('dupe_strategy'), ['skip', 'update', 'create'], true) ? input('dupe_strategy') : 'skip';
        Database::run("UPDATE import_sessions SET mapping=?, dupe_strategy=?, status='ready' WHERE id=?",
            [json_encode($mapping), $strategy, $session['id']]);

        $data = Reader::read($session['storage_path'], pathinfo($session['storage_path'], PATHINFO_EXTENSION));
        $result = ImportService::validate($def, $data['rows'], $mapping);

        $this->view('admin/import-preview', [
            'title' => 'Review Import', 'heading' => 'Review: ' . $def['label'],
            'session' => Database::first("SELECT * FROM import_sessions WHERE id=?", [$session['id']]),
            'def' => $def, 'validCount' => count($result['valid']), 'errors' => $result['errors'], 'strategy' => $strategy,
        ], 'admin/layouts/admin');
    }

    public function execute(array $params): void
    {
        Auth::requireAdmin();
        $session = $this->session((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/imports/' . $session['id']); }
        if ($session['status'] === 'completed') { redirect('/imports/' . $session['id'] . '/result'); }
        ImportService::execute($session);
        flash('success', 'Import complete.');
        redirect('/imports/' . $session['id'] . '/result');
    }

    public function result(array $params): void
    {
        Auth::requireAdmin();
        $session = $this->session((int) ($params['id'] ?? 0));
        $this->view('admin/import-result', [
            'title' => 'Import Result', 'heading' => 'Import Result',
            'session' => $session, 'def' => Registry::get($session['section']),
            'errors' => json_decode($session['errors'] ?? '[]', true) ?: [],
        ], 'admin/layouts/admin');
    }

    public function rollback(array $params): void
    {
        Auth::requireAdmin();
        $session = $this->session((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/imports/history'); }
        $admin = Auth::user();
        if (!$admin || !password_verify((string) input('password', ''), $admin['password'])) {
            flash('error', 'Rollback needs your admin password.');
            redirect('/imports/' . $session['id'] . '/result');
        }
        $n = ImportService::rollback($session);
        flash('success', "Rolled back {$n} imported records.");
        redirect('/imports/history');
    }

    private function session(int $id): array
    {
        $s = Database::first("SELECT * FROM import_sessions WHERE id = ?", [$id]);
        if (!$s) { flash('error', 'Import session not found.'); redirect('/imports'); }
        return $s;
    }

    private static function writeCsv(string $path, array $parsed): string
    {
        $fh = fopen($path, 'w');
        fputcsv($fh, $parsed['headers']);
        foreach ($parsed['rows'] as $r) { fputcsv($fh, $r); }
        fclose($fh);
        return $path;
    }

    /** Prevent CSV formula injection in exports. */
    private static function antiInjection(string $v): string
    {
        return (isset($v[0]) && in_array($v[0], ['=', '+', '-', '@'], true)) ? "'" . $v : $v;
    }
}
