<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class BatchController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT b.*, c.title AS course, r.name AS room, s.name AS teacher,
                    (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = b.id AND bs.status='active') AS students
             FROM batches b
             JOIN courses c ON c.id = b.course_id
             LEFT JOIN classrooms r ON r.id = b.classroom_id
             LEFT JOIN staff s ON s.id = b.staff_id
             ORDER BY b.status, b.start_date DESC");
        $this->view('admin/batches', ['title' => 'Batches', 'heading' => 'Batches & Classes', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('admin/batch-form', $this->formData(null), 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/batches'); }
        $name = trim((string) input('name', ''));
        $courseId = (int) input('course_id');
        if ($name === '' || !$courseId) { flash('error', 'Batch name and program are required.'); redirect('/batches/create'); }
        $id = Database::run(
            "INSERT INTO batches (course_id,name,classroom_id,staff_id,capacity,schedule,start_date,end_date,status)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$courseId, $name, (int) input('classroom_id') ?: null, (int) input('staff_id') ?: null,
             (int) input('capacity', 30), input('schedule'), input('start_date'), input('end_date'),
             input('status', 'active')]);
        flash('success', 'Batch created. Now add students.');
        redirect('/batches/' . $id);
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $batch = Database::first(
            "SELECT b.*, c.title AS course, c.slug AS course_slug, r.name AS room, s.name AS teacher
             FROM batches b JOIN courses c ON c.id = b.course_id
             LEFT JOIN classrooms r ON r.id = b.classroom_id
             LEFT JOIN staff s ON s.id = b.staff_id WHERE b.id = ?", [$id]);
        if (!$batch) { redirect('/batches'); }

        $students = Database::all(
            "SELECT bs.*, u.name, u.email, u.phone FROM batch_students bs
             JOIN users u ON u.id = bs.user_id WHERE bs.batch_id = ? ORDER BY bs.roll_no, u.name", [$id]);
        $enrolledIds = array_column($students, 'user_id');
        $available = Database::all("SELECT id,name,email FROM users WHERE role='student' ORDER BY name");
        $available = array_filter($available, fn($u) => !in_array($u['id'], $enrolledIds));

        $this->view('admin/batch-detail', [
            'title' => $batch['name'], 'heading' => 'Batch: ' . $batch['name'],
            'batch' => $batch, 'students' => $students, 'available' => $available,
        ], 'admin/layouts/admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/batches'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run(
            "UPDATE batches SET course_id=?,name=?,classroom_id=?,staff_id=?,capacity=?,schedule=?,start_date=?,end_date=?,status=? WHERE id=?",
            [(int) input('course_id'), input('name'), (int) input('classroom_id') ?: null, (int) input('staff_id') ?: null,
             (int) input('capacity', 30), input('schedule'), input('start_date'), input('end_date'),
             input('status', 'active'), $id]);
        flash('success', 'Batch updated.');
        redirect('/batches/' . $id);
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/batches'); }
        $id = (int) ($params['id'] ?? 0);
        $batch = Database::first("SELECT * FROM batches WHERE id = ?", [$id]);
        if ($batch) {
            trash_record('batches', $batch, 'Batch: ' . $batch['name'],
                ['batch_students' => Database::all("SELECT * FROM batch_students WHERE batch_id = ?", [$id])]);
            audit('delete', 'batches', $id, 'Deleted batch "' . $batch['name'] . '"');
        }
        Database::run("DELETE FROM batch_students WHERE batch_id = ?", [$id]);
        Database::run("DELETE FROM batches WHERE id = ?", [$id]);
        flash('success', 'Batch deleted.');
        redirect('/batches');
    }

    public function enroll(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/batches'); }
        $batchId = (int) ($params['id'] ?? 0);
        $userId = (int) input('user_id');
        if ($userId && !Database::scalar("SELECT COUNT(*) FROM batch_students WHERE batch_id=? AND user_id=?", [$batchId, $userId])) {
            Database::run("INSERT INTO batch_students (batch_id,user_id,roll_no) VALUES (?,?,?)",
                [$batchId, $userId, trim((string) input('roll_no', ''))]);
            flash('success', 'Student added to batch.');
        }
        redirect('/batches/' . $batchId);
    }

    public function removeStudent(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/batches'); }
        $batchId = (int) ($params['id'] ?? 0);
        Database::run("DELETE FROM batch_students WHERE batch_id=? AND user_id=?", [$batchId, (int) input('user_id')]);
        flash('success', 'Student removed from batch.');
        redirect('/batches/' . $batchId);
    }

    private function formData(?array $batch): array
    {
        return [
            'title' => $batch ? 'Edit Batch' : 'New Batch',
            'heading' => $batch ? 'Edit Batch' : 'New Batch',
            'batch' => $batch,
            'courses' => Database::all("SELECT id,title FROM courses ORDER BY sort"),
            'rooms' => Database::all("SELECT id,name FROM classrooms ORDER BY name"),
            'staff' => Database::all("SELECT id,name FROM staff WHERE is_published=1 ORDER BY sort"),
        ];
    }
}
