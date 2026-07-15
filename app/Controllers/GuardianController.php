<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Fee;

/**
 * Guardian / Parent portal - read-only view of a student's attendance, fees,
 * results, progress, notices and timetable. Deliberately exposes NO lesson
 * content and NO private community discussions.
 */
class GuardianController extends Controller
{
    public function showLogin(): void
    {
        if (!empty($_SESSION['guardian_student_id'])) { redirect('/guardian/dashboard'); }
        $this->view('guardian/login', ['title' => 'Guardian Portal - ' . config('app.name')], 'layouts/guardian');
    }

    public function login(): void
    {
        if (!csrf_verify(input('_csrf'))) { flash('error', 'Session expired.'); redirect('/guardian'); }
        $reg = trim((string) input('reg_no', ''));
        $pin = trim((string) input('pin', ''));
        $student = Database::first("SELECT * FROM users WHERE role='student' AND reg_no = ?", [$reg]);
        if (!$student || empty($student['guardian_pin']) || !hash_equals((string) $student['guardian_pin'], $pin)) {
            flash('error', 'Invalid registration number or PIN. Please ask the institute for your guardian access.');
            redirect('/guardian');
        }
        ensure_session();
        $_SESSION['guardian_student_id'] = (int) $student['id'];
        redirect('/guardian/dashboard');
    }

    public function dashboard(): void
    {
        $student = $this->guardianStudent();
        $id = (int) $student['id'];

        // Attendance across the student's batches.
        $att = Database::all("SELECT status, date FROM attendance WHERE user_id=? ORDER BY date DESC", [$id]);
        $present = $late = $absent = 0;
        foreach ($att as $a) { $a['status'] === 'present' ? $present++ : ($a['status'] === 'late' ? $late++ : $absent++); }
        $totalMarked = $present + $late + $absent;
        $attPct = $totalMarked ? round(($present + $late) / $totalMarked * 100) : 0;

        // Results.
        $results = Database::all(
            "SELECT qa.score, qa.passed, qa.created_at, ch.title AS chapter, c.title AS course
             FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id
             JOIN chapters ch ON ch.id=q.chapter_id JOIN courses c ON c.id=q.course_id
             WHERE qa.user_id=? ORDER BY qa.created_at DESC LIMIT 20", [$id]);

        // Progress per enrolled course.
        $progress = [];
        $overallTotal = 0; $overallDone = 0;
        foreach (Database::all("SELECT c.* FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? AND e.status='active'", [$id]) as $c) {
            $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id=?", [$c['id']]);
            $done = (int) Database::scalar("SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id=lp.lecture_id WHERE lp.user_id=? AND l.course_id=?", [$id, $c['id']]);
            $progress[] = ['title' => $c['title'], 'pct' => $total ? round($done / $total * 100) : 0, 'done' => $done, 'total' => $total];
            $overallTotal += $total; $overallDone += $done;
        }
        $overallPct = $overallTotal ? round($overallDone / $overallTotal * 100) : 0;

        // Score trend (oldest -> newest) for the line chart.
        $scoreTrend = array_reverse(array_map(fn($r) => (int) $r['score'], array_slice($results, 0, 12)));

        // Attendance by month (last 6 months) for the bar chart.
        $attMonthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $mKey = date('Y-m', strtotime("-{$i} months"));
            $p = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id=? AND status IN ('present','late') AND date LIKE ?", [$id, $mKey . '-%']);
            $tot = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id=? AND date LIKE ?", [$id, $mKey . '-%']);
            $attMonthly[] = ['label' => date('M', strtotime($mKey . '-01')), 'pct' => $tot ? round($p / $tot * 100) : 0, 'n' => $tot];
        }

        // Published (approved) exam results the student appears in.
        $published = Database::all(
            "SELECT rs.* FROM result_sets rs WHERE rs.status='approved' AND (
               (rs.batch_id IS NOT NULL AND rs.batch_id IN (SELECT batch_id FROM batch_students WHERE user_id=? AND status='active'))
               OR (rs.course_id IS NOT NULL AND rs.course_id IN (SELECT course_id FROM enrollments WHERE user_id=?))
             ) ORDER BY rs.approved_at DESC", [$id, $id]);
        $examCards = [];
        foreach ($published as $set) {
            $full = \App\Models\ResultSet::find((int) $set['id']);
            $comps = \App\Models\ResultSet::components((int) $set['id']);
            $bands = \App\Models\ResultSet::bands($full);
            $examCards[] = ['title' => $full['title'], 'r' => \App\Models\ResultSet::compute($full, $comps, $id, $bands)];
        }

        // Quiz pass/fail tallies for the donut.
        $passCount = 0; $failCount = 0;
        foreach ($results as $r) { ((int) $r['passed']) ? $passCount++ : $failCount++; }

        $this->view('guardian/dashboard', [
            'title' => 'Guardian Portal', 'student' => $student,
            'att' => ['present' => $present, 'late' => $late, 'absent' => $absent, 'pct' => $attPct, 'recent' => array_slice($att, 0, 10), 'monthly' => $attMonthly],
            'fees' => ['billed' => Fee::billed($id), 'paid' => Fee::paid($id), 'balance' => Fee::balance($id),
                       'payments' => Database::all("SELECT * FROM fee_payments WHERE user_id=? ORDER BY paid_at DESC LIMIT 5", [$id])],
            'results' => $results, 'progress' => $progress, 'overallPct' => $overallPct,
            'scoreTrend' => $scoreTrend, 'examCards' => $examCards,
            'tally' => ['pass' => $passCount, 'fail' => $failCount],
            'notices' => Database::all("SELECT * FROM notices WHERE is_published=1 AND audience IN ('all','guardians') ORDER BY created_at DESC LIMIT 10"),
            'timetable' => Database::all("SELECT * FROM timetable WHERE is_published=1 ORDER BY sort"),
            'batches' => Database::all("SELECT b.name, c.title AS course FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id WHERE bs.user_id=?", [$id]),
        ], 'layouts/guardian');
    }

    public function logout(): void
    {
        ensure_session();
        unset($_SESSION['guardian_student_id']);
        redirect('/guardian');
    }

    private function guardianStudent(): array
    {
        ensure_session();
        if (empty($_SESSION['guardian_student_id'])) { redirect('/guardian'); }
        $student = Database::first("SELECT * FROM users WHERE id=?", [(int) $_SESSION['guardian_student_id']]);
        if (!$student) { unset($_SESSION['guardian_student_id']); redirect('/guardian'); }
        return $student;
    }
}
