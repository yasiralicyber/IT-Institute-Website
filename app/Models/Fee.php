<?php
namespace App\Models;

use App\Core\Database;

class Fee
{
    /** Net billed (after discount) for a student. */
    public static function billed(int $userId): int
    {
        return (int) Database::scalar(
            "SELECT COALESCE(SUM(amount - discount),0) FROM fee_invoices WHERE user_id = ? AND status != 'waived'", [$userId]);
    }

    /** Total cash received from a student (across all payments). */
    public static function paid(int $userId): int
    {
        return (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE user_id = ?", [$userId]);
    }

    public static function balance(int $userId): int
    {
        return self::billed($userId) - self::paid($userId);
    }

    /** Amount allocated to a single invoice (payment allocation system). */
    public static function invoicePaid(int $invoiceId): int
    {
        return (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_allocations WHERE invoice_id = ?", [$invoiceId]);
    }

    /** Net outstanding on a single invoice. */
    public static function invoiceDue(array $inv): int
    {
        if ($inv['status'] === 'waived') { return 0; }
        $net = (int) $inv['amount'] - (int) $inv['discount'];
        return max(0, $net - self::invoicePaid((int) $inv['id']));
    }

    /** Unallocated credit a student is holding (paid more than allocated). */
    public static function credit(int $userId): int
    {
        $received = self::paid($userId);
        $allocated = (int) Database::scalar(
            "SELECT COALESCE(SUM(a.amount),0) FROM fee_allocations a
             JOIN fee_payments p ON p.id = a.payment_id WHERE p.user_id = ?", [$userId]);
        return max(0, $received - $allocated);
    }

    /** Open invoices (unpaid/partial), oldest first - for auto-allocation. */
    public static function outstandingInvoices(int $userId): array
    {
        $rows = Database::all(
            "SELECT * FROM fee_invoices WHERE user_id = ? AND status NOT IN ('waived','restructured','cancelled')
             ORDER BY COALESCE(due_date, fee_month, created_at) ASC, id ASC", [$userId]);
        return array_values(array_filter($rows, fn($i) => self::invoiceDue($i) > 0));
    }

    /**
     * Allocate a payment across invoices.
     * @param array<int,int> $manual  invoice_id => amount (optional; empty = auto oldest-first)
     */
    public static function allocate(int $paymentId, int $userId, int $amount, array $manual = []): void
    {
        $remaining = $amount;
        if ($manual) {
            foreach ($manual as $invId => $amt) {
                $amt = (int) $amt;
                if ($amt <= 0) { continue; }
                $inv = Database::first("SELECT * FROM fee_invoices WHERE id = ? AND user_id = ?", [(int) $invId, $userId]);
                if (!$inv) { continue; }
                $amt = min($amt, self::invoiceDue($inv), $remaining);
                if ($amt <= 0) { continue; }
                Database::run("INSERT INTO fee_allocations (payment_id,invoice_id,amount) VALUES (?,?,?)",
                    [$paymentId, (int) $inv['id'], $amt]);
                self::refreshInvoiceStatus((int) $inv['id']);
                $remaining -= $amt;
            }
        }
        // Auto-allocate any leftover oldest-first.
        if ($remaining > 0) {
            foreach (self::outstandingInvoices($userId) as $inv) {
                if ($remaining <= 0) { break; }
                $amt = min(self::invoiceDue($inv), $remaining);
                if ($amt <= 0) { continue; }
                Database::run("INSERT INTO fee_allocations (payment_id,invoice_id,amount) VALUES (?,?,?)",
                    [$paymentId, (int) $inv['id'], $amt]);
                self::refreshInvoiceStatus((int) $inv['id']);
                $remaining -= $amt;
            }
        }
        // Any still-unallocated remainder stays as student credit (advance).
    }

    /** Recompute an invoice's status from its allocations. */
    public static function refreshInvoiceStatus(int $invoiceId): void
    {
        $inv = Database::first("SELECT * FROM fee_invoices WHERE id = ?", [$invoiceId]);
        if (!$inv || in_array($inv['status'], ['waived', 'restructured', 'cancelled'], true)) { return; }
        $net = (int) $inv['amount'] - (int) $inv['discount'];
        $paid = self::invoicePaid($invoiceId);
        $status = $paid <= 0 ? 'unpaid' : ($paid >= $net ? 'paid' : 'partial');
        Database::run("UPDATE fee_invoices SET status = ? WHERE id = ?", [$status, $invoiceId]);
    }

    /** Late fee owed on an overdue invoice, per its plan rules. */
    public static function lateFee(array $inv): int
    {
        if (empty($inv['due_date']) || self::invoiceDue($inv) <= 0) { return 0; }
        $plan = !empty($inv['plan_id']) ? Database::first("SELECT * FROM fee_plans WHERE id = ?", [(int) $inv['plan_id']]) : null;
        if (!$plan) { return 0; }
        $grace = (int) $plan['grace_days'];
        $overdue = (int) floor((time() - strtotime($inv['due_date'] . ' 23:59:59')) / 86400) - $grace;
        if ($overdue <= 0) { return 0; }
        return (int) $plan['late_fee_flat'] + $overdue * (int) $plan['late_fee_per_day'];
    }

    public static function nextReceiptNo(): string
    {
        $n = (int) Database::scalar("SELECT COUNT(*) FROM fee_payments") + 1;
        return 'RC-' . date('Y') . '-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }
}
