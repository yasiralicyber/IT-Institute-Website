<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class FacilityController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM facilities ORDER BY sort, id");
        $this->view('admin/facilities', ['title' => 'Facilities', 'heading' => 'Our Facilities', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/facilities'); }
        $title = trim((string) input('title', ''));
        if ($title === '') {
            flash('error', 'Title is required.');
            redirect('/facilities');
        }
        $image = store_upload('image', 'facilities', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM facilities") + 1;
        Database::run("INSERT INTO facilities (title,description,image,sort,is_published) VALUES (?,?,?,?,1)",
            [$title, trim((string) input('description', '')), $image, $sort]);
        audit('create', 'facilities', null, 'Added facility "' . $title . '".');
        flash('success', 'Facility added.');
        redirect('/facilities');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/facilities'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = Database::first("SELECT image FROM facilities WHERE id=?", [$id]);
        if (!$cur) { redirect('/facilities'); }
        $image = store_upload('image', 'facilities', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304) ?: ($cur['image'] ?? null);
        Database::run("UPDATE facilities SET title=?, description=?, image=?, is_published=? WHERE id=?",
            [trim((string) input('title', '')), trim((string) input('description', '')), $image, input('is_published') ? 1 : 0, $id]);
        audit('update', 'facilities', $id, 'Updated facility.');
        flash('success', 'Facility updated.');
        redirect('/facilities');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/facilities'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM facilities WHERE id = ?", [$id])) {
            trash_record('facilities', $row, 'Facility: ' . ($row['title'] ?: ('#' . $row['id'])));
            audit('delete', 'facilities', $id, 'Deleted facility "' . ($row['title'] ?: ('#' . $row['id'])) . '".');
        }
        Database::run("DELETE FROM facilities WHERE id = ?", [$id]);
        flash('success', 'Deleted.');
        redirect('/facilities');
    }

    public function move(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/facilities'); }
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM facilities WHERE id=?", [$id]);
        if ($row) {
            $dir = input('dir') === 'up' ? 'up' : 'down';
            $neighbor = $dir === 'up'
                ? Database::first("SELECT * FROM facilities WHERE sort < ? ORDER BY sort DESC, id DESC LIMIT 1", [$row['sort']])
                : Database::first("SELECT * FROM facilities WHERE sort > ? ORDER BY sort ASC, id ASC LIMIT 1", [$row['sort']]);
            if ($neighbor) {
                Database::run("UPDATE facilities SET sort=? WHERE id=?", [$neighbor['sort'], $row['id']]);
                Database::run("UPDATE facilities SET sort=? WHERE id=?", [$row['sort'], $neighbor['id']]);
            }
        }
        redirect('/facilities');
    }
}
