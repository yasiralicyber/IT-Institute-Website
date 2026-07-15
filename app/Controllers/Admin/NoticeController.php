<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class NoticeController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM notices ORDER BY created_at DESC");
        $this->view('admin/notices', ['title' => 'Notices', 'heading' => 'Notices & Announcements', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/notices'); }
        $title = trim((string) input('title', ''));
        if ($title !== '') {
            Database::run("INSERT INTO notices (title,body,audience,is_published) VALUES (?,?,?,1)",
                [$title, trim((string) input('body', '')), input('audience', 'all')]);
            flash('success', 'Notice published.');
        }
        redirect('/notices');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/notices'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM notices WHERE id = ?", [$id])) {
            trash_record('notices', $row, 'Notice: ' . $row['title']);
            audit('delete', 'notices', $id, 'Deleted notice "' . $row['title'] . '"');
        }
        Database::run("DELETE FROM notices WHERE id = ?", [$id]);
        flash('success', 'Notice deleted.');
        redirect('/notices');
    }
}
