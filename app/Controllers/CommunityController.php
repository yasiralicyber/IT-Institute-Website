<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class CommunityController extends Controller
{
    public function index(): void
    {
        $user = Auth::requireStudent();
        $threads = Database::all(
            "SELECT t.*, u.name AS author, c.title AS course_title,
                    (SELECT COUNT(*) FROM community_replies r WHERE r.thread_id = t.id) AS replies
             FROM community_threads t
             JOIN users u ON u.id = t.user_id
             LEFT JOIN courses c ON c.id = t.course_id
             ORDER BY t.created_at DESC LIMIT 100"
        );
        $courses = Database::all("SELECT id,title FROM courses WHERE is_published = 1 ORDER BY sort");
        $this->view('student/community', [
            'title'   => 'Community - ' . config('app.name'),
            'user'    => $user,
            'threads' => $threads,
            'courses' => $courses,
        ], 'layouts/dash');
    }

    public function store(): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/community'); }

        $title = trim((string) input('title', ''));
        $body  = trim((string) input('body', ''));
        if (mb_strlen($title) < 5 || mb_strlen($body) < 5) {
            flash('error', 'Please write a clear question title and details.');
            redirect('/community');
        }
        $courseId = (int) input('course_id') ?: null;
        $id = Database::run(
            "INSERT INTO community_threads (course_id,user_id,title,body) VALUES (?,?,?,?)",
            [$courseId, $user['id'], $title, $body]
        );
        redirect('/community/' . $id);
    }

    public function show(array $params): void
    {
        $user = Auth::requireStudent();
        $thread = Database::first(
            "SELECT t.*, u.name AS author, c.title AS course_title
             FROM community_threads t JOIN users u ON u.id = t.user_id
             LEFT JOIN courses c ON c.id = t.course_id WHERE t.id = ?", [(int) ($params['id'] ?? 0)]);
        if (!$thread) { redirect('/community'); }

        $replies = Database::all(
            "SELECT r.*, u.name AS author, u.role FROM community_replies r
             JOIN users u ON u.id = r.user_id WHERE r.thread_id = ? ORDER BY r.created_at ASC",
            [$thread['id']]);

        $this->view('student/community-thread', [
            'title'   => $thread['title'],
            'user'    => $user,
            'thread'  => $thread,
            'replies' => $replies,
        ], 'layouts/dash');
    }

    public function reply(array $params): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/community'); }
        $threadId = (int) ($params['id'] ?? 0);
        $body = trim((string) input('body', ''));
        if (mb_strlen($body) >= 2 && Database::scalar("SELECT COUNT(*) FROM community_threads WHERE id = ?", [$threadId])) {
            Database::run(
                "INSERT INTO community_replies (thread_id,user_id,is_admin,body) VALUES (?,?,?,?)",
                [$threadId, $user['id'], $user['role'] === 'admin' ? 1 : 0, $body]
            );
        }
        redirect('/community/' . $threadId);
    }
}
