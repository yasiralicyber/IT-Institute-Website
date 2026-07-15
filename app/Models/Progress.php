<?php
namespace App\Models;

use App\Core\Database;

/**
 * Progress, chapter-test gating and lecture access rules.
 * Rule: chapter 1 unlocks on enrollment; each later chapter unlocks only
 * after the previous chapter's test is passed.
 */
class Progress
{
    /** Ordered chapters for a course. */
    public static function chapters(int $courseId): array
    {
        return Database::all("SELECT * FROM chapters WHERE course_id = ? ORDER BY sort ASC", [$courseId]);
    }

    /** Has the user passed the test for this chapter? */
    public static function chapterPassed(int $userId, int $chapterId): bool
    {
        return (bool) Database::scalar(
            "SELECT COUNT(*) FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.user_id = ? AND q.chapter_id = ? AND qa.passed = 1",
            [$userId, $chapterId]
        );
    }

    /** Is this chapter unlocked for the user? (first chapter, or prev passed) */
    public static function chapterUnlocked(int $userId, int $courseId, int $chapterId): bool
    {
        $chapters = self::chapters($courseId);
        $prevId = null;
        foreach ($chapters as $ch) {
            if ((int) $ch['id'] === $chapterId) {
                return $prevId === null ? true : self::chapterPassed($userId, $prevId);
            }
            $prevId = (int) $ch['id'];
        }
        return false;
    }

    public static function lectureCompleted(int $userId, int $lectureId): bool
    {
        return (bool) Database::scalar(
            "SELECT COUNT(*) FROM lecture_progress WHERE user_id = ? AND lecture_id = ?",
            [$userId, $lectureId]
        );
    }

    public static function markComplete(int $userId, int $lectureId): void
    {
        if (!self::lectureCompleted($userId, $lectureId)) {
            Database::run("INSERT INTO lecture_progress (user_id,lecture_id) VALUES (?,?)", [$userId, $lectureId]);
        }
    }

    /** Quiz for a chapter (with question count). */
    public static function quizForChapter(int $chapterId): ?array
    {
        $quiz = Database::first("SELECT * FROM quizzes WHERE chapter_id = ? LIMIT 1", [$chapterId]);
        if ($quiz) {
            $quiz['question_count'] = (int) Database::scalar("SELECT COUNT(*) FROM questions WHERE quiz_id = ?", [$quiz['id']]);
            $quiz['attempts_used'] = 0;
        }
        return $quiz;
    }

    public static function attemptsUsed(int $userId, int $quizId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?", [$userId, $quizId]);
    }

    /** Whole-course completion (all lectures done). */
    public static function courseCompleted(int $userId, int $courseId): bool
    {
        $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id = ?", [$courseId]);
        if ($total === 0) { return false; }
        $done = (int) Database::scalar(
            "SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id = lp.lecture_id
             WHERE lp.user_id = ? AND l.course_id = ?", [$userId, $courseId]);
        return $done >= $total;
    }
}
