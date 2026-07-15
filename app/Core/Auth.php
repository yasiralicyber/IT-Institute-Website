<?php
namespace App\Core;

/**
 * Session-based authentication helper for students (and admins).
 * Stores the user id + active device id in the session.
 */
class Auth
{
    public static function login(array $user, ?int $deviceId = null): void
    {
        ensure_session();
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['uname'] = $user['name'];
        if ($deviceId !== null) {
            $_SESSION['device_id'] = $deviceId;
        }
    }

    public static function logout(): void
    {
        ensure_session();
        unset($_SESSION['uid'], $_SESSION['role'], $_SESSION['uname'], $_SESSION['device_id']);
    }

    public static function check(): bool
    {
        ensure_session();
        return !empty($_SESSION['uid']);
    }

    public static function id(): ?int
    {
        ensure_session();
        return isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
    }

    public static function role(): ?string
    {
        ensure_session();
        return $_SESSION['role'] ?? null;
    }

    /** Id of the device the current session logged in from, or null. */
    public static function deviceId(): ?int
    {
        ensure_session();
        return isset($_SESSION['device_id']) ? (int) $_SESSION['device_id'] : null;
    }

    /** Full device row for the active session device, or null. */
    public static function device(): ?array
    {
        $id = self::deviceId();
        return $id ? Database::first("SELECT * FROM devices WHERE id = ?", [$id]) : null;
    }

    /** Current user row (fetched fresh), or null. */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return Database::first("SELECT * FROM users WHERE id = ?", [self::id()]);
    }

    /** Require a logged-in admin; redirect to admin login otherwise. */
    public static function requireAdmin(): array
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'admin') {
            flash('error', 'Please log in as an administrator.');
            redirect('/login');
        }
        return $user;
    }

    /** Require a logged-in student; redirect to login otherwise. */
    public static function requireStudent(): array
    {
        $user = self::user();
        if (!$user) {
            flash('error', 'Please log in to continue.');
            redirect('/login');
        }
        if ($user['status'] === 'suspended') {
            self::logout();
            flash('error', 'Your account is suspended. Please contact the institute.');
            redirect('/login');
        }
        // Validate the active device still matches the session (device-lock).
        if (!empty($_SESSION['device_id'])) {
            $exists = Database::scalar(
                "SELECT COUNT(*) FROM devices WHERE id = ? AND user_id = ?",
                [$_SESSION['device_id'], $user['id']]
            );
            if (!$exists) {
                self::logout();
                flash('error', 'Your session ended because this device was removed. Please log in again.');
                redirect('/login');
            }
        }
        return $user;
    }
}
