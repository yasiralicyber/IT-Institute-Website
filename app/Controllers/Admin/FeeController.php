<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Fee;
use App\Models\FeePlan;

class FeeController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $q = trim((string) input('q', ''));
        $students = Database::all("SELECT id,name,email,reg_no FROM users WHERE role='student' ORDER BY name");
        $rows = [];
        foreach ($students as $s) {
            if ($q !== '' && stripos($s['name'] . ' ' . $s['email'], $q) === false) { continue; }
            $billed = Fee::billed((int) $s['id']); $paid = Fee::paid((int) $s['id']);
            $rows[] = $s + ['billed' => $billed, 'paid' => $paid, 'balance' => $billed - $paid];
        }
        $totals = [
            'billed' => (int) Database::scalar("SELECT COALESCE(SUM(amount-discount),0) FROM fee_invoices WHERE status!='waived'"),
            'collected' => (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments"),
        ];
        $totals['outstanding'] = $totals['billed'] - $totals['collected'];

        $this->view('admin/fees-index', [
            'title' => 'Fees', 'heading' => 'Fee Management', 'rows' => $rows, 'q' => $q, 'totals' => $totals,
        ], 'admin/layouts/admin');
    }

    public function ledger(array $params): void
    {
        Auth::requireAdmin();
        $student = Database::first("SELECT * FROM users WHERE id=? AND role='student'", [(int) ($params['id'] ?? 0)]);
        if (!$student) { redirect('/fees'); }
        $id = (int) $student['id'];

        $invoices = Database::all("SELECT * FROM fee_invoices WHERE user_id=? ORDER BY created_at DESC", [$id]);
        $payments = Database::all("SELECT * FROM fee_payments WHERE user_id=? ORDER BY paid_at DESC", [$id]);
        $batches = Database::all(
            "SELECT b.id,b.name FROM batch_students bs JOIN batches b ON b.id=bs.batch_id WHERE bs.user_id=?", [$id]);

        // Per-invoice paid + late fee for the allocation/late views.
        $invMeta = [];
        foreach ($invoices as $inv) {
            $invMeta[$inv['id']] = ['paid' => Fee::invoicePaid((int) $inv['id']), 'due' => Fee::invoiceDue($inv), 'late' => Fee::lateFee($inv)];
        }
        $restructures = Database::all("SELECT * FROM fee_restructures WHERE user_id=? ORDER BY created_at DESC", [$id]);

        $this->view('admin/fee-ledger', [
            'title' => 'Fees - ' . $student['name'], 'heading' => 'Fees: ' . $student['name'],
            'student' => $student, 'invoices' => $invoices, 'payments' => $payments, 'batches' => $batches,
            'billed' => Fee::billed($id), 'paid' => Fee::paid($id), 'balance' => Fee::balance($id),
            'credit' => Fee::credit($id), 'invMeta' => $invMeta, 'plans' => FeePlan::all(),
            'restructures' => $restructures, 'outstanding' => Fee::outstandingInvoices($id),
        ], 'admin/layouts/admin');
    }

    public function storeInvoice(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fees'); }
        $id = (int) ($params['id'] ?? 0);
        $title = trim((string) input('title', ''));
        $amount = (int) input('amount');
        if ($title === '' || $amount <= 0) { flash('error', 'Enter a title and amount.'); redirect('/fees/' . $id); }
        Database::run(
            "INSERT INTO fee_invoices (user_id,batch_id,type,title,amount,discount,fee_month,due_date,status,notes,created_by)
             VALUES (?,?,?,?,?,?,?,?,'unpaid',?,?)",
            [$id, (int) input('batch_id') ?: null, input('type', 'monthly'), $title, $amount,
             (int) input('discount'), input('fee_month'), input('due_date'), input('notes'), Auth::id()]);
        flash('success', 'Fee charge added.');
        redirect('/fees/' . $id);
    }

    public function deleteInvoice(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fees'); }
        $inv = Database::first("SELECT * FROM fee_invoices WHERE id=?", [(int) ($params['id'] ?? 0)]);
        if ($inv) {
            $pays = Database::all("SELECT * FROM fee_payments WHERE invoice_id=?", [$inv['id']]);
            trash_record('fee_invoices', $inv, 'Fee: ' . $inv['title'], $pays ? ['fee_payments' => $pays] : []);
            Database::run("DELETE FROM fee_payments WHERE invoice_id=?", [$inv['id']]);
            Database::run("DELETE FROM fee_invoices WHERE id=?", [$inv['id']]);
            audit('delete', 'fee_invoices', (int) $inv['id'], 'Deleted charge "' . $inv['title'] . '"');
            flash('success', 'Charge removed.');
            redirect('/fees/' . $inv['user_id']);
        }
        redirect('/fees');
    }

    public function recordPayment(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fees'); }
        $id = (int) ($params['id'] ?? 0);
        $amount = (int) input('amount');
        if ($amount <= 0) { flash('error', 'Enter a valid amount.'); redirect('/fees/' . $id); }
        $receiptNo = Fee::nextReceiptNo();
        $payId = (int) Database::run(
            "INSERT INTO fee_payments (user_id,amount,method,reference,receipt_no,note,received_by,paid_at)
             VALUES (?,?,?,?,?,?,?,?)",
            [$id, $amount, input('method', 'cash'), input('reference'), $receiptNo, input('note'), Auth::id(), date('Y-m-d H:i:s')]);

        // Payment allocation: split across invoices (manual map) or auto oldest-first.
        $manual = [];
        foreach ((array) input('alloc', []) as $invId => $amt) {
            if ((int) $amt > 0) { $manual[(int) $invId] = (int) $amt; }
        }
        Fee::allocate($payId, $id, $amount, $manual);

        Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
            [$id, 'Fee payment received', 'We received your payment of ' . pkr($amount) . '. Receipt: ' . $receiptNo]);
        audit('fee_payment', 'fee_payments', $payId, 'Received ' . pkr($amount) . ' (' . $receiptNo . ')', ['amount' => $amount, 'method' => input('method', 'cash')]);
        flash('success', 'Payment recorded. Receipt ' . $receiptNo . '.');
        redirect('/fees/receipt/' . $payId);
    }

    public function receipt(array $params): void
    {
        Auth::requireAdmin();
        $pay = Database::first(
            "SELECT p.*, u.name AS student, u.reg_no, u.phone
             FROM fee_payments p JOIN users u ON u.id=p.user_id WHERE p.id=?", [(int) ($params['id'] ?? 0)]);
        if (!$pay) { redirect('/fees'); }
        $allocations = Database::all(
            "SELECT a.amount, i.title FROM fee_allocations a JOIN fee_invoices i ON i.id=a.invoice_id WHERE a.payment_id=?",
            [(int) $pay['id']]);
        echo \App\Core\View::render('admin/fee-receipt', [
            'title' => 'Receipt ' . $pay['receipt_no'], 'pay' => $pay, 'allocations' => $allocations,
            'balance' => Fee::balance((int) $pay['user_id']),
        ], '');
    }

    /** Apply a configurable fee plan to a student (generates the invoices). */
    public function applyPlan(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fees'); }
        $id = (int) ($params['id'] ?? 0);
        $plan = FeePlan::find((int) input('plan_id'));
        if (!$plan) { flash('error', 'Choose a fee plan.'); redirect('/fees/' . $id); }
        $month = preg_match('/^\d{4}-\d{2}$/', (string) input('start_month', '')) ? input('start_month') : date('Y-m');
        $dpRaw = input('discount_pct');
        $dp = ($dpRaw === '' || $dpRaw === null) ? null : (int) $dpRaw;
        $n = FeePlan::apply($plan, $id, (int) input('batch_id') ?: null, $month, $dp);
        audit('fee_plan_apply', 'users', $id, 'Applied plan "' . $plan['name'] . '" (' . $n . ' charges)');
        flash('success', "Applied \"{$plan['name']}\" - generated {$n} charge(s).");
        redirect('/fees/' . $id);
    }

    /** Restructure remaining unpaid installments (originals preserved + logged). */
    public function restructure(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fees'); }
        $id = (int) ($params['id'] ?? 0);
        $newTotal = (int) input('new_total');
        $newCount = max(1, (int) input('new_installments'));
        $startMonth = preg_match('/^\d{4}-\d{2}$/', (string) input('start_month', '')) ? input('start_month') : date('Y-m');
        $reason = trim((string) input('reason', ''));
        if ($newTotal <= 0) { flash('error', 'Enter the new total to reschedule.'); redirect('/fees/' . $id); }

        // Snapshot the ORIGINAL remaining installments (never overwrite - preserve as history).
        $old = Database::all(
            "SELECT * FROM fee_invoices WHERE user_id=? AND type='installment' AND status IN ('unpaid','partial')", [$id]);
        $oldRemaining = 0;
        foreach ($old as $o) { $oldRemaining += Fee::invoiceDue($o); }

        // Mark the old unpaid installments as 'restructured' (kept, not deleted).
        foreach ($old as $o) {
            Database::run("UPDATE fee_invoices SET status='restructured' WHERE id=?", [$o['id']]);
        }

        // Create the new installment schedule.
        $per = intdiv($newTotal, $newCount);
        $alloc = 0;
        for ($i = 1; $i <= $newCount; $i++) {
            $amt = ($i === $newCount) ? ($newTotal - $alloc) : $per;
            $alloc += $amt;
            $month = date('Y-m', strtotime($startMonth . '-01 +' . ($i - 1) . ' months'));
            Database::run(
                "INSERT INTO fee_invoices (user_id,installment_no,type,title,amount,fee_month,due_date,status,notes,created_by)
                 VALUES (?,?,'installment',?,?,?,?,'unpaid',?,?)",
                [$id, $i, "Restructured Installment {$i} of {$newCount}", $amt, $month,
                 date('Y-m-10', strtotime($month . '-01')), 'Restructured: ' . $reason, Auth::id()]);
        }

        Database::run(
            "INSERT INTO fee_restructures (user_id,reason,old_plan,new_plan,created_by) VALUES (?,?,?,?,?)",
            [$id, $reason,
             json_encode(['remaining' => $oldRemaining, 'count' => count($old)]),
             json_encode(['total' => $newTotal, 'count' => $newCount, 'start' => $startMonth]),
             Auth::id()]);
        audit('fee_restructure', 'users', $id, 'Restructured ' . pkr($oldRemaining) . ' into ' . $newCount . ' installments');
        flash('success', 'Installments restructured. Original schedule preserved in history.');
        redirect('/fees/' . $id);
    }

    /** Bulk-generate a monthly fee for every student in a batch. */
    public function generateMonthly(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fees'); }
        $batchId = (int) input('batch_id');
        $amount = (int) input('amount');
        $month = preg_match('/^\d{4}-\d{2}$/', (string) input('fee_month', '')) ? input('fee_month') : date('Y-m');
        if (!$batchId || $amount <= 0) { flash('error', 'Choose a batch and amount.'); redirect('/fees'); }
        $students = Database::all("SELECT user_id FROM batch_students WHERE batch_id=? AND status='active'", [$batchId]);
        $count = 0;
        foreach ($students as $s) {
            $exists = Database::scalar("SELECT COUNT(*) FROM fee_invoices WHERE user_id=? AND batch_id=? AND type='monthly' AND fee_month=?",
                [$s['user_id'], $batchId, $month]);
            if ($exists) { continue; }
            Database::run(
                "INSERT INTO fee_invoices (user_id,batch_id,type,title,amount,fee_month,status,created_by)
                 VALUES (?,?,'monthly',?,?,?,'unpaid',?)",
                [$s['user_id'], $batchId, 'Monthly Fee - ' . date('F Y', strtotime($month . '-01')), $amount, $month, Auth::id()]);
            $count++;
        }
        flash('success', "Generated {$count} monthly fee charges for " . date('F Y', strtotime($month . '-01')) . '.');
        redirect('/fees');
    }
}
