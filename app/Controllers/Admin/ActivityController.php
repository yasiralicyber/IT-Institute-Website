<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class ActivityController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT *, (SELECT COUNT(*) FROM activity_photos WHERE category_id = activity_categories.id) AS photo_count
             FROM activity_categories ORDER BY sort, id"
        );
        $this->view('admin/activities', ['title' => 'Activities', 'heading' => 'Activity Categories', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/activities'); }
        $name = trim((string) input('name', ''));
        if ($name === '') { flash('error', 'Category name is required.'); redirect('/activities'); }
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM activity_categories") + 1;
        Database::run("INSERT INTO activity_categories (name,sort,is_published) VALUES (?,?,1)", [$name, $sort]);
        flash('success', 'Category added.');
        redirect('/activities');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/activities'); }
        $id = (int) ($params['id'] ?? 0);
        $name = trim((string) input('name', ''));
        if ($name === '') { flash('error', 'Category name is required.'); redirect('/activities'); }
        Database::run("UPDATE activity_categories SET name=? WHERE id=?", [$name, $id]);
        flash('success', 'Category renamed.');
        redirect('/activities');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/activities'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM activity_categories WHERE id = ?", [$id])) {
            $photos = Database::all("SELECT * FROM activity_photos WHERE category_id = ?", [$id]);
            trash_record('activity_categories', $row, 'Category: ' . $row['name'], ['photos' => $photos]);
            audit('delete', 'activity_categories', $id, 'Deleted category "' . $row['name'] . '"');
            Database::run("DELETE FROM activity_photos WHERE category_id = ?", [$id]);
            Database::run("DELETE FROM activity_categories WHERE id = ?", [$id]);
        }
        flash('success', 'Category deleted.');
        redirect('/activities');
    }

    public function move(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/activities'); }
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM activity_categories WHERE id=?", [$id]);
        if ($row) {
            $dir = input('dir') === 'up' ? 'up' : 'down';
            $neighbor = $dir === 'up'
                ? Database::first("SELECT * FROM activity_categories WHERE sort < ? ORDER BY sort DESC, id DESC LIMIT 1", [$row['sort']])
                : Database::first("SELECT * FROM activity_categories WHERE sort > ? ORDER BY sort ASC, id ASC LIMIT 1", [$row['sort']]);
            if ($neighbor) {
                Database::run("UPDATE activity_categories SET sort=? WHERE id=?", [$neighbor['sort'], $row['id']]);
                Database::run("UPDATE activity_categories SET sort=? WHERE id=?", [$row['sort'], $neighbor['id']]);
            }
        }
        redirect('/activities');
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $category = Database::first("SELECT * FROM activity_categories WHERE id=?", [$id]);
        if (!$category) { redirect('/activities'); }
        $photos = Database::all("SELECT * FROM activity_photos WHERE category_id=? ORDER BY sort, id", [$id]);
        $this->view('admin/activity-category', [
            'title'    => 'Manage Photos',
            'heading'  => 'Photos: ' . $category['name'],
            'category' => $category,
            'photos'   => $photos,
        ], 'admin/layouts/admin');
    }

    public function storePhoto(array $params): void
    {
        Auth::requireAdmin();
        $categoryId = (int) ($params['id'] ?? 0);
        if (!csrf_verify(input('_csrf'))) { redirect('/activities/' . $categoryId); }
        $image = store_upload('image', 'activities', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        if (!$image) {
            flash('error', 'Please choose a valid image (jpg, jpeg, png, webp, max 4MB).');
            redirect('/activities/' . $categoryId);
        }
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM activity_photos WHERE category_id=?", [$categoryId]) + 1;
        Database::run(
            "INSERT INTO activity_photos (category_id,image,caption,sort,is_published) VALUES (?,?,?,?,1)",
            [$categoryId, $image, input('caption'), $sort]
        );
        flash('success', 'Photo added.');
        redirect('/activities/' . $categoryId);
    }

    public function destroyPhoto(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM activity_photos WHERE id = ?", [$id]);
        $categoryId = (int) ($row['category_id'] ?? 0);
        if (!csrf_verify(input('_csrf'))) { redirect('/activities/' . $categoryId); }
        if ($row) {
            trash_record('activity_photos', $row, 'Photo: ' . ($row['caption'] ?: ('#' . $row['id'])));
            audit('delete', 'activity_photos', $id, 'Deleted photo #' . $id);
            Database::run("DELETE FROM activity_photos WHERE id = ?", [$id]);
        }
        flash('success', 'Photo deleted.');
        redirect('/activities/' . $categoryId);
    }

    public function movePhoto(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM activity_photos WHERE id=?", [$id]);
        $categoryId = (int) ($row['category_id'] ?? 0);
        if (!csrf_verify(input('_csrf'))) { redirect('/activities/' . $categoryId); }
        if ($row) {
            $dir = input('dir') === 'up' ? 'up' : 'down';
            $neighbor = $dir === 'up'
                ? Database::first("SELECT * FROM activity_photos WHERE sort < ? AND category_id=? ORDER BY sort DESC, id DESC LIMIT 1", [$row['sort'], $categoryId])
                : Database::first("SELECT * FROM activity_photos WHERE sort > ? AND category_id=? ORDER BY sort ASC, id ASC LIMIT 1", [$row['sort'], $categoryId]);
            if ($neighbor) {
                Database::run("UPDATE activity_photos SET sort=? WHERE id=?", [$neighbor['sort'], $row['id']]);
                Database::run("UPDATE activity_photos SET sort=? WHERE id=?", [$row['sort'], $neighbor['id']]);
            }
        }
        redirect('/activities/' . $categoryId);
    }
}
