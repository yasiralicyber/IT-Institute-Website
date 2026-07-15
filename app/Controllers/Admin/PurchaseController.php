<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class PurchaseController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $status = in_array(input('status'), ['pending', 'approved', 'declined'], true) ? input('status') : 'pending';
        $rows = Database::all(
            "SELECT pr.*, u.name AS student, u.email, c.title AS course, c.slug
             FROM purchase_requests pr
             JOIN users u ON u.id = pr.user_id
             JOIN courses c ON c.id = pr.course_id
             WHERE pr.status = ? ORDER BY pr.created_at DESC", [$status]);

        $counts = [];
        foreach (['pending', 'approved', 'declined'] as $s) {
            $counts[$s] = (int) Database::scalar("SELECT COUNT(*) FROM purchase_requests WHERE status = ?", [$s]);
        }

        $this->view('admin/purchases', [
            'title' => 'Payment Approvals', 'heading' => 'Payment Approvals',
            'rows' => $rows, 'status' => $status, 'counts' => $counts,
        ], 'admin/layouts/admin');
    }

    /** Stream a receipt file to the admin (files live outside the web root). */
    public function receipt(array $params): void
    {
        Auth::requireAdmin();
        $row = Database::first("SELECT receipt_path FROM purchase_requests WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        $path = $row ? BASE_PATH . '/storage/uploads/' . $row['receipt_path'] : '';
        if (!$row || !is_file($path)) {
            http_response_code(404);
            echo 'Receipt not found.';
            return;
        }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="receipt"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function approve(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/purchases'); }
        $pr = Database::first("SELECT * FROM purchase_requests WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if ($pr && $pr['status'] === 'pending') {
            // Grant access (create enrollment if missing).
            $has = (int) Database::scalar("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?",
                [$pr['user_id'], $pr['course_id']]);
            if (!$has) {
                Database::run("INSERT INTO enrollments (user_id,course_id,status) VALUES (?,?,'active')",
                    [$pr['user_id'], $pr['course_id']]);
            }
            Database::run("UPDATE purchase_requests SET status = 'approved', reviewed_at = ? WHERE id = ?",
                [date('Y-m-d H:i:s'), $pr['id']]);
            $course = Database::scalar("SELECT title FROM courses WHERE id = ?", [$pr['course_id']]);
            Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
                [$pr['user_id'], 'Course unlocked!', 'Your payment for "' . $course . '" was approved. You now have full access.']);
            audit('approve', 'purchase_requests', (int) $pr['id'], 'Approved & unlocked "' . $course . '"');
            \App\Models\Workflow::fire('payment_approved', ['user_id' => (int) $pr['user_id'], 'course_id' => (int) $pr['course_id']]);
            flash('success', 'Approved - the course is now unlocked for the student.');
        }
        redirect('/purchases');
    }

    public function decline(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/purchases'); }
        $pr = Database::first("SELECT * FROM purchase_requests WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if ($pr && $pr['status'] === 'pending') {
            $reason = trim((string) input('reason', '')) ?: 'Payment could not be verified.';
            Database::run("UPDATE purchase_requests SET status = 'declined', admin_note = ?, reviewed_at = ? WHERE id = ?",
                [$reason, date('Y-m-d H:i:s'), $pr['id']]);
            $course = Database::scalar("SELECT title FROM courses WHERE id = ?", [$pr['course_id']]);
            Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
                [$pr['user_id'], 'Payment declined', 'Your request for "' . $course . '" was declined: ' . $reason]);
            audit('decline', 'purchase_requests', (int) $pr['id'], 'Declined "' . $course . '": ' . $reason);
            flash('success', 'Request declined and the student was notified.');
        }
        redirect('/purchases');
    }
}
