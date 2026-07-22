<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class HeroSlideController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM hero_slides ORDER BY sort, id");
        $this->view('admin/hero-slides', ['title' => 'Hero Images', 'heading' => 'Hero Images', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/hero-slides'); }
        $image = store_upload('image', 'hero', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        if (!$image) {
            flash('error', 'Please choose an image.');
            redirect('/hero-slides');
        }
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM hero_slides") + 1;
        Database::run("INSERT INTO hero_slides (image,alt,sort,is_published) VALUES (?,?,?,1)",
            [$image, trim((string) input('alt', '')), $sort]);
        audit('create', 'hero_slides', null, 'Added hero slide.');
        flash('success', 'Slide added.');
        redirect('/hero-slides');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/hero-slides'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = Database::first("SELECT image FROM hero_slides WHERE id=?", [$id]);
        if (!$cur) { redirect('/hero-slides'); }
        $image = store_upload('image', 'hero', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304) ?: ($cur['image'] ?? null);
        Database::run("UPDATE hero_slides SET image=?, alt=?, is_published=? WHERE id=?",
            [$image, trim((string) input('alt', '')), input('is_published') ? 1 : 0, $id]);
        audit('update', 'hero_slides', $id, 'Updated hero slide.');
        flash('success', 'Slide updated.');
        redirect('/hero-slides');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/hero-slides'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM hero_slides WHERE id = ?", [$id])) {
            trash_record('hero_slides', $row, 'Hero slide: ' . ($row['alt'] ?: ('#' . $row['id'])));
            audit('delete', 'hero_slides', $id, 'Deleted hero slide "' . ($row['alt'] ?: ('#' . $row['id'])) . '".');
        }
        Database::run("DELETE FROM hero_slides WHERE id = ?", [$id]);
        flash('success', 'Deleted.');
        redirect('/hero-slides');
    }

    public function move(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/hero-slides'); }
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM hero_slides WHERE id=?", [$id]);
        if ($row) {
            $dir = input('dir') === 'up' ? 'up' : 'down';
            $neighbor = $dir === 'up'
                ? Database::first("SELECT * FROM hero_slides WHERE sort < ? ORDER BY sort DESC, id DESC LIMIT 1", [$row['sort']])
                : Database::first("SELECT * FROM hero_slides WHERE sort > ? ORDER BY sort ASC, id ASC LIMIT 1", [$row['sort']]);
            if ($neighbor) {
                Database::run("UPDATE hero_slides SET sort=? WHERE id=?", [$neighbor['sort'], $row['id']]);
                Database::run("UPDATE hero_slides SET sort=? WHERE id=?", [$row['sort'], $neighbor['id']]);
            }
        }
        redirect('/hero-slides');
    }
}
