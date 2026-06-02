<?php

// Declaring namespace
namespace LaswitchTech\Core\Middleware;

use LaswitchTech\Core\Objects\RouteDTO;
use LaswitchTech\Core\Response;

/**
 * Middleware contract
 *
 * Each middleware receives the route being accessed and can return a Response
 * to short-circuit the chain (e.g., redirect to login on auth failure).
 * Returning null allows the chain to continue.
 */
interface MiddlewareInterface
{
    /**
     * Handle the request
     *
     * @param RouteDTO $route The route being accessed
     * @return Response|null Returns a Response to short-circuit, null to continue
     */
    public function handle(RouteDTO $route): ?Response;
}
