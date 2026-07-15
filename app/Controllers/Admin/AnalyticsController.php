<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Fee;
use App\Models\Progress;

class AnalyticsController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        // Overall KPIs
        $students = (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'");
        $enrollments = (int) Database::scalar("SELECT COUNT(*) FROM enrollments WHERE status='active'");
        $certs = (int) Database::scalar("SELECT COUNT(*) FROM certificates");
        $attMarked = (int) Database::scalar("SELECT COUNT(*) FROM attendance");
        $attPresent = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE status IN ('present','late')");
        $attRate = $attMarked ? (int) round($attPresent / $attMarked * 100) : 0;
        $quizTotal = (int) Database::scalar("SELECT COUNT(*) FROM quiz_attempts");
        $quizPass = (int) Database::scalar("SELECT COUNT(*) FROM quiz_attempts WHERE passed=1");
        $passRate = $quizTotal ? (int) round($quizPass / $quizTotal * 100) : 0;

        $feeBilled = (int) Database::scalar("SELECT COALESCE(SUM(amount-discount),0) FROM fee_invoices WHERE status!='waived'");
        $feeCollected = (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments");
        $online = (int) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM purchase_requests WHERE status='approved'");

        // Avg course completion across active enrollments
        $rows = Database::all("SELECT user_id, course_id FROM enrollments WHERE status='active'");
        $sumPct = 0;
        foreach ($rows as $r) {
            $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id=?", [$r['course_id']]);
            $done = (int) Database::scalar("SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id=lp.lecture_id WHERE lp.user_id=? AND l.course_id=?", [$r['user_id'], $r['course_id']]);
            $sumPct += $total ? $done / $total * 100 : 0;
        }
        $avgCompletion = $rows ? (int) round($sumPct / count($rows)) : 0;

        // Top courses by enrollment
        $topCourses = Database::all(
            "SELECT c.title, COUNT(e.id) AS n FROM enrollments e JOIN courses c ON c.id=e.course_id
             WHERE e.status='active' GROUP BY e.course_id ORDER BY n DESC LIMIT 6");
        $maxCourse = max(1, (int) ($topCourses[0]['n'] ?? 1));

        // Per-student performance
        $perStudent = [];
        foreach (Database::all("SELECT id,name,phone FROM users WHERE role='student' ORDER BY name") as $s) {
            $id = (int) $s['id'];
            $enr = (int) Database::scalar("SELECT COUNT(*) FROM enrollments WHERE user_id=? AND status='active'", [$id]);
            $avgScore = (int) round((float) Database::scalar("SELECT COALESCE(AVG(score),0) FROM quiz_attempts WHERE user_id=?", [$id]));
            $am = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id=?", [$id]);
            $ap = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id=? AND status IN ('present','late')", [$id]);
            $perStudent[] = [
                'id' => $id, 'name' => $s['name'], 'enrolls' => $enr,
                'score' => $avgScore, 'att' => $am ? (int) round($ap / $am * 100) : null,
                'balance' => Fee::balance($id),
                'certs' => (int) Database::scalar("SELECT COUNT(*) FROM certificates WHERE user_id=?", [$id]),
            ];
        }
        usort($perStudent, fn($a, $b) => $b['score'] <=> $a['score']);

        $this->view('admin/analytics', [
            'title' => 'Analytics', 'heading' => 'Analytics & Performance',
            'kpi' => compact('students', 'enrollments', 'certs', 'attRate', 'passRate', 'avgCompletion', 'feeBilled', 'feeCollected', 'online'),
            'topCourses' => $topCourses, 'maxCourse' => $maxCourse, 'perStudent' => $perStudent,
        ], 'admin/layouts/admin');
    }
}
