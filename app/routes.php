<?php
/**
 * Public site routes. $router is provided by public/index.php.
 */

use App\Controllers\HomeController;
use App\Controllers\CourseController;
use App\Controllers\PageController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentSearchController;
use App\Controllers\EnrollController;
use App\Controllers\LearnController;
use App\Controllers\QuizController;
use App\Controllers\CertificateController;
use App\Controllers\CommunityController;
use App\Controllers\ReviewController;
use App\Controllers\CheckinController;
use App\Controllers\GuardianController;
use App\Controllers\ProjectController;
use App\Controllers\AchievementController;
use App\Controllers\EventController;

/** @var \App\Core\Router $router */

// ---- Public marketing ----
$router->get('/',                 [HomeController::class, 'index']);
$router->get('/courses',          [CourseController::class, 'index']);
$router->get('/courses/{slug}',   [CourseController::class, 'show']);

$router->get('/about',            [PageController::class, 'about']);
$router->get('/faculty',          [PageController::class, 'faculty']);
$router->get('/staff-photo/{id}', [PageController::class, 'staffPhoto']);
$router->get('/facility-image/{id}', [PageController::class, 'facilityImage']);
$router->get('/site-logo',        [PageController::class, 'siteLogo']);
$router->get('/activities',       [PageController::class, 'activities']);
$router->get('/campus',           [PageController::class, 'campusRedirect']);
$router->get('/activity-image/{id}', [PageController::class, 'activityImage']);
$router->get('/awards',           [PageController::class, 'awards']);
$router->get('/award-image/{id}', [PageController::class, 'awardImage']);
$router->get('/hero-image/{id}',  [PageController::class, 'heroImage']);
$router->get('/course-thumbnail/{id}', [CourseController::class, 'thumbnail']);
$router->get('/instructor',       [PageController::class, 'instructor']);
$router->get('/contact',          [PageController::class, 'contact']);
$router->get('/timetable',        [PageController::class, 'timetable']);
$router->get('/timetable-image/{id}', [PageController::class, 'timetableImage']);
$router->get('/verify',           [PageController::class, 'verify']);
$router->get('/admission',        [PageController::class, 'admission']);
$router->get('/playground',       [PageController::class, 'playground']);
$router->get('/transparency',     [PageController::class, 'transparency']);
$router->get('/hall',             [AchievementController::class, 'hall']);
$router->get('/achievements',     [AchievementController::class, 'mine']);
$router->get('/events',           [EventController::class, 'index']);
$router->get('/event-image/{id}', [EventController::class, 'image']);
$router->get('/board',            [EventController::class, 'board']);
$router->get('/labs',             [PageController::class, 'labs']);
$router->get('/labs/{tool}',      [PageController::class, 'lab']);
$router->post('/admission',       [PageController::class, 'storeAdmission']);

// ---- Auth ----
$router->get('/login',            [AuthController::class, 'showLogin']);
$router->post('/login',           [AuthController::class, 'login']);
$router->get('/register',         [AuthController::class, 'showRegister']);
$router->post('/register',        [AuthController::class, 'register']);
$router->get('/logout',           [AuthController::class, 'logout']);
$router->get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);

// ---- Student dashboard ----
$router->get('/dashboard',        [DashboardController::class, 'index']);
$router->get('/my-id-card.pdf',   [DashboardController::class, 'idCardPdf']);
$router->get('/search',           [StudentSearchController::class, 'index']);
$router->get('/my-courses',       [DashboardController::class, 'courses']);
$router->get('/my-results',        [DashboardController::class, 'results']);
$router->post('/appeals',          [DashboardController::class, 'fileAppeal']);
$router->get('/devices',          [DashboardController::class, 'devices']);
$router->post('/devices/remove',  [DashboardController::class, 'removeDevice']);

// ---- Enrollment & payment (receipt upload) ----
$router->get('/enroll/{slug}',    [EnrollController::class, 'show']);
$router->post('/enroll/{slug}',   [EnrollController::class, 'submit']);

// ---- Learning (secure player) ----
$router->get('/learn/preview/{id}', [LearnController::class, 'preview']);
$router->get('/learn/{slug}',       [LearnController::class, 'course']);
$router->get('/learn/{slug}/roadmap', [LearnController::class, 'roadmap']);
$router->get('/learn/{slug}/test/{chapterId}',  [QuizController::class, 'show']);
$router->post('/learn/{slug}/test/{chapterId}', [QuizController::class, 'submit']);
$router->post('/learn/{slug}/test/{chapterId}/violation', [QuizController::class, 'violation']);
$router->post('/learn/{slug}/{lectureId}/complete', [LearnController::class, 'complete']);
$router->post('/learn/{slug}/{lectureId}/ack', [LearnController::class, 'acknowledge']);
$router->post('/learn/{slug}/{lectureId}/note', [LearnController::class, 'saveNote']);
$router->post('/learn/{slug}/{lectureId}/bookmark', [LearnController::class, 'addBookmark']);
$router->post('/learn/{slug}/{lectureId}/bookmark/{id}/delete', [LearnController::class, 'deleteBookmark']);
$router->get('/learn/{slug}/{lectureId}/notes.txt', [LearnController::class, 'notesTxt']);
$router->get('/learn/{slug}/{lectureId}', [LearnController::class, 'lecture']);
// Protected video stream for self-hosted (uploaded) lectures - access-checked, no download header.
$router->get('/media/{lectureId}', [LearnController::class, 'stream']);
$router->get('/lecture-resource/{lectureId}', [LearnController::class, 'resource']);

// ---- Community (Q&A) ----
$router->get('/community',             [CommunityController::class, 'index']);
$router->post('/community',            [CommunityController::class, 'store']);
$router->get('/community/{id}',        [CommunityController::class, 'show']);
$router->post('/community/{id}/reply', [CommunityController::class, 'reply']);

// ---- Reviews ----
$router->post('/courses/{slug}/review', [ReviewController::class, 'store']);

// ---- Student projects & public portfolios ----
$router->get('/my/projects',          [ProjectController::class, 'mine']);
$router->post('/my/projects',         [ProjectController::class, 'store']);
$router->post('/my/projects/{id}/delete', [ProjectController::class, 'destroy']);
$router->get('/projects',             [ProjectController::class, 'showcase']);
$router->get('/portfolio/{id}',       [ProjectController::class, 'portfolio']);
$router->get('/project-image/{id}',   [ProjectController::class, 'image']);
$router->get('/project-file/{id}',    [ProjectController::class, 'file']);

// ---- Attendance QR check-in (student) ----
$router->get('/checkin/{token}', [CheckinController::class, 'checkin']);

// ---- Guardian / Parent portal ----
$router->get('/guardian',           [GuardianController::class, 'showLogin']);
$router->post('/guardian/login',    [GuardianController::class, 'login']);
$router->get('/guardian/dashboard', [GuardianController::class, 'dashboard']);
$router->get('/guardian/logout',    [GuardianController::class, 'logout']);

// ---- Student ID verification (QR) ----
$router->get('/verify-id/{token}',  [PageController::class, 'verifyId']);

// ---- Public result check (by roll / registration number) ----
$router->get('/result', [PageController::class, 'result']);

// ---- Certificates ----
$router->get('/certificate/{credential}/pdf', [CertificateController::class, 'download']);
$router->get('/certificate/{credential}', [CertificateController::class, 'show']);

// ---- Honeytoken decoy (any hit is logged + alerts admins) ----
$router->get('/internal/export/{token}', [\App\Controllers\HoneytokenController::class, 'hit']);
