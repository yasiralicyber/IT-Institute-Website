<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Gamification;
use App\Models\Project;
use App\Content;

class AchievementController extends Controller
{
    /** Student-facing achievements + leaderboard. */
    public function mine(): void
    {
        $user = Auth::requireStudent();
        $id = (int) $user['id'];
        $board = Gamification::leaderboard(20);
        $rank = null;
        foreach ($board as $i => $r) { if ($r['id'] === $id) { $rank = $i + 1; break; } }

        $this->view('student/achievements', [
            'title' => 'My Achievements - ' . config('app.name'),
            'user' => $user, 'stats' => Gamification::stats($id), 'badges' => Gamification::badges($id),
            'board' => $board, 'rank' => $rank,
        ], 'layouts/dash');
    }

    /** Public Hall of Fame. */
    public function hall(): void
    {
        $topCourses = Database::all(
            "SELECT c.title, COUNT(ct.id) AS issued FROM certificates ct
             JOIN courses c ON c.id = ct.course_id GROUP BY ct.course_id ORDER BY issued DESC LIMIT 6");
        $this->view('public/hall', [
            'title' => 'Hall of Fame - ' . config('app.name'),
            'awards' => Content::awards(),
            'projects' => array_slice(array_filter(Project::showcase(12), fn($p) => $p['featured']), 0, 6),
            'stats' => [
                'students' => max(250, (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'") + 1200),
                'certs' => (int) Database::scalar("SELECT COUNT(*) FROM certificates"),
                'projects' => (int) Database::scalar("SELECT COUNT(*) FROM projects WHERE status='approved'"),
            ],
            'topCourses' => $topCourses,
            'leaders' => Gamification::leaderboard(10),
        ]);
    }
}
