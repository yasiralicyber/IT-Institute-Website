<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\TestMark;

/**
 * Admin: enter physical / in-class test marks for students. Students then see
 * their marks online on their dashboard.
 */
class TestMarkController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/test-marks', [
            'title'    => 'Test Marks',
            'heading'  => 'Physical Test Marks',
            'students' => Database::all("SELECT id, name, reg_no FROM users WHERE role='student' ORDER BY name"),
            'rows'     => TestMark::recent(150),
        ], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/test-marks'); }

        $userId   = (int) input('user_id');
        $testName = trim((string) input('test_name', ''));
        if (!$userId || $testName === '') {
            flash('error', 'Please choose a student and enter the test name.');
            redirect('/test-marks');
        }
        $obtained = (float) input('marks_obtained', 0);
        $total    = (float) input('total_marks', 100);
        if ($total <= 0) { $total = 100; }
        if ($obtained > $total) {
            flash('error', 'Marks obtained cannot exceed total marks.');
            redirect('/test-marks');
        }

        $id = TestMark::create([
            'user_id'        => $userId,
            'test_name'      => $testName,
            'subject'        => input('subject'),
            'marks_obtained' => $obtained,
            'total_marks'    => $total,
            'test_date'      => input('test_date'),
            'remarks'        => input('remarks'),
            'status'         => input('status', 'published'),
        ], Auth::user());

        $sname = (string) Database::scalar("SELECT name FROM users WHERE id=?", [$userId]);
        audit('testmark.add', 'test_marks', $id, "Recorded '$testName' ($obtained/$total) for $sname");
        flash('success', "Marks saved for $sname — $obtained / $total. The student can now see it online.");
        redirect('/test-marks');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/test-marks'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = TestMark::find($id)) {
            trash_record('test_marks', $row, 'Test mark: ' . $row['test_name']);
            TestMark::delete($id);
            audit('testmark.delete', 'test_marks', $id, 'Deleted test mark #' . $id);
            flash('success', 'Test mark deleted.');
        }
        redirect('/test-marks');
    }
}
