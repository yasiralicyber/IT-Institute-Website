<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Device;

class CheckinController extends Controller
{
    /**
     * Student scans the batch QR -> marks themselves present for today.
     *
     * STRICT anti-cheat rules (no loopholes):
     *  - Must be logged in as an active student.
     *  - The QR token must be valid, for today, and NOT expired
     *    (tokens rotate every ~30s, so a photographed/forwarded code dies fast).
     *  - Must be enrolled (active) in that batch.
     *  - Must be scanning from a MOBILE device that is the account's OWN
     *    registered phone (session device). Desktops cannot self-check-in.
     *  - If that phone is bound to another account -> BOTH suspended.
     *  - If that phone already marked a DIFFERENT student present in this
     *    session -> proxy attendance -> BOTH suspended.
     *  - Anyone enrolled who never scans is auto-marked ABSENT when the
     *    teacher closes the session (see AttendanceController::finalize).
     */
    public function checkin(array $params): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'student') {
            flash('error', 'Please log in as a student to check in.');
            redirect('/login?next=checkin');
        }
        if (($user['status'] ?? 'active') === 'suspended') {
            Auth::logout();
            flash('error', 'Your account is suspended. Please contact the institute.');
            redirect('/login');
        }

        $token   = (string) ($params['token'] ?? '');
        $session = Database::first("SELECT * FROM batch_checkin WHERE token = ?", [$token]);

        $state = 'invalid';
        $batchName = '';

        if ($session) {
            $batchName = (string) Database::scalar("SELECT name FROM batches WHERE id=?", [$session['batch_id']]);
            $inBatch = (int) Database::scalar(
                "SELECT COUNT(*) FROM batch_students WHERE batch_id=? AND user_id=? AND status='active'",
                [$session['batch_id'], $user['id']]
            );
            $expired = $session['date'] !== date('Y-m-d')
                || (int) ($session['is_open'] ?? 1) === 0
                || (!empty($session['expires_at']) && $session['expires_at'] < date('Y-m-d H:i:s'));

            $device = Auth::device();

            if ($expired) {
                $state = 'expired';
            } elseif (!$inBatch) {
                $state = 'not_in_batch';
            } elseif (!$device) {
                $state = 'no_device';
            } elseif (($device['device_type'] ?? '') !== 'mobile') {
                $state = 'desktop';
            } else {
                $fp = (string) $device['fingerprint'];

                // (a) This phone bound to any OTHER account? -> sharing -> suspend all.
                $others = Device::otherOwners($fp, (int) $user['id']);
                if ($others) {
                    Device::suspendShared(array_merge($others, [(int) $user['id']]), $fp);
                    Auth::logout();
                    $state = 'shared';
                }
                // (b) This phone already marked a DIFFERENT student today/this batch? -> proxy.
                elseif ($proxyUid = $this->proxyOwner($session, $fp, (int) $user['id'])) {
                    Device::suspendShared([$proxyUid, (int) $user['id']], $fp);
                    Auth::logout();
                    $state = 'proxy';
                } else {
                    $already = (int) Database::scalar(
                        "SELECT COUNT(*) FROM attendance WHERE batch_id=? AND user_id=? AND date=?",
                        [$session['batch_id'], $user['id'], $session['date']]
                    );
                    if (!$already) {
                        Database::run(
                            "INSERT INTO attendance (batch_id,user_id,date,status,method,device_id,fingerprint)
                             VALUES (?,?,?,'present','qr',?,?)",
                            [$session['batch_id'], $user['id'], $session['date'], (int) $device['id'], $fp]
                        );
                        $state = 'ok';
                    } else {
                        $state = 'already';
                    }
                }
            }
        }

        $this->view('student/checkin', [
            'title' => 'Attendance Check-in', 'state' => $state, 'batch' => $batchName, 'user' => $user,
        ], 'layouts/dash');
    }

    /** Has this exact device already marked some OTHER student present in this session? */
    private function proxyOwner(array $session, string $fingerprint, int $selfId): ?int
    {
        $row = Database::first(
            "SELECT user_id FROM attendance
             WHERE batch_id=? AND date=? AND fingerprint=? AND user_id<>? LIMIT 1",
            [$session['batch_id'], $session['date'], $fingerprint, $selfId]
        );
        return $row ? (int) $row['user_id'] : null;
    }
}
