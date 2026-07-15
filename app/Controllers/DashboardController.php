<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Device;
use App\Models\ResultSet;

class DashboardController extends Controller
{
    /** Student transcript: every approved result the student appears in. */
    public function results(): void
    {
        $user = Auth::requireStudent();
        $sets = Database::all(
            "SELECT rs.* FROM result_sets rs WHERE rs.status='approved'
             AND (
               (rs.batch_id IS NOT NULL AND rs.batch_id IN (SELECT batch_id FROM batch_students WHERE user_id=? AND status='active'))
               OR (rs.course_id IS NOT NULL AND rs.course_id IN (SELECT course_id FROM enrollments WHERE user_id=?))
             ) ORDER BY rs.approved_at DESC", [$user['id'], $user['id']]);

        $cards = [];
        foreach ($sets as $set) {
            $full = ResultSet::find((int) $set['id']);
            $components = ResultSet::components((int) $set['id']);
            $bands = ResultSet::bands($full);
            $cards[] = [
                'set' => $full,
                'components' => $components,
                'r' => ResultSet::compute($full, $components, (int) $user['id'], $bands),
            ];
        }
        $appeals = Database::all("SELECT * FROM score_appeals WHERE user_id=? ORDER BY created_at DESC", [$user['id']]);
        $this->view('student/results', [
            'title' => 'My Results', 'user' => $user, 'cards' => $cards, 'appeals' => $appeals,
        ], 'layouts/dash');
    }

    /** Student files a score appeal against a published result. */
    public function fileAppeal(): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/my-results'); }
        $subject = trim((string) input('subject', ''));
        $reason = trim((string) input('reason', ''));
        if ($subject === '' || mb_strlen($reason) < 10) {
            flash('error', 'Please add a subject and explain your appeal (at least 10 characters).');
            redirect('/my-results');
        }
        Database::run(
            "INSERT INTO score_appeals (user_id,ref_type,ref_id,subject,reason,status) VALUES (?,?,?,?,?,'open')",
            [$user['id'], 'result', (int) input('ref_id') ?: null, $subject, $reason]);
        audit('appeal_filed', 'score_appeals', null, 'Student filed a score appeal: ' . $subject);
        flash('success', 'Your appeal has been submitted. The examinations office will review it.');
        redirect('/my-results');
    }

    public function index(): void
    {
        $user = Auth::requireStudent();
        $courses = User::enrolledCourses((int) $user['id']);

        // Attach progress to each enrolled course.
        foreach ($courses as &$c) {
            $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id = ?", [$c['id']]);
            $done  = (int) Database::scalar(
                "SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id = lp.lecture_id
                 WHERE lp.user_id = ? AND l.course_id = ?", [$user['id'], $c['id']]);
            $c['progress'] = $total ? (int) round($done / $total * 100) : 0;
            $c['done'] = $done; $c['total'] = $total;
        }
        unset($c);

        $pending = Database::all(
            "SELECT pr.*, c.title, c.slug FROM purchase_requests pr
             JOIN courses c ON c.id = pr.course_id
             WHERE pr.user_id = ? ORDER BY pr.created_at DESC", [$user['id']]
        );

        $certs = Database::all(
            "SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC", [$user['id']]
        );

        $this->view('student/dashboard', [
            'title'   => 'My Dashboard - ' . config('app.name'),
            'user'    => $user,
            'courses' => $courses,
            'pending' => $pending,
            'certs'   => $certs,
            'recs'    => \App\Models\Recommendation::forUser((int) $user['id']),
            'revisions' => \App\Models\Learning::revisionsDue((int) $user['id']),
            'testMarks' => \App\Models\TestMark::forStudent((int) $user['id']),
        ], 'layouts/dash');
    }

    /** Let the logged-in student download their own official ID card (front+back) PDF. */
    public function idCardPdf(): void
    {
        $user = Auth::requireStudent();
        $student = Database::first("SELECT * FROM users WHERE id=?", [(int) $user['id']]);
        // Generate a registration number + verification token once, if missing.
        if (empty($student['reg_no']) || empty($student['id_token'])) {
            $reg = $student['reg_no'] ?: 'ITTI-' . date('Y') . '-' . str_pad((string) $student['id'], 4, '0', STR_PAD_LEFT);
            $tok = $student['id_token'] ?: bin2hex(random_bytes(10));
            Database::run("UPDATE users SET reg_no=?, id_token=? WHERE id=?", [$reg, $tok, $student['id']]);
            $student['reg_no'] = $reg; $student['id_token'] = $tok;
        }
        $program = Database::scalar(
            "SELECT c.title FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id
             WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']])
            ?: (Database::scalar("SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$student['id']]) ?: 'General Student');

        $pdf  = \App\Services\Pdf::idCard($student, (string) $program);
        $name = 'My-ID-Card-' . preg_replace('/[^A-Za-z0-9]+/', '-', (string) $student['reg_no']) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdf;
        exit;
    }

    public function courses(): void
    {
        $user = Auth::requireStudent();
        $this->view('student/courses', [
            'title'   => 'My Courses - ' . config('app.name'),
            'user'    => $user,
            'courses' => User::enrolledCourses((int) $user['id']),
        ], 'layouts/dash');
    }

    public function devices(): void
    {
        $user = Auth::requireStudent();
        $this->view('student/devices', [
            'title'   => 'My Devices - ' . config('app.name'),
            'user'    => $user,
            'devices' => Device::forUser((int) $user['id']),
        ], 'layouts/dash');
    }

    public function removeDevice(): void
    {
        $user = Auth::requireStudent();
        if (csrf_verify(input('_csrf'))) {
            Device::remove((int) input('device_id'), (int) $user['id']);
            flash('success', 'Device removed. That slot is now free for a new device.');
        }
        redirect('/devices');
    }
}
