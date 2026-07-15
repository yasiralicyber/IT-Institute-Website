<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Models\Project;

class ProjectController extends Controller
{
    /* ---------- Student ---------- */

    public function mine(): void
    {
        $user = Auth::requireStudent();
        $this->view('student/projects', [
            'title' => 'My Projects - ' . config('app.name'),
            'user' => $user, 'projects' => Project::forUser((int) $user['id']), 'types' => Project::TYPES,
        ], 'layouts/dash');
    }

    public function store(): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/my/projects'); }
        $title = trim((string) input('title', ''));
        if (mb_strlen($title) < 3) { flash('error', 'Please give your project a title.'); redirect('/my/projects'); }
        $image = store_upload('image', 'projects', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        $file  = store_upload('file', 'projects', ['zip', 'pdf', 'png', 'jpg', 'jpeg', 'txt'], 10_485_760);
        Database::run(
            "INSERT INTO projects (user_id,title,type,description,link,image,file,status) VALUES (?,?,?,?,?,?,?,'pending')",
            [$user['id'], $title, input('type'), input('description'), trim((string) input('link', '')), $image, $file]);
        flash('success', 'Project submitted! It will appear on your portfolio once an instructor approves it.');
        redirect('/my/projects');
    }

    public function destroy(array $params): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/my/projects'); }
        Database::run("DELETE FROM projects WHERE id = ? AND user_id = ?", [(int) ($params['id'] ?? 0), $user['id']]);
        flash('success', 'Project removed.');
        redirect('/my/projects');
    }

    /* ---------- Public ---------- */

    public function showcase(): void
    {
        $this->view('public/showcase', [
            'title' => 'Student Projects - ' . config('app.name'),
            'projects' => Project::showcase(40),
        ]);
    }

    public function portfolio(array $params): void
    {
        $student = Database::first("SELECT id,name,reg_no FROM users WHERE id = ? AND role='student'", [(int) ($params['id'] ?? 0)]);
        if (!$student) { http_response_code(404); echo View::render('errors/404', ['title' => 'Not found']); return; }
        $program = Database::scalar(
            "SELECT c.title FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']])
            ?: Database::scalar("SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$student['id']]);
        $this->view('public/portfolio', [
            'title' => $student['name'] . ' - Portfolio',
            'student' => $student, 'program' => $program,
            'projects' => Project::approvedForUser((int) $student['id']),
            'certs' => Database::all("SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC", [$student['id']]),
        ]);
    }

    public function image(array $params): void
    {
        $p = Project::find((int) ($params['id'] ?? 0));
        if (!$p || !$p['image'] || !Project::viewable($p, Auth::id())) { http_response_code(404); exit; }
        self::stream(BASE_PATH . '/storage/uploads/' . $p['image']);
    }

    public function file(array $params): void
    {
        $p = Project::find((int) ($params['id'] ?? 0));
        if (!$p || !$p['file'] || !Project::viewable($p, Auth::id())) { http_response_code(404); exit; }
        $path = BASE_PATH . '/storage/uploads/' . $p['file'];
        if (!is_file($path)) { http_response_code(404); exit; }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($p['file']) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path); exit;
    }

    private static function stream(string $path): void
    {
        if (!is_file($path)) { http_response_code(404); exit; }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path); exit;
    }
}
