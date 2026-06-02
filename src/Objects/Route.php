<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;
use LaswitchTech\Core\View;

class Route {

    // Global Properties
    protected $Config;
    protected $Auth;
    protected $Locale;
    protected $Request;
    protected $Output;
    protected $CSRF;
    protected $Model;
    protected $Helper;
    protected $Builder;
    protected $Style;

    // Parent Object
    public $Router;

    // Properties
    protected $Route;
    protected $Directory;
    protected $Template;
    protected $View;
    protected $Public = true;
    protected $Location = [];
    protected $Level = 0;
    protected $Parent;
    protected $Label;
    protected $Icon;
    protected $Color;
    protected $Action;
    protected $Call;
    protected $Interrupt = false;
    protected $Hooks = [
        "widgets" => "Widget.php",
    ];

    /**
     * Constructor
     *
     * @param object $router
     * @param string $route
     * @param array|null $data
     * @param string|null $directory
     */
    public function __construct(object $router, string $route, ?array $data = null, ?string $directory = null)
    {
        // Global Variables
        global $CONFIG, $REQUEST, $OUTPUT, $LOCALE , $AUTH, $CSRF, $MODEL, $HELPER, $BUILDER, $STYLE;

        // Set Global Properties
        $this->Config = $CONFIG;
        $this->Auth = $AUTH;
        $this->Locale = $LOCALE;
        $this->Request = $REQUEST;
        $this->Output = $OUTPUT;
        $this->CSRF = $CSRF;
        $this->Model = $MODEL;
        $this->Helper = $HELPER;
        $this->Builder = $BUILDER;
        $this->Style = $STYLE;
        $this->Directory = $directory;

        // Set Router
        $this->Router = $router;

        // Set Properties
        $this->Route = $route;

        // Set Data
        if(!is_null($data) && !empty($data)){
            $this->set($data);
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Save Route
        $this->save();
    }

    /**
     * Set Route Data
     *
     * @param array $data
     * @param string $directory
     * @return self
     */
    public function set(array $data): self
    {
        // Set Data
        foreach($data as $key => $value){
            if(!is_null($value)){
                switch($key){
                    case "template":
                        $this->template($value);
                        break;
                    case "view":
                        $this->view($value);
                        break;
                    case "public":
                        $this->public($value);
                        break;
                    case "location":
                        foreach($value as $location){
                            $this->location($location);
                        }
                        break;
                    case "level":
                        $this->level($value);
                        break;
                    case "parent":
                        $this->parent($value);
                        break;
                    case "label":
                        $this->label($value);
                        break;
                    case "icon":
                        $this->icon($value);
                        break;
                    case "color":
                        $this->color($value);
                        break;
                    case "action":
                        $this->action($value);
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Get or Set Template File.
     *
     * @param string|null $template
     * @return string
     */
    public function template(?string $template = null): ?string
    {

        // Set View
        if(!is_null($template)){

            // Set View
            $this->Template = $template;
        }

        // Generate the path
        $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $this->Template;
        if(!file_exists($path)){
            $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $this->Config->get('application','theme') . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $this->Template;
        }
        if(!file_exists($path)){
            $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laswitchtech' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $this->Template;
        }

        return $path;
    }

    /**
     * Get or Set View File.
     *
     * @param string|null $view
     * @return string
     */
    public function view(?string $view = null): ?string
    {

        // Set View
        if(!is_null($view)){

            // Set View
            $this->View = $view;
        }

        // Generate the path
        $path = $this->Config->root();
        $defaultPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laswitchtech' . DIRECTORY_SEPARATOR . 'core';
        if($this->Directory){
            $path .= DIRECTORY_SEPARATOR . $this->Directory;
            $defaultPath .= DIRECTORY_SEPARATOR . $this->Directory;
        }
        $path .= DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $this->View;
        $defaultPath .= DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $this->View;

        // Set View
        if(!is_null($this->View)){

            // Check if the file exists
            if(!is_file($path)){

                // Use the default path if the file does not exist
                if(is_file($defaultPath)){
                    $path = $defaultPath;
                } else {

                    // Check if development mode is enabled
                    if($this->Config->get('application','development') && $this->Auth->isAuthenticated() && $this->Auth->isAuthorized('Developer', 1)){

                        // Check if the view directory exists recursively and create it if it does not
                        if(!is_dir(dirname($path))){
                            mkdir(dirname($path), 0755, true);
                        }

                        // Create the file if it does not exist
                        if(!is_file($path)){
                            $content = "<!--" . PHP_EOL;
                            $content .= "  Core Framework - View File" . PHP_EOL . PHP_EOL;
                            $content .= "  @license    MIT (https://mit-license.org/)" . PHP_EOL;
                            $content .= "  @author     Full Name <user@domain.com>" . PHP_EOL;
                            $content .= "-->" . PHP_EOL;
                            file_put_contents($path, $content);
                        }
                    }
                }
            }
        }

        return $path;
    }

    /**
     * Get or Set Namespace.
     *
     * @param string|null $namespace
     * @return string
     */
    public function namespace(): ?string
    {
        return $this->Route;
    }

    /**
     * Get or Set Public.
     *
     * @param bool|null $public
     * @return bool
     */
    public function public(?bool $public = null): ?bool
    {
        if(!is_null($public)){
            $this->Public = $public;
        }
        return $this->Public;
    }

    /**
     * Get or Set Location.
     *
     * @param string|null $location
     * @return array
     */
    public function location(?string $location = null): ?array
    {
        // Add Location
        if(!is_null($location)){
            $this->Location[] = $location;
        }

        // Filter and Unique
        $this->Location = array_filter($this->Location);
        $this->Location = array_unique($this->Location);

        return $this->Location;
    }

    /**
     * Get or Set Level.
     *
     * @param int|null $level
     * @return int
     */
    public function level(?int $level = null): int
    {
        if(!is_null($level)){
            $this->Level = $level;
        }
        return $this->Level;
    }

    /**
     * Get or Set Parent.
     *
     * @param mixed $parent
     * @return string
     */
    public function parent(mixed $parent = null): mixed
    {
        if(!is_null($parent)){
            $this->Parent = $parent;
        }
        return $this->Parent;
    }

    /**
     * Get or Set Label.
     *
     * @param string|null $label
     * @return string
     */
    public function label(?string $label = null): ?string
    {
        if(!is_null($label)){
            $this->Label = $label;
        }
        return $this->Label;
    }

    /**
     * Get or Set Icon.
     *
     * @param string|null $icon
     * @return string
     */
    public function icon(?string $icon = null): ?string
    {
        if(!is_null($icon)){
            $this->Icon = $icon;
        }
        return $this->Icon;
    }

    /**
     * Get or Set Color.
     *
     * @param string|null $color
     * @return string
     */
    public function color(?string $color = null): ?string
    {
        if(!is_null($color)){
            $this->Color = $color;
        }
        return $this->Color;
    }

    /**
     * Get or Set Action.
     *
     * @param string|null $action
     * @return string
     */
    public function action(?string $action = null): ?string
    {
        if(!is_null($action)){
            $this->Action = $action;
        }
        return $this->Action;
    }

    /**
     * Save Route to Config
     *
     * @return self
     */
    public function save(): self
    {
        // Check if development mode is enabled
        if(!$this->Config->get('application','development') ?? false){
            return $this;
        }

        // Check if the route is a module
        if(in_array(str_replace('/','',$this->Route), $this->Router::Modules)){
            return $this;
        }

        // Check if the route is from a plugin
        if($this->Directory){

            // Retrieve the routes file
            $routes = json_decode(file_get_contents($this->Config->root() . DIRECTORY_SEPARATOR . $this->Directory . DIRECTORY_SEPARATOR . 'routes.cfg'), true);

            // Update the route
            $routes[$this->Route] = [
                'template' => $this->Template,
                'view' => $this->View,
                'public' => $this->Public,
                'action' => $this->Action,
                'location' => $this->Location,
                'level' => $this->Level,
                'parent' => $this->Parent,
                'label' => $this->Label,
                'icon' => $this->Icon,
                'color' => $this->Color,
            ];

            // Save the routes file
            file_put_contents($this->Config->root() . DIRECTORY_SEPARATOR . $this->Directory . DIRECTORY_SEPARATOR . 'routes.cfg', json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {

            // Save Route to Config
            $this->Config->set('routes', $this->Route, [
                'template' => $this->Template,
                'view' => $this->View,
                'public' => $this->Public,
                'action' => $this->Action,
                'location' => $this->Location,
                'level' => $this->Level,
                'parent' => $this->Parent,
                'label' => $this->Label,
                'icon' => $this->Icon,
                'color' => $this->Color,
            ]);
        }

        return $this;
    }

    /**
     * Call an action
     *
     * @param string|null $key
     * @return mixed
     */
    protected function call(?string $key = null): mixed
    {
        // Check if the action was already called
        if(empty($this->Call) || is_null($this->Call)){

            // Check if the action is set
            if($this->Action){

                // Convert the action to an array
                $parts = explode('/', strtolower($this->Action));
                $controller = $parts[0] ?? null;
                $action = $parts[1] ?? null;

                // Check if the controller and action are set
                if(!is_null($controller) && !is_null($action)){

                    // Set the controller and action names
                    $controllerName = ucfirst($controller) . 'Controller';
                    $actionName = $action . 'Action';

                    // Check if the controller file exists
                    $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . $controllerName . '.php';
                    if(!is_file($path)){
                        $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . 'Controller.php';
                    }

                    // Check if the controller file exists
                    if(is_file($path)){

                        // Load the controller
                        require_once $path;

                        // Check if the class exists
                        if(class_exists($controllerName)){

                            // Initialize the class
                            $class = new $controllerName();

                            // Check if the method exists
                            if(method_exists($class, $actionName)){

                                // Call the method
                                $this->Call = $class->$actionName();
                            }
                        }
                    }
                }
            }
        }

        if(!is_null($key) && is_array($this->Call)){
            return $this->Call[$key] ?? null;
        }
        return $this->Call;
    }

    /**
     * Get the route metadata
     *
     * @return array
     */
    public function metadata(): array
    {
        return [
            'route' => $this->Route,
            'public' => $this->Public,
            'level' => $this->Level,
            'label' => $this->Label,
            'icon' => $this->Icon,
            'color' => $this->Color,
        ];
    }

    /**
     * Interrupt the execution
     *
     * @return self
     */
    protected function interrupt(): self
    {
        // Interrupt the execution
        $this->Interrupt = true;
        return $this;
    }

    /**
     * Load the hooks
     *
     * @param string $hook
     * @return self
     */
    protected function hook(string $hook): self
    {
        // Check if the hook is valid
        if(array_key_exists($hook, $this->Hooks)){
            // Load Plugins Hooks
            $path = $this->Config->root() . '/lib/plugins';
            if(is_dir($path)){
                $plugins = array_diff(scandir($path), array('..', '.','.git','.DS_Store'));
                foreach($plugins as $file){
                    $filePath = $file . DIRECTORY_SEPARATOR . $this->Hooks[$hook];
                    if(is_file($path.DIRECTORY_SEPARATOR.$filePath)){
                        include_once $path . DIRECTORY_SEPARATOR . $filePath;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Render the route
     *
     * Delegates to the View engine for template+view composition.
     * Module routes (css/logo) are handled directly (not templating).
     *
     * @param bool $full Whether to render the template wrapper
     * @return self
     */
    public function render(bool $full = true): self
    {
        // Check if the route is a module
        if(in_array(str_replace('/','',$this->Route), $this->Router::Modules)){

            // Handle the module
            switch(str_replace('/','',$this->Route)){
                case 'css':
                    header('Content-Type: text/css; charset=utf-8');
                    header('Cache-Control: public, max-age=31536000, immutable');
                    echo $this->Style->compile();
                    break;
                case 'logo':
                    $path = $this->Config->root() . '/webroot/' . $this->Builder->logo();
                    header('Content-Type: '.mime_content_type($path).'; charset=utf-8');
                    header('Cache-Control: public, max-age=31536000, immutable');
                    echo file_get_contents($path);
                    break;
            }

            return $this;
        }

        // Check if we should interrupt (early termination)
        if($this->Interrupt) {
            return $this;
        }

        // Delegate to the View engine
        if($full && $this->Template){
            $view = new View();
            // Use output buffering to capture the rendered output
            ob_start();
            $html = $view->render($this->Template, $this->View, ['directory' => $this->Directory]);
            // Echo the rendered HTML (existing behavior: direct output)
            echo $html;
        } elseif($this->View){
            // View-only (no template wrapper)
            $view = new View();
            ob_start();
            $html = $view->view($this->View, $this->Directory);
            echo $html;
        }

        return $this;
    }
}
