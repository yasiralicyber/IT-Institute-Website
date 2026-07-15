<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $today = date('Y-m-d');
        $month = date('Y-m');

        $billed = (int) Database::scalar("SELECT COALESCE(SUM(amount-discount),0) FROM fee_invoices WHERE status!='waived'");
        $collected = (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments");

        $stats = [
            'students'    => (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'"),
            'present'     => (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE date=? AND status IN ('present','late')", [$today]),
            'fee_today'   => (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE paid_at LIKE ?", [$today . '%']),
            'exp_today'   => (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE date=?", [$today]),
            'fee_month'   => (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE paid_at LIKE ?", [$month . '%']),
            'exp_month'   => (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE date LIKE ?", [$month . '-%']),
            'outstanding' => max(0, $billed - $collected),
            'adm_new'     => (int) Database::scalar("SELECT COUNT(*) FROM admissions WHERE status='new'"),
            'pending_pay' => (int) Database::scalar("SELECT COUNT(*) FROM purchase_requests WHERE status='pending'"),
            'fee_due_count' => 0,
        ];
        // How many students currently owe money (real, actionable).
        $stats['fee_due_count'] = (int) Database::scalar(
            "SELECT COUNT(*) FROM (
                SELECT u.id,
                  (SELECT COALESCE(SUM(amount-discount),0) FROM fee_invoices i WHERE i.user_id=u.id AND i.status!='waived')
                  - (SELECT COALESCE(SUM(amount),0) FROM fee_payments p WHERE p.user_id=u.id) AS bal
                FROM users u WHERE u.role='student'
             ) t WHERE bal > 0");

        $this->view('admin/dashboard', [
            'title' => 'Dashboard', 'stats' => $stats,
            'admissions' => Database::all("SELECT * FROM admissions WHERE status='new' ORDER BY created_at DESC LIMIT 6"),
            'payments' => Database::all(
                "SELECT p.amount, p.receipt_no, p.paid_at, u.name FROM fee_payments p JOIN users u ON u.id=p.user_id ORDER BY p.id DESC LIMIT 6"),
            'expenses' => Database::all(
                "SELECT date, category, amount, payee FROM expenses ORDER BY id DESC LIMIT 6"),
        ], 'admin/layouts/admin');
    }
}
