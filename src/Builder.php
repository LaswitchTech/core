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
        foreach($this->Config->get('routes') as $route => $param) {
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

            if($param['parent'] && isset($menu[$param['parent']])){
                $menu[$param['parent']]['items'][$route] = $param;
            } else {
                $menu[$route] = $param;
            }
        }

        return $menu;
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
            $filePath = $file . '/script.js';
            if(is_file($path.'/'.$filePath)){
                $html .= '<script src="/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
            }
        }
        return $html;
    }
}
