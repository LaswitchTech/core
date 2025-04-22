<?php

/**
 * Core Framework - Builder
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Builder {

    // Constants

    // Global Properties
    private $Config;

    // Properties

    /**
     * Constructor
     */
    public function __construct()
    {

        // Global Variables
        global $CONFIG;

        // Set Global Properties
        $this->Config = $CONFIG;

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
        global $AUTH;
        $menu = [];
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

        foreach($routes as $route => $param) {
            if($parent){
                if(!isset($param['parent']) || $param['parent'] !== $parent) continue;
            }
            if(!isset($param['location'])) continue;
            if(is_string($param['location']) && $param['location'] !== $location) continue;
            if(is_array($param['location']) && !in_array($location,$param['location'])) continue;
            if(!$param['public'] && !$AUTH->isAuthenticated()) continue;
            if(!$param['public'] && !$AUTH->isAuthorized("Route>" . $route, $param['level'])) continue;

            $parts = array_filter(explode('/', $route));
            if(empty($parts)) $parts = [""];

            $param['items'] = [];
            $param['link'] = $route;

            if($param['parent']){
                if(is_array($param['parent'])){
                    foreach($param['parent'] as $parent){
                        if(!array_key_exists($parent, $menu)){
                            $menu[$parent] = [];
                        }
                        if(!array_key_exists('items', $menu[$parent])){
                            $menu[$parent]['items'] = [];
                        }
                        $menu[$parent]['items'][$route] = $param;
                    }
                } else {
                    if(!array_key_exists($param['parent'], $menu)){
                        $menu[$param['parent']] = [];
                    }
                    if(!array_key_exists('items', $menu[$param['parent']])){
                        $menu[$param['parent']]['items'] = [];
                    }
                    $menu[$param['parent']]['items'][$route] = $param;
                }
            } else {
                if(isset($menu[$route])){
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
        $css = $this->Config->get('css');
        foreach($css as $file){
            if(is_file($this->Config->root().'/webroot/'.trim($file,'/'))){
                $html .= '<link rel="stylesheet" type="text/css" href="/'.trim($file,'/').'">' . PHP_EOL;
            }
        }
        $path = $this->Config->root() . '/lib/themes';
        $themes = array_diff(scandir($path), array('..', '.'));
        foreach($themes as $file){
            $filePath = $file . '/styles.css';
            if(is_file($path.'/'.$filePath)){
                if($this->Config->get('application','theme') == $file){
                    $html .= '<link rel="stylesheet" type="text/css" href="/themes/'.trim($filePath,'/').'" data-theme="'.$file.'">' . PHP_EOL;
                } else {
                    $html .= '<link rel="stylesheet" type="text/css" href="/themes/'.trim($filePath,'/').'" data-theme="'.$file.'" disabled="">' . PHP_EOL;
                }
            }
        }
        $path = $this->Config->root() . '/lib/plugins';
        $plugins = array_diff(scandir($path), array('..', '.'));
        foreach($plugins as $file){
            $filePath = $file . '/style.css';
            if(is_file($path.'/'.$filePath)){
                $html .= '<link rel="stylesheet" type="text/css" href="/plugins/'.trim($filePath,'/').'" data-plugin="'.$file.'">' . PHP_EOL;
            }
        }
        return $html;
    }

    /**
     * Generate the HTML tags for the JS files
     */
    public function js()
    {
        $html = '';
        $js = $this->Config->get('js');
        foreach($js as $file){
            if(is_file($this->Config->root().'/dist/'.trim($file,'/'))){
                // Check if the file is a module by looking for the file extension
                if(str_ends_with($file, '.mjs')){
                    $html .= '<script type="module" src="/'.trim($file,'/').'"></script>' . PHP_EOL;
                } else {
                    $html .= '<script src="/'.trim($file,'/').'"></script>' . PHP_EOL;
                }
            }
        }
        $path = $this->Config->root() . '/lib/plugins';
        $plugins = array_diff(scandir($path), array('..', '.'));
        foreach($plugins as $file){
            $filePath = $file . '/library.js';
            if(is_file($path.'/'.$filePath)){
                $html .= '<script src="/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
            }
        }
        foreach($plugins as $file){
            $filePath = $file . '/script.js';
            if(is_file($path.'/'.$filePath)){
                $html .= '<script src="/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
            }
        }
        return $html;
    }
}
