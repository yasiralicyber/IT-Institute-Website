<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class StaffController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM staff ORDER BY sort, id");
        $this->view('admin/staff', ['title' => 'Staff', 'heading' => 'Staff & Instructors', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('admin/staff-form', ['title' => 'Add Staff', 'heading' => 'Add Staff Member', 'staff' => null], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/staff'); }
        $name = trim((string) input('name', ''));
        if ($name === '') { flash('error', 'Name is required.'); redirect('/staff/create'); }
        $photo = store_upload('photo', 'staff', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM staff") + 1;
        Database::run(
            "INSERT INTO staff (name,role,phone,email,expertise,bio,photo,sort,is_published) VALUES (?,?,?,?,?,?,?,?,?)",
            [$name, input('role'), input('phone'), input('email'), input('expertise'), input('bio'),
             $photo, $sort, input('is_published') ? 1 : 0]);
        flash('success', 'Staff member added.');
        redirect('/staff');
    }

    public function edit(array $params): void
    {
        Auth::requireAdmin();
        $staff = Database::first("SELECT * FROM staff WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if (!$staff) { redirect('/staff'); }
        $this->view('admin/staff-form', ['title' => 'Edit Staff', 'heading' => 'Edit: ' . $staff['name'], 'staff' => $staff], 'admin/layouts/admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/staff'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = Database::first("SELECT photo FROM staff WHERE id = ?", [$id]);
        $photo = store_upload('photo', 'staff', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304) ?: ($cur['photo'] ?? null);
        Database::run(
            "UPDATE staff SET name=?,role=?,phone=?,email=?,expertise=?,bio=?,photo=?,is_published=? WHERE id=?",
            [input('name'), input('role'), input('phone'), input('email'), input('expertise'), input('bio'),
             $photo, input('is_published') ? 1 : 0, $id]);
        flash('success', 'Staff member updated.');
        redirect('/staff');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/staff'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM staff WHERE id = ?", [$id])) {
            trash_record('staff', $row, 'Staff: ' . $row['name']);
            audit('delete', 'staff', $id, 'Deleted staff "' . $row['name'] . '"');
        }
        Database::run("DELETE FROM staff WHERE id = ?", [$id]);
        flash('success', 'Staff member removed.');
        redirect('/staff');
    }

    /** Stream a staff photo from private storage. */
    public function photo(array $params): void
    {
        Auth::requireAdmin();
        $row = Database::first("SELECT photo FROM staff WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        $path = $row && $row['photo'] ? BASE_PATH . '/storage/uploads/' . $row['photo'] : '';
        if (!$path || !is_file($path)) { http_response_code(404); exit; }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
