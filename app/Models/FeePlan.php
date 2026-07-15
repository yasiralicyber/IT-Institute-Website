<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

/**
 * Configurable fee rules engine. Admin defines a plan (admission +
 * tuition split into N installments + late-fee + discount rules); the
 * engine materialises the actual invoices when the plan is applied to a
 * student. Admin enters every amount - nothing is hard-coded.
 */
class FeePlan
{
    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM fee_plans WHERE id = ?", [$id]);
    }

    public static function all(): array
    {
        return Database::all(
            "SELECT p.*, c.title AS course FROM fee_plans p
             LEFT JOIN courses c ON c.id = p.course_id ORDER BY p.is_active DESC, p.name");
    }

    /** Per-installment amount (last one absorbs the rounding remainder). */
    public static function installmentAmount(array $plan): int
    {
        $n = max(1, (int) $plan['installments']);
        return intdiv((int) $plan['tuition_fee'], $n);
    }

    /**
     * Apply a plan to a student: create admission, security and the
     * installment invoices with monthly due dates. Returns invoice count.
     */
    public static function apply(array $plan, int $userId, ?int $batchId, string $startMonth, ?int $discountPct = null): int
    {
        $by = Auth::id();
        $created = 0;
        // null = use the plan's early-payment default; an explicit value overrides.
        $discountPct = $discountPct === null ? (int) $plan['early_discount_pct'] : max(0, min(100, $discountPct));

        if ((int) $plan['admission_fee'] > 0) {
            Database::run(
                "INSERT INTO fee_invoices (user_id,batch_id,plan_id,type,title,amount,due_date,status,created_by)
                 VALUES (?,?,?,'admission',?,?,?,'unpaid',?)",
                [$userId, $batchId, $plan['id'], 'Admission Fee', (int) $plan['admission_fee'],
                 date('Y-m-d', strtotime($startMonth . '-01')), $by]);
            $created++;
        }
        if ((int) $plan['security_deposit'] > 0) {
            Database::run(
                "INSERT INTO fee_invoices (user_id,batch_id,plan_id,type,title,amount,due_date,status,created_by)
                 VALUES (?,?,?,'security',?,?,?,'unpaid',?)",
                [$userId, $batchId, $plan['id'], 'Security Deposit', (int) $plan['security_deposit'],
                 date('Y-m-d', strtotime($startMonth . '-01')), $by]);
            $created++;
        }

        $n = max(1, (int) $plan['installments']);
        $per = self::installmentAmount($plan);
        $allocated = 0;
        for ($i = 1; $i <= $n; $i++) {
            $amt = ($i === $n) ? ((int) $plan['tuition_fee'] - $allocated) : $per;
            $allocated += $amt;
            $disc = (int) round($amt * $discountPct / 100);
            $month = date('Y-m', strtotime($startMonth . '-01 +' . ($i - 1) . ' months'));
            $due = date('Y-m-10', strtotime($month . '-01'));
            $title = $n === 1 ? 'Tuition Fee' : "Tuition Installment {$i} of {$n}";
            Database::run(
                "INSERT INTO fee_invoices (user_id,batch_id,plan_id,installment_no,type,title,amount,discount,fee_month,due_date,status,created_by)
                 VALUES (?,?,?,?,'installment',?,?,?,?,?,'unpaid',?)",
                [$userId, $batchId, $plan['id'], $i, $title, $amt, $disc, $month, $due, $by]);
            $created++;
        }
        return $created;
    }
}
