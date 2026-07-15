<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class CommunityController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $threads = Database::all(
            "SELECT t.*, u.name AS author, c.title AS course_title,
                    (SELECT COUNT(*) FROM community_replies r WHERE r.thread_id = t.id) AS replies,
                    (SELECT COUNT(*) FROM community_replies r WHERE r.thread_id = t.id AND r.is_admin = 1) AS admin_replies
             FROM community_threads t
             JOIN users u ON u.id = t.user_id
             LEFT JOIN courses c ON c.id = t.course_id
             ORDER BY CASE WHEN (SELECT COUNT(*) FROM community_replies r WHERE r.thread_id = t.id AND r.is_admin = 1) = 0 THEN 0 ELSE 1 END, t.created_at DESC");
        $this->view('admin/community', [
            'title' => 'Community', 'heading' => 'Community Q&A', 'threads' => $threads,
        ], 'admin/layouts/admin');
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();
        $thread = Database::first(
            "SELECT t.*, u.name AS author, c.title AS course_title
             FROM community_threads t JOIN users u ON u.id = t.user_id
             LEFT JOIN courses c ON c.id = t.course_id WHERE t.id = ?", [(int) ($params['id'] ?? 0)]);
        if (!$thread) { redirect('/community'); }
        $replies = Database::all(
            "SELECT r.*, u.name AS author, u.role FROM community_replies r
             JOIN users u ON u.id = r.user_id WHERE r.thread_id = ? ORDER BY r.created_at ASC", [$thread['id']]);
        $this->view('admin/community-thread', [
            'title' => $thread['title'], 'heading' => 'Q&A Thread', 'thread' => $thread, 'replies' => $replies,
        ], 'admin/layouts/admin');
    }

    public function reply(array $params): void
    {
        $admin = Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/community'); }
        $threadId = (int) ($params['id'] ?? 0);
        $body = trim((string) input('body', ''));
        if ($body !== '' && Database::scalar("SELECT COUNT(*) FROM community_threads WHERE id = ?", [$threadId])) {
            Database::run("INSERT INTO community_replies (thread_id,user_id,is_admin,body) VALUES (?,?,1,?)",
                [$threadId, $admin['id'], $body]);
            // Notify the question's author.
            $authorId = (int) Database::scalar("SELECT user_id FROM community_threads WHERE id = ?", [$threadId]);
            Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
                [$authorId, 'Instructor replied', 'You got a reply to your community question.']);
        }
        redirect('/community/' . $threadId);
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/community'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("DELETE FROM community_replies WHERE thread_id = ?", [$id]);
        Database::run("DELETE FROM community_threads WHERE id = ?", [$id]);
        flash('success', 'Thread deleted.');
        redirect('/community');
    }
}
