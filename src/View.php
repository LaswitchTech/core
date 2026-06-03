<?php

// Declaring namespace
namespace LaswitchTech\Core;

/**
 * View Engine
 *
 * Replaces require_once-based rendering with output-buffered composition.
 * Resolves templates and views through the same cascade as the current Route object.
 * Returns a string (for tests or controller output).
 *
 * Resolution cascade for templates:
 *   1. {root}/Template/View/{template}
 *   2. {root}/lib/themes/{theme}/Template/View/{template}
 *   3. {root}/vendor/laswitchtech/core/Template/View/{template}
 *
 * Resolution cascade for views:
 *   1. {root}/{directory}/View/{view}
 *   2. {root}/vendor/laswitchtech/core/View/{view}
 */
class View
{
    /**
     * Render a template wrapping a view
     *
     * Uses output buffering so the rendered string can be returned (for tests)
     * or echoed (for HTTP output). Existing templates require zero changes.
     *
     * @param string $template Template name (e.g., 'panel', 'website', 'fullscreen')
     * @param string|null $view View name (e.g., 'index', '404')
     * @param array $data Extra data to extract into template scope
     * @return string Rendered HTML
     */
    public function render(string $template, ?string $view = null, array $data = []): string
    {
        // Resolve paths
        $templatePath = $this->resolveTemplate($template);
        $viewPath = $view !== null ? $this->resolveView($view, $data['directory'] ?? null) : null;

        // Extract ViewGlobals into template scope (guaranteed context for every layout)
        ViewGlobals::apply();

        // Extract extra data into template scope
        if (!empty($data)) {
            extract($data);
        }

        // Output buffer the template
        ob_start();

        // Load the template (which internally require_once's the view)
        require_once $templatePath;

        $html = ob_get_clean();

        // Auto-load the view after the template (current behavior)
        // This is preserved for routes that call render() twice
        if ($viewPath !== null) {
            // View will be loaded by the template's require_once
            // Nothing additional to do here
        }

        return $html;
    }

    /**
     * Render a standalone view (no template wrapper)
     *
     * @param string $view View name
     * @param string|null $directory Plugin directory (e.g., 'lib/plugins/dashboard')
     * @param array $data Extra data to extract into view scope
     * @return string Rendered view
     */
    public function view(string $view, ?string $directory = null, array $data = []): string
    {
        $viewPath = $this->resolveView($view, $directory);

        extract($data);

        ob_start();
        require_once $viewPath;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Resolve a template path through the cascade
     *
     * @param string $name Template name
     * @param string|null $theme Theme override (null = use config)
     * @return string Absolute path
     */
    public function resolveTemplate(string $name, ?string $theme = null): string
    {
        // Get root path
        global $CONFIG;
        $root = $CONFIG ? $CONFIG->root() : getcwd();

        // Build cascade paths
        $paths = [
            // 1. App's own templates
            $root . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name,
        ];

        // 2. Theme override (if configured)
        if ($theme !== null) {
            $paths[] = $root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme
                . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;
        } else {
            $activeTheme = $CONFIG ? $CONFIG->get('application', 'theme') : null;
            if ($activeTheme) {
                $paths[] = $root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $activeTheme
                    . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;
            }
        }

        // 3. Vendor/core fallback
        $paths[] = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laswitchtech' . DIRECTORY_SEPARATOR . 'core'
            . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;

        // Find first existing path
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Fallback: return the first path (will fail if file doesn't exist)
        return $paths[0];
    }

    /**
     * Resolve a view path through the cascade
     *
     * @param string $name View name
     * @param string|null $directory Plugin directory (e.g., 'lib/plugins/dashboard')
     * @return string Absolute path
     */
    public function resolveView(string $name, ?string $directory = null): string
    {
        global $CONFIG;
        $root = $CONFIG ? $CONFIG->root() : getcwd();
        $defaultPath = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laswitchtech' . DIRECTORY_SEPARATOR . 'core';

        if ($directory !== null) {
            $appPath = $root . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;
            $defaultPath .= DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;
        } else {
            $appPath = $root . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;
            $defaultPath .= DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $name;
        }

        // Use app path if it exists, otherwise vendor fallback
        if (file_exists($appPath)) {
            return $appPath;
        }

        // Vendor fallback
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        // Last resort: return app path (will fail if file doesn't exist)
        return $appPath;
    }
}
