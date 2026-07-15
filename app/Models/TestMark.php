<?php
namespace App\Models;

use App\Core\Database;

/**
 * Lightweight physical / in-class test marks. Admin enters a student's marks
 * for a test; the student sees them online. Kept deliberately simple (one row
 * per student per test) — separate from the weighted result_sets engine.
 */
class TestMark
{
    /** Marks for one student (published only, newest first). */
    public static function forStudent(int $userId, bool $publishedOnly = true): array
    {
        $where = $publishedOnly ? "AND status = 'published'" : '';
        return Database::all(
            "SELECT * FROM test_marks WHERE user_id = ? $where ORDER BY test_date DESC, id DESC",
            [$userId]
        );
    }

    /** Recent entries for the admin list (joined with student name). */
    public static function recent(int $limit = 100): array
    {
        return Database::all(
            "SELECT t.*, u.name AS student_name, u.reg_no
             FROM test_marks t JOIN users u ON u.id = t.user_id
             ORDER BY t.created_at DESC LIMIT ?", [$limit]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM test_marks WHERE id = ?", [$id]);
    }

    public static function create(array $d, ?array $admin = null): int
    {
        Database::run(
            "INSERT INTO test_marks
             (user_id, test_name, subject, marks_obtained, total_marks, test_date, remarks, status, entered_by, entered_by_name)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [
                (int) ($d['user_id'] ?? 0),
                trim((string) ($d['test_name'] ?? '')),
                trim((string) ($d['subject'] ?? '')),
                (float) ($d['marks_obtained'] ?? 0),
                (float) ($d['total_marks'] ?? 100),
                trim((string) ($d['test_date'] ?? '')) ?: date('Y-m-d'),
                trim((string) ($d['remarks'] ?? '')),
                ($d['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
                $admin['id'] ?? null,
                $admin['name'] ?? 'admin',
            ]
        );
        return (int) Database::pdo()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::run("DELETE FROM test_marks WHERE id = ?", [$id]);
    }

    /** Percentage (0-100) for a row. */
    public static function pct(array $row): float
    {
        $total = (float) $row['total_marks'];
        return $total > 0 ? round((float) $row['marks_obtained'] / $total * 100, 1) : 0.0;
    }

    /** Simple letter grade from a percentage. */
    public static function grade(float $pct): string
    {
        return match (true) {
            $pct >= 80 => 'A+',
            $pct >= 70 => 'A',
            $pct >= 60 => 'B',
            $pct >= 50 => 'C',
            $pct >= 40 => 'D',
            default    => 'F',
        };
    }
}
