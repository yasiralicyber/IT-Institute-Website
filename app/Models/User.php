<?php
namespace App\Models;

use App\Core\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::first("SELECT * FROM users WHERE email = ?", [strtolower(trim($email))]);
    }

    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public static function create(array $d): int
    {
        return (int) Database::run(
            "INSERT INTO users (role,name,email,password,phone,verify_token,status)
             VALUES ('student',?,?,?,?,?,'active')",
            [
                $d['name'],
                strtolower(trim($d['email'])),
                password_hash($d['password'], PASSWORD_DEFAULT),
                $d['phone'] ?? null,
                $d['verify_token'] ?? null,
            ]
        );
    }

    public static function verifyEmail(string $token): bool
    {
        $user = Database::first("SELECT id FROM users WHERE verify_token = ?", [$token]);
        if (!$user) {
            return false;
        }
        Database::run("UPDATE users SET email_verified_at = ?, verify_token = NULL WHERE id = ?",
            [date('Y-m-d H:i:s'), $user['id']]);
        return true;
    }

    public static function suspend(int $id): void
    {
        Database::run("UPDATE users SET status = 'suspended' WHERE id = ?", [$id]);
    }

    /** Courses a student is enrolled in (approved access). */
    public static function enrolledCourses(int $userId): array
    {
        return Database::all(
            "SELECT c.* FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             WHERE e.user_id = ? AND e.status = 'active'
             ORDER BY e.approved_at DESC", [$userId]
        );
    }

    public static function hasAccess(int $userId, int $courseId): bool
    {
        return (bool) Database::scalar(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'active'",
            [$userId, $courseId]
        );
    }
}
