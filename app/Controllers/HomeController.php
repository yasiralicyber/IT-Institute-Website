<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Course;

class HomeController extends Controller
{
    public function index(): void
    {
        $courses = Course::published();

        // Light stats for the hero / stats band.
        $stats = [
            'courses'  => count($courses),
            'lectures' => (int) Database::scalar("SELECT COUNT(*) FROM lectures"),
            'students' => max(250, (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'") + 248),
        ];

        $reviews = Database::all(
            "SELECT r.*, u.name AS author, c.title AS course_title
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             JOIN courses c ON c.id = r.course_id
             WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 6"
        );

        $this->view('home', [
            'title'      => config('app.name') . ' - IT Courses in Kumber Maidan',
            'courses'    => $courses,
            'stats'      => $stats,
            'reviews'    => $reviews,
            'heroSlides' => Database::all("SELECT * FROM hero_slides WHERE is_published=1 ORDER BY sort, id"),
            'awards'     => Database::all("SELECT * FROM awards WHERE is_published=1 ORDER BY sort, id LIMIT 4"),
            'faculty'    => Database::all("SELECT * FROM staff WHERE is_published=1 ORDER BY sort, id LIMIT 3"),
            'galleryPhotos' => Database::all("SELECT * FROM activity_photos WHERE is_published=1 ORDER BY sort, id LIMIT 8"),
        ]);
    }
}
