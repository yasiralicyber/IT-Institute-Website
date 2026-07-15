<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Device;
use App\Models\Course;

class AuthController extends Controller
{
    /* ---------------- Login ---------------- */

    public function showLogin(): void
    {
        if (Auth::check()) { redirect('/dashboard'); }
        $this->view('auth/login', [
            'title'  => 'Log in - ' . config('app.name'),
            'intent' => input('intent'),
            'course' => input('course'),
        ], 'layouts/auth');
    }

    public function login(): void
    {
        if (!csrf_verify(input('_csrf'))) {
            flash('error', 'Your session expired. Please try again.');
            redirect('/login');
        }

        $emailIn = (string) input('email', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $fpIn = substr((string) input('fp', ''), 0, 128) ?: hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

        $user = User::findByEmail($emailIn);
        if (!$user || !password_verify((string) input('password', ''), $user['password'])) {
            \App\Models\Security::logLogin($user['id'] ?? null, $emailIn, $ip, $fpIn, 40, ['bad credentials'], 'failed');
            flash('error', 'Incorrect email or password.');
            redirect('/login');
        }

        if ($user['status'] === 'suspended') {
            \App\Models\Security::logLogin((int) $user['id'], $emailIn, $ip, $fpIn, 100, ['suspended account'], 'blocked');
            flash('error', 'Your account is suspended. Please contact the institute to restore access.');
            redirect('/login');
        }

        // Risk-based login: score signals, log, alert admins on high risk.
        $score = \App\Models\Security::scoreLogin($user, $ip, $fpIn);
        $highRisk = $score['risk'] >= \App\Models\Security::highRiskThreshold();
        \App\Models\Security::logLogin((int) $user['id'], $emailIn, $ip, $fpIn, $score['risk'], $score['reasons'], $highRisk ? 'flagged' : 'allowed');
        if ($highRisk) {
            \App\Models\Security::alertAdmins('Risky login detected',
                $user['name'] . ' signed in with elevated risk (' . $score['risk'] . '): ' . implode(', ', $score['reasons']) . ' from ' . $ip . '.');
        }
        Database::run("UPDATE users SET last_login_ip=? WHERE id=?", [$ip, $user['id']]);

        // Admins belong on the admin panel.
        if ($user['role'] === 'admin') {
            Auth::login($user);
            redirect('/dashboard');
        }

        // Device-lock enforcement.
        $fingerprint = $fpIn;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $result = Device::authorise((int) $user['id'], $fingerprint, $ua);

        if ($result['status'] !== 'ok') {
            flash('error', $result['message']);
            redirect('/login');
        }

        Auth::login($user, $result['device_id']);

        // Post-login redirect: straight to a course if that was the intent.
        $course = input('course');
        if (input('intent') === 'enroll' && $course && ($c = Course::findBySlug((string) $course))) {
            redirect('/enroll/' . $c['slug']);
        }
        redirect('/dashboard');
    }

    /* ---------------- Register ---------------- */

    public function showRegister(): void
    {
        if (Auth::check()) { redirect('/dashboard'); }
        $this->view('auth/register', [
            'title'  => 'Create account - ' . config('app.name'),
            'intent' => input('intent'),
            'course' => input('course'),
        ], 'layouts/auth');
    }

    public function register(): void
    {
        if (!csrf_verify(input('_csrf'))) {
            flash('error', 'Your session expired. Please try again.');
            redirect('/register');
        }

        $name  = trim((string) input('name', ''));
        $email = strtolower(trim((string) input('email', '')));
        $phone = trim((string) input('phone', ''));
        $pass  = (string) input('password', '');

        $errors = [];
        if (mb_strlen($name) < 3)             { $errors[] = 'Please enter your full name.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email.'; }
        if (strlen($pass) < 8)                { $errors[] = 'Password must be at least 8 characters.'; }
        if (User::findByEmail($email))        { $errors[] = 'An account with this email already exists.'; }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/register');
        }

        $token = bin2hex(random_bytes(20));
        $id = User::create([
            'name' => $name, 'email' => $email, 'phone' => $phone,
            'password' => $pass, 'verify_token' => $token,
        ]);
        $user = User::find($id);
        \App\Models\Workflow::fire('student_registered', ['user_id' => $id]);

        // TODO(production): email the verification link abs_url('/verify-email/'.$token).
        // Register the signing-up device as the first device of its type.
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $fingerprint = substr((string) input('fp', ''), 0, 128) ?: hash('sha256', $ua);
        $result = Device::authorise($id, $fingerprint, $ua);
        Auth::login($user, $result['device_id'] ?? null);

        flash('success', 'Welcome to IT Training Institute! Your account is ready.');
        $course = input('course');
        if (input('intent') === 'enroll' && $course && ($c = Course::findBySlug((string) $course))) {
            redirect('/enroll/' . $c['slug']);
        }
        redirect('/dashboard');
    }

    /* ---------------- Logout / verify ---------------- */

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect('/');
    }

    public function verifyEmail(array $params): void
    {
        $ok = User::verifyEmail((string) ($params['token'] ?? ''));
        flash($ok ? 'success' : 'error',
            $ok ? 'Your email has been verified. Thank you!' : 'Invalid or expired verification link.');
        redirect(Auth::check() ? '/dashboard' : '/login');
    }
}
