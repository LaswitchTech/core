<?php

// Declaring namespace
namespace LaswitchTech\Core;

/**
 * ViewGlobals — centralized context for layout templates
 *
 * Provides a guaranteed set of global variables in the template scope.
 * Each layout should call `ViewGlobals::apply()` at its entry point
 * before accessing any view variables.
 *
 * Guarantees (provided on every layout):
 *   $config      — Config (app + bootstrap settings)
 *   $auth        — Auth module instance
 *   $currentUser — Current authenticated user (or null if guest)
 *   $menu        — Builder instance (menu builder)
 *   $breadcrumbs  — Breadcrumb data from current route
 *   $locale      — Locales instance
 *   $csrf        — CSRF instance
 *   $request      — Request instance
 *   $output      — Output instance
 *   $app          — Application metadata (name, theme, installed)
 *
 * Usage in layout templates:
 *   <?php ViewGlobals::apply(); ?>
 *   <?php if (!$config->get('application', 'installed')): ?>
 *       ...
 *   <?php endif; ?>
 */
class ViewGlobals
{
    /**
     * The guaranteed global variable names
     *
     * @var string[]
     */
    public const NAMES = [
        'config',
        'auth',
        'currentUser',
        'menu',
        'breadcrumbs',
        'locale',
        'csrf',
        'request',
        'output',
        'app',
    ];

    /**
     * Extract all guaranteed globals into the current template scope
     *
     * @return void
     */
    public static function apply(): void
    {
        extract(static::contextFromScope(), EXTR_SKIP);
    }

    /**
     * Extract all guaranteed globals into the current template scope
     * as a data array for View::render()
     *
     * @return array
     */
    public static function context(): array
    {
        return static::contextFromScope();
    }

    /**
     * Build the global context array from request-scope globals
     *
     * Returns all globals with safe defaults for guest/missing context.
     * No silent patches around missing globals — if a global is truly
     * undefined, its alias is null.
     *
     * @return array Associative array of global name → value
     */
    protected static function contextFromScope(): array
    {
        // Import request-scope globals
        global $CONFIG, $AUTH, $BUILDER, $LOCALE, $CSRF, $REQUEST, $OUTPUT;

        // Determine current user (null-safe)
        $currentUser = null;
        if (isset($AUTH) && $AUTH instanceof \LaswitchTech\Core\Auth) {
            $currentUser = $AUTH->isAuthenticated() ? $AUTH->user() : null;
        }

        // Application metadata (null-safe)
        $app = [
            'name' => isset($CONFIG) ? ($CONFIG->get('application', 'name') ?? 'Core-Web') : 'Core-Web',
            'theme' => isset($CONFIG) ? ($CONFIG->get('application', 'theme') ?? null) : null,
            'installed' => isset($CONFIG) ? ($CONFIG->get('application', 'installed') ?? false) : false,
        ];

        // Breadcrumbs — default to empty (routes set their own)
        $breadcrumbs = isset($CONFIG) ? ($CONFIG->get('breadcrumbs', 'items') ?? []) : [];

        return [
            'config' => $CONFIG ?? null,
            'auth' => $AUTH ?? null,
            'currentUser' => $currentUser,
            'menu' => $BUILDER ?? null,
            'breadcrumbs' => $breadcrumbs,
            'locale' => $LOCALE ?? null,
            'csrf' => $CSRF ?? null,
            'request' => $REQUEST ?? null,
            'output' => $OUTPUT ?? null,
            'app' => $app,
        ];
    }
}
