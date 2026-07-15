<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::role() === 'admin') { redirect('/'); }
        $this->view('admin/login', ['title' => 'Admin Login'], '');
    }

    public function login(): void
    {
        if (!csrf_verify(input('_csrf'))) {
            flash('error', 'Session expired, please try again.');
            redirect('/login');
        }
        $emailIn = (string) input('email', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $fp = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|admin');
        $user = User::findByEmail($emailIn);
        if (!$user || $user['role'] !== 'admin' || !password_verify((string) input('password', ''), $user['password'])) {
            \App\Models\Security::logLogin($user['id'] ?? null, $emailIn, $ip, $fp, 50, ['bad admin credentials'], 'failed');
            flash('error', 'Invalid administrator credentials.');
            redirect('/login');
        }
        // Risk-based login monitoring for admin accounts too.
        $score = \App\Models\Security::scoreLogin($user, $ip, $fp);
        $high = $score['risk'] >= \App\Models\Security::highRiskThreshold();
        \App\Models\Security::logLogin((int) $user['id'], $emailIn, $ip, $fp, $score['risk'], $score['reasons'], $high ? 'flagged' : 'allowed');
        if ($high) {
            \App\Models\Security::alertAdmins('Risky admin login', $user['name'] . ' admin login risk ' . $score['risk'] . ': ' . implode(', ', $score['reasons']) . ' from ' . $ip);
        }
        \App\Core\Database::run("UPDATE users SET last_login_ip=? WHERE id=?", [$ip, $user['id']]);
        Auth::login($user);
        redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Logged out.');
        redirect('/login');
    }
}
