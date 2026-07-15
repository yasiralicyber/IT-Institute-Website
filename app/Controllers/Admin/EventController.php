<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class EventController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT * FROM events ORDER BY COALESCE(event_date, created_at) DESC, id DESC");
        $this->view('admin/events', ['title' => 'Events & News', 'heading' => 'Events & News', 'rows' => $rows], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/events'); }
        $title = trim((string) input('title', ''));
        if ($title === '') { flash('error', 'Title is required.'); redirect('/events'); }
        $image = store_upload('image', 'events', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        Database::run("INSERT INTO events (title,type,body,event_date,image,is_published) VALUES (?,?,?,?,?,1)",
            [$title, input('type', 'event'), input('body'), input('event_date'), $image]);
        audit('create', 'events', null, 'Posted ' . input('type') . ' "' . $title . '"');
        flash('success', 'Published.');
        redirect('/events');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/events'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM events WHERE id = ?", [$id])) {
            trash_record('events', $row, ucfirst($row['type']) . ': ' . $row['title']);
            audit('delete', 'events', $id, 'Deleted "' . $row['title'] . '"');
        }
        Database::run("DELETE FROM events WHERE id = ?", [$id]);
        flash('success', 'Deleted.');
        redirect('/events');
    }
}
