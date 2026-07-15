<?php
/**
 * Central configuration loader.
 * Reads the .env file (simple KEY=VALUE format) and exposes config() + env().
 * No external libraries — works on any plain PHP host (Hostinger friendly).
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

/**
 * Parse the .env file once and cache it in $GLOBALS.
 */
function load_env(): array
{
    if (isset($GLOBALS['__env'])) {
        return $GLOBALS['__env'];
    }
    $env = [];
    $file = BASE_PATH . '/.env';
    if (is_file($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));
            // strip surrounding quotes
            if (strlen($val) >= 2 && ($val[0] === '"' || $val[0] === "'")) {
                $val = substr($val, 1, -1);
            }
            $env[$key] = $val;
        }
    }
    $GLOBALS['__env'] = $env;
    return $env;
}

/**
 * Get an environment value with an optional default.
 */
function env(string $key, $default = null)
{
    $env = load_env();
    if (!array_key_exists($key, $env)) {
        return $default;
    }
    $val = $env[$key];
    return match (strtolower($val)) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => $val,
    };
}

/**
 * Load admin-editable settings from the DB (cached, safe before migration).
 */
function load_settings(): array
{
    if (isset($GLOBALS['__settings'])) {
        return $GLOBALS['__settings'];
    }
    $settings = [];
    try {
        foreach (\App\Core\Database::all("SELECT key_name, value FROM settings") as $row) {
            $settings[$row['key_name']] = $row['value'];
        }
    } catch (\Throwable $e) {
        // settings table not ready (e.g. during migration) — ignore.
    }
    $GLOBALS['__settings'] = $settings;
    return $settings;
}

/** Read a single editable setting with a fallback. */
function setting(string $key, $default = null)
{
    $s = load_settings();
    return (isset($s[$key]) && $s[$key] !== '') ? $s[$key] : $default;
}

/**
 * Build (and cache) the application config array.
 */
function config(?string $key = null, $default = null)
{
    if (!isset($GLOBALS['__config'])) {
        $GLOBALS['__config'] = [
            'app' => [
                'name'  => env('APP_NAME', 'IT Training Institute and College'),
                'short' => env('APP_SHORT', 'ITTI'),
                'env'   => env('APP_ENV', 'production'),
                'debug' => (bool) env('APP_DEBUG', false),
                'url'   => rtrim((string) env('APP_URL', ''), '/'),
                'admin_url' => rtrim((string) env('ADMIN_URL', ''), '/'),
                'key'   => env('APP_KEY', 'change-me'),
            ],
            'db' => [
                'driver' => env('DB_DRIVER', 'sqlite'),
                'host'   => env('DB_HOST', 'localhost'),
                'port'   => env('DB_PORT', '3306'),
                'name'   => env('DB_NAME', 'itti_lms'),
                'user'   => env('DB_USER', 'root'),
                'pass'   => env('DB_PASS', ''),
                'sqlite' => BASE_PATH . '/' . ltrim((string) env('DB_SQLITE_PATH', 'storage/database/itti.sqlite'), '/'),
            ],
            'mail' => [
                'host' => env('MAIL_HOST'), 'port' => env('MAIL_PORT'),
                'user' => env('MAIL_USER'), 'pass' => env('MAIL_PASS'),
                'from' => env('MAIL_FROM'), 'encryption' => env('MAIL_ENCRYPTION'),
            ],
            'institute' => [
                'name'      => env('INSTITUTE_NAME', 'IT Training Institute and College, Kumber Maidan'),
                'phone'     => env('INSTITUTE_PHONE', '+92 305 8382085'),
                'whatsapp'  => env('WHATSAPP_NUMBER', '923058382085'),
                'email'     => env('INSTITUTE_EMAIL', 'info@itti.com.pk'),
                'facebook'  => env('FACEBOOK_URL'),
                'youtube'   => env('YOUTUBE_PLAYLIST'),
                'map'       => env('GOOGLE_MAP_URL'),
            ],
            'pay' => [
                'bank'    => env('PAY_BANK_NAME'),
                'title'   => env('PAY_ACCOUNT_TITLE'),
                'number'  => env('PAY_ACCOUNT_NUMBER'),
                'note'    => env('PAY_INSTRUCTIONS'),
            ],
        ];
        // Overlay admin-editable settings from the DB (safe if table missing).
        foreach (load_settings() as $k => $v) {
            if ($v === null || $v === '') { continue; }
            $map = [
                'pay_bank' => ['pay', 'bank'], 'pay_title' => ['pay', 'title'],
                'pay_number' => ['pay', 'number'], 'pay_note' => ['pay', 'note'],
                'inst_phone' => ['institute', 'phone'], 'inst_whatsapp' => ['institute', 'whatsapp'],
                'inst_email' => ['institute', 'email'], 'inst_facebook' => ['institute', 'facebook'],
                'inst_youtube' => ['institute', 'youtube'], 'inst_map' => ['institute', 'map'],
            ];
            if (isset($map[$k])) {
                $GLOBALS['__config'][$map[$k][0]][$map[$k][1]] = $v;
            }
        }
    }
    $cfg = $GLOBALS['__config'];
    if ($key === null) {
        return $cfg;
    }
    foreach (explode('.', $key) as $segment) {
        if (is_array($cfg) && array_key_exists($segment, $cfg)) {
            $cfg = $cfg[$segment];
        } else {
            return $default;
        }
    }
    return $cfg;
}
