<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class OpsController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $today = date('Y-m-d');
        $att = [
            'present' => (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE date=? AND status IN ('present','late')", [$today]),
            'absent'  => (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE date=? AND status='absent'", [$today]),
        ];
        $data = [
            'batches' => Database::all(
                "SELECT b.name, b.schedule, c.title AS course, s.name AS teacher, r.name AS room,
                        (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id=b.id AND bs.status='active') AS students
                 FROM batches b JOIN courses c ON c.id=b.course_id
                 LEFT JOIN staff s ON s.id=b.staff_id LEFT JOIN classrooms r ON r.id=b.classroom_id
                 WHERE b.status='active' ORDER BY b.name LIMIT 8"),
            'rooms'   => Database::all("SELECT name, capacity FROM classrooms ORDER BY name"),
            'events'  => Database::all("SELECT title, type, event_date FROM events WHERE is_published=1 ORDER BY COALESCE(event_date,created_at) DESC LIMIT 5"),
            'notices' => Database::all("SELECT title FROM notices WHERE is_published=1 ORDER BY created_at DESC LIMIT 5"),
            'att'     => $att,
            'alerts'  => [
                'approvals'  => (int) Database::scalar("SELECT COUNT(*) FROM purchase_requests WHERE status='pending'"),
                'admissions' => (int) Database::scalar("SELECT COUNT(*) FROM admissions WHERE status='new'"),
                'reviews'    => (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status='pending'"),
                'projects'   => (int) Database::scalar("SELECT COUNT(*) FROM projects WHERE status='pending'"),
            ],
            'stats'   => [
                'students'    => (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'"),
                'enrollments' => (int) Database::scalar("SELECT COUNT(*) FROM enrollments WHERE status='active'"),
                'revenue'     => (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE paid_at LIKE ?", [$today . '%']),
            ],
        ];
        echo View::render('admin/ops', $data, '');
    }
}
