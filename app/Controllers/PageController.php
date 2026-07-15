<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Certificate;

class PageController extends Controller
{
    public function about(): void
    {
        $this->view('pages/about', ['title' => 'About Us - ' . config('app.name')]);
    }

    public function faculty(): void
    {
        $this->view('pages/faculty', [
            'title'   => 'Our Faculty - ' . config('app.name'),
            'faculty' => \App\Content::faculty(),
        ]);
    }

    public function campus(): void
    {
        $this->view('pages/campus', [
            'title'      => 'Campus & Facilities - ' . config('app.name'),
            'facilities' => \App\Content::facilities(),
            'gallery'    => \App\Content::gallery(),
        ]);
    }

    public function awards(): void
    {
        $this->view('pages/awards', [
            'title'  => 'Awards & Achievements - ' . config('app.name'),
            'awards' => \App\Content::awards(),
        ]);
    }

    public function playground(): void
    {
        $this->view('pages/playground', ['title' => 'Coding Playground - ' . config('app.name')]);
    }

    public function labs(): void
    {
        $this->view('pages/labs', ['title' => 'Interactive Labs - ' . config('app.name')]);
    }

    public function lab(array $params): void
    {
        $tool = preg_replace('/[^a-z]/', '', (string) ($params['tool'] ?? ''));
        $view = 'pages/lab-' . $tool;
        if (!in_array($tool, ['network', 'cctv', 'cyber'], true)) { redirect('/labs'); }
        $titles = ['network' => 'Network Topology Lab', 'cctv' => 'CCTV Coverage Planner', 'cyber' => 'Cyber Investigation'];
        $this->view($view, ['title' => $titles[$tool] . ' - ' . config('app.name')]);
    }

    public function transparency(): void
    {
        $courses = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM lectures l WHERE l.course_id=c.id) AS lectures
             FROM courses c WHERE is_published=1 ORDER BY sort");
        $this->view('pages/transparency', [
            'title' => 'Fees, Policies & Transparency - ' . config('app.name'),
            'courses' => $courses,
            'policies' => [
                'refund'      => setting('policy_refund', 'Course fees are non-refundable once access is granted and lessons are unlocked. If your payment was declined, no charge applies. For genuine concerns, contact the office within 7 days of enrolment.'),
                'certificate' => setting('policy_certificate', 'Certificates are issued automatically after you complete all lessons in a chapter (chapter certificate) or the full course (course certificate) and pass the required tests (default 70%). Every certificate carries a unique ID and QR code, verifiable on this website.'),
                'support'     => setting('policy_support', 'Support is available during institute hours via WhatsApp, phone and the in-app community. Instructors reply to community questions regularly.'),
                'equipment'   => setting('policy_equipment', 'For online learning you need a phone or computer with a stable internet connection. For hands-on courses (CCNA labs, CCTV, hardware) practical equipment is provided at the institute.'),
                'delivery'    => setting('policy_delivery', 'Courses are available online (video lessons + tests + certificate) and on-campus (physical classes with batches, attendance and instructor support). Many students combine both.'),
            ],
        ]);
    }

    public function instructor(): void
    {
        $this->view('pages/instructor', ['title' => 'Meet Your Instructor - ' . config('app.name')]);
    }

    public function contact(): void
    {
        $this->view('pages/contact', ['title' => 'Contact Us - ' . config('app.name')]);
    }

    public function timetable(): void
    {
        $rows = Database::all("SELECT * FROM timetable WHERE is_published = 1 ORDER BY sort ASC");
        $this->view('pages/timetable', [
            'title' => 'Class Timetable - ' . config('app.name'),
            'rows'  => $rows,
        ]);
    }

    /** Public student-ID verification (from the ID card QR). */
    public function verifyId(array $params): void
    {
        $student = Database::first("SELECT id,name,reg_no,status FROM users WHERE id_token = ? AND role='student'",
            [(string) ($params['token'] ?? '')]);
        $program = null;
        if ($student) {
            $program = Database::scalar(
                "SELECT c.title FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id
                 WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']])
                ?: Database::scalar("SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$student['id']]);
        }
        $this->view('pages/verify-id', [
            'title' => 'Verify Student ID - ' . config('app.name'),
            'student' => $student, 'program' => $program,
        ]);
    }

    /** Stream a published timetable image (public). */
    public function timetableImage(array $params): void
    {
        $row = Database::first("SELECT image_path FROM timetable WHERE id = ? AND is_published = 1", [(int) ($params['id'] ?? 0)]);
        $path = $row && $row['image_path'] ? BASE_PATH . '/storage/uploads/' . $row['image_path'] : '';
        if (!$path || !is_file($path)) { http_response_code(404); exit; }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function verify(): void
    {
        $id = trim((string) input('id', ''));
        $result = null;
        if ($id !== '') {
            // 1) Online course/chapter certificate.
            $cert = Certificate::findByCredential($id);
            if ($cert) {
                $result = ['valid' => true, 'kind' => 'online', 'cert' => $cert];
            } else {
                // 2) In-house / physical certificate registry.
                $manual = \App\Models\ManualCertificate::findByNumber($id);
                if ($manual) {
                    $result = [
                        'valid'   => $manual['status'] !== 'revoked',
                        'revoked' => $manual['status'] === 'revoked',
                        'kind'    => 'physical',
                        'manual'  => $manual,
                    ];
                } else {
                    $result = ['valid' => false];
                }
            }
        }
        $this->view('pages/verify', [
            'title'  => 'Verify a Certificate - ' . config('app.name'),
            'query'  => $id,
            'result' => $result,
        ]);
    }

    /** Public result checker: student enters roll/registration number + name. */
    public function result(): void
    {
        $reg   = trim((string) input('reg_no', ''));
        $name  = trim((string) input('name', ''));
        $rc    = null;   // the result-card payload (NOT named "data" — that key
                         //  collides with View::render()'s own $data via extract)
        $error = null;

        if ($reg !== '') {
            $student = Database::first(
                "SELECT id, name, father_name, reg_no, photo FROM users WHERE UPPER(reg_no) = UPPER(?) AND role = 'student'",
                [$reg]
            );
            if (!$student) {
                $error = 'No student was found with registration number "' . $reg . '". Please check and try again.';
            } elseif ($name === '') {
                $error = 'Please also enter the student name (as printed on the ID card) to view the result.';
            } elseif (stripos($student['name'], $name) === false && stripos((string) $student['father_name'], $name) === false) {
                $error = 'The name does not match our records for this registration number.';
            } else {
                $program = Database::scalar(
                    "SELECT c.title FROM batch_students bs JOIN batches b ON b.id=bs.batch_id JOIN courses c ON c.id=b.course_id
                     WHERE bs.user_id=? ORDER BY bs.id DESC LIMIT 1", [$student['id']])
                    ?: Database::scalar("SELECT c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=? LIMIT 1", [$student['id']]);
                $rc = [
                    'student' => $student,
                    'program' => $program,
                    'marks'   => \App\Models\TestMark::forStudent((int) $student['id']),
                    'certs'   => Database::all("SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC", [$student['id']]),
                ];
            }
        }

        $this->view('pages/result', [
            'title' => 'Check Result - ' . config('app.name'),
            'reg'   => $reg,
            'name'  => $name,
            'rc'    => $rc,
            'error' => $error,
        ]);
    }

    public function admission(): void
    {
        $programs = Database::all("SELECT title FROM courses WHERE is_published = 1 ORDER BY sort ASC");
        $this->view('pages/admission', [
            'title'    => 'Admission Form - ' . config('app.name'),
            'programs' => $programs,
        ]);
    }

    public function storeAdmission(): void
    {
        if (!csrf_verify(input('_csrf'))) {
            flash('error', 'Your session expired. Please submit the form again.');
            redirect('/admission');
        }

        $name = trim((string) input('name', ''));
        $father = trim((string) input('father_name', ''));
        $contact = trim((string) input('contact', ''));
        $program = trim((string) input('programs', ''));
        if (mb_strlen($name) < 3 || mb_strlen($father) < 3 || $contact === '' || $program === '') {
            flash('error', 'Please fill in your name, father\'s name, contact number and program.');
            redirect('/admission');
        }

        $photo = store_upload('photo', 'admissions', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);

        Database::run(
            "INSERT INTO admissions (name,father_name,address,contact,dob,form_b,email,programs,gender,photo,status)
             VALUES (?,?,?,?,?,?,?,?,?,?,'new')",
            [
                $name, $father,
                trim((string) input('address', '')),
                $contact,
                trim((string) input('dob', '')),
                trim((string) input('form_b', '')),
                trim((string) input('email', '')),
                $program,
                trim((string) input('gender', '')),
                $photo,
            ]
        );

        flash('success', 'Your admission application has been submitted! Our team will contact you soon. ');
        redirect('/admission');
    }
}
