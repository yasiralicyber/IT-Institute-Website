<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

/**
 * Concept search for students - type a phrase like "how routing works" and it
 * finds related lessons, courses and discussions by matching keywords across
 * titles and descriptions (indexed keyword search, no AI needed).
 */
class StudentSearchController extends Controller
{
    private const STOP = ['the','a','an','to','of','is','in','on','how','what','do','does','i','my','for','and','with','it'];

    public function index(): void
    {
        $user = Auth::requireStudent();
        $q = trim((string) input('q', ''));
        $tokens = array_values(array_filter(
            array_map(fn($w) => strtolower(trim($w)), preg_split('/\s+/', $q)),
            fn($w) => strlen($w) >= 2 && !in_array($w, self::STOP, true)
        ));

        $lessons = $courses = $threads = [];
        if ($tokens) {
            $lessons = $this->rank(Database::all(
                "SELECT l.id, l.title, l.description, l.is_free, c.title AS course, c.slug FROM lectures l JOIN courses c ON c.id=l.course_id WHERE c.is_published=1"),
                $tokens, ['title', 'description'], 40);
            foreach ($lessons as &$L) {
                $L['accessible'] = $L['is_free'] || User::hasAccess((int) $user['id'], (int) Database::scalar("SELECT id FROM courses WHERE slug=?", [$L['slug']]));
            }
            unset($L);
            $courses = $this->rank(Database::all("SELECT id,title,subtitle,category,slug FROM courses WHERE is_published=1"),
                $tokens, ['title', 'subtitle', 'category'], 8);
            $threads = $this->rank(Database::all("SELECT id,title,body FROM community_threads"),
                $tokens, ['title', 'body'], 8);
        }

        $this->view('student/search', [
            'title' => 'Search - ' . config('app.name'),
            'user' => $user, 'q' => $q, 'tokens' => $tokens,
            'lessons' => $lessons, 'courses' => $courses, 'threads' => $threads,
        ], 'layouts/dash');
    }

    /** Score rows by how many tokens appear in the given fields, drop zeros, sort. */
    private function rank(array $rows, array $tokens, array $fields, int $limit): array
    {
        $scored = [];
        foreach ($rows as $row) {
            $hay = '';
            foreach ($fields as $f) { $hay .= ' ' . strtolower((string) ($row[$f] ?? '')); }
            $score = 0;
            foreach ($tokens as $t) { if (str_contains($hay, $t)) { $score++; } }
            if ($score > 0) { $row['_score'] = $score; $scored[] = $row; }
        }
        usort($scored, fn($a, $b) => $b['_score'] <=> $a['_score']);
        return array_slice($scored, 0, $limit);
    }
}
