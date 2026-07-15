<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class ProjectController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT p.*, u.name AS author FROM projects p JOIN users u ON u.id = p.user_id
             ORDER BY CASE p.status WHEN 'pending' THEN 0 ELSE 1 END, p.created_at DESC");
        $this->view('admin/projects', ['title' => 'Projects', 'heading' => 'Student Projects', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function status(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/projects'); }
        $id = (int) ($params['id'] ?? 0);
        $new = in_array(input('status'), ['approved', 'rejected', 'pending'], true) ? input('status') : 'pending';
        Database::run("UPDATE projects SET status = ?, admin_note = ? WHERE id = ?", [$new, input('note'), $id]);
        $p = Database::first("SELECT user_id, title FROM projects WHERE id = ?", [$id]);
        if ($p) {
            Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
                [$p['user_id'], 'Project ' . $new, 'Your project "' . $p['title'] . '" was ' . $new . '.']);
            audit('project_' . $new, 'projects', $id, ucfirst($new) . ' project "' . $p['title'] . '"');
        }
        flash('success', 'Project ' . $new . '.');
        redirect('/projects');
    }

    public function feature(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/projects'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = (int) Database::scalar("SELECT featured FROM projects WHERE id = ?", [$id]);
        Database::run("UPDATE projects SET featured = ? WHERE id = ?", [$cur ? 0 : 1, $id]);
        flash('success', $cur ? 'Removed from featured.' : 'Marked as featured.');
        redirect('/projects');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/projects'); }
        $id = (int) ($params['id'] ?? 0);
        if ($p = Database::first("SELECT * FROM projects WHERE id = ?", [$id])) {
            trash_record('projects', $p, 'Project: ' . $p['title']);
            audit('delete', 'projects', $id, 'Deleted project "' . $p['title'] . '"');
        }
        Database::run("DELETE FROM projects WHERE id = ?", [$id]);
        flash('success', 'Project deleted.');
        redirect('/projects');
    }
}
