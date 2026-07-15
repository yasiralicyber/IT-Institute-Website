<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class ExamIntegrityController extends Controller
{
    /** Score appeals queue. */
    public function appeals(): void
    {
        Auth::requireAdmin();
        $appeals = Database::all(
            "SELECT a.*, u.name AS student, u.reg_no FROM score_appeals a
             JOIN users u ON u.id=a.user_id ORDER BY (a.status='open') DESC, a.created_at DESC");
        $this->view('admin/appeals', [
            'title' => 'Score Appeals', 'heading' => 'Score Appeals', 'appeals' => $appeals,
        ], 'admin/layouts/admin');
    }

    public function reviewAppeal(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/appeals'); }
        $id = (int) ($params['id'] ?? 0);
        $appeal = Database::first("SELECT * FROM score_appeals WHERE id=?", [$id]);
        if (!$appeal) { redirect('/appeals'); }
        $decision = input('decision') === 'approved' ? 'approved' : 'rejected';
        $response = trim((string) input('response', ''));
        Database::run("UPDATE score_appeals SET status=?, response=?, reviewed_by=?, reviewed_at=? WHERE id=?",
            [$decision, $response, Auth::id(), date('Y-m-d H:i:s'), $id]);
        Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
            [$appeal['user_id'], 'Appeal ' . $decision,
             'Your appeal "' . $appeal['subject'] . '" was ' . $decision . '.' . ($response ? ' ' . $response : '')]);
        audit('appeal_' . $decision, 'score_appeals', $id, 'Appeal ' . $decision . ': ' . $appeal['subject'],
            ['response' => $response]);
        flash('success', 'Appeal ' . $decision . ' and the student has been notified.');
        redirect('/appeals');
    }

    /** Locked-exam violation log. */
    public function violations(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT v.user_id, u.name AS student, q.title AS quiz, q.id AS quiz_id,
                    COUNT(*) AS total, MAX(v.created_at) AS last_at
             FROM exam_violations v JOIN users u ON u.id=v.user_id JOIN quizzes q ON q.id=v.quiz_id
             GROUP BY v.user_id, v.quiz_id ORDER BY last_at DESC LIMIT 200");
        $kinds = Database::all(
            "SELECT kind, COUNT(*) c FROM exam_violations GROUP BY kind ORDER BY c DESC");
        $this->view('admin/exam-violations', [
            'title' => 'Exam Integrity', 'heading' => 'Locked Exam - Violation Log',
            'rows' => $rows, 'kinds' => $kinds,
        ], 'admin/layouts/admin');
    }
}
