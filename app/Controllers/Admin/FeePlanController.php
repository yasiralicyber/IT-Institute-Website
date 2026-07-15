<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\FeePlan;

class FeePlanController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/fee-plans', [
            'title' => 'Fee Plans', 'heading' => 'Fee Rules Engine',
            'plans' => FeePlan::all(),
        ], 'admin/layouts/admin');
    }

    public function form(array $params): void
    {
        Auth::requireAdmin();
        $plan = isset($params['id']) ? FeePlan::find((int) $params['id']) : null;
        $this->view('admin/fee-plan-form', [
            'title' => $plan ? 'Edit Fee Plan' : 'New Fee Plan',
            'heading' => $plan ? 'Edit Fee Plan' : 'New Fee Plan',
            'plan' => $plan,
            'courses' => Database::all("SELECT id,title FROM courses ORDER BY title"),
        ], 'admin/layouts/admin');
    }

    public function save(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fee-plans'); }
        $name = trim((string) input('name', ''));
        if ($name === '') { flash('error', 'Enter a plan name.'); redirect('/fee-plans/new'); }

        $fields = [
            'name' => $name,
            'course_id' => (int) input('course_id') ?: null,
            'admission_fee' => (int) input('admission_fee'),
            'security_deposit' => (int) input('security_deposit'),
            'tuition_fee' => (int) input('tuition_fee'),
            'installments' => max(1, (int) input('installments')),
            'late_fee_flat' => (int) input('late_fee_flat'),
            'late_fee_per_day' => (int) input('late_fee_per_day'),
            'grace_days' => (int) input('grace_days'),
            'sibling_discount_pct' => (int) input('sibling_discount_pct'),
            'early_discount_pct' => (int) input('early_discount_pct'),
            'scholarship_note' => trim((string) input('scholarship_note', '')),
            'is_active' => input('is_active') ? 1 : 0,
        ];

        $id = (int) ($params['id'] ?? 0);
        if ($id && FeePlan::find($id)) {
            $set = implode(',', array_map(fn($k) => "$k=?", array_keys($fields)));
            Database::run("UPDATE fee_plans SET $set WHERE id=?", [...array_values($fields), $id]);
            audit('update', 'fee_plans', $id, 'Updated fee plan "' . $name . '"');
        } else {
            $cols = implode(',', array_keys($fields)) . ',created_by';
            $ph = implode(',', array_fill(0, count($fields) + 1, '?'));
            $id = (int) Database::run("INSERT INTO fee_plans ($cols) VALUES ($ph)", [...array_values($fields), Auth::id()]);
            audit('create', 'fee_plans', $id, 'Created fee plan "' . $name . '"');
        }
        flash('success', 'Fee plan saved.');
        redirect('/fee-plans');
    }

    public function delete(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/fee-plans'); }
        $plan = FeePlan::find((int) ($params['id'] ?? 0));
        if ($plan) {
            trash_record('fee_plans', $plan, 'Fee plan: ' . $plan['name']);
            Database::run("DELETE FROM fee_plans WHERE id=?", [$plan['id']]);
            audit('delete', 'fee_plans', (int) $plan['id'], 'Deleted fee plan "' . $plan['name'] . '"');
            flash('success', 'Fee plan deleted.');
        }
        redirect('/fee-plans');
    }
}
