<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class SettingController extends Controller
{
    private array $keys = [
        'hero_title', 'hero_subtitle',
        'pay_bank', 'pay_title', 'pay_number', 'pay_note',
        'inst_phone', 'inst_whatsapp', 'inst_email', 'inst_facebook', 'inst_youtube', 'inst_map',
        'policy_refund', 'policy_certificate', 'policy_support', 'policy_equipment', 'policy_delivery',
    ];

    public function index(): void
    {
        Auth::requireAdmin();
        $values = [];
        foreach (Database::all("SELECT key_name, value FROM settings") as $r) {
            $values[$r['key_name']] = $r['value'];
        }
        // Fall back to current config for unset payment/contact fields.
        $defaults = [
            'pay_bank' => config('pay.bank'), 'pay_title' => config('pay.title'),
            'pay_number' => config('pay.number'), 'pay_note' => config('pay.note'),
            'inst_phone' => config('institute.phone'), 'inst_whatsapp' => config('institute.whatsapp'),
            'inst_email' => config('institute.email'), 'inst_facebook' => config('institute.facebook'),
            'inst_youtube' => config('institute.youtube'), 'inst_map' => config('institute.map'),
        ];
        foreach ($defaults as $k => $v) {
            if (empty($values[$k])) { $values[$k] = $v; }
        }

        $this->view('admin/settings', [
            'title' => 'Settings', 'heading' => 'Site Settings', 'v' => $values,
        ], 'admin/layouts/admin');
    }

    public function save(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/settings'); }
        foreach ($this->keys as $k) {
            $this->put($k, trim((string) input($k, '')));
        }
        audit('settings', 'settings', null, 'Updated site settings');
        flash('success', 'Settings saved. Changes are live on the website.');
        redirect('/settings');
    }

    private function put(string $key, string $value): void
    {
        $exists = Database::scalar("SELECT COUNT(*) FROM settings WHERE key_name = ?", [$key]);
        if ($exists) {
            Database::run("UPDATE settings SET value = ? WHERE key_name = ?", [$value, $key]);
        } else {
            Database::run("INSERT INTO settings (key_name, value) VALUES (?, ?)", [$key, $value]);
        }
    }
}
