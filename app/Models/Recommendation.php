<?php
namespace App\Models;

use App\Core\Database;

/**
 * Rule-based personalised recommendations (no AI) - configurable rules computed
 * from the student's own activity. Returns a ranked list of next-best actions.
 */
class Recommendation
{
    public static function forUser(int $userId): array
    {
        $recs = [];
        $add = function ($priority, $icon, $text, $href, $tone = 'brand') use (&$recs) {
            $recs[] = compact('priority', 'icon', 'text', 'href', 'tone');
        };

        $enrolled = Database::all(
            "SELECT c.* FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? AND e.status='active'", [$userId]);

        // Not enrolled yet.
        if (empty($enrolled)) {
            $add(10, 'rocket', 'Start learning - watch 5 free lessons in any course, no payment needed.', '/courses');
        }

        $labMap = ['ccna-200-301' => 'network', 'cctv-camera-installation' => 'cctv', 'cyber-security' => 'cyber', 'ethical-hacking' => 'cyber'];

        foreach ($enrolled as $c) {
            $cid = (int) $c['id'];
            $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE course_id=?", [$cid]);
            $done = (int) Database::scalar("SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id=lp.lecture_id WHERE lp.user_id=? AND l.course_id=?", [$userId, $cid]);
            $pct = $total ? (int) round($done / $total * 100) : 0;

            if ($pct >= 80 && $pct < 100) {
                $add(9, 'trophy', "You're {$pct}% through {$c['title']} - finish it to earn your certificate!", '/learn/' . $c['slug'], 'gold');
            } elseif ($done > 0 && $pct < 80) {
                $add(5, 'play', "Continue {$c['title']} - you're {$pct}% done.", '/learn/' . $c['slug']);
            } elseif ($done === 0) {
                $add(6, 'play', "Begin {$c['title']} - your first lessons are waiting.", '/learn/' . $c['slug']);
            }

            // Repeated test failure → revision.
            $struggle = Database::first(
                "SELECT ch.title AS chapter, ch.id, COUNT(*) AS fails FROM quiz_attempts qa
                 JOIN quizzes q ON q.id=qa.quiz_id JOIN chapters ch ON ch.id=q.chapter_id
                 WHERE qa.user_id=? AND q.course_id=? AND qa.passed=0
                 AND NOT EXISTS (SELECT 1 FROM quiz_attempts p WHERE p.user_id=qa.user_id AND p.quiz_id=qa.quiz_id AND p.passed=1)
                 GROUP BY q.id HAVING fails>=2 ORDER BY fails DESC LIMIT 1", [$userId, $cid]);
            if ($struggle) {
                $add(8, 'refresh', "Revise \"{$struggle['chapter']}\" - review the lessons, then retake the test.", '/learn/' . $c['slug'], 'amber');
            }

            // Lab recommendation.
            if (isset($labMap[$c['slug']])) {
                $add(3, 'beaker', "Practise hands-on in the interactive lab for {$c['title']}.", '/labs/' . $labMap[$c['slug']]);
            }
        }

        // High achiever.
        $best = Database::first("SELECT score, q.course_id FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.user_id=? ORDER BY qa.score DESC LIMIT 1", [$userId]);
        if ($best && (int) $best['score'] >= 90) {
            $add(4, 'star', 'Excellent test scores! Keep your streak going and aim for the leaderboard.', '/achievements', 'gold');
        }

        // Pending fee.
        if (Fee::balance($userId) > 0) {
            $add(7, 'money', 'You have an outstanding fee balance - clear it to keep full access.', '/dashboard', 'amber');
        }

        // Showcase a project.
        $lectures = (int) Database::scalar("SELECT COUNT(*) FROM lecture_progress WHERE user_id=?", [$userId]);
        $projects = (int) Database::scalar("SELECT COUNT(*) FROM projects WHERE user_id=?", [$userId]);
        if ($lectures >= 3 && $projects === 0) {
            $add(4, 'chart', 'Show off what you can build - submit your first project for your portfolio.', '/my/projects');
        }

        usort($recs, fn($a, $b) => $b['priority'] <=> $a['priority']);
        return array_slice($recs, 0, 5);
    }
}
