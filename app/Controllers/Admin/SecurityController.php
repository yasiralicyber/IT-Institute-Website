<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Security;

class SecurityController extends Controller
{
    /* ---------------- Risk-based login log ---------------- */
    public function risk(): void
    {
        Auth::requireAdmin();
        $events = Database::all(
            "SELECT le.*, u.name AS student FROM login_events le LEFT JOIN users u ON u.id=le.user_id
             ORDER BY (le.outcome IN ('flagged','blocked','failed')) DESC, le.created_at DESC LIMIT 200");
        $this->view('admin/login-risk', [
            'title' => 'Login Risk', 'heading' => 'Risk-Based Login Monitor', 'events' => $events,
            'threshold' => Security::highRiskThreshold(),
        ], 'admin/layouts/admin');
    }

    /* ---------------- Honeytokens ---------------- */
    public function honeytokens(): void
    {
        Auth::requireAdmin();
        $tokens = Database::all("SELECT * FROM honeytokens ORDER BY hits DESC, created_at DESC");
        $hits = Database::all(
            "SELECT h.*, t.label FROM honeytoken_hits h JOIN honeytokens t ON t.id=h.token_id ORDER BY h.created_at DESC LIMIT 50");
        $this->view('admin/honeytokens', [
            'title' => 'Honeytokens', 'heading' => 'Honeytokens (Breach Decoys)', 'tokens' => $tokens, 'hits' => $hits,
        ], 'admin/layouts/admin');
    }

    public function createHoneytoken(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/honeytokens'); }
        $label = trim((string) input('label', ''));
        if ($label === '') { flash('error', 'Enter a label.'); redirect('/honeytokens'); }
        $token = 'ht_' . bin2hex(random_bytes(8));
        Database::run("INSERT INTO honeytokens (label,token) VALUES (?,?)", [$label, $token]);
        flash('success', 'Honeytoken created. Decoy URL: ' . abs_url('/internal/export/' . $token));
        redirect('/honeytokens');
    }

    public function deleteHoneytoken(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/honeytokens'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("DELETE FROM honeytoken_hits WHERE token_id=?", [$id]);
        Database::run("DELETE FROM honeytokens WHERE id=?", [$id]);
        flash('success', 'Honeytoken removed.');
        redirect('/honeytokens');
    }

    /* ---------------- Two-approver sensitive actions ---------------- */
    public function approvals(): void
    {
        Auth::requireAdmin();
        $requests = Database::all(
            "SELECT sr.*, ru.name AS requester, au.name AS approver FROM sensitive_requests sr
             LEFT JOIN users ru ON ru.id=sr.requested_by LEFT JOIN users au ON au.id=sr.approved_by
             ORDER BY (sr.status='pending') DESC, sr.created_at DESC");
        $students = Database::all("SELECT id,name,reg_no FROM users WHERE role='student' ORDER BY name");
        $this->view('admin/sensitive-approvals', [
            'title' => 'Approvals', 'heading' => 'Sensitive Actions (Two-Approver)',
            'requests' => $requests, 'students' => $students, 'me' => Auth::id(),
        ], 'admin/layouts/admin');
    }

    public function requestSensitive(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/approvals'); }
        $action = input('action');
        $actions = self::actions();
        if (!isset($actions[$action])) { flash('error', 'Unknown action.'); redirect('/approvals'); }
        $targetId = (int) input('target_id');
        $label = $actions[$action];
        $summary = $label;
        if ($action === 'purge_student' && $targetId) {
            $name = Database::scalar("SELECT name FROM users WHERE id=?", [$targetId]);
            $summary = $label . ': ' . ($name ?: ('#' . $targetId));
        }
        Security::requestSensitive($action, $summary, ['target_id' => $targetId]);
        flash('success', 'Request submitted. A second administrator must approve it.');
        redirect('/approvals');
    }

    public function decide(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/approvals'); }
        $req = Database::first("SELECT * FROM sensitive_requests WHERE id=?", [(int) ($params['id'] ?? 0)]);
        if (!$req || $req['status'] !== 'pending') { redirect('/approvals'); }

        // Governance rule: the approver must be a DIFFERENT admin than the requester.
        if ((int) $req['requested_by'] === Auth::id()) {
            flash('error', 'A sensitive action must be approved by a different administrator than the one who requested it.');
            redirect('/approvals');
        }
        $decision = input('decision') === 'approve' ? 'approve' : 'reject';
        $note = trim((string) input('note', ''));

        if ($decision === 'reject') {
            Database::run("UPDATE sensitive_requests SET status='rejected', approved_by=?, decision_note=?, decided_at=? WHERE id=?",
                [Auth::id(), $note, date('Y-m-d H:i:s'), $req['id']]);
            audit('sensitive_reject', 'sensitive_requests', (int) $req['id'], 'Rejected: ' . $req['summary']);
            flash('success', 'Request rejected.');
            redirect('/approvals');
        }

        // Approved: execute the action, then mark executed.
        $this->execute($req);
        Database::run("UPDATE sensitive_requests SET status='executed', approved_by=?, decision_note=?, decided_at=? WHERE id=?",
            [Auth::id(), $note, date('Y-m-d H:i:s'), $req['id']]);
        audit('sensitive_execute', 'sensitive_requests', (int) $req['id'], 'Approved & executed: ' . $req['summary']);
        flash('success', 'Approved and executed: ' . $req['summary']);
        redirect('/approvals');
    }

    private function execute(array $req): void
    {
        $payload = json_decode($req['payload'] ?? '{}', true) ?: [];
        $targetId = (int) ($payload['target_id'] ?? 0);
        if ($req['action'] === 'purge_student' && $targetId) {
            $student = Database::first("SELECT * FROM users WHERE id=? AND role='student'", [$targetId]);
            if ($student) {
                if (function_exists('trash_record')) { trash_record('users', $student, 'Purged student: ' . $student['name']); }
                foreach (['enrollments', 'devices', 'lecture_progress', 'quiz_attempts', 'fee_invoices', 'fee_payments', 'batch_students'] as $t) {
                    Database::run("DELETE FROM {$t} WHERE user_id=?", [$targetId]);
                }
                Database::run("DELETE FROM users WHERE id=?", [$targetId]);
            }
        } elseif ($req['action'] === 'reset_violations' && $targetId) {
            Database::run("UPDATE users SET device_violations=0, status='active' WHERE id=?", [$targetId]);
        }
    }

    /* ---------------- Staff roles (field-level RBAC) ---------------- */
    public function roles(): void
    {
        Auth::requireAdmin();
        $admins = Database::all("SELECT id,name,email,staff_role FROM users WHERE role='admin' ORDER BY name");
        $this->view('admin/staff-roles', [
            'title' => 'Staff Roles', 'heading' => 'Field-Level Access (Staff Roles)',
            'admins' => $admins, 'roles' => Security::staffRoles(), 'me' => Auth::id(),
        ], 'admin/layouts/admin');
    }

    public function setRole(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/staff-roles'); }
        $id = (int) ($params['id'] ?? 0);
        $role = input('staff_role');
        if (!array_key_exists($role, Security::staffRoles())) { $role = 'super'; }
        Database::run("UPDATE users SET staff_role=? WHERE id=? AND role='admin'", [$role, $id]);
        audit('staff_role', 'users', $id, 'Set staff role to ' . $role);
        flash('success', 'Staff role updated.');
        redirect('/staff-roles');
    }

    public static function actions(): array
    {
        return [
            'purge_student'   => 'Permanently purge a student account',
            'reset_violations'=> 'Reset a student\'s device violations & unsuspend',
        ];
    }
}
