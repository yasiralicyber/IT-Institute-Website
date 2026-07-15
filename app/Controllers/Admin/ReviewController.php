<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class ReviewController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT r.*, u.name AS author, c.title AS course FROM reviews r
             JOIN users u ON u.id = r.user_id JOIN courses c ON c.id = r.course_id
             ORDER BY CASE r.status WHEN 'pending' THEN 0 ELSE 1 END, r.created_at DESC");
        $this->view('admin/reviews', [
            'title' => 'Reviews', 'heading' => 'Course Reviews', 'rows' => $rows,
        ], 'admin/layouts/admin');
    }

    public function status(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/reviews'); }
        $new = input('status') === 'approved' ? 'approved' : 'hidden';
        Database::run("UPDATE reviews SET status = ? WHERE id = ?", [$new, (int) ($params['id'] ?? 0)]);
        flash('success', $new === 'approved' ? 'Review approved and now visible on the site.' : 'Review hidden.');
        redirect('/reviews');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/reviews'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = Database::first("SELECT * FROM reviews WHERE id = ?", [$id])) {
            trash_record('reviews', $row, 'Review #' . $id);
            audit('delete', 'reviews', $id, 'Deleted a review');
        }
        Database::run("DELETE FROM reviews WHERE id = ?", [$id]);
        flash('success', 'Review deleted.');
        redirect('/reviews');
    }
}
