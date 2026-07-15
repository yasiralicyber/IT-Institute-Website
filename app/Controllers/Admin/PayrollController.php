<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class PayrollController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $month = preg_match('/^\d{4}-\d{2}$/', (string) input('month', '')) ? input('month') : date('Y-m');

        $staff = Database::all("SELECT * FROM staff ORDER BY name");
        $paidMap = [];
        foreach (Database::all("SELECT * FROM salary_payments WHERE fee_month=?", [$month]) as $p) {
            $paidMap[(int) $p['staff_id']] = $p;
        }
        $rows = [];
        $totalDue = 0; $totalPaid = 0;
        foreach ($staff as $s) {
            $paid = $paidMap[(int) $s['id']] ?? null;
            $rows[] = ['staff' => $s, 'paid' => $paid];
            $totalDue += (int) $s['salary'];
            if ($paid) { $totalPaid += (int) $paid['amount']; }
        }

        $this->view('admin/payroll', [
            'title' => 'Payroll', 'heading' => 'Staff Salary / Payroll',
            'rows' => $rows, 'month' => $month, 'totalDue' => $totalDue, 'totalPaid' => $totalPaid,
        ], 'admin/layouts/admin');
    }

    /** Set a staff member's monthly salary. */
    public function setSalary(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/payroll'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("UPDATE staff SET salary=? WHERE id=?", [max(0, (int) input('salary')), $id]);
        flash('success', 'Salary updated.');
        redirect('/payroll?month=' . (input('month') ?: date('Y-m')));
    }

    /** Pay a month's salary: record the payment AND an expense, marked once. */
    public function pay(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/payroll'); }
        $id = (int) ($params['id'] ?? 0);
        $month = preg_match('/^\d{4}-\d{2}$/', (string) input('month', '')) ? input('month') : date('Y-m');
        $staff = Database::first("SELECT * FROM staff WHERE id=?", [$id]);
        if (!$staff) { redirect('/payroll'); }
        if (Database::scalar("SELECT id FROM salary_payments WHERE staff_id=? AND fee_month=?", [$id, $month])) {
            flash('error', $staff['name'] . ' is already paid for ' . $month . '.');
            redirect('/payroll?month=' . $month);
        }
        $amount = (int) input('amount') ?: (int) $staff['salary'];
        if ($amount <= 0) { flash('error', 'Set a salary amount first.'); redirect('/payroll?month=' . $month); }

        Database::run(
            "INSERT INTO salary_payments (staff_id,fee_month,amount,method,note,paid_by) VALUES (?,?,?,?,?,?)",
            [$id, $month, $amount, input('method', 'cash'), input('note'), Auth::id()]);
        // Mirror into the expense ledger so it flows through the Day Book + Expenses.
        Database::run(
            "INSERT INTO expenses (date,category,amount,method,payee,note,created_by) VALUES (?,?,?,?,?,?,?)",
            [date('Y-m-t', strtotime($month . '-01')), 'salary', $amount, input('method', 'cash'),
             $staff['name'], 'Salary for ' . date('F Y', strtotime($month . '-01')), Auth::id()]);
        audit('salary_paid', 'staff', $id, 'Paid salary ' . pkr($amount) . ' to ' . $staff['name'] . ' for ' . $month);
        flash('success', 'Paid ' . pkr($amount) . ' to ' . $staff['name'] . ' for ' . date('F Y', strtotime($month . '-01')) . '.');
        redirect('/payroll?month=' . $month);
    }
}
