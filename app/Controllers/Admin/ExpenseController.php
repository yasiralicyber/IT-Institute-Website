<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class ExpenseController extends Controller
{
    /** Day-to-day expense categories with a matching icon name. */
    public static function categories(): array
    {
        return [
            'tea'         => ['Tea', 'beaker'],
            'breakfast'   => ['Breakfast', 'gift'],
            'lunch'       => ['Lunch / Meals', 'gift'],
            'refreshment' => ['Refreshments', 'beaker'],
            'guest'       => ['Guest / Hospitality', 'users'],
            'transport'   => ['Transport / Fuel', 'computer'],
            'stationery'  => ['Stationery', 'note'],
            'printing'    => ['Printing / Photocopy', 'note'],
            'cleaning'    => ['Cleaning', 'sparkles'],
            'utilities'   => ['Electricity / Gas / Water', 'bolt'],
            'internet'    => ['Internet / Phone', 'chat'],
            'rent'        => ['Rent', 'room'],
            'maintenance' => ['Maintenance / Repairs', 'wrench'],
            'equipment'   => ['Equipment', 'computer'],
            'marketing'   => ['Marketing / Ads', 'chart'],
            'salary'      => ['Staff Salary', 'money'],
            'general'     => ['Miscellaneous', 'doc'],
        ];
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $month = preg_match('/^\d{4}-\d{2}$/', (string) input('month', '')) ? input('month') : date('Y-m');
        $cats = self::categories();

        $rows = Database::all("SELECT * FROM expenses WHERE date LIKE ? ORDER BY date DESC, id DESC", [$month . '-%']);
        $monthTotal = (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE date LIKE ?", [$month . '-%']);
        $todayTotal = (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE date = ?", [date('Y-m-d')]);

        // Breakdown by category for the month.
        $byCat = [];
        foreach (Database::all("SELECT category, SUM(amount) AS total, COUNT(*) AS n FROM expenses WHERE date LIKE ? GROUP BY category ORDER BY total DESC", [$month . '-%']) as $r) {
            $byCat[] = ['key' => $r['category'], 'label' => $cats[$r['category']][0] ?? ucfirst($r['category']), 'total' => (int) $r['total'], 'n' => (int) $r['n']];
        }

        $this->view('admin/expenses', [
            'title' => 'Expenses', 'heading' => 'Daily Expenses',
            'rows' => $rows, 'cats' => $cats, 'month' => $month,
            'monthTotal' => $monthTotal, 'todayTotal' => $todayTotal, 'byCat' => $byCat,
        ], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/expenses'); }
        $amount = (int) input('amount');
        $cats = self::categories();
        $category = array_key_exists(input('category'), $cats) ? input('category') : 'general';
        if ($amount <= 0) { flash('error', 'Enter an amount.'); redirect('/expenses'); }
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) input('date', '')) ? input('date') : date('Y-m-d');
        Database::run(
            "INSERT INTO expenses (date,category,amount,method,payee,note,created_by) VALUES (?,?,?,?,?,?,?)",
            [$date, $category, $amount, input('method', 'cash'), input('payee'), input('note'), Auth::id()]);
        audit('expense', 'expenses', null, 'Expense ' . pkr($amount) . ' - ' . ($cats[$category][0] ?? $category));
        flash('success', 'Expense recorded: ' . pkr($amount) . ' for ' . ($cats[$category][0] ?? $category) . '.');
        redirect('/expenses?month=' . substr($date, 0, 7));
    }

    public function delete(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/expenses'); }
        $id = (int) ($params['id'] ?? 0);
        $exp = Database::first("SELECT * FROM expenses WHERE id=?", [$id]);
        if ($exp) {
            Database::run("DELETE FROM expenses WHERE id=?", [$id]);
            audit('expense_delete', 'expenses', $id, 'Removed expense ' . pkr((int) $exp['amount']));
            flash('success', 'Expense removed.');
        }
        redirect('/expenses?month=' . substr($exp['date'] ?? date('Y-m-d'), 0, 7));
    }
}
