<?php
namespace App\Models;

use App\Core\Database;

/**
 * XP, levels, badges and leaderboard - all COMPUTED from existing activity
 * (no extra columns to keep in sync). Award rules live here.
 */
class Gamification
{
    private const XP_LECTURE = 10;
    private const XP_QUIZ    = 50;
    private const XP_CERT    = 100;
    private const XP_PROJECT = 75;
    private const PER_LEVEL  = 500;

    public static function stats(int $userId): array
    {
        $lectures = (int) Database::scalar("SELECT COUNT(*) FROM lecture_progress WHERE user_id=?", [$userId]);
        $quizzes  = (int) Database::scalar("SELECT COUNT(DISTINCT quiz_id) FROM quiz_attempts WHERE user_id=? AND passed=1", [$userId]);
        $certs    = (int) Database::scalar("SELECT COUNT(*) FROM certificates WHERE user_id=?", [$userId]);
        $projects = (int) Database::scalar("SELECT COUNT(*) FROM projects WHERE user_id=? AND status='approved'", [$userId]);

        $xp = $lectures * self::XP_LECTURE + $quizzes * self::XP_QUIZ + $certs * self::XP_CERT + $projects * self::XP_PROJECT;
        $level = intdiv($xp, self::PER_LEVEL) + 1;
        $into  = $xp % self::PER_LEVEL;

        return [
            'xp' => $xp, 'level' => $level,
            'next_level_xp' => self::PER_LEVEL, 'into_level' => $into,
            'progress' => (int) round($into / self::PER_LEVEL * 100),
            'counts' => compact('lectures', 'quizzes', 'certs', 'projects'),
        ];
    }

    public static function badges(int $userId): array
    {
        $s = self::stats($userId)['counts'];
        $bestScore = (int) Database::scalar("SELECT MAX(score) FROM quiz_attempts WHERE user_id=?", [$userId]);
        $coursesDone = 0;
        foreach (Database::all("SELECT id FROM courses") as $c) {
            if (Progress::courseCompleted($userId, (int) $c['id'])) { $coursesDone++; }
        }
        $att = (int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id=?", [$userId]);
        $attPct = $att ? (int) round((int) Database::scalar("SELECT COUNT(*) FROM attendance WHERE user_id=? AND status IN ('present','late')", [$userId]) / $att * 100) : 0;

        $defs = [
            ['first',     'First Steps',     '', $s['lectures'] >= 1,            'Complete your first lesson'],
            ['ten',       'Getting Serious', '', $s['lectures'] >= 10,           'Complete 10 lessons'],
            ['quiz',      'Quiz Master',     '', $s['quizzes'] >= 5,             'Pass 5 chapter tests'],
            ['perfect',   'Perfect Score',   '', $bestScore >= 100,             'Score 100% on a test'],
            ['certified', 'Certified',       '', $s['certs'] >= 1,              'Earn a certificate'],
            ['builder',   'Project Builder', '', $s['projects'] >= 1,           'Get a project approved'],
            ['champion',  'Course Champion', '', $coursesDone >= 1,             'Complete a whole course'],
            ['present',   'Always Present',  '', $att >= 5 && $attPct >= 90,    '90%+ attendance'],
        ];
        return array_map(fn($d) => ['key' => $d[0], 'label' => $d[1], 'icon' => $d[2], 'earned' => (bool) $d[3], 'hint' => $d[4]], $defs);
    }

    public static function leaderboard(int $limit = 20): array
    {
        $rows = [];
        foreach (Database::all("SELECT id,name FROM users WHERE role='student' AND status='active'") as $u) {
            $st = self::stats((int) $u['id']);
            if ($st['xp'] > 0) { $rows[] = ['id' => (int) $u['id'], 'name' => $u['name'], 'xp' => $st['xp'], 'level' => $st['level']]; }
        }
        usort($rows, fn($a, $b) => $b['xp'] <=> $a['xp']);
        return array_slice($rows, 0, $limit);
    }

    /** First name + last initial, for privacy on leaderboards. */
    public static function publicName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        return $parts[0] . (isset($parts[1]) ? ' ' . mb_substr($parts[1], 0, 1) . '.' : '');
    }
}
