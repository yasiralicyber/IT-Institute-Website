<?php
namespace App\Models;

use App\Core\Database;

class Certificate
{
    /** Issue (once) a chapter or course certificate; returns credential id. */
    public static function issue(int $userId, int $courseId, ?int $chapterId, string $type): ?string
    {
        // Avoid duplicates.
        $existing = Database::first(
            "SELECT credential_id FROM certificates
             WHERE user_id = ? AND course_id = ? AND type = ? AND (chapter_id IS ? OR chapter_id = ?)",
            [$userId, $courseId, $type, $chapterId, $chapterId]
        );
        if ($existing) {
            return $existing['credential_id'];
        }

        $user = User::find($userId);
        $course = Course::find($courseId);
        if (!$user || !$course) { return null; }

        $credential = 'ITTI-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
        Database::run(
            "INSERT INTO certificates (user_id,course_id,chapter_id,type,credential_id,student_name,course_title)
             VALUES (?,?,?,?,?,?,?)",
            [$userId, $courseId, $chapterId, $type, $credential, $user['name'], $course['title']]
        );

        Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
            [$userId, 'Certificate issued',
             'You earned a ' . $type . ' certificate for "' . $course['title'] . '". Credential: ' . $credential]);

        return $credential;
    }

    public static function findByCredential(string $credential): ?array
    {
        $cert = Database::first(
            "SELECT ct.*, u.name AS student, u.father_name, u.reg_no AS student_reg,
                    c.title AS course
             FROM certificates ct
             JOIN users u ON u.id = ct.user_id
             JOIN courses c ON c.id = ct.course_id
             WHERE ct.credential_id = ?", [strtoupper(trim($credential))]
        );
        if ($cert) {
            // Fetch enrollment date as the course start date
            $cert['enrolled_at'] = Database::scalar(
                "SELECT approved_at FROM enrollments WHERE user_id=? AND course_id=? LIMIT 1",
                [$cert['user_id'], $cert['course_id']]
            );
        }
        return $cert;
    }
}
