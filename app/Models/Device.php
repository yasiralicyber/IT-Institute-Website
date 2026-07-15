<?php
namespace App\Models;

use App\Core\Database;

/**
 * Device-lock enforcement: each account may be active on AT MOST
 * one mobile + one desktop at a time.
 *  - A matching fingerprint logs in normally (updates last_seen).
 *  - A new device within the per-type limit is registered.
 *  - A device that EXCEEDS the limit is blocked with a warning, and the
 *    violation is counted. On a repeated violation the account is suspended.
 */
class Device
{
    public const MAX_PER_TYPE = 1;          // 1 mobile + 1 desktop
    public const SUSPEND_AFTER = 2;         // suspend on the 2nd violation

    /** Classify the device from the User-Agent string. */
    public static function detectType(string $ua): string
    {
        return preg_match('/Mobile|Android|iPhone|iPad|iPod|Opera Mini|IEMobile|BlackBerry/i', $ua)
            ? 'mobile' : 'desktop';
    }

    /** A short human label for the device list. */
    public static function label(string $ua): string
    {
        $os = 'Device';
        foreach ([
            'Windows' => 'Windows PC', 'Macintosh' => 'Mac', 'iPhone' => 'iPhone',
            'iPad' => 'iPad', 'Android' => 'Android', 'Linux' => 'Linux PC',
        ] as $needle => $name) {
            if (stripos($ua, $needle) !== false) { $os = $name; break; }
        }
        $browser = 'Browser';
        foreach (['Edg' => 'Edge', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'] as $n => $b) {
            if (stripos($ua, $n) !== false) { $browser = $b; break; }
        }
        return "{$os} · {$browser}";
    }

    /**
     * Attempt to register/authorise a device for a user at login.
     * @return array{status:string, device_id:?int, message:?string}
     *   status = 'ok' | 'blocked' | 'suspended'
     */
    public static function authorise(int $userId, string $fingerprint, string $ua): array
    {
        $type = self::detectType($ua);

        // 1) Known device → allow, refresh last_seen.
        $existing = Database::first(
            "SELECT id FROM devices WHERE user_id = ? AND fingerprint = ?",
            [$userId, $fingerprint]
        );
        if ($existing) {
            Database::run("UPDATE devices SET last_seen = ? WHERE id = ?",
                [date('Y-m-d H:i:s'), $existing['id']]);
            return ['status' => 'ok', 'device_id' => (int) $existing['id'], 'message' => null];
        }

        // 1b) ZERO-TOLERANCE cross-account check: is this exact device already
        //     bound to a DIFFERENT account? Two accounts on one phone = sharing.
        //     Suspend EVERY account that has touched this device, including this one.
        $others = self::otherOwners($fingerprint, $userId);
        if ($others) {
            self::suspendShared(array_merge($others, [$userId]), $fingerprint);
            return [
                'status' => 'suspended',
                'device_id' => null,
                'message' => 'This phone is already linked to another student account. Sharing one device between accounts is not allowed, so ALL accounts using this device have been suspended. Please contact the institute.',
            ];
        }

        // 2) New device - is there room for this type?
        $count = (int) Database::scalar(
            "SELECT COUNT(*) FROM devices WHERE user_id = ? AND device_type = ?",
            [$userId, $type]
        );
        if ($count < self::MAX_PER_TYPE) {
            $id = (int) Database::run(
                "INSERT INTO devices (user_id,device_type,fingerprint,label,last_seen)
                 VALUES (?,?,?,?,?)",
                [$userId, $type, $fingerprint, self::label($ua), date('Y-m-d H:i:s')]
            );
            return ['status' => 'ok', 'device_id' => $id, 'message' => null];
        }

        // 3) Limit exceeded → a sharing attempt. Count the violation.
        $violations = (int) Database::scalar("SELECT device_violations FROM users WHERE id = ?", [$userId]) + 1;
        Database::run("UPDATE users SET device_violations = ? WHERE id = ?", [$violations, $userId]);

        $typeLabel = $type === 'mobile' ? 'mobile phone' : 'computer/laptop';
        if ($violations >= self::SUSPEND_AFTER) {
            Database::run("UPDATE users SET status = 'suspended' WHERE id = ?", [$userId]);
            return [
                'status' => 'suspended',
                'device_id' => null,
                'message' => 'Your account has been suspended because the login was shared across too many devices. Please contact the institute to restore access.',
            ];
        }

        return [
            'status' => 'blocked',
            'device_id' => null,
            'message' => "You are already logged in on another {$typeLabel}. Each account allows only ONE mobile and ONE computer. This attempt has been recorded - trying again will suspend your account.",
        ];
    }

    /** User ids (other than $exceptUserId) that have this exact device fingerprint. */
    public static function otherOwners(string $fingerprint, int $exceptUserId): array
    {
        $rows = Database::all(
            "SELECT DISTINCT user_id FROM devices WHERE fingerprint = ? AND user_id <> ?",
            [$fingerprint, $exceptUserId]
        );
        return array_map(static fn($r) => (int) $r['user_id'], $rows);
    }

    /** Suspend a set of accounts caught sharing one physical device. */
    public static function suspendShared(array $userIds, string $fingerprint): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        foreach ($userIds as $uid) {
            Database::run(
                "UPDATE users SET status = 'suspended', device_violations = device_violations + 1 WHERE id = ?",
                [$uid]
            );
            if (function_exists('audit')) {
                audit('device.shared_suspend', 'user', $uid,
                    'Suspended: device shared across accounts', [
                        'fingerprint' => substr($fingerprint, 0, 32),
                        'co_accounts' => array_values(array_diff($userIds, [$uid])),
                    ]);
            }
        }
    }

    public static function forUser(int $userId): array
    {
        return Database::all(
            "SELECT * FROM devices WHERE user_id = ? ORDER BY device_type, last_seen DESC", [$userId]
        );
    }

    /** Admin/student action: remove a device (frees a slot). */
    public static function remove(int $deviceId, int $userId): void
    {
        Database::run("DELETE FROM devices WHERE id = ? AND user_id = ?", [$deviceId, $userId]);
    }
}
