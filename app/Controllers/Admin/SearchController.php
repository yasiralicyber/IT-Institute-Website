<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class SearchController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $q = trim((string) input('q', ''));
        $like = '%' . $q . '%';
        $results = ['students' => [], 'courses' => [], 'batches' => [], 'staff' => [], 'admissions' => []];
        if (mb_strlen($q) >= 2) {
            $results['students'] = Database::all("SELECT id,name,email FROM users WHERE role='student' AND (name LIKE ? OR email LIKE ? OR reg_no LIKE ?) LIMIT 10", [$like, $like, $like]);
            $results['courses'] = Database::all("SELECT id,title,slug FROM courses WHERE title LIKE ? LIMIT 10", [$like]);
            $results['batches'] = Database::all("SELECT id,name FROM batches WHERE name LIKE ? LIMIT 10", [$like]);
            $results['staff'] = Database::all("SELECT id,name,role FROM staff WHERE name LIKE ? OR role LIKE ? LIMIT 10", [$like, $like]);
            $results['admissions'] = Database::all("SELECT id,name,programs FROM admissions WHERE name LIKE ? LIMIT 10", [$like]);
        }
        $this->view('admin/search', [
            'title' => 'Search', 'heading' => 'Search Results', 'q' => $q, 'results' => $results,
        ], 'admin/layouts/admin');
    }
}
