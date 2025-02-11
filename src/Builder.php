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
        $this->Config->add('css')->add('js');
    }

    // /**
    //  * Get a menu
    //  *
    //  * @param string $location
    //  * @param string $parent
    //  * @return array
    //  */
    // protected function menu($location = 'sidebar', $parent = null) {
    //     $menu = [];
    //     foreach($this->getRoutes() as $route => $param) {
    //         if($parent){
    //             if(!isset($param['parent']) || $param['parent'] !== $parent) continue;
    //         }
    //         if(!isset($param['location'])) continue;
    //         if(is_string($param['location']) && $param['location'] !== $location) continue;
    //         if(is_array($param['location']) && !in_array($location,$param['location'])) continue;
    //         if(!$param['public'] && !$this->isAuthenticated()) continue;
    //         if(!$param['public'] && $param['permission'] && !$this->hasPermission("Route>" . $route, $param['level'])) continue;

    //         $parts = array_filter(explode('/', $route));
    //         if(empty($parts)) $parts = [""];

    //         $current = &$menu;
    //         $accumulated_route = "";
    //         foreach($parts as $part) {
    //             $accumulated_route .= "/$part";

    //             // Create intermediate nodes with default parameters if they don't exist
    //             if(!isset($current[$part])) {
    //                 $current[$part] = ['label' => ucfirst($part), 'icon' => 'default-icon', 'link' => $accumulated_route, 'items' => []];
    //             }

    //             // If we're at the last part of the route, override the parameters with the ones provided in $param
    //             if ($part === end($parts)) {
    //                 $current[$part]['label'] = $param['label'];
    //                 $current[$part]['icon'] = $param['icon'];
    //                 $current[$part]['color'] = $param['color'];
    //                 $current[$part]['parent'] = $param['parent'];
    //                 $current[$part]['view'] = $param['view'];
    //                 $current[$part]['link'] = $route;
    //             }

    //             $current = &$current[$part]['items'];
    //         }
    //     }

    //     return $menu;
    // }

    // /**
    //  * Generate the HTML tags for the CSS files
    //  */
    // protected function css(){
    //     $html = '';
    //     $css = $this->Config->get('css');
    //     foreach($css as $file){
    //         if(is_file($this->Config->root().'/webroot/'.trim($file,'/'))){
    //             $html .= '<link rel="stylesheet" type="text/css" href="/'.trim($file,'/').'">' . PHP_EOL;
    //         }
    //     }
    //     return $html;
    // }

    // /**
    //  * Generate the HTML tags for the JS files
    //  */
    // protected function js(){
    //     $html = '';
    //     $js = $this->Config->get('js');
    //     foreach($js as $file){
    //         if(is_file($this->Config->root().'/webroot/'.trim($file,'/'))){
    //             // Check if the file is a module by looking for the file extension
    //             if(str_ends_with($file, '.mjs')){
    //                 $html .= '<script type="module" src="/'.trim($file,'/').'"></script>' . PHP_EOL;
    //             } else {
    //                 $html .= '<script src="/'.trim($file,'/').'"></script>' . PHP_EOL;
    //             }
    //         }
    //     }
    //     return $html;
    // }
}
