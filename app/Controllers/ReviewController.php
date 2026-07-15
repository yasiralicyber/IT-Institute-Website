<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Course;
use App\Models\User;

class ReviewController extends Controller
{
    public function store(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }

        if (!csrf_verify(input('_csrf')) || !User::hasAccess((int) $user['id'], (int) $course['id'])) {
            flash('error', 'Only enrolled students can review this course.');
            redirect('/learn/' . $course['slug']);
        }

        $rating = max(1, min(5, (int) input('rating', 5)));
        $body   = trim((string) input('body', ''));

        // One review per student per course (update if it exists).
        $existing = Database::first("SELECT id FROM reviews WHERE user_id = ? AND course_id = ?",
            [$user['id'], $course['id']]);
        if ($existing) {
            Database::run("UPDATE reviews SET rating = ?, body = ?, status = 'pending' WHERE id = ?",
                [$rating, $body, $existing['id']]);
        } else {
            Database::run("INSERT INTO reviews (user_id,course_id,rating,body,status) VALUES (?,?,?,?,'pending')",
                [$user['id'], $course['id'], $rating, $body]);
        }

        flash('success', 'Thank you! Your review was submitted and will appear after approval.');
        redirect('/learn/' . $course['slug']);
    }
}
