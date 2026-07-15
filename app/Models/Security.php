<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

/**
 * Security & governance (task #36): risk-based login scoring,
 * field-level RBAC, honeytoken hit logging and the two-approver
 * sensitive-action queue.
 */
class Security
{
    /* ---------------- Risk-based login ---------------- */

    /** @return array{risk:int, reasons:array<string>} */
    public static function scoreLogin(array $user, string $ip, string $fp): array
    {
        $risk = 0; $reasons = [];

        // Unknown device fingerprint for this account.
        if ($fp !== '' && !Database::scalar("SELECT COUNT(*) FROM devices WHERE user_id=? AND fingerprint=?", [$user['id'], $fp])) {
            $risk += 30; $reasons[] = 'new device';
        }
        // New IP (different from last successful login).
        if (!empty($user['last_login_ip']) && $user['last_login_ip'] !== $ip) {
            $risk += 25; $reasons[] = 'new IP address';
        }
        // Odd-hour login (local midnight - 5am).
        $hour = (int) date('G');
        if ($hour >= 0 && $hour < 5) {
            $risk += 15; $reasons[] = 'unusual hour';
        }
        // Recent failed attempts on this account/email in the last hour.
        $fails = (int) Database::scalar(
            "SELECT COUNT(*) FROM login_events WHERE email=? AND outcome='failed' AND created_at >= ?",
            [$user['email'], date('Y-m-d H:i:s', time() - 3600)]);
        if ($fails >= 3) { $risk += 30; $reasons[] = $fails . ' recent failed attempts'; }

        return ['risk' => min(100, $risk), 'reasons' => $reasons];
    }

    public static function logLogin(?int $userId, string $email, string $ip, string $fp, int $risk, array $reasons, string $outcome): void
    {
        Database::run(
            "INSERT INTO login_events (user_id,email,ip,fingerprint,risk,reasons,outcome) VALUES (?,?,?,?,?,?,?)",
            [$userId, $email, $ip, substr($fp, 0, 64), $risk, implode(', ', $reasons), $outcome]);
    }

    public static function highRiskThreshold(): int { return 50; }

    /** Notify all admins about a risky login. */
    public static function alertAdmins(string $title, string $body): void
    {
        foreach (Database::all("SELECT id FROM users WHERE role='admin'") as $a) {
            Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)", [$a['id'], $title, $body]);
        }
    }

    /* ---------------- Field-level RBAC ---------------- */

    /**
     * What an admin sub-role may see. 'super' (or no staff_role) sees all.
     * Sensitive fields: cnic, guardian, fees, contact.
     */
    private const FIELD_MATRIX = [
        'super'      => ['cnic' => true,  'guardian' => true,  'fees' => true,  'contact' => true],
        'finance'    => ['cnic' => false, 'guardian' => false, 'fees' => true,  'contact' => true],
        'academic'   => ['cnic' => false, 'guardian' => true,  'fees' => false, 'contact' => true],
        'front_desk' => ['cnic' => false, 'guardian' => false, 'fees' => false, 'contact' => true],
    ];

    public static function canViewField(string $field, ?array $admin = null): bool
    {
        $admin = $admin ?? Auth::user();
        $role = $admin['staff_role'] ?? 'super';
        if ($role === '' || $role === null || $role === 'super') { return true; }
        return self::FIELD_MATRIX[$role][$field] ?? false;
    }

    public static function mask(string $field, ?string $value, ?array $admin = null): string
    {
        if (self::canViewField($field, $admin)) { return (string) $value; }
        return '••••••';
    }

    public static function staffRoles(): array
    {
        return ['super' => 'Super (all fields)', 'finance' => 'Finance', 'academic' => 'Academic', 'front_desk' => 'Front Desk'];
    }

    /* ---------------- Honeytokens ---------------- */

    public static function recordHoneytokenHit(array $token, string $ip, string $ua, ?int $userId): void
    {
        Database::run("INSERT INTO honeytoken_hits (token_id,ip,ua,user_id) VALUES (?,?,?,?)",
            [$token['id'], $ip, substr($ua, 0, 255), $userId]);
        Database::run("UPDATE honeytokens SET hits=hits+1, last_ip=?, last_at=? WHERE id=?",
            [$ip, date('Y-m-d H:i:s'), $token['id']]);
        self::alertAdmins('Honeytoken tripped', 'Decoy "' . $token['label'] . '" was accessed from ' . $ip . '. This may indicate scraping or a breach.');
        audit('honeytoken_hit', 'honeytokens', (int) $token['id'], 'Honeytoken "' . $token['label'] . '" accessed from ' . $ip);
    }

    /* ---------------- Two-approver sensitive actions ---------------- */

    public static function requestSensitive(string $action, string $summary, array $payload): int
    {
        $id = (int) Database::run(
            "INSERT INTO sensitive_requests (action,summary,payload,requested_by,requested_name) VALUES (?,?,?,?,?)",
            [$action, $summary, json_encode($payload), Auth::id(), Auth::user()['name'] ?? '']);
        self::alertAdmins('Approval needed', 'A sensitive action needs a second approver: ' . $summary);
        audit('sensitive_request', 'sensitive_requests', $id, 'Requested: ' . $summary);
        return $id;
    }
}
