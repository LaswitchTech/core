<?php

// Declaring namespace
namespace LaswitchTech\Core\Middleware;

use LaswitchTech\Core\Response;
use LaswitchTech\Core\RouteDTO;

/**
 * Authentication middleware
 *
 * Replicates the auth chain from Router::set(). Returns a Response
 * to short-circuit if any auth check fails.
 *
 * Chain (in order):
 *   1. Auth module loaded? → 430
 *   2. User authenticated? → 430
 *   3. User deleted? → 401
 *   4. User banned? → 403
 *   5. User verified? → 432
 *   6. User authorized for route+level? → 403
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Handle the request
     *
     * @param RouteDTO $route
     * @return Response|null
     */
    public function handle(RouteDTO $route): ?Response
    {
        // Only apply to private routes
        if ($route->public) {
            return null;
        }

        // Access globals
        global $AUTH;

        // 1. Is Auth module loaded?
        if (!in_array(get_class($AUTH), ['Module', 'LaswitchTech\Core\Module'])) {

            // 2. Is Auth loaded?
            if (!$AUTH->isLoaded()) {
                return Response::error(430, 'Authentication not loaded.');
            }

            // 3. Is user authenticated?
            if (!$AUTH->isAuthenticated()) {
                return Response::error(430, 'Unauthenticated.');
            }

            // 4. Is user deleted?
            if ($AUTH->user()->deleted()) {
                return Response::error(401, 'Unauthorized — account deleted.');
            }

            // 5. Is user banned?
            if ($AUTH->user()->banned()) {
                return Response::error(403, 'Forbidden — account banned.');
            }

            // 6. Is user verified?
            if (!$AUTH->user()->verified()) {
                return Response::error(432, 'Verification required.');
            }

            // 7. Is user authorized for this route + level?
            if (!$AUTH->isAuthorized('Route>' . $route->namespace, (int) $route->level)) {
                return Response::error(403, 'Forbidden — insufficient permissions.');
            }
        }

        return null;
    }
}
