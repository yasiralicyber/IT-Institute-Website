<?php
/**
 * Front controller for the PUBLIC site (itti.com.pk).
 * All non-asset requests are routed here via .htaccess (Apache) or
 * the dev router (php -S).
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;

$router = new Router();
require BASE_PATH . '/app/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
