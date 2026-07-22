<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class AwardController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM awards ORDER BY sort, id");
        $this->view('admin/awards', ['title' => 'Awards', 'heading' => 'Awards & Achievements', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('admin/award-form', ['title' => 'Add Award', 'heading' => 'Add Award', 'award' => null], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/awards'); }
        $title = trim((string) input('title', ''));
        if ($title === '') { flash('error', 'Title is required.'); redirect('/awards/create'); }
        $image = store_upload('image', 'awards', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM awards") + 1;
        Database::run(
            "INSERT INTO awards (year,title,org,description,image,sort,is_published) VALUES (?,?,?,?,?,?,?)",
            [input('year'), $title, input('org'), input('description'), $image, $sort, input('is_published') ? 1 : 0]);
        flash('success', 'Award added.');
        redirect('/awards');
    }

    public function edit(array $params): void
    {
        Auth::requireAdmin();
        $award = Database::first("SELECT * FROM awards WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if (!$award) { redirect('/awards'); }
        $this->view('admin/award-form', ['title' => 'Edit Award', 'heading' => 'Edit: ' . $award['title'], 'award' => $award], 'admin/layouts/admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/awards'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = Database::first("SELECT image FROM awards WHERE id = ?", [$id]);
        $image = store_upload('image', 'awards', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304) ?: ($cur['image'] ?? null);
        Database::run(
            "UPDATE awards SET year=?,title=?,org=?,description=?,image=?,is_published=? WHERE id=?",
            [input('year'), input('title'), input('org'), input('description'), $image, input('is_published') ? 1 : 0, $id]);
        flash('success', 'Award updated.');
        redirect('/awards');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/awards'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM awards WHERE id = ?", [$id])) {
            trash_record('awards', $row, 'Award: ' . $row['title']);
            audit('delete', 'awards', $id, 'Deleted award "' . $row['title'] . '"');
        }
        Database::run("DELETE FROM awards WHERE id = ?", [$id]);
        flash('success', 'Award removed.');
        redirect('/awards');
    }

    public function move(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/awards'); }
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM awards WHERE id=?", [$id]);
        if ($row) {
            $dir = input('dir') === 'up' ? 'up' : 'down';
            $neighbor = $dir === 'up'
                ? Database::first("SELECT * FROM awards WHERE sort < ? ORDER BY sort DESC, id DESC LIMIT 1", [$row['sort']])
                : Database::first("SELECT * FROM awards WHERE sort > ? ORDER BY sort ASC, id ASC LIMIT 1", [$row['sort']]);
            if ($neighbor) {
                Database::run("UPDATE awards SET sort=? WHERE id=?", [$neighbor['sort'], $row['id']]);
                Database::run("UPDATE awards SET sort=? WHERE id=?", [$row['sort'], $neighbor['id']]);
            }
        }
        redirect('/awards');
    }
}
