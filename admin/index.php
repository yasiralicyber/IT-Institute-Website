<?php
/**
 * Front controller for the ADMIN panel (admin.itti.com.pk).
 * Shares the same application code and database as the public site,
 * but loads the admin route table and runs on its own docroot/subdomain.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;

// Mark this request as the admin context (used by helpers/links if needed).
define('ADMIN_CONTEXT', true);

$router = new Router();
require BASE_PATH . '/app/admin-routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
