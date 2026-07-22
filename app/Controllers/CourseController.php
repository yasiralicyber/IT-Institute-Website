<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\View;
use App\Models\Course;

class CourseController extends Controller
{
    public function index(): void
    {
        $this->view('courses/index', [
            'title'   => 'All Courses - ' . config('app.name'),
            'courses' => Course::published(),
        ]);
    }

    public function show(array $params): void
    {
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) {
            http_response_code(404);
            echo View::render('errors/404', ['title' => 'Course not found']);
            return;
        }

        $courseId = (int) $course['id'];
        $this->view('courses/show', [
            'title'       => $course['title'] . ' - ' . config('app.name'),
            'course'      => $course,
            'curriculum'  => Course::curriculum($courseId),
            'outcomes'    => Course::outcomes($course),
            'reviews'     => Course::reviews($courseId),
            'rating'      => Course::ratingSummary($courseId),
            'freeCount'   => Course::freeLectureCount($courseId),
            'totalCount'  => Course::lectureCount($courseId),
        ]);
    }

    /** Stream an admin-uploaded course thumbnail (public — shown to anonymous visitors on course cards/pages). */
    public function thumbnail(array $params): void
    {
        $row = Database::first("SELECT thumbnail FROM courses WHERE id=?", [(int) ($params['id'] ?? 0)]);
        $path = $row && $row['thumbnail'] ? BASE_PATH . '/storage/uploads/' . $row['thumbnail'] : '';
        if (!$path || !is_file($path)) { http_response_code(404); exit; }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
    }
}
