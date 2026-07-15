<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Fee;

/**
 * Flags students who may need human follow-up. It RECOMMENDS contact - it never
 * auto-punishes the student.
 */
class RiskController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/risk', [
            'title' => 'At-Risk Students', 'heading' => 'Students Needing Follow-up',
            'rows' => self::flagged(),
        ], 'admin/layouts/admin');
    }

    /** Returns students with one or more risk reasons, most-at-risk first. */
    public static function flagged(int $limit = 200): array
    {
        $students = Database::all("SELECT id,name,email,phone FROM users WHERE role='student' AND status='active' ORDER BY name");
        $cutoff = date('Y-m-d H:i:s', strtotime('-14 days'));
        $flagged = [];
        foreach ($students as $s) {
            $id = (int) $s['id'];
            $reasons = [];

            $lastActive = Database::scalar(
                "SELECT MAX(t) FROM (
                    SELECT MAX(last_seen) t FROM devices WHERE user_id = ?
                    UNION ALL SELECT MAX(completed_at) FROM lecture_progress WHERE user_id = ?
                    UNION ALL SELECT MAX(created_at) FROM quiz_attempts WHERE user_id = ?
                 ) t", [$id, $id, $id]);
            if (!$lastActive) { $reasons[] = 'Never started learning'; }
            elseif ($lastActive < $cutoff) { $reasons[] = 'Inactive since ' . date('d M', strtotime($lastActive)); }

            $failedQuiz = (int) Database::scalar(
                "SELECT MAX(c) FROM (SELECT COUNT(*) c FROM quiz_attempts WHERE user_id = ? AND passed = 0 GROUP BY quiz_id) t", [$id]);
            $passedAny = (int) Database::scalar("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND passed = 1", [$id]);
            if ($failedQuiz >= 3) { $reasons[] = 'Failed a chapter test ' . $failedQuiz . 'x'; }

            // Attendance %
            $marked = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id = ?", [$id]);
            if ($marked >= 4) {
                $present = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND status IN ('present','late')", [$id]);
                $pct = (int) round($present / $marked * 100);
                if ($pct < 60) { $reasons[] = 'Low attendance (' . $pct . '%)'; }
            }

            $balance = Fee::balance($id);
            if ($balance > 0) { $reasons[] = 'Fees due (' . pkr($balance) . ')'; }

            if ($reasons) {
                $flagged[] = $s + ['reasons' => $reasons, 'severity' => count($reasons)];
            }
        }
        usort($flagged, fn($a, $b) => $b['severity'] <=> $a['severity']);
        return array_slice($flagged, 0, $limit);
    }
}
