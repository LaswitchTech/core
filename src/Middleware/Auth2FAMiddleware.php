<?php

// Declaring namespace
namespace LaswitchTech\Core\Middleware;

use LaswitchTech\Core\Response;
use LaswitchTech\Core\Objects\RouteDTO;

/**
 * 2FA verification middleware
 *
 * Placed between AuthMiddleware and MaintenanceMiddleware in the chain.
 * If the user has TOTP enabled but hasn't verified 2FA in this session,
 * redirects them to the 2FA verify page.
 */
class Auth2FAMiddleware implements MiddlewareInterface
{
    /**
     * Handle the request
     *
     * @param RouteDTO $route
     * @return Response|null
     */
    public function handle(RouteDTO $route): ?Response
    {
        // Access globals
        global $AUTH, $REQUEST;

        // Only apply to private routes
        if ($route->public) {
            return null;
        }

        // If Auth hasn't loaded, skip (will be caught by AuthMiddleware)
        if (!in_array(get_class($AUTH), ['Module', 'LaswitchTech\Core\Module'])) {
            return null;
        }

        // If auth isn't loaded at all, skip
        if (!$AUTH->isLoaded()) {
            return null;
        }

        // Check if password auth succeeded but 2FA verification is pending
        if ($AUTH->needs_2fa === true) {
            return Response::error(427, 'Two-factor verification required.', null, '2fa-verify/index.php');
        }

        // User has TOTP enabled but no session 2FA token — require verification
        $user = $AUTH->user();
        if ($user && $user->setting('totp_enabled') === true) {
            // Allow 2FA verify/setup routes to pass through for self-referential access
            if (in_array($route->namespace, ['/2fa-verify', '/2fa-setup'])) {
                return null;
            }

            $twoFaVerified = $REQUEST->getParams('SESSION', 'auth-2fa-' . session_id());
            if (!$twoFaVerified) {
                return Response::error(427, 'Two-factor verification required.', null, '2fa-verify/index.php');
            }
        }

        return null; // pass through
    }
}
