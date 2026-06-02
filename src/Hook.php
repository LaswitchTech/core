<?php

// Declaring namespace
namespace LaswitchTech\Core;

/**
 * Hook system for plugin extension points
 *
 * Simple register/fire pattern. Plugins register listeners for named hooks.
 *
 * Default hooks:
 *   route.registered — fired when a route is registered
 *   auth.fail — fired when an auth check fails (args: $status, $message)
 *   view.before — fired before rendering (args: $template, $view)
 *   view.after — fired after rendering (args: $html)
 */
class Hook
{
    /**
     * Registered hooks: [$name => [callable, ...]]
     *
     * @var array
     */
    protected static array $hooks = [];

    /**
     * Default hook definitions
     *
     * @var array
     */
    protected static array $defaults = [
        'route.registered',
        'auth.fail',
        'view.before',
        'view.after',
    ];

    /**
     * Register a listener for a hook
     *
     * @param string $name Hook name
     * @param callable $callback Listener callback
     * @return void
     */
    public static function register(string $name, callable $callback): void
    {
        if (!isset(static::$hooks[$name])) {
            static::$hooks[$name] = [];
        }
        static::$hooks[$name][] = $callback;
    }

    /**
     * Fire all listeners for a hook
     *
     * @param string $name Hook name
     * @param mixed ...$args Arguments passed to listeners
     * @return array Results from all listeners
     */
    public static function fire(string $name, mixed ...$args): array
    {
        $results = [];
        if (isset(static::$hooks[$name])) {
            foreach (static::$hooks[$name] as $callback) {
                $results[] = $callback(...$args);
            }
        }
        return $results;
    }

    /**
     * Check if a hook has listeners
     *
     * @param string $name Hook name
     * @return bool
     */
    public static function hasListeners(string $name): bool
    {
        return isset(static::$hooks[$name]) && !empty(static::$hooks[$name]);
    }

    /**
     * Get all hook names
     *
     * @return array
     */
    public static function names(): array
    {
        return array_keys(static::$hooks);
    }

    /**
     * Get all default hook names
     *
     * @return array
     */
    public static function defaults(): array
    {
        return static::$defaults;
    }
}
