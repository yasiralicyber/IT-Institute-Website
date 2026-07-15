<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class AttendanceController extends Controller
{
    /** Pick a batch to take attendance for. */
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT b.*, c.title AS course, s.name AS teacher,
                    (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id=b.id AND bs.status='active') AS students
             FROM batches b JOIN courses c ON c.id=b.course_id
             LEFT JOIN staff s ON s.id=b.staff_id WHERE b.status='active' ORDER BY b.name");
        $this->view('admin/attendance-index', ['title' => 'Attendance', 'heading' => 'Attendance', 'rows' => $rows], 'admin/layouts/admin');
    }

    /** Attendance sheet for a batch on a date. */
    public function mark(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        $date = $this->validDate((string) input('date', date('Y-m-d')));

        $students = Database::all(
            "SELECT bs.user_id, bs.roll_no, u.name FROM batch_students bs
             JOIN users u ON u.id=bs.user_id WHERE bs.batch_id=? AND bs.status='active'
             ORDER BY bs.roll_no, u.name", [$batch['id']]);
        // Existing marks for that date.
        $existing = [];
        foreach (Database::all("SELECT user_id,status FROM attendance WHERE batch_id=? AND date=?", [$batch['id'], $date]) as $a) {
            $existing[$a['user_id']] = $a['status'];
        }
        $this->view('admin/attendance-mark', [
            'title' => 'Mark Attendance', 'heading' => 'Attendance: ' . $batch['name'],
            'batch' => $batch, 'date' => $date, 'students' => $students, 'existing' => $existing,
        ], 'admin/layouts/admin');
    }

    public function save(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/attendance/' . $batch['id']); }
        $date = $this->validDate((string) input('date', date('Y-m-d')));
        $marks = (array) input('status', []);
        foreach ($marks as $userId => $status) {
            $status = in_array($status, ['present', 'late', 'absent'], true) ? $status : 'present';
            Database::run("DELETE FROM attendance WHERE batch_id=? AND user_id=? AND date=?", [$batch['id'], (int) $userId, $date]);
            Database::run("INSERT INTO attendance (batch_id,user_id,date,status,method,marked_by) VALUES (?,?,?,?,'manual',?)",
                [$batch['id'], (int) $userId, $date, $status, Auth::id()]);
        }
        flash('success', 'Attendance saved for ' . $date . '.');
        redirect('/attendance/' . $batch['id'] . '?date=' . $date);
    }

    /** Monthly report (summary + day grid). */
    public function report(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        $month = preg_match('/^\d{4}-\d{2}$/', (string) input('month', '')) ? input('month') : date('Y-m');

        $students = Database::all(
            "SELECT bs.user_id, bs.roll_no, u.name FROM batch_students bs JOIN users u ON u.id=bs.user_id
             WHERE bs.batch_id=? AND bs.status='active' ORDER BY bs.roll_no, u.name", [$batch['id']]);
        $records = Database::all("SELECT user_id,date,status FROM attendance WHERE batch_id=? AND date LIKE ?",
            [$batch['id'], $month . '-%']);
        $grid = [];
        foreach ($records as $r) { $grid[$r['user_id']][(int) substr($r['date'], 8, 2)] = $r['status']; }

        $this->view('admin/attendance-report', [
            'title' => 'Attendance Report', 'heading' => 'Report: ' . $batch['name'],
            'batch' => $batch, 'month' => $month, 'students' => $students, 'grid' => $grid,
            'days' => (int) date('t', strtotime($month . '-01')),
        ], 'admin/layouts/admin');
    }

    /** How long a single QR token stays valid (seconds). Short = anti-forwarding. */
    private const QR_TTL = 30;

    /** Show a rotating QR for students to self check-in today. */
    public function qr(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        $date = date('Y-m-d');
        [$token, $expires] = $this->freshToken($batch['id'], $date);
        $this->view('admin/attendance-qr', [
            'title' => 'Attendance QR', 'heading' => 'QR Check-in: ' . $batch['name'],
            'batch' => $batch, 'token' => $token, 'date' => $date,
            'expires' => $expires, 'ttl' => self::QR_TTL,
        ], 'admin/layouts/admin');
    }

    /** AJAX: rotate the token so the on-screen QR changes every few seconds. */
    public function rotate(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        [$token, $expires] = $this->freshToken($batch['id'], date('Y-m-d'), true);
        header('Content-Type: application/json');
        echo json_encode(['token' => $token, 'expires' => $expires, 'ttl' => self::QR_TTL]);
    }

    /** Close today's session and auto-mark every non-scanner ABSENT. */
    public function finalize(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/attendance/' . $batch['id'] . '/qr'); }
        $date = date('Y-m-d');

        Database::run("UPDATE batch_checkin SET is_open=0 WHERE batch_id=? AND date=?", [$batch['id'], $date]);

        $present = [];
        foreach (Database::all("SELECT user_id FROM attendance WHERE batch_id=? AND date=?", [$batch['id'], $date]) as $r) {
            $present[(int) $r['user_id']] = true;
        }
        $absent = 0;
        foreach (Database::all("SELECT user_id FROM batch_students WHERE batch_id=? AND status='active'", [$batch['id']]) as $s) {
            $uid = (int) $s['user_id'];
            if (!isset($present[$uid])) {
                Database::run(
                    "INSERT INTO attendance (batch_id,user_id,date,status,method,marked_by) VALUES (?,?,?,'absent','auto',?)",
                    [$batch['id'], $uid, $date, Auth::id()]
                );
                $absent++;
            }
        }
        flash('success', "Session closed. {$absent} student(s) who did not scan were marked absent.");
        redirect('/attendance/' . $batch['id'] . '/report');
    }

    /**
     * Get the current valid token, regenerating it if expired (or if $force).
     * @return array{0:string,1:string} [token, expires_at]
     */
    private function freshToken(int $batchId, string $date, bool $force = false): array
    {
        $row = Database::first("SELECT * FROM batch_checkin WHERE batch_id=? AND date=?", [$batchId, $date]);
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + self::QR_TTL);
        if (!$row) {
            $token = bin2hex(random_bytes(12));
            Database::run("INSERT INTO batch_checkin (batch_id,token,date,expires_at,is_open) VALUES (?,?,?,?,1)",
                [$batchId, $token, $date, $expires]);
            return [$token, $expires];
        }
        if ($force || empty($row['expires_at']) || $row['expires_at'] < $now) {
            $token = bin2hex(random_bytes(12));
            Database::run("UPDATE batch_checkin SET token=?, expires_at=?, is_open=1 WHERE id=?",
                [$token, $expires, $row['id']]);
            return [$token, $expires];
        }
        return [$row['token'], $row['expires_at']];
    }

    /** Biometric / fingerprint device CSV import. */
    public function importForm(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        $this->view('admin/attendance-import', [
            'title' => 'Import Attendance', 'heading' => 'Import from Device: ' . $batch['name'], 'batch' => $batch,
        ], 'admin/layouts/admin');
    }

    public function import(array $params): void
    {
        Auth::requireAdmin();
        $batch = $this->batch((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/attendance/' . $batch['id'] . '/import'); }

        if (empty($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Please choose a CSV file exported from your device.');
            redirect('/attendance/' . $batch['id'] . '/import');
        }
        // Map batch students by roll_no and email.
        $byRoll = []; $byEmail = [];
        foreach (Database::all("SELECT bs.user_id, bs.roll_no, u.email FROM batch_students bs JOIN users u ON u.id=bs.user_id WHERE bs.batch_id=?", [$batch['id']]) as $s) {
            if ($s['roll_no']) { $byRoll[strtolower(trim($s['roll_no']))] = $s['user_id']; }
            if ($s['email']) { $byEmail[strtolower(trim($s['email']))] = $s['user_id']; }
        }

        $fh = fopen($_FILES['csv']['tmp_name'], 'r');
        $header = null; $imported = 0; $skipped = 0;
        while (($line = fgetcsv($fh)) !== false) {
            if ($header === null) { $header = array_map(fn($h) => strtolower(trim($h)), $line); continue; }
            $row = @array_combine($header, $line);
            if (!$row) { $skipped++; continue; }
            $key = strtolower(trim($row['roll_no'] ?? $row['roll'] ?? $row['email'] ?? $row['id'] ?? ''));
            $uid = $byRoll[$key] ?? $byEmail[$key] ?? null;
            $date = $this->validDate(trim($row['date'] ?? ''));
            if (!$uid || !$date) { $skipped++; continue; }
            $status = strtolower(trim($row['status'] ?? 'present'));
            $status = in_array($status, ['present', 'late', 'absent'], true) ? $status : 'present';
            Database::run("DELETE FROM attendance WHERE batch_id=? AND user_id=? AND date=?", [$batch['id'], $uid, $date]);
            Database::run("INSERT INTO attendance (batch_id,user_id,date,status,method,marked_by) VALUES (?,?,?,?,'biometric',?)",
                [$batch['id'], $uid, $date, $status, Auth::id()]);
            $imported++;
        }
        fclose($fh);
        flash('success', "Imported {$imported} attendance records ({$skipped} skipped).");
        redirect('/attendance/' . $batch['id'] . '/report');
    }

    public function guide(): void
    {
        Auth::requireAdmin();
        $this->view('admin/attendance-guide', ['title' => 'Biometric Setup', 'heading' => 'Connect a Fingerprint Device'], 'admin/layouts/admin');
    }

    private function batch(int $id): array
    {
        $batch = Database::first("SELECT b.*, c.title AS course FROM batches b JOIN courses c ON c.id=b.course_id WHERE b.id=?", [$id]);
        if (!$batch) { redirect('/attendance'); }
        return $batch;
    }

    private function validDate(string $d): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d');
    }
}
