<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;

class EventController extends Controller
{
    public function index(): void
    {
        $this->view('public/events', [
            'title' => 'Events & News - ' . config('app.name'),
            'events' => Database::all("SELECT * FROM events WHERE is_published=1 ORDER BY COALESCE(event_date, created_at) DESC LIMIT 50"),
        ]);
    }

    public function image(array $params): void
    {
        $row = Database::first("SELECT image FROM events WHERE id=? AND is_published=1", [(int) ($params['id'] ?? 0)]);
        $path = $row && $row['image'] ? BASE_PATH . '/storage/uploads/' . $row['image'] : '';
        if (!$path || !is_file($path)) { http_response_code(404); exit; }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path); exit;
    }

    /** Full-screen auto-rotating noticeboard for a TV/monitor at the institute. */
    public function board(): void
    {
        $slides = [];
        foreach (Database::all("SELECT title, body, audience FROM notices WHERE is_published=1 ORDER BY created_at DESC LIMIT 8") as $n)
            $slides[] = ['kind' => 'Notice', 'title' => $n['title'], 'body' => $n['body']];
        foreach (Database::all("SELECT title, type, body, event_date FROM events WHERE is_published=1 ORDER BY COALESCE(event_date,created_at) DESC LIMIT 8") as $e)
            $slides[] = ['kind' => ucfirst($e['type']), 'title' => $e['title'], 'body' => $e['body'], 'date' => $e['event_date']];
        foreach (Database::all("SELECT title, body FROM timetable WHERE is_published=1 ORDER BY sort LIMIT 4") as $t)
            $slides[] = ['kind' => 'Timetable', 'title' => $t['title'], 'body' => $t['body']];

        echo View::render('public/board', [
            'title' => 'Noticeboard',
            'slides' => $slides,
            'stats' => [
                'students' => max(250, (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'") + 1200),
                'courses'  => (int) Database::scalar("SELECT COUNT(*) FROM courses WHERE is_published=1"),
                'certs'    => (int) Database::scalar("SELECT COUNT(*) FROM certificates"),
            ],
        ], '');
    }
}
