<?php
namespace App\Core;

/**
 * Base controller with a view() convenience method.
 */
abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'layouts/app'): void
    {
        View::send($template, $data, $layout);
    }
}
