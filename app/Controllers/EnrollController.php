<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Course;
use App\Models\User;

class EnrollController extends Controller
{
    public function show(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }

        if (User::hasAccess((int) $user['id'], (int) $course['id'])) {
            redirect('/learn/' . $course['slug']);
        }

        $pending = Database::first(
            "SELECT * FROM purchase_requests WHERE user_id = ? AND course_id = ? AND status = 'pending'
             ORDER BY created_at DESC LIMIT 1", [$user['id'], $course['id']]
        );

        $this->view('student/enroll', [
            'title'   => 'Enroll - ' . $course['title'],
            'heading' => 'Enroll: ' . $course['title'],
            'user'    => $user,
            'course'  => $course,
            'pending' => $pending,
            'pay'     => config('pay'),
        ], 'layouts/dash');
    }

    public function submit(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }

        if (!csrf_verify(input('_csrf'))) {
            flash('error', 'Your session expired. Please try again.');
            redirect('/enroll/' . $course['slug']);
        }

        if (User::hasAccess((int) $user['id'], (int) $course['id'])) {
            redirect('/learn/' . $course['slug']);
        }

        $receipt = store_upload('receipt', 'receipts', ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 5_242_880);
        if (!$receipt) {
            flash('error', 'Please upload a clear payment receipt (JPG, PNG or PDF, up to 5MB).');
            redirect('/enroll/' . $course['slug']);
        }

        Database::run(
            "INSERT INTO purchase_requests (user_id,course_id,amount,reference_no,receipt_path,status)
             VALUES (?,?,?,?,?,'pending')",
            [$user['id'], $course['id'], (int) $course['price'],
             substr((string) input('reference_no', ''), 0, 80), $receipt]
        );

        // Notify the student in-app.
        Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
            [$user['id'], 'Enrollment request received',
             'We received your payment receipt for "' . $course['title'] . '". You will get access once it is approved.']);

        flash('success', 'Your receipt was submitted! We will verify your payment and unlock the course shortly.');
        redirect('/dashboard');
    }
}
