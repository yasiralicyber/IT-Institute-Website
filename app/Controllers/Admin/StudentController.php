<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Fee;

class StudentController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $q = trim((string) input('q', ''));
        $filter = (string) input('filter', '');
        $params = [];
        $where = "role = 'student'";
        if ($q !== '') {
            $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $like = '%' . $q . '%';
            $params = [$like, $like, $like];
        }
        $students = Database::all(
            "SELECT u.*,
                (SELECT COUNT(*) FROM enrollments e WHERE e.user_id = u.id AND e.status='active') AS enrolls,
                (SELECT COUNT(*) FROM devices d WHERE d.user_id = u.id) AS devices
             FROM users u WHERE {$where} ORDER BY u.created_at DESC", $params);

        // Attach fee status + apply filters (paid / unpaid / fee-due / enrolled).
        foreach ($students as &$s) {
            $id = (int) $s['id'];
            $s['billed'] = Fee::billed($id);
            $s['paid']   = Fee::paid($id);
            $s['balance'] = $s['billed'] - $s['paid'];
        }
        unset($s);
        $students = array_values(array_filter($students, function ($s) use ($filter) {
            return match ($filter) {
                'enrolled'     => (int) $s['enrolls'] > 0,
                'not_enrolled' => (int) $s['enrolls'] === 0,
                'fee_due'      => $s['balance'] > 0,
                'fee_clear'    => $s['billed'] > 0 && $s['balance'] <= 0,
                'no_payment'   => $s['paid'] === 0,
                default        => true,
            };
        }));

        // Numbers for the one-click WhatsApp broadcast helper.
        $waNumbers = [];
        foreach ($students as $s) {
            $digits = preg_replace('/\D/', '', (string) $s['phone']);
            if ($digits === '') { continue; }
            if (str_starts_with($digits, '0')) { $digits = '92' . substr($digits, 1); }
            $waNumbers[] = $digits;
        }

        $this->view('admin/students', [
            'title' => 'Students', 'heading' => 'Students',
            'students' => $students, 'q' => $q, 'filter' => $filter, 'waNumbers' => $waNumbers,
        ], 'admin/layouts/admin');
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $student = Database::first("SELECT * FROM users WHERE id = ? AND role = 'student'", [$id]);
        if (!$student) { redirect('/students'); }

        $this->view('admin/student-detail', [
            'title'    => $student['name'], 'heading' => 'Student: ' . $student['name'],
            'student'  => $student,
            'enrollments' => Database::all(
                "SELECT e.*, c.title FROM enrollments e JOIN courses c ON c.id = e.course_id
                 WHERE e.user_id = ? ORDER BY e.approved_at DESC", [$id]),
            'purchases' => Database::all(
                "SELECT pr.*, c.title FROM purchase_requests pr JOIN courses c ON c.id = pr.course_id
                 WHERE pr.user_id = ? ORDER BY pr.created_at DESC", [$id]),
            'devices'  => Database::all("SELECT * FROM devices WHERE user_id = ? ORDER BY device_type", [$id]),
            'certs'    => Database::all("SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC", [$id]),
        ], 'admin/layouts/admin');
    }

    public function status(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/students'); }
        $id = (int) ($params['id'] ?? 0);
        $new = input('action') === 'suspend' ? 'suspended' : 'active';
        Database::run("UPDATE users SET status = ? WHERE id = ? AND role = 'student'", [$new, $id]);
        audit('student_status', 'users', $id, 'Set student status to ' . $new);
        flash('success', $new === 'suspended' ? 'Student account suspended.' : 'Student account re-activated.');
        redirect('/students/' . $id);
    }

    public function resetDevices(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/students'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("DELETE FROM devices WHERE user_id = ?", [$id]);
        Database::run("UPDATE users SET device_violations = 0 WHERE id = ?", [$id]);
        audit('reset_devices', 'users', $id, 'Reset device locks');
        flash('success', 'Device locks reset. The student can now log in fresh on a new mobile and computer.');
        redirect('/students/' . $id);
    }

    /** Unified support timeline for a student. */
    public function timeline(array $params): void
    {
        Auth::requireAdmin();
        $student = Database::first("SELECT * FROM users WHERE id=? AND role='student'", [(int) ($params['id'] ?? 0)]);
        if (!$student) { redirect('/students'); }
        $id = (int) $student['id'];
        $events = [];
        $add = function ($time, $icon, $text, $tone = 'slate') use (&$events) {
            if ($time) { $events[] = ['t' => $time, 'icon' => $icon, 'text' => $text, 'tone' => $tone]; }
        };

        foreach (Database::all("SELECT e.approved_at, c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=?", [$id]) as $r)
            $add($r['approved_at'], '', 'Enrolled in ' . $r['title'], 'brand');
        foreach (Database::all("SELECT pr.created_at, pr.status, c.title FROM purchase_requests pr JOIN courses c ON c.id=pr.course_id WHERE pr.user_id=?", [$id]) as $r)
            $add($r['created_at'], '', 'Online payment ' . $r['status'] . ' - ' . $r['title'], $r['status'] === 'approved' ? 'emerald' : 'amber');
        foreach (Database::all("SELECT paid_at, amount, receipt_no FROM fee_payments WHERE user_id=?", [$id]) as $r)
            $add($r['paid_at'], '', 'Fee paid ' . pkr($r['amount']) . ' (' . $r['receipt_no'] . ')', 'emerald');
        foreach (Database::all("SELECT qa.created_at, qa.score, qa.passed, ch.title FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id JOIN chapters ch ON ch.id=q.chapter_id WHERE qa.user_id=? ORDER BY qa.created_at DESC LIMIT 15", [$id]) as $r)
            $add($r['created_at'], $r['passed'] ? '' : '', 'Test "' . $r['title'] . '": ' . (int) $r['score'] . '%', $r['passed'] ? 'emerald' : 'red');
        foreach (Database::all("SELECT issued_at, course_title, type FROM certificates WHERE user_id=?", [$id]) as $r)
            $add($r['issued_at'], '', ucfirst($r['type']) . ' certificate - ' . $r['course_title'], 'gold');
        foreach (Database::all("SELECT created_at, title, status FROM projects WHERE user_id=?", [$id]) as $r)
            $add($r['created_at'], '', 'Project "' . $r['title'] . '" (' . $r['status'] . ')', 'brand');
        foreach (Database::all("SELECT created_at, label FROM devices WHERE user_id=?", [$id]) as $r)
            $add($r['created_at'], '', 'Device registered: ' . $r['label'], 'slate');
        foreach (Database::all("SELECT created_at, action, summary FROM audit_log WHERE entity='users' AND entity_id=? ORDER BY id DESC LIMIT 20", [$id]) as $r)
            $add($r['created_at'], '', $r['summary'], 'slate');
        foreach (Database::all("SELECT created_at, author_name, body FROM student_notes WHERE user_id=?", [$id]) as $r)
            $add($r['created_at'], '', 'Note (' . $r['author_name'] . '): ' . $r['body'], 'amber');

        usort($events, fn($a, $b) => strcmp((string) $b['t'], (string) $a['t']));

        $this->view('admin/student-timeline', [
            'title' => 'Timeline - ' . $student['name'], 'heading' => 'Timeline: ' . $student['name'],
            'student' => $student, 'events' => $events,
            'notes' => Database::all("SELECT * FROM student_notes WHERE user_id=? ORDER BY created_at DESC", [$id]),
        ], 'admin/layouts/admin');
    }

    public function addNote(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/students'); }
        $id = (int) ($params['id'] ?? 0);
        $body = trim((string) input('body', ''));
        if ($body !== '') {
            $admin = Auth::user();
            Database::run("INSERT INTO student_notes (user_id,author_id,author_name,body) VALUES (?,?,?,?)",
                [$id, $admin['id'] ?? null, $admin['name'] ?? 'Staff', $body]);
            flash('success', 'Note added.');
        }
        redirect('/students/' . $id . '/timeline');
    }

    /** Save guardian/parent access details for a student. */
    public function saveGuardian(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/students'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("UPDATE users SET father_name=?, dob=?, blood_group=?, address=?, guardian_name=?, guardian_phone=?, guardian_pin=? WHERE id=?",
            [trim((string) input('father_name', '')), trim((string) input('dob', '')),
             trim((string) input('blood_group', '')), trim((string) input('address', '')),
             trim((string) input('guardian_name', '')),
             trim((string) input('guardian_phone', '')), trim((string) input('guardian_pin', '')), $id]);
        flash('success', 'Student & guardian details saved. They now appear on the ID card / guardian portal.');
        redirect('/students/' . $id);
    }

    /** Printable digital ID card. */
    public function idCard(array $params): void
    {
        Auth::requireAdmin();
        $student = Database::first("SELECT * FROM users WHERE id=? AND role='student'", [(int) ($params['id'] ?? 0)]);
        if (!$student) { redirect('/students'); }
        $student = self::ensureIdentity($student);

        $batch = Database::first(
            "SELECT b.name AS batch, c.title AS program FROM batch_students bs
             JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id
             WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']]);
        $program = $batch['program'] ?? (Database::scalar(
            "SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$student['id']]) ?: 'General Student');

        echo \App\Core\View::render('admin/id-card', [
            'title' => 'ID Card - ' . $student['name'],
            'student' => $student, 'program' => $program, 'batch' => $batch['batch'] ?? '-',
        ], '');
    }

    /** Download the official filled ID card (front+back) as a high-quality PDF. */
    public function idCardPdf(array $params): void
    {
        Auth::requireAdmin();
        $student = Database::first("SELECT * FROM users WHERE id=? AND role='student'", [(int) ($params['id'] ?? 0)]);
        if (!$student) { redirect('/students'); }
        $student = self::ensureIdentity($student);
        $program = Database::scalar(
            "SELECT b.name FROM batch_students bs JOIN batches b ON b.id=bs.batch_id WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']])
            ? Database::scalar("SELECT c.title FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']])
            : (Database::scalar("SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$student['id']]) ?: 'General Student');

        $pdf = \App\Services\Pdf::idCard($student, (string) $program);
        audit('idcard.download', 'user', (int) $student['id'], 'ID card PDF for ' . $student['name']);
        self::streamPdf($pdf, 'ID-Card-' . self::slugName($student) . '.pdf');
    }

    /** Download all (or one batch's) ID cards packed onto A4 pages as a single PDF. */
    public function idCardsPdf(): void
    {
        Auth::requireAdmin();
        $batchId = (int) input('batch_id');
        if ($batchId) {
            $students = Database::all(
                "SELECT u.* FROM batch_students bs JOIN users u ON u.id=bs.user_id
                 WHERE bs.batch_id=? AND bs.status='active' AND u.role='student' ORDER BY u.name", [$batchId]);
        } else {
            $students = Database::all("SELECT * FROM users WHERE role='student' ORDER BY name");
        }
        $cards = [];
        foreach ($students as $s) {
            $s = self::ensureIdentity($s);
            $program = Database::scalar(
                "SELECT c.title FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id
                 WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$s['id']])
                ?: (Database::scalar("SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$s['id']]) ?: 'General Student');
            $cards[] = ['student' => $s, 'program' => $program];
        }
        $pdf = \App\Services\Pdf::idCardsBulk($cards);
        audit('idcard.bulk', null, null, count($cards) . ' ID cards (bulk PDF)');
        self::streamPdf($pdf, 'ID-Cards-' . ($batchId ? 'batch-' . $batchId : 'all') . '.pdf');
    }

    private static function slugName(array $s): string
    {
        return preg_replace('/[^A-Za-z0-9]+/', '-', trim(($s['reg_no'] ?? '') . '-' . ($s['name'] ?? 'student'))) ?: 'student';
    }

    private static function streamPdf(string $bytes, string $filename, bool $inline = false): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($bytes));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $bytes;
        exit;
    }

    /** Bulk print physical ID cards (multi-up sheet), optionally by batch. */
    public function idCards(): void
    {
        Auth::requireAdmin();
        $batchId = (int) input('batch_id');
        if ($batchId) {
            $students = Database::all(
                "SELECT u.* FROM batch_students bs JOIN users u ON u.id=bs.user_id
                 WHERE bs.batch_id=? AND bs.status='active' AND u.role='student' ORDER BY u.name", [$batchId]);
            $batchName = (string) Database::scalar("SELECT name FROM batches WHERE id=?", [$batchId]);
        } else {
            $students = Database::all("SELECT * FROM users WHERE role='student' ORDER BY name");
            $batchName = '';
        }
        // Ensure every card has a reg number + program/batch label.
        $cards = [];
        foreach ($students as $s) {
            $s = self::ensureIdentity($s);
            $b = Database::first(
                "SELECT b.name AS batch, c.title AS program FROM batch_students bs
                 JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id
                 WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$s['id']]);
            $program = $b['program'] ?? (Database::scalar(
                "SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$s['id']]) ?: 'General Student');
            $cards[] = ['student' => $s, 'program' => $program, 'batch' => $b['batch'] ?? '-'];
        }
        echo \App\Core\View::render('admin/id-cards', [
            'title' => 'Bulk ID Cards', 'cards' => $cards, 'batchName' => $batchName,
            'batches' => Database::all("SELECT b.id, b.name, c.title course FROM batches b JOIN courses c ON c.id=b.course_id ORDER BY b.name"),
            'batchId' => $batchId,
        ], '');
    }

    /** Generate a registration number + verification token once. */
    public static function ensureIdentity(array $student): array
    {
        if (empty($student['reg_no']) || empty($student['id_token'])) {
            $reg = $student['reg_no'] ?: 'ITTI-' . date('Y') . '-' . str_pad((string) $student['id'], 4, '0', STR_PAD_LEFT);
            $tok = $student['id_token'] ?: bin2hex(random_bytes(10));
            Database::run("UPDATE users SET reg_no=?, id_token=? WHERE id=?", [$reg, $tok, $student['id']]);
            $student['reg_no'] = $reg; $student['id_token'] = $tok;
        }
        return $student;
    }
}
