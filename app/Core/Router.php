<?php
namespace App\Core;

/**
 * Tiny regex router. Supports GET/POST and {param} placeholders.
 * Handlers are [ControllerClass, 'method'] or a Closure.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void  { $this->add('GET', $path, $handler); }
    public function post(string $path, $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, $handler): void
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'  => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');
        if ($uri === '//') { $uri = '/'; }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        echo View::render('errors/404', ['title' => 'Page not found']);
    }

    private function call($handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            (new $class())->$method($params);
            return;
        }
        $handler($params);
    }
}
