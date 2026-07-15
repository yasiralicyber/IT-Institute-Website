<?php
/**
 * Application bootstrap: defines paths, registers a PSR-4-ish autoloader
 * for the App\ namespace, loads config + helpers, sets error handling.
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/config/config.php';
require BASE_PATH . '/app/helpers.php';

// Composer autoloader (FPDI/FPDF/QR for PDF generation), if installed.
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// Simple autoloader: App\Core\Database -> app/Core/Database.php
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Error display depends on debug flag.
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    // In production, never leak a stack trace: log it and show a clean 500 page.
    set_exception_handler(function (\Throwable $e) {
        error_log('[ITTI] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) { http_response_code(500); }
        $view = BASE_PATH . '/resources/views/errors/500.php';
        if (is_file($view)) { include $view; }
        else { echo 'Something went wrong. Please try again later.'; }
    });
}

date_default_timezone_set('Asia/Karachi');
ensure_session();
