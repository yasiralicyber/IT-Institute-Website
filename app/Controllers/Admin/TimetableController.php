<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class TimetableController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM timetable ORDER BY sort ASC, id DESC");
        $this->view('admin/timetable', [
            'title' => 'Timetable', 'heading' => 'Timetable Management', 'rows' => $rows,
        ], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/timetable'); }
        $title = trim((string) input('title', ''));
        if ($title === '') { flash('error', 'Title is required.'); redirect('/timetable'); }

        $image = store_upload('image', 'timetable', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM timetable") + 1;
        Database::run(
            "INSERT INTO timetable (title,body,image_path,sort,is_published) VALUES (?,?,?,?,1)",
            [$title, trim((string) input('body', '')), $image, $sort]);
        flash('success', 'Timetable entry published.');
        redirect('/timetable');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/timetable'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM timetable WHERE id = ?", [$id])) {
            trash_record('timetable', $row, 'Timetable: ' . $row['title']);
            audit('delete', 'timetable', $id, 'Deleted timetable "' . $row['title'] . '"');
        }
        Database::run("DELETE FROM timetable WHERE id = ?", [$id]);
        flash('success', 'Timetable entry deleted.');
        redirect('/timetable');
    }
}
