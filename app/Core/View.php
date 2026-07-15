<?php
namespace App\Core;

/**
 * Minimal view renderer. Renders a PHP template inside a layout.
 * Templates live in /resources/views and use plain PHP + the e() helper.
 */
class View
{
    /**
     * Render a template (optionally wrapped in a layout) to a string.
     *
     * Note: the incoming variables are extracted into the template scope. We use
     * underscore-prefixed locals ($__view, $__vars, …) so a view variable named
     * "data", "content", "layout", etc. can never collide with our own locals.
     */
    public static function render(string $__template, array $__vars = [], string $__layout = 'layouts/app'): string
    {
        $__view = BASE_PATH . '/resources/views/' . $__template . '.php';
        if (!is_file($__view)) {
            throw new \RuntimeException("View not found: {$__template}");
        }

        // Render the inner template to a string.
        extract($__vars, EXTR_OVERWRITE);
        ob_start();
        include $__view;
        $content = ob_get_clean();

        if ($__layout === '') {
            return $content;
        }

        $__layoutFile = BASE_PATH . '/resources/views/' . $__layout . '.php';
        if (!is_file($__layoutFile)) {
            return $content;
        }

        // Make $title etc. available to the layout, plus $content.
        ob_start();
        include $__layoutFile;
        return ob_get_clean();
    }

    public static function send(string $template, array $data = [], string $layout = 'layouts/app'): void
    {
        echo self::render($template, $data, $layout);
    }
}
