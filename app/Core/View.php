<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * View — Template rendering engine with layout support.
 *
 * Usage (static):
 *   View::render('home', ['title' => 'Home'], 'app');
 *
 * Usage (fluent):
 *   echo view('home', ['title' => 'Home'])->layout('app');
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class View
{
    /** @var string Base path for view files. */
    private static string $viewsPath = '';

    /** @var string Name of the view file (without .php). */
    private string $view;

    /** @var array<string, mixed> Variables to expose inside the view. */
    private array $data;

    /** @var string|null Layout name (without .php). */
    private ?string $layoutName = null;

    // ── Configuration ─────────────────────────────────────────────────────────

    /**
     * Override the default views directory path.
     */
    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/');
    }

    private static function getViewsPath(): string
    {
        if (self::$viewsPath !== '') {
            return self::$viewsPath;
        }

        return defined('BASE_PATH')
            ? BASE_PATH . '/app/Views'
            : dirname(__DIR__, 2) . '/app/Views';
    }

    // ── Constructor (private — use static factory) ────────────────────────────

    private function __construct(string $view, array $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    // ── Fluent factory ────────────────────────────────────────────────────────

    /**
     * Create a View instance for fluent chaining.
     *
     * @param string               $view View name (e.g. "home").
     * @param array<string, mixed> $data Variables for the template.
     */
    public static function make(string $view, array $data = []): self
    {
        return new self($view, $data);
    }

    /**
     * Set the layout and render immediately (returns HTML string).
     *
     * @param string $layout Layout name (e.g. "app"). Looks in views/layouts/.
     */
    public function layout(string $layout): string
    {
        $this->layoutName = $layout;
        return $this->toString();
    }

    /**
     * Add / override template variables.
     *
     * @param array<string, mixed> $data
     */
    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Render without a layout.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    // ── Static shorthand ──────────────────────────────────────────────────────

    /**
     * Render a view, optionally inside a layout, and return the HTML string.
     *
     * @param string               $view   View name (e.g. "home").
     * @param array<string, mixed> $data   Variables exposed inside the template.
     * @param string|null          $layout Layout name (e.g. "app"), or null for no layout.
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $instance = new self($view, $data);
        $instance->layoutName = $layout;
        return $instance->toString();
    }

    // ── Core rendering ────────────────────────────────────────────────────────

    private function toString(): string
    {
        // 1. Render the inner view into a buffer
        $content = $this->renderFile($this->resolveViewPath($this->view), $this->data);

        if ($this->layoutName === null) {
            return $content;
        }

        // 2. Inject the buffered content into the layout
        $layoutPath = $this->resolveLayoutPath($this->layoutName);
        $layoutData = array_merge($this->data, ['content' => $content]);

        return $this->renderFile($layoutPath, $layoutData);
    }

    /**
     * Render a single PHP template file with isolated variable scope.
     *
     * @param string               $filePath Absolute path to the .php file.
     * @param array<string, mixed> $data     Variables to extract into scope.
     */
    private function renderFile(string $filePath, array $data): string
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("View file not found: [{$filePath}].");
        }

        // Use a static closure to prevent accidental $this access from templates
        $render = static function (string $_file, array $_data): string {
            extract($_data, EXTR_SKIP);
            ob_start();

            try {
                include $_file;
                return (string) ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        };

        return $render($filePath, $data);
    }

    private function resolveViewPath(string $view): string
    {
        return self::getViewsPath() . '/' . str_replace('.', '/', $view) . '.php';
    }

    private function resolveLayoutPath(string $layout): string
    {
        return self::getViewsPath() . '/layouts/' . $layout . '.php';
    }
}

// ── Global helper function ────────────────────────────────────────────────────

if (!function_exists('view')) {
    /**
     * Create a View instance for fluent rendering.
     *
     * Example: echo view('home', ['title' => 'Home'])->layout('app');
     *
     * @param string               $view
     * @param array<string, mixed> $data
     */
    function view(string $view, array $data = []): \App\Core\View
    {
        return \App\Core\View::make($view, $data);
    }
}
