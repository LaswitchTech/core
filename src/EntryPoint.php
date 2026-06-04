<?php

// Declaring namespace
namespace LaswitchTech\Core;

use LaswitchTech\Core\Middleware\Auth2FAMiddleware;
use LaswitchTech\Core\Middleware\AuthMiddleware;
use LaswitchTech\Core\Middleware\MaintenanceMiddleware;
use LaswitchTech\Core\Response;

/**
 * EntryPoint — thin coordinator
 *
 * Coordinates the request flow: Bootstrap → Router → Middleware chain → Controller → View.
 * No business logic — just wiring components together.
 *
 * Request flow:
 *   1. Bootstrap loads globals
 *   2. Router loads routes
 *   3. Middleware chain runs (Auth → Maintenance)
 *   4. If no middleware short-circuits: dispatch controller
 *   5. Controller returns Response
 *   6. Response is sent
 */
class EntryPoint
{
    /**
     * Execute a request
     *
     * @param Router $router
     * @param string $namespace The request namespace (e.g., "/dashboard", "/crm")
     * @return Response|null
     */
    public function execute(Router $router, string $namespace): ?Response
    {
        // Get the route
        $route = $router->match($namespace);
        if ($route === null) {
            // Not found — return 404
            return Response::error(404, 'Not found.');
        }

        // Run middleware chain
        $middlewareChain = [
            new AuthMiddleware(),
            new Auth2FAMiddleware(),
            new MaintenanceMiddleware(),
        ];

        foreach ($middlewareChain as $middleware) {
            $result = $middleware->handle($route);
            if ($result !== null) {
                // Middleware short-circuited — send response and stop
                return $result;
            }
        }

        // Dispatch controller
        $response = $this->dispatch($router, $route);
        return $response;
    }

    /**
     * Dispatch a route to its controller
     *
     * @param Router $router
     * @param RouteDTO $route
     * @return Response
     */
    protected function dispatch(Router $router, RouteDTO $route): Response
    {
        // Check if this is a module route (handled directly by Router)
        if (in_array(str_replace('/', '', $route->namespace), Router::Modules)) {
            return $this->dispatchModule($router, $route);
        }

        // Check if the route has an action (controller dispatch)
        if ($route->action !== null) {
            return $this->dispatchController($route);
        }

        // No action — default to rendering template+view
        return Response::render($route->template, $route->view, [
            'directory' => null,
        ]);
    }

    /**
     * Dispatch a module route (CSS, logo)
     *
     * @param Router $router
     * @param RouteDTO $route
     * @return Response
     */
    protected function dispatchModule(Router $router, RouteDTO $route): Response
    {
        // Module routes are handled by the existing Router (CSS compilation, logo serving)
        // For now, delegate to the Router's existing module handling
        return Response::error(501, 'Module route handling pending migration.');
    }

    /**
     * Dispatch a controller action
     *
     * @param RouteDTO $route
     * @return Response
     */
    protected function dispatchController(RouteDTO $route): Response
    {
        // Resolve controller from action
        $parts = explode('/', strtolower($route->action));
        $controllerName = ucfirst($parts[0] ?? '') . 'Controller';
        $actionName = ($parts[1] ?? '') . 'Action';

        // Resolve controller path
        $path = Config::root() . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . $controllerName . '.php';
        if (!is_file($path)) {
            $path = Config::root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . ($parts[0] ?? '') . DIRECTORY_SEPARATOR . 'Controller.php';
        }

        if (!is_file($path) || !class_exists($controllerName)) {
            return Response::error(500, "Controller not found: {$controllerName}");
        }

        // Load controller
        require_once $path;

        // Instantiate controller
        $controller = new $controllerName();

        // Set route metadata on controller
        if ($controller instanceof Controller) {
            $controller->getRoute($route);
        }

        // Call action
        if (method_exists($controller, $actionName)) {
            $result = $controller->$actionName();

            // Check if the action returned a Response
            if ($result instanceof Response) {
                return $result;
            }

            // Check if the action set $this->Response
            if ($controller instanceof Controller && $controller->Response() instanceof Response) {
                return $controller->Response();
            }
        }

        // No response returned — default to rendering
        return Response::render($route->template, $route->view, [
            'directory' => null,
        ]);
    }
}
