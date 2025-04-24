<?php

/**
 * Core Framework - Router
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects;
use Exception;

class Router {

    // Constants
    const Modules = ["css"];
    const ModulesLabels = [
        "css" => "CSS", // CSS Module
    ];
    const HttpCodes = [400,401,403,404,405,422,423,427,428,429,430,432,500,501,503];
    const HttpCustomCodes = [427,430,432];
    const HttpLabels = [
        "400" => "Bad Request", // 400 Error Document // Bad Request
        "401" => "Unauthorized", // 401 Error Document // Unauthorized
        "403" => "Forbidden", // 403 Error Document // Forbidden
        "404" => "Not Found", // 404 Error Document // Not Found
        "405" => "Method Not Allowed", // 405 Error Document // Method Not Allowed
        "422" => "Unprocessable Content", // 422 Error Document // Unprocessable Content
        "423" => "Locked", // 423 Error Document // Locked
        "427" => "2FA Required", // 427 Error Document // 2FA Required
        "428" => "Verification Required", // 428 Error Document // Verification Required
        "429" => "Too Many Requests", // 429 Error Document // Too Many Requests
        "430" => "Unauthenticated", // 430 Error Document // Unauthenticated
        "432" => "Unverified", // 432 Error Document // Unverified
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
        // Load Error Routes
        foreach(self::HttpCodes as $Code){
            $Code = strval($Code);
            $this->Routes[$Code] = $this->route($Code, ['label' => self::HttpLabels[$Code], 'view' => $Code . '.php']);
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
        foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
            $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
            if(is_dir($pluginPath) && is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')){
                foreach(json_decode(file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg'),true) as $route => $param){
                    $this->Routes[$route] = $this->route($route, $param, 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin);
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
        return new Objects\Route($this, $route, $data, $directory);
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
                        $this->Route = $this->Routes['428'];

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
    public function render($route = null): self
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
        $this->Route->render();

        // Return the instance
        return $this;
    }

    /**
     * Start the router
     *
     * @return self
     */
    public function start(): self
    {
        // Import Global Variables
        global $HELPER;

        // Initialize the Core Framework
        $HELPER->Core->init();

        // Render the route
        $this->render($this->Request->getNamespace());

        // Return the instance
        return $this;
    }
}
