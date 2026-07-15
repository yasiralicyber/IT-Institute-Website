<?php
namespace App\Models;

use App\Core\Database;

class Course
{
    /** All published courses for the catalog. */
    public static function published(): array
    {
        return Database::all(
            "SELECT * FROM courses WHERE is_published = 1 ORDER BY sort ASC"
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::first("SELECT * FROM courses WHERE slug = ?", [$slug]);
    }

    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM courses WHERE id = ?", [$id]);
    }

    /** Chapters with their lectures for a course (ordered). */
    public static function curriculum(int $courseId): array
    {
        $chapters = Database::all(
            "SELECT * FROM chapters WHERE course_id = ? ORDER BY sort ASC", [$courseId]
        );
        foreach ($chapters as &$ch) {
            $ch['lectures'] = Database::all(
                "SELECT * FROM lectures WHERE chapter_id = ? ORDER BY sort ASC", [$ch['id']]
            );
        }
        return $chapters;
    }

    public static function lectureCount(int $courseId): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id = ?", [$courseId]);
    }

    public static function freeLectureCount(int $courseId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM lectures WHERE course_id = ? AND is_free = 1", [$courseId]
        );
    }

    /** Decoded outcomes array. */
    public static function outcomes(array $course): array
    {
        $decoded = json_decode($course['outcomes'] ?? '[]', true);
        return is_array($decoded) ? $decoded : [];
    }

    /** Approved reviews for a course (with author name). */
    public static function reviews(int $courseId): array
    {
        return Database::all(
            "SELECT r.*, u.name AS author FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.course_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC", [$courseId]
        );
    }

    public static function ratingSummary(int $courseId): array
    {
        $row = Database::first(
            "SELECT COUNT(*) AS c, AVG(rating) AS a FROM reviews
             WHERE course_id = ? AND status = 'approved'", [$courseId]
        );
        return ['count' => (int) ($row['c'] ?? 0), 'avg' => round((float) ($row['a'] ?? 0), 1)];
    }
}
