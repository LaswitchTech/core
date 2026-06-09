<?php

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects;
use LaswitchTech\Core\Objects\RouteDTO;
use Exception;

class Router {

    // Constants
    const Modules = ["css","logo"];
    const ModulesLabels = [
        "css" => "CSS",
        "logo" => "Logo",
    ];
    const HttpCodes = [330,400,401,403,404,405,422,423,427,428,429,430,432,500,501,503];
    const HttpCustomCodes = [330,427,430,432];
    const HttpLabels = [
        "330" => "Reset Password", // 330 Custom Document // Reset Password
        "400" => "Bad Request", // 400 Error Document // Bad Request
        "401" => "Unauthorized", // 401 Error Document // Unauthorized
        "403" => "Forbidden", // 403 Error Document // Forbidden
        "404" => "Not Found", // 404 Error Document // Not Found
        "405" => "Method Not Allowed", // 405 Error Document // Method Not Allowed
        "422" => "Unprocessable Content", // 422 Error Document // Unprocessable Content
        "423" => "Locked", // 423 Error Document // Locked
        "427" => "2FA Required", // 427 Custom Document // 2FA Required
        "428" => "Verification Required", // 428 Error Document // Verification Required
        "429" => "Too Many Requests", // 429 Error Document // Too Many Requests
        "430" => "Unauthenticated", // 430 Custom Document // Unauthenticated
        "432" => "Unverified", // 432 Custom Document // Unverified
        "500" => "Internal Server Error", // 500 Error Document // Internal Server Error
        "501" => "Not Implemented", // 501 Error Document // Not Implemented
        "503" => "Service Unavailable", // 503 Error Document // Service Unavailable
    ];

    // Global Properties
    private $Config;
    private $Auth;
    private $Request;
    private $Output;

    // Properties
    private $Route;
    private $Routes = [];
    private $dtoRoutes = []; // Parallel storage for RouteDTO (new MVC path)

    /**
     * Constructor
     */
    public function __construct()
    {
        // Global Variables
        global $CONFIG, $REQUEST, $OUTPUT, $AUTH;

        // Set Global Properties
        $this->Config = $CONFIG;
        $this->Request = $REQUEST;
        $this->Output = $OUTPUT;
        $this->Auth = $AUTH;

        // Configure Globals
        $this->Config->add('routes');

        // Load Routes
        $this->load();
    }

    /**
     * Load all routes
     *
     * @return self
     */
    private function load(): self
    {
        // Load Http Routes
        foreach(self::HttpCodes as $Code){
            $Code = strval($Code);
            if(in_array(intval($Code), self::HttpCustomCodes)){
                // $this->Routes[$Code] = $this->route($Code, ['label' => self::HttpLabels[$Code], 'view' => $Code . '.php']);
                $this->Routes[$Code] = $this->route($Code, ['label' => self::HttpLabels[$Code], 'template' => 'internal.php', 'view' => $Code . '.php']);
            } else {
                $this->Routes[$Code] = $this->route($Code, ['label' => self::HttpLabels[$Code], 'template' => 'error.php', 'view' => $Code . '.php']);
            }
        }

        // Load Modules Routes
        foreach(self::Modules as $Module){
            $Module = strval($Module);
            $this->Routes['/'.$Module] = $this->route('/'.$Module, ['label' => self::ModulesLabels[$Module]]);
        }

        // Load Routes
        if($this->Config->get('routes')){
            foreach($this->Config->get('routes') as $route => $param){
                $this->Routes[$route] = $this->route($route, $param);
            }
        }

        // Load Plugins Routes
        $pluginsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        if(is_dir($pluginsPath)){
            foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
                $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
                if(is_dir($pluginPath) && is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')){
                    $content = file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg');
                    if($content){
                        foreach(json_decode($content,true) as $route => $param){
                            $this->Routes[$route] = $this->route($route, $param, 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add/Generate a route
     *
     * @param string $route
     * @param array $data
     * @return self
     */
    public function route(string $route, ?array $data = null, ?string $directory = null): Objects\Route
    {
        $object = new Objects\Route($this, $route, $data, $directory);

        // Also create a DTO for the new MVC path
        $this->dtoRoutes[$route] = new RouteDTO($route, $data);

        return $object;
    }

    /**
     * Retrieve the routes or a specific route
     *
     * @param string|null $route
     * @return array|null
     */
    public function routes(?string $route = null): mixed
    {
        if($route){
            return $this->Routes[$route] ?? null;
        }

        return $this->Routes;
    }

    /**
     * Register a route (MVC path)
     *
     * @param string $namespace The route path
     * @param RouteDTO $route The route DTO
     * @return self
     */
    public function register(string $namespace, RouteDTO $route): self
    {
        $this->dtoRoutes[$namespace] = $route;
        return $this;
    }

    /**
     * Match a route by namespace (MVC path)
     *
     * @param string $namespace The request namespace
     * @return RouteDTO|null
     */
    public function match(string $namespace): ?RouteDTO
    {
        return $this->dtoRoutes[$namespace] ?? null;
    }

    /**
     * Start routing
     *
     * @return void
     */
    public function start(): void
    {
        // Try to match route first, otherwise provide fallback
        try {
            $route = $this->match();
            if ($route !== null) {
                $this->render($route);
                return;
            }
        } catch (Exception $e) {
            // If route matching fails due to incomplete setup, handle gracefully
        }

        // Fallback for when no routes are defined or config is missing
        try {
            // Check if we're on the root path and the install system isn't complete
            $path = $this->Request->getPath();
            if ($path === '/' || $path == '') {
                // Create a basic fallback page
                $this->renderFallbackPage();
                return;
            }
        } catch (Exception $e) {
            // If everything fails, show minimal error with install help
            $this->renderErrorPage($e);
            return;
        }
    }

    /**
     * Render a fallback page for when no routes are found
     *
     * @return void
     */
    private function renderFallbackPage(): void
    {
        // Try to load basic installer if needed
        global $INSTALLER, $CONFIG;
        
        // Check configuration status
        try {
            $isInstalled = false;
            if (isset($INSTALLER)) {
                $isInstalled = $INSTALLER->isInstalled();
            }
            
            if (!$isInstalled && isset($CONFIG)) {
                // Show installation guidance
                echo "<h1>Core-Web Framework Setup Required</h1>";
                echo "<p>This application requires initial setup.</p>";
                echo "<p>Please run the installer or configure your database in <code>config/database.cfg</code></p>";
                return;
            } else {
                // Default fallback to basic page
                echo "<h1>Welcome to Core-Web Framework</h1>";
                echo "<p>The system is properly configured but no routes are defined yet.</p>";
                echo "<p>Your installation is complete, ready for development!</p>";
                return;
            }
        } catch (Exception $e) {
            // Final fallback
            echo "<html><body><h1>Core-Web Framework</h1><p>Installation in progress...</p></body></html>";
        }
    }

    /**
     * Render an error page when system is not working properly
     *
     * @param Exception $e
     * @return void
     */
    private function renderErrorPage(Exception $e): void
    {
        echo "<h1>Core-Web Framework Error</h1>";
        echo "<p>System has encountered an error during initialization:</p>";
        echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
        echo "<p>Please check your configuration files in the <code>config/</code> directory.</p>";
    }

    /**
     * Load routes from config files (MVC path)
     *
     * @return self
     */
    public function loadFromConfig(): self
    {
        global $CONFIG;

        // Load from config/routes.cfg (app-level routes)
        $routesCfg = $CONFIG->root() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.cfg';
        if (is_file($routesCfg)) {
            $content = file_get_contents($routesCfg);
            if ($content) {
                $routes = json_decode($content, true);
                if (is_array($routes)) {
                    foreach ($routes as $namespace => $data) {
                        $this->dtoRoutes[$namespace] = new RouteDTO($namespace, $data);
                    }
                }
            }
        }

        // Load from plugin routes.cfg files
        $pluginsPath = $CONFIG->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        if (is_dir($pluginsPath)) {
            foreach (array_diff(scandir($pluginsPath), array('..', '.')) as $plugin) {
                $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
                if (is_dir($pluginPath) && is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')) {
                    $content = file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg');
                    if ($content) {
                        $routes = json_decode($content, true);
                        if (is_array($routes)) {
                            foreach ($routes as $namespace => $data) {
                                $this->dtoRoutes[$namespace] = new RouteDTO($namespace, $data);
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Set the current route
     *
     * @param string $route
     * @return self
     */
    public function set(string $route): self
    {
        if(!isset($this->Routes[$route])){

            // Send Not Found
            $this->Route = $this->Routes['404'];
        } else {

            // Check if the route is public
            if(!$this->Routes[$route]->public()){

                // Check if Auth is available
                if(!in_array(get_class($this->Auth),["Module","LaswitchTech\Core\Module"])){

                    // Check if the user is loaded
                    if(!$this->Auth->isLoaded()){
                        $this->Route = $this->Routes['430'];

                        return $this;
                    }

                    // Check if the user is authenticated
                    if(!$this->Auth->isAuthenticated()){

                        // Send Unauthorized
                        $this->Route = $this->Routes['430'];

                        return $this;
                    }

                    // Check if the user is deleted
                    if($this->Auth->user()->deleted()){

                        // Send Unauthorized
                        $this->Route = $this->Routes['401'];

                        return $this;
                    }

                    // Check if the user is banned
                    if($this->Auth->user()->banned()){

                        // Send Forbidden
                        $this->Route = $this->Routes['403'];

                        return $this;
                    }

                    // Check if the user is verified
                    if(!$this->Auth->user()->verified()){

                        // Send Unverified
                        $this->Route = $this->Routes['432'];

                        return $this;
                    }

                    // Check if the user has the required permission
                    if(!$this->Auth->isAuthorized('Route>' . $this->Routes[$route]->namespace(), intval($this->Routes[$route]->level()))){

                        // Send Forbidden
                        $this->Route = $this->Routes['403'];

                        return $this;
                    }

                    // Set the route
                    $this->Route = $this->Routes[$route];
                }
            } else {

                // Set the route
                $this->Route = $this->Routes[$route];
            }
        }

        return $this;
    }

    /**
     * Render the route
     *
     * @param string $route
     * @return self
     */
    public function render(?string $route = null, bool $full = true): self
    {
        // Set the route
        if($route){
            $this->set($route);
        }

        // Check if maintenance mode is enabled
        if($this->Config->get('application','maintenance') && !$this->Auth->isAuthorized('Administrator', 1)){

            // Set the route to maintenance
            $this->set(503);
        }

        // Render the route
        $this->Route->render((($this->Route->namespace() === $route) ? $full : true));

        // Return the instance
        return $this;
    }

    /**
    /**
     * Start the router using the MVC architecture
     *
     * Coordinates Bootstrap → Router → Middleware chain → Controller → View.
     * Backward-compatible: existing start() is unchanged.
     *
     * @return Response
     */
    public function startMVC(): Response
    {
        global $HELPER;

        // Initialize the Core Framework
        $HELPER->Core->init();

        // Determine namespace (same logic as start())
        $namespace = ($this->Config->get('application', 'installed')
                      || $this->Request->getNamespace() === '/css')
            ? $this->Request->getNamespace()
            : '/install';

        // Load DTO routes from config (only once)
        if (empty($this->dtoRoutes)) {
            $this->loadFromConfig();
        }

        // Execute through EntryPoint
        $entryPoint = new EntryPoint();
        $response = $entryPoint->execute($this, $namespace);
        $response->send();
        return $response;
    }

    /**
     * Returns all registered routes.
     */
    public function all(): array
    {
        return array_values($this->dtoRoutes);
    }
}
