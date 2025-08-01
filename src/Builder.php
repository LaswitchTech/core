<?php

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Builder {

    // Constants

    // Global Properties
    protected $Config;
    protected $CSRF;
    protected $Auth;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Global Variables
        global $CONFIG, $CSRF, $AUTH;

        // Set Global Properties
        $this->Config = $CONFIG;
        $this->CSRF = $CSRF;
        $this->Auth = $AUTH;

        // Configure Globals
        $this->Config->add('css')->add('js')->add('routes');
    }

    /**
     * Get a menu
     *
     * @param string $location
     * @param string $parent
     * @return array
     */
    public function menu($location = 'sidebar', $parent = null)
    {
        // Import Global Variables
        global $AUTH;
        $menu = [];
        $routes = $this->Config->get('routes');

        // Load Plugins Routes
        $pluginsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        if(is_dir($pluginsPath)){
            foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
                $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
                if(is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')){
                    foreach(json_decode(file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg'),true) as $route => $param){
                        if(!isset($routes[$route])){
                            $routes[$route] = $param;
                        }
                    }
                }
            }
        }

        // Sort the routes
        ksort($routes);

        foreach($routes as $route => $param) {
            if(!isset($param['parent']) || is_null($param['parent'])) $param['parent'] = [];
            if(!is_array($param['parent'])) $param['parent'] = [$param['parent']];
            if($parent && !in_array($parent,$param['parent'])) continue;
            if(!isset($param['location'])) continue;
            if(is_string($param['location']) && $param['location'] !== $location) continue;
            if(is_array($param['location']) && !in_array($location,$param['location'])) continue;
            if(!$param['public'] && !$AUTH->isAuthenticated()) continue;
            if(!$param['public'] && !$AUTH->isAuthorized("Route>" . $route, $param['level'])) continue;

            $parts = array_filter(explode('/', $route));
            if(empty($parts)) $parts = [""];

            $param['items'] = [];
            $param['link'] = $route;

            if(!empty($param['parent'])){
                foreach($param['parent'] as $par){
                    if(!array_key_exists($par, $menu)){
                        $menu[$par] = [];
                    }
                    if(!array_key_exists('items', $menu[$par])){
                        $menu[$par]['items'] = [];
                    }
                    $menu[$par]['items'][$route] = $param;
                }
            } else {
                if(array_key_exists($route, $menu)){
                    $menu[$route] = array_merge_recursive($menu[$route], $param);
                } else {
                    $menu[$route] = $param;
                }
            }
        }

        foreach($menu as $route => $param) {
            if((!array_key_exists('link',$param) || is_null($param['link'])) && array_key_exists('items',$param)){
                foreach($param['items'] as $item => $parameters){
                    if(!is_null($parameters['link']) && !array_key_exists($parameters['link'],$menu)){
                        $menu[$parameters['link']] = $parameters;
                        unset($menu[$route]['items'][$item]);
                    }
                }
            }
        }

        foreach($menu as $route => $param) {
            if((!array_key_exists('link',$param) || is_null($param['link'])) && array_key_exists('items',$param)){
                if(empty($param['items'])){
                    unset($menu[$route]);
                }
            }
        }

        return $menu;
    }

    /**
     * Create Crumbs
     *
     * @param string $location
     * @param string $parent
     * @return array
     */
    public function crumbs()
    {
        // Import Global Variables
        global $REQUEST;

        // Initialize the crumbs
        $crumbs = [];

        // Retrieve the routes
        $routes = $this->Config->get('routes');

        // Load Plugins Routes
        $pluginsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
            $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
            if(is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')){
                foreach(json_decode(file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg'),true) as $route => $param){
                    if(!isset($routes[$route])){
                        $routes[$route] = $param;
                    }
                }
            }
        }

        // Retrieve the current url
        $url = $REQUEST->getUri();
        if($REQUEST->getQueryString()){
            $url .= '?' . $REQUEST->getQueryString();
        }

        // Create Base URL
        $base = '';
        $parts = explode('/', $url);
        foreach($parts as $part){
            $base = rtrim($base,'/') . '/' . rtrim($part,'/');
            $route = explode('?', $base)[0];
            if(isset($routes[$route])){
                $crumbs[$route] = $routes[$route];
                $crumbs[$route]['link'] = $base;
            }
        }

        return $crumbs;
    }

    /**
     * Generate the HTML tags for the CSS files
     */
    public function css()
    {
        $html = '';

        // Load Core CSS
        $path = $this->Config->root() . '/vendor/laswitchtech/core/assets/css';
        if(is_file($path.'.cfg')){
            if(is_dir($path)){
                $assets = json_decode(file_get_contents($path.'.cfg') ?? '[]',true);
                foreach($assets as $file){
                    if(is_file($path.'/'.$file)){
                        $html .= '<link rel="stylesheet" type="text/css" href="/assets/core/css/'.trim($file,'/').'">' . PHP_EOL;
                    }
                }
            }
        }

        // Load Themes Assets CSS
        $path = $this->Config->root() . '/lib/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $extension){
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            $html .= '<link rel="stylesheet" type="text/css" href="/assets/themes/'.$extension.'/assets/css/'.trim($asset,'/').'">' . PHP_EOL;
                        }
                    }
                }
            }
        }

        // Load Plugins Assets CSS
        $path = $this->Config->root() . '/lib/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $extension){
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            $html .= '<link rel="stylesheet" type="text/css" href="/assets/themes/'.$extension.'/assets/css/'.trim($asset,'/').'">' . PHP_EOL;
                        }
                    }
                }
            }
        }

        // Load Global CSS
        $css = $this->Config->get('css');
        foreach($css as $file){
            if(is_file($this->Config->root().'/webroot/assets/css/'.trim($file,'/'))){
                $html .= '<link rel="stylesheet" type="text/css" href="/assets/css/'.trim($file,'/').'">' . PHP_EOL;
            } elseif(is_file($this->Config->root().'/webroot/assets/js/'.trim($file,'/'))){
                $html .= '<link rel="stylesheet" type="text/css" href="/assets/js/'.trim($file,'/').'">' . PHP_EOL;
            }
        }

        // Load Theme CSS
        $path = $this->Config->root() . '/webroot/assets/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $file){
                $filePath = $file . '/styles.css';
                if(is_file($path.'/'.$filePath)){
                    if($this->Config->get('application','theme') == $file){
                        $html .= '<link rel="stylesheet" type="text/css" href="/assets/themes/'.trim($filePath,'/').'" data-theme="'.$file.'">' . PHP_EOL;
                    }
                }
            }
        }

        // Load Plugins CSS
        $path = $this->Config->root() . '/webroot/assets/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $file){
                $filePath = $file . '/styles.css';
                if(is_file($path.'/'.$filePath)){
                    $html .= '<link rel="stylesheet" type="text/css" href="/assets/plugins/'.trim($filePath,'/').'" data-plugin="'.$file.'">' . PHP_EOL;
                }
            }
        }

        // Load Less CSS
        $html .= '<link rel="stylesheet" href="/css">' . PHP_EOL;

        // Return the HTML
        return $html;
    }

    /**
     * Generate the HTML tags for the JS files
     */
    public function js()
    {
        $html = '';

        // Load Constants JS
        $html .= '<script>' . PHP_EOL;
        $html .= '' . PHP_EOL;
        $html .= '    // Global Variables' . PHP_EOL;
        $html .= '    const CSRF_KEY = "' . $this->CSRF->key() . '"' . PHP_EOL;
        $html .= '    var CSRF_TOKEN = "' . $this->CSRF->token() . '"' . PHP_EOL;
        $html .= '    const PUBLIC = ' . ($this->Config->get('application','public') ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const AUTHENTICATED = ' . ($this->Auth->isAuthenticated() ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const USER_ID = ' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->id : null) . ';' . PHP_EOL;
        $html .= '    const USER_USERNAME = "' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->username : '') . '";' . PHP_EOL;
        $html .= '    const USER_ORGANIZATION = ' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->organization()->id : 'null') . ';' . PHP_EOL;
        $html .= '    const USER_TOKEN = "' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->token() : '') . '";' . PHP_EOL;
        $html .= '    const USER_ROLES = ' . json_encode($this->Auth->isAuthenticated() ? $this->Auth->user()->roles() : null) . ';' . PHP_EOL;
        $html .= '    const DEV_MODE = ' . ($this->Auth->isAuthorized('Developer', 1) ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const ADMIN_MODE = ' . ($this->Auth->isAuthorized('Administrator', 1) ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '</script>' . PHP_EOL;

        // Load Core JS
        $path = $this->Config->root() . '/vendor/laswitchtech/core/assets/js';
        if(is_file($path.'.cfg')){
            if(is_dir($path)){
                $assets = json_decode(file_get_contents($path.'.cfg') ?? '[]',true);
                foreach($assets as $file){
                    if(is_file($path.'/'.$file)){
                        if(str_ends_with($file, '.mjs')){
                            $html .= '<script type="module" src="/assets/core/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                        } else {
                            $html .= '<script src="/assets/core/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                        }
                    }
                }
            }
        }

        // Load Themes Assets JS
        $path = $this->Config->root() . '/lib/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $extension){
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            if(str_ends_with($asset, '.mjs')){
                                $html .= '<script type="module" src="/assets/themes/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            } else {
                                $html .= '<script src="/assets/themes/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }

        // Load Plugins Assets JS
        $path = $this->Config->root() . '/lib/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $extension){
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            if(str_ends_with($asset, '.mjs')){
                                $html .= '<script type="module" src="/assets/plugins/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            } else {
                                $html .= '<script src="/assets/plugins/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }

        // Load Global JS
        $js = $this->Config->get('js');
        foreach($js as $file){
            if(is_file($this->Config->root().'/webroot/assets/js/'.trim($file,'/'))){
                // Check if the file is a module by looking for the file extension
                if(str_ends_with($file, '.mjs')){
                    $html .= '<script type="module" src="/assets/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                } else {
                    $html .= '<script src="/assets/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                }
            }
        }

        // Load Themes JS
        $path = $this->Config->root() . '/lib/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $file){
                $filePath = $file . '/library.js';
                if(is_file($path.'/'.$filePath)){
                    if($this->Config->get('application','theme') == $file){
                        $html .= '<script src="/assets/themes/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                    }
                }
            }
            foreach($themes as $file){
                $filePath = $file . '/script.js';
                if(is_file($path.'/'.$filePath)){
                    if($this->Config->get('application','theme') == $file){
                        $html .= '<script src="/assets/themes/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                    }
                }
            }
        }

        // Load Plugins JS
        $path = $this->Config->root() . '/lib/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $file){
                $filePath = $file . '/library.js';
                if(is_file($path.'/'.$filePath)){
                    $html .= '<script src="/assets/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                }
            }
            foreach($plugins as $file){
                $filePath = $file . '/script.js';
                if(is_file($path.'/'.$filePath)){
                    $html .= '<script src="/assets/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                }
            }
        }

        // Return the HTML
        return $html;
    }

    /**
     * Get the logo path
     *
     * @return string
     */
    public function logo()
    {
        $src = '/assets/img/logo.svg';
        if(!is_file($this->Config->root() . '/webroot/' . $src)){
            $src = '/assets/img/logo.jpg';
        }
        if(!is_file($this->Config->root() . '/webroot/' . $src)){
            $src = '/assets/img/logo.gif';
        }
        if(!is_file($this->Config->root() . '/webroot/' . $src)){
            $src = '/assets/img/logo.webp';
        }
        if(!is_file($this->Config->root() . '/webroot/' . $src)){
            $src = '/assets/img/logo.png';
        }
        return $src;
    }
}
