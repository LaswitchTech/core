<?php

// Declaring namespace
namespace LaswitchTech\Core\Middleware;

use LaswitchTech\Core\Response;
use LaswitchTech\Core\Objects\RouteDTO;

/**
 * Maintenance mode middleware
 *
 * Returns 503 if maintenance mode is enabled and user is not Administrator.
 * Skips if maintenance is disabled or user is an Administrator.
 */
class MaintenanceMiddleware implements MiddlewareInterface
{
    /**
     * Handle the request
     *
     * @param RouteDTO $route
     * @return Response|null
     */
    public function handle(RouteDTO $route): ?Response
    {
        global $CONFIG, $AUTH;

        // Check if maintenance mode is enabled
        $maintenance = $CONFIG->get('application', 'maintenance');
        if (!$maintenance) {
            return null;
        }

        // Check if user is Administrator
        if (isset($AUTH) && $AUTH->isAuthorized('Administrator', 1)) {
            return null;
        }

        return Response::error(503, 'Service temporarily unavailable. Maintenance in progress.');
    }
}
