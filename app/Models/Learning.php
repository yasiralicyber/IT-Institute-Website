<?php
namespace App\Models;

use App\Core\Database;

/**
 * Learning enhancements (task #35): multi-path placement, knowledge-decay
 * revision scheduling, milestone snapshots, reusable blocks, content
 * expiry and acknowledgment tracking. Cross-cutting helpers used by the
 * quiz, learn and player flows.
 */
class Learning
{
    /** Weeks after passing a chapter before a revision test is due. */
    public static function revisionWeeks(): int
    {
        $v = Database::scalar("SELECT value FROM settings WHERE key_name='revision_weeks'");
        return max(1, (int) ($v ?: 8));
    }

    /** Called when a student passes a chapter test. */
    public static function onChapterPassed(int $userId, int $courseId, int $chapterId, array $quiz): void
    {
        // 1) Knowledge-decay: schedule a revision test in N weeks (if not already).
        self::scheduleRevision($userId, $courseId, $chapterId);
        // Passing a revision retake clears the current due item.
        Database::run("UPDATE revision_schedule SET done_at=? WHERE user_id=? AND chapter_id=? AND due_at<=?",
            [date('Y-m-d H:i:s'), $userId, $chapterId, date('Y-m-d')]);

        // 2) Multi-path: a placement test tests the student out of earlier chapters.
        if (!empty($quiz['is_placement']) && (int) $quiz['placement_skips'] > 0) {
            self::applyPlacement($userId, $courseId, (int) $quiz['placement_skips']);
        }

        // 3) Milestone snapshot if completion crossed a threshold.
        self::snapshotMilestone($userId, $courseId);
    }

    public static function scheduleRevision(int $userId, int $courseId, int $chapterId): void
    {
        $exists = Database::scalar("SELECT id FROM revision_schedule WHERE user_id=? AND chapter_id=?", [$userId, $chapterId]);
        $due = date('Y-m-d', strtotime('+' . self::revisionWeeks() . ' weeks'));
        if ($exists) {
            Database::run("UPDATE revision_schedule SET due_at=?, done_at=NULL WHERE id=?", [$due, $exists]);
        } else {
            Database::run("INSERT INTO revision_schedule (user_id,course_id,chapter_id,due_at) VALUES (?,?,?,?)",
                [$userId, $courseId, $chapterId, $due]);
        }
    }

    /** Mark the first N chapters of a course as tested-out + auto-complete them. */
    public static function applyPlacement(int $userId, int $courseId, int $skips): void
    {
        $chapters = Database::all("SELECT id FROM chapters WHERE course_id=? ORDER BY sort, id LIMIT ?", [$courseId, $skips]);
        foreach ($chapters as $ch) {
            $cid = (int) $ch['id'];
            if (!Database::scalar("SELECT id FROM placement_skips WHERE user_id=? AND chapter_id=?", [$userId, $cid])) {
                Database::run("INSERT INTO placement_skips (user_id,course_id,chapter_id) VALUES (?,?,?)", [$userId, $courseId, $cid]);
            }
            // Auto-complete every lecture in the skipped chapter.
            foreach (Database::all("SELECT id FROM lectures WHERE chapter_id=?", [$cid]) as $l) {
                Progress::markComplete($userId, (int) $l['id']);
            }
        }
    }

    public static function isSkipped(int $userId, int $chapterId): bool
    {
        return (bool) Database::scalar("SELECT COUNT(*) FROM placement_skips WHERE user_id=? AND chapter_id=?", [$userId, $chapterId]);
    }

    /** Record a snapshot when course completion crosses 25/50/75/100%. */
    public static function snapshotMilestone(int $userId, int $courseId): void
    {
        $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id=?", [$courseId]);
        if (!$total) { return; }
        $done = (int) Database::scalar(
            "SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id=lp.lecture_id WHERE lp.user_id=? AND l.course_id=?",
            [$userId, $courseId]);
        $pct = (int) round($done / $total * 100);
        foreach ([25, 50, 75, 100] as $m) {
            if ($pct >= $m && !Database::scalar("SELECT id FROM progress_snapshots WHERE user_id=? AND course_id=? AND milestone=?", [$userId, $courseId, $m])) {
                Database::run("INSERT INTO progress_snapshots (user_id,course_id,milestone,percent,note) VALUES (?,?,?,?,?)",
                    [$userId, $courseId, $m, $pct, 'Reached ' . $m . '% of the course']);
            }
        }
    }

    /** Revision tests now due for a student (overdue, not yet done). */
    public static function revisionsDue(int $userId): array
    {
        return Database::all(
            "SELECT rs.*, ch.title AS chapter, c.title AS course, c.slug
             FROM revision_schedule rs
             JOIN chapters ch ON ch.id=rs.chapter_id
             JOIN courses c ON c.id=rs.course_id
             WHERE rs.user_id=? AND rs.done_at IS NULL AND rs.due_at<=? ORDER BY rs.due_at",
            [$userId, date('Y-m-d')]);
    }

    /** Reusable blocks attached to a lecture. */
    public static function blocksForLecture(int $lectureId): array
    {
        return Database::all(
            "SELECT b.* FROM lecture_blocks lb JOIN learning_blocks b ON b.id=lb.block_id
             WHERE lb.lecture_id=? ORDER BY lb.sort, lb.id", [$lectureId]);
    }

    public static function isExpired(array $lecture): bool
    {
        return !empty($lecture['expires_at']) && $lecture['expires_at'] < date('Y-m-d');
    }

    public static function hasAck(int $userId, string $refType, int $refId): bool
    {
        return (bool) Database::scalar(
            "SELECT COUNT(*) FROM acknowledgments WHERE user_id=? AND ref_type=? AND ref_id=?",
            [$userId, $refType, $refId]);
    }

    public static function acknowledge(int $userId, string $refType, int $refId, string $note = ''): void
    {
        if (self::hasAck($userId, $refType, $refId)) { return; }
        Database::run("INSERT INTO acknowledgments (user_id,ref_type,ref_id,note) VALUES (?,?,?,?)",
            [$userId, $refType, $refId, $note]);
    }
}
