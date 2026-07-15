<?php
/**
 * Admin panel routes (served from the admin/ docroot → app.ittimaidan.com).
 * $router is provided by admin/index.php.
 */

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\PurchaseController;
use App\Controllers\Admin\StudentController;
use App\Controllers\Admin\CourseController;
use App\Controllers\Admin\AdmissionController;
use App\Controllers\Admin\TimetableController;
use App\Controllers\Admin\ReviewController;
use App\Controllers\Admin\CommunityController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\ClassroomController;
use App\Controllers\Admin\StaffController;
use App\Controllers\Admin\BatchController;
use App\Controllers\Admin\AttendanceController;
use App\Controllers\Admin\FeeController;
use App\Controllers\Admin\FeePlanController;
use App\Controllers\Admin\DayBookController;
use App\Controllers\Admin\ExpenseController;
use App\Controllers\Admin\PayrollController;
use App\Controllers\Admin\ResultController;
use App\Controllers\Admin\CertificateRegistryController;
use App\Controllers\Admin\TestMarkController;
use App\Controllers\Admin\GradingSchemeController;
use App\Controllers\Admin\ExamIntegrityController;
use App\Controllers\Admin\LearningController;
use App\Controllers\Admin\SecurityController;
use App\Controllers\Admin\NoticeController;
use App\Controllers\Admin\SystemController;
use App\Controllers\Admin\SearchController;
use App\Controllers\Admin\ImportController;
use App\Controllers\Admin\ProjectController;
use App\Controllers\Admin\RiskController;
use App\Controllers\Admin\EventController;
use App\Controllers\Admin\OpsController;
use App\Controllers\Admin\WorkflowController;
use App\Controllers\Admin\AnalyticsController;

/** @var \App\Core\Router $router */

// ---- Auth ----
$router->get('/login',   [AuthController::class, 'showLogin']);
$router->post('/login',  [AuthController::class, 'login']);
$router->get('/logout',  [AuthController::class, 'logout']);

// ---- Dashboard ----
$router->get('/',        [DashboardController::class, 'index']);
$router->get('/search',  [SearchController::class, 'index']);
$router->get('/ops',     [OpsController::class, 'index']);
$router->get('/analytics', [AnalyticsController::class, 'index']);

// ---- Purchase requests (receipt approvals) ----
$router->get('/purchases',              [PurchaseController::class, 'index']);
$router->get('/receipt/{id}',           [PurchaseController::class, 'receipt']);
$router->post('/purchases/{id}/approve',[PurchaseController::class, 'approve']);
$router->post('/purchases/{id}/decline',[PurchaseController::class, 'decline']);

// ---- Students ----
$router->get('/students',                  [StudentController::class, 'index']);
$router->get('/students/id-cards',         [StudentController::class, 'idCards']);
$router->get('/students/id-cards.pdf',     [StudentController::class, 'idCardsPdf']);
$router->get('/students/{id}',             [StudentController::class, 'show']);
$router->get('/students/{id}/id-card.pdf', [StudentController::class, 'idCardPdf']);
$router->post('/students/{id}/status',     [StudentController::class, 'status']);
$router->post('/students/{id}/reset-devices', [StudentController::class, 'resetDevices']);
$router->post('/students/{id}/guardian',   [StudentController::class, 'saveGuardian']);
$router->get('/students/{id}/id-card',     [StudentController::class, 'idCard']);
$router->get('/students/{id}/timeline',    [StudentController::class, 'timeline']);
$router->post('/students/{id}/notes',      [StudentController::class, 'addNote']);

// ---- Student projects (moderation) + at-risk ----
$router->get('/projects',                  [ProjectController::class, 'index']);
$router->post('/projects/{id}/status',     [ProjectController::class, 'status']);
$router->post('/projects/{id}/feature',    [ProjectController::class, 'feature']);
$router->post('/projects/{id}/delete',     [ProjectController::class, 'destroy']);
$router->get('/risk',                      [RiskController::class, 'index']);

// ---- Courses / chapters / lectures / quizzes ----
$router->get('/courses',                 [CourseController::class, 'index']);
$router->get('/courses/create',          [CourseController::class, 'create']);
$router->post('/courses',                [CourseController::class, 'store']);
$router->get('/courses/{id}/edit',       [CourseController::class, 'edit']);
$router->post('/courses/{id}',           [CourseController::class, 'update']);
$router->post('/courses/{id}/delete',    [CourseController::class, 'destroy']);
$router->post('/courses/{id}/chapters',  [CourseController::class, 'storeChapter']);
$router->post('/chapters/{id}/delete',   [CourseController::class, 'deleteChapter']);
$router->post('/chapters/{id}/lectures', [CourseController::class, 'storeLecture']);
$router->post('/lectures/{id}/update',   [CourseController::class, 'updateLecture']);
$router->post('/lectures/{id}/delete',   [CourseController::class, 'deleteLecture']);
$router->get('/lectures/{id}/interactive',  [CourseController::class, 'interactive']);
$router->post('/lectures/{id}/interactive', [CourseController::class, 'saveInteractive']);
$router->get('/chapters/{id}/quiz',      [CourseController::class, 'quiz']);
$router->post('/chapters/{id}/quiz',     [CourseController::class, 'saveQuiz']);

// ---- Admissions (with secure verification gate) ----
$router->get('/admissions',            [AdmissionController::class, 'index']);
$router->post('/admissions/verify',    [AdmissionController::class, 'verify']);
$router->get('/admissions/{id}',       [AdmissionController::class, 'show']);
$router->get('/admission-photo/{id}',  [AdmissionController::class, 'photo']);
$router->post('/admissions/{id}/status', [AdmissionController::class, 'status']);

// ---- Institute: classrooms / staff / batches ----
$router->get('/classrooms',             [ClassroomController::class, 'index']);
$router->post('/classrooms',            [ClassroomController::class, 'store']);
$router->post('/classrooms/{id}',       [ClassroomController::class, 'update']);
$router->post('/classrooms/{id}/delete',[ClassroomController::class, 'destroy']);

$router->get('/staff',                  [StaffController::class, 'index']);
$router->get('/staff/create',           [StaffController::class, 'create']);
$router->post('/staff',                 [StaffController::class, 'store']);
$router->get('/staff/{id}/edit',        [StaffController::class, 'edit']);
$router->post('/staff/{id}',            [StaffController::class, 'update']);
$router->post('/staff/{id}/delete',     [StaffController::class, 'destroy']);
$router->get('/staff-photo/{id}',       [StaffController::class, 'photo']);

$router->get('/batches',                [BatchController::class, 'index']);
$router->get('/batches/create',         [BatchController::class, 'create']);
$router->post('/batches',               [BatchController::class, 'store']);
$router->get('/batches/{id}',           [BatchController::class, 'show']);
$router->post('/batches/{id}',          [BatchController::class, 'update']);
$router->post('/batches/{id}/delete',   [BatchController::class, 'destroy']);
$router->post('/batches/{id}/enroll',   [BatchController::class, 'enroll']);
$router->post('/batches/{id}/remove',   [BatchController::class, 'removeStudent']);

// ---- Attendance ----
$router->get('/attendance',                  [AttendanceController::class, 'index']);
$router->get('/attendance-guide',            [AttendanceController::class, 'guide']);
$router->get('/attendance/{id}',             [AttendanceController::class, 'mark']);
$router->post('/attendance/{id}/save',       [AttendanceController::class, 'save']);
$router->get('/attendance/{id}/report',      [AttendanceController::class, 'report']);
$router->get('/attendance/{id}/qr',          [AttendanceController::class, 'qr']);
$router->get('/attendance/{id}/qr/rotate',   [AttendanceController::class, 'rotate']);
$router->post('/attendance/{id}/finalize',   [AttendanceController::class, 'finalize']);
$router->get('/attendance/{id}/import',      [AttendanceController::class, 'importForm']);
$router->post('/attendance/{id}/import',     [AttendanceController::class, 'import']);

// ---- Fees (institute finance) ----
$router->get('/fees',                    [FeeController::class, 'index']);
$router->post('/fees/generate-monthly',  [FeeController::class, 'generateMonthly']);
$router->get('/fees/receipt/{id}',       [FeeController::class, 'receipt']);
$router->post('/fees/invoice/{id}/delete',[FeeController::class, 'deleteInvoice']);
$router->get('/fees/{id}',               [FeeController::class, 'ledger']);
$router->post('/fees/{id}/invoice',      [FeeController::class, 'storeInvoice']);
$router->post('/fees/{id}/payment',      [FeeController::class, 'recordPayment']);
$router->post('/fees/{id}/apply-plan',   [FeeController::class, 'applyPlan']);
$router->post('/fees/{id}/restructure',  [FeeController::class, 'restructure']);

// ---- Fee rules engine (plans) ----
$router->get('/fee-plans',               [FeePlanController::class, 'index']);
$router->get('/fee-plans/new',           [FeePlanController::class, 'form']);
$router->post('/fee-plans',              [FeePlanController::class, 'save']);
$router->get('/fee-plans/{id}/edit',     [FeePlanController::class, 'form']);
$router->post('/fee-plans/{id}',         [FeePlanController::class, 'save']);
$router->post('/fee-plans/{id}/delete',  [FeePlanController::class, 'delete']);

// ---- Day book (cash register) ----
$router->get('/daybook',                 [DayBookController::class, 'index']);
$router->post('/daybook/open',           [DayBookController::class, 'open']);
$router->post('/daybook/expense',        [DayBookController::class, 'addExpense']);
$router->post('/daybook/close',          [DayBookController::class, 'close']);

// ---- Daily expenses ----
$router->get('/expenses',                [ExpenseController::class, 'index']);
$router->post('/expenses',               [ExpenseController::class, 'store']);
$router->post('/expenses/{id}/delete',   [ExpenseController::class, 'delete']);

// ---- Payroll (staff salary) ----
$router->get('/payroll',                 [PayrollController::class, 'index']);
$router->post('/payroll/{id}/salary',    [PayrollController::class, 'setSalary']);
$router->post('/payroll/{id}/pay',       [PayrollController::class, 'pay']);

// ---- Physical / in-house certificate registry ----
$router->get('/certificates',                  [CertificateRegistryController::class, 'index']);
$router->post('/certificates',                 [CertificateRegistryController::class, 'store']);
$router->get('/certificates/{id}/pdf',         [CertificateRegistryController::class, 'pdf']);
$router->post('/certificates/{id}/revoke',     [CertificateRegistryController::class, 'revoke']);
$router->post('/certificates/{id}/delete',     [CertificateRegistryController::class, 'destroy']);

// ---- Physical test marks (quick, student-visible) ----
$router->get('/test-marks',                    [TestMarkController::class, 'index']);
$router->post('/test-marks',                   [TestMarkController::class, 'store']);
$router->post('/test-marks/{id}/delete',       [TestMarkController::class, 'destroy']);

// ---- Results management ----
$router->get('/results',                       [ResultController::class, 'index']);
$router->get('/results/new',                   [ResultController::class, 'create']);
$router->post('/results',                      [ResultController::class, 'store']);
$router->get('/results/{id}',                  [ResultController::class, 'show']);
$router->post('/results/{id}/component',        [ResultController::class, 'addComponent']);
$router->post('/results/{id}/component/{cid}/delete', [ResultController::class, 'deleteComponent']);
$router->post('/results/{id}/scores',          [ResultController::class, 'saveScores']);
$router->post('/results/{id}/sync',            [ResultController::class, 'syncOnline']);
$router->post('/results/{id}/import',          [ResultController::class, 'import']);
$router->post('/results/{id}/submit',          [ResultController::class, 'submit']);
$router->post('/results/{id}/approve',         [ResultController::class, 'approve']);
$router->post('/results/{id}/reopen',          [ResultController::class, 'reopen']);
$router->get('/results/{id}/card/{uid}',       [ResultController::class, 'card']);
$router->get('/results/{id}/merit',            [ResultController::class, 'merit']);

// ---- Learning enhancements (blocks, lesson rules, revision, acks) ----
$router->get('/learning',                 [LearningController::class, 'index']);
$router->post('/learning/block',          [LearningController::class, 'saveBlock']);
$router->post('/learning/block/{id}/delete', [LearningController::class, 'deleteBlock']);
$router->post('/learning/attach',         [LearningController::class, 'attachBlock']);
$router->post('/learning/lecture-rules',  [LearningController::class, 'saveLectureRules']);
$router->post('/learning/revision',       [LearningController::class, 'saveRevision']);
$router->get('/acknowledgments',          [LearningController::class, 'acknowledgments']);

// ---- Exam integrity: appeals + violation log ----
$router->get('/appeals',                  [ExamIntegrityController::class, 'appeals']);
$router->post('/appeals/{id}/review',     [ExamIntegrityController::class, 'reviewAppeal']);
$router->get('/exam-violations',          [ExamIntegrityController::class, 'violations']);

// ---- Grading schemes ----
$router->get('/grading-schemes',          [GradingSchemeController::class, 'index']);
$router->post('/grading-schemes',         [GradingSchemeController::class, 'save']);
$router->post('/grading-schemes/{id}',    [GradingSchemeController::class, 'save']);
$router->post('/grading-schemes/{id}/delete', [GradingSchemeController::class, 'delete']);

// ---- Timetable ----
$router->get('/timetable',             [TimetableController::class, 'index']);
$router->post('/timetable',            [TimetableController::class, 'store']);
$router->post('/timetable/{id}/delete',[TimetableController::class, 'destroy']);

// ---- Reviews ----
$router->get('/reviews',               [ReviewController::class, 'index']);
$router->post('/reviews/{id}/status',  [ReviewController::class, 'status']);
$router->post('/reviews/{id}/delete',  [ReviewController::class, 'destroy']);

// ---- Community ----
$router->get('/community',             [CommunityController::class, 'index']);
$router->get('/community/{id}',        [CommunityController::class, 'show']);
$router->post('/community/{id}/reply', [CommunityController::class, 'reply']);
$router->post('/community/{id}/delete',[CommunityController::class, 'destroy']);

// ---- Events & News ----
$router->get('/events',             [EventController::class, 'index']);
$router->post('/events',            [EventController::class, 'store']);
$router->post('/events/{id}/delete',[EventController::class, 'destroy']);

// ---- Notices ----
$router->get('/notices',            [NoticeController::class, 'index']);
$router->post('/notices',           [NoticeController::class, 'store']);
$router->post('/notices/{id}/delete',[NoticeController::class, 'destroy']);

// ---- Settings ----
$router->get('/settings',              [SettingController::class, 'index']);
$router->post('/settings',             [SettingController::class, 'save']);

// ---- Bulk Import ----
$router->get('/imports',                  [ImportController::class, 'index']);
$router->post('/imports/start',           [ImportController::class, 'start']);
$router->get('/imports/history',          [ImportController::class, 'history']);
$router->get('/imports/template/{section}',[ImportController::class, 'template']);
$router->get('/imports/export/{section}', [ImportController::class, 'export']);
$router->get('/imports/{id}',             [ImportController::class, 'map']);
$router->post('/imports/{id}/map',        [ImportController::class, 'validateStep']);
$router->post('/imports/{id}/execute',    [ImportController::class, 'execute']);
$router->get('/imports/{id}/result',      [ImportController::class, 'result']);
$router->post('/imports/{id}/rollback',   [ImportController::class, 'rollback']);

// ---- Automations ----
$router->get('/automations',            [WorkflowController::class, 'index']);
$router->post('/automations',           [WorkflowController::class, 'store']);
$router->post('/automations/{id}/toggle',[WorkflowController::class, 'toggle']);
$router->post('/automations/{id}/delete',[WorkflowController::class, 'destroy']);

// ---- System: audit / recycle bin / backups ----
$router->get('/audit',                 [SystemController::class, 'audit']);
$router->get('/recycle',               [SystemController::class, 'recycle']);
$router->post('/recycle/{id}/restore', [SystemController::class, 'restore']);
$router->post('/recycle/{id}/purge',   [SystemController::class, 'purge']);
$router->get('/backups',               [SystemController::class, 'backups']);
$router->post('/backups',              [SystemController::class, 'createBackup']);
$router->get('/backups/{id}/download', [SystemController::class, 'downloadBackup']);
$router->post('/backups/{id}/delete',  [SystemController::class, 'deleteBackup']);

// ---- Security & governance (task #36) ----
$router->get('/login-risk',            [SecurityController::class, 'risk']);
$router->get('/honeytokens',           [SecurityController::class, 'honeytokens']);
$router->post('/honeytokens',          [SecurityController::class, 'createHoneytoken']);
$router->post('/honeytokens/{id}/delete', [SecurityController::class, 'deleteHoneytoken']);
$router->get('/approvals',             [SecurityController::class, 'approvals']);
$router->post('/approvals/request',    [SecurityController::class, 'requestSensitive']);
$router->post('/approvals/{id}/decide',[SecurityController::class, 'decide']);
$router->get('/staff-roles',           [SecurityController::class, 'roles']);
$router->post('/staff-roles/{id}',     [SecurityController::class, 'setRole']);
