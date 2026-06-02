<?php

// Declaring namespace
namespace LaswitchTech\Core;

use LaswitchTech\Core\Abstracts\Controller as BaseController;
use LaswitchTech\Core\Response;

/**
 * Controller base class
 *
 * Extends Abstracts\Controller to inherit globals ($AUTH, $MODEL, $HELPER,
 * $OUTPUT, $REQUEST, $CONFIG). Adds Response and View support.
 *
 * Usage:
 *   class DashboardController extends Controller {
 *       public function indexAction() {
 *           // Default rendering (template=panel, view=index)
 *           return $this->defaultAction();
 *       }
 *
 *       public function fetchAction() {
 *           // Custom response (opt out of rendering)
 *           return Response::json(['data' => '...']);
 *       }
 *
 *       public function redirectAction() {
 *           return Response::redirect('/dashboard');
 *       }
 *   }
 *
 * Actions are named: <action>Action()
 * The action name comes from Route::action (e.g., "dashboard/fetch" → "fetchAction")
 */
class Controller extends BaseController
{
    /**
     * Response object (set by action, sent by EntryPoint)
     *
     * @var Response
     */
    protected ?Response $Response = null;

    /**
     * View engine instance
     *
     * @var View
     */
    protected ?View $View = null;

    /**
     * Route metadata (set by Router/EntryPoint)
     *
     * @var RouteDTO|null
     */
    protected ?RouteDTO $Route = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get or set the Response object
     *
     * @param Response|null $response
     * @return Response|null
     */
    public function Response(?Response $response = null): ?Response
    {
        if ($response !== null) {
            $this->Response = $response;
        }
        return $this->Response;
    }

    /**
     * Get the View engine instance (lazy-create)
     *
     * @return View
     */
    public function getView(): View
    {
        if ($this->View === null) {
            $this->View = new View();
        }
        return $this->View;
    }

    /**
     * Get or set the Route DTO
     *
     * @param RouteDTO|null $route
     * @return RouteDTO|null
     */
    public function getRoute(?RouteDTO $route = null): ?RouteDTO
    {
        if ($route !== null) {
            $this->Route = $route;
        }
        return $this->Route;
    }

    /**
     * Default action — renders the route's template + view
     *
     * Uses Route metadata to resolve template and view.
     * Returns a Response (type=render) for the EntryPoint to handle.
     *
     * @return Response
     */
    public function defaultAction(): Response
    {
        if ($this->Route === null) {
            return Response::error(500, 'No route metadata available.');
        }

        // Check if template is set (module routes like /css, /logo are handled differently)
        if ($this->Route->template === null) {
            return Response::error(404, 'No template configured for this route.');
        }

        return Response::render($this->Route->template, $this->Route->view, [
            'directory' => null, // Will be resolved by View engine
        ]);
    }

    /**
     * Send a response to the client
     *
     * Called by the EntryPoint after an action returns a Response.
     * Handles headers, output, and early termination.
     *
     * @param Response $response
     * @return void
     */
    public function sendResponse(Response $response): void
    {
        $response->send();
    }

    /**
     * Alias for Response::json — convenient shortcut
     *
     * @param mixed $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    protected function jsonResponse(mixed $content, int $status = 200, array $headers = []): Response
    {
        return Response::json($content, $status, $headers);
    }

    /**
     * Alias for Response::redirect — convenient shortcut
     *
     * @param string $url
     * @param int $status
     * @return Response
     */
    protected function redirectResponse(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    /**
     * Alias for Response::error — convenient shortcut
     *
     * @param int $status
     * @param string $message
     * @param string|null $template
     * @param string|null $view
     * @return Response
     */
    protected function errorResponse(int $status, string $message = '', ?string $template = null, ?string $view = null): Response
    {
        return Response::error($status, $message, $template, $view);
    }

    /**
     * Magic Method to catch all undefined methods
     *
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        // Send the output
        $this->Output->print('Controller Action[' . $name . '] not Implemented', ['HTTP/1.1 501 Not Implemented']);
    }
}
