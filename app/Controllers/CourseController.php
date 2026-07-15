<?php
namespace App\Controllers;

use App\Core\Controller;
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
}
