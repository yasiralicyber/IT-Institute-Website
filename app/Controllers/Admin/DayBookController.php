<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\DayBook;

class DayBookController extends Controller
{
    private function validDate(string $d): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d');
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $date = $this->validDate((string) input('date', date('Y-m-d')));
        $book = DayBook::forDate($date);
        $opening = $book ? (int) $book['opening'] : 0;

        $payments = Database::all(
            "SELECT p.*, u.name AS student FROM fee_payments p JOIN users u ON u.id=p.user_id
             WHERE date(p.paid_at)=? ORDER BY p.paid_at DESC", [$date]);
        $expenses = Database::all("SELECT * FROM expenses WHERE date=? ORDER BY id DESC", [$date]);

        $cashIn = DayBook::cashIn($date);
        $cashOut = DayBook::cashOut($date);
        $expected = DayBook::expected($date, $opening);

        // Recent days summary.
        $recent = Database::all("SELECT * FROM day_books ORDER BY date DESC LIMIT 14");

        $this->view('admin/daybook', [
            'title' => 'Day Book', 'heading' => 'Day Book - Cash Register',
            'date' => $date, 'book' => $book, 'opening' => $opening,
            'payments' => $payments, 'expenses' => $expenses,
            'cashIn' => $cashIn, 'cashOut' => $cashOut, 'expected' => $expected, 'recent' => $recent,
        ], 'admin/layouts/admin');
    }

    /** Set/lock the opening cash for a day. */
    public function open(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/daybook'); }
        $date = $this->validDate((string) input('date', date('Y-m-d')));
        $opening = (int) input('opening');
        if (DayBook::forDate($date)) {
            Database::run("UPDATE day_books SET opening=? WHERE date=? AND status='open'", [$opening, $date]);
        } else {
            Database::run("INSERT INTO day_books (date,opening,status,opened_by) VALUES (?,?,'open',?)",
                [$date, $opening, Auth::id()]);
        }
        flash('success', 'Opening cash set to ' . pkr($opening) . ' for ' . $date . '.');
        redirect('/daybook?date=' . $date);
    }

    public function addExpense(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/daybook'); }
        $date = $this->validDate((string) input('date', date('Y-m-d')));
        $amount = (int) input('amount');
        if ($amount <= 0) { flash('error', 'Enter an expense amount.'); redirect('/daybook?date=' . $date); }
        Database::run(
            "INSERT INTO expenses (date,category,amount,method,payee,note,created_by) VALUES (?,?,?,?,?,?,?)",
            [$date, input('category', 'general'), $amount, input('method', 'cash'),
             input('payee'), input('note'), Auth::id()]);
        audit('expense', 'expenses', null, 'Cash out ' . pkr($amount) . ' (' . input('category', 'general') . ')');
        flash('success', 'Expense recorded.');
        redirect('/daybook?date=' . $date);
    }

    /** Count the drawer and flag any discrepancy. */
    public function close(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/daybook'); }
        $date = $this->validDate((string) input('date', date('Y-m-d')));
        $book = DayBook::forDate($date);
        if (!$book) {
            Database::run("INSERT INTO day_books (date,opening,status,opened_by) VALUES (?,0,'open',?)", [$date, Auth::id()]);
            $book = DayBook::forDate($date);
        }
        $expected = DayBook::expected($date, (int) $book['opening']);
        $actual = (int) input('actual_close');
        $disc = $actual - $expected;
        Database::run(
            "UPDATE day_books SET expected_close=?, actual_close=?, discrepancy=?, status='closed', closed_by=?, closed_at=?, note=? WHERE id=?",
            [$expected, $actual, $disc, Auth::id(), date('Y-m-d H:i:s'), input('note'), $book['id']]);
        audit('daybook_close', 'day_books', (int) $book['id'],
            'Closed ' . $date . ': expected ' . pkr($expected) . ', counted ' . pkr($actual) . ', diff ' . pkr($disc));
        if ($disc !== 0) {
            flash('error', 'Day closed with a discrepancy of ' . pkr($disc) . ' (' . ($disc > 0 ? 'surplus' : 'shortfall') . '). Please investigate.');
        } else {
            flash('success', 'Day closed and balanced perfectly.');
        }
        redirect('/daybook?date=' . $date);
    }
}
