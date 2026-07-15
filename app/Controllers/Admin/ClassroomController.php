<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class ClassroomController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM batches b WHERE b.classroom_id = c.id AND b.status='active') AS batches
             FROM classrooms c ORDER BY c.name");
        $this->view('admin/classrooms', ['title' => 'Classrooms', 'heading' => 'Classrooms & Rooms', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/classrooms'); }
        $name = trim((string) input('name', ''));
        if ($name !== '') {
            Database::run("INSERT INTO classrooms (name,capacity,location,notes) VALUES (?,?,?,?)",
                [$name, (int) input('capacity'), trim((string) input('location', '')), trim((string) input('notes', ''))]);
            flash('success', 'Classroom added.');
        }
        redirect('/classrooms');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/classrooms'); }
        Database::run("UPDATE classrooms SET name=?,capacity=?,location=?,notes=? WHERE id=?",
            [trim((string) input('name', '')), (int) input('capacity'), trim((string) input('location', '')),
             trim((string) input('notes', '')), (int) ($params['id'] ?? 0)]);
        flash('success', 'Classroom updated.');
        redirect('/classrooms');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/classrooms'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM classrooms WHERE id = ?", [$id])) {
            trash_record('classrooms', $row, 'Classroom: ' . $row['name']);
            audit('delete', 'classrooms', $id, 'Deleted classroom "' . $row['name'] . '"');
        }
        Database::run("DELETE FROM classrooms WHERE id = ?", [$id]);
        flash('success', 'Classroom deleted.');
        redirect('/classrooms');
    }
}
