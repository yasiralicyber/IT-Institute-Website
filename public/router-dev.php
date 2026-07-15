<?php
/**
 * Router for the PHP built-in dev server only.
 *   php -S localhost:8088 -t public public/router-dev.php
 * Serves existing static files directly, routes everything else to index.php.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the asset
}
require __DIR__ . '/index.php';
