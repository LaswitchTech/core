<?php

/**
 * Core Framework - Helpers
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Helpers {

    // Properties
    protected $Path = null;
    protected array $Helpers = []; // store them here

    public function __construct() {

        // Import Global Variables
        global $CONFIG;

        // Set Path
        $this->Path = $CONFIG->root() . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "laswitchtech" . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "Helper";

        // Check if the Helper directory exists
        if(is_dir($this->Path)){

            // Loop through all the files in the directory
            foreach(scandir($this->Path) as $helper){

                // Check if the file is a Helper
                if (!preg_match('/(.+)Helper\.php$/i', $helper, $matches)) {
                    continue;
                }

                // Get the Helper Base Name and Class Name
                $baseName = $matches[1];
                $className = $baseName . 'Helper';

                // Check if a Helper already exist
                if(!isset($this->Helpers[$baseName])){

                    // Include the Helper
                    require_once $this->Path . "/" . $helper;

                    // Check if the class exists
                    if (class_exists($className)) {

                        // Create the Helper
                        $this->Helpers[$baseName] = new $className();
                    }
                }
            }
        }

        // Set Path
        $this->Path = $CONFIG->root() . "/Helper";

        // Check if the Helper directory exists
        if(is_dir($this->Path)){

            // Loop through all the files in the directory
            foreach(scandir($this->Path) as $helper){

                // Check if the file is a Helper
                if (!preg_match('/(.+)Helper\.php$/i', $helper, $matches)) {
                    continue;
                }

                // Get the Helper Base Name and Class Name
                $baseName = $matches[1];
                $className = $baseName . 'Helper';

                // Check if a Helper already exist
                if(!isset($this->Helpers[$baseName])){

                    // Include the Helper
                    require_once $this->Path . "/" . $helper;

                    // Check if the class exists
                    if (class_exists($className)) {

                        // Create the Helper
                        $this->Helpers[$baseName] = new $className();
                    }
                }
            }
        }

        // Set Path
        $this->Path = $CONFIG->root() . "/lib/plugins";

        // Check if the plugins directory exists
        if(is_dir($this->Path)){

            // Loop through all the files in the directory
            foreach(scandir($this->Path) as $plugin){

                // Check if a Helper already exist
                if(!isset($this->Helpers[ucfirst($plugin)])){

                    // Set Plugin path
                    $path = $this->Path . "/" . $plugin . "/Helper.php";

                    // Check if the plugin includes a Helper
                    if(is_file($path)){

                        // Include the Helper
                        require_once $path;

                        // Get the Helper Base Name and Class Name
                        $baseName = ucfirst($plugin);
                        $className = $baseName . 'Helper';

                        // Check if the class exists
                        if (class_exists($className)) {

                            // Create the Helper
                            $this->Helpers[$baseName] = new $className();
                        }
                    }
                }
            }
        }
    }

    // Magic getter to retrieve a Helper
    public function __get($name) {
        return $this->Helpers[$name] ?? null;
    }

    // Magic setter to add a Helper
    public function __set($name, $value) {
        $this->Helpers[$name] = $value;
    }
}
