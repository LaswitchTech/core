<?php

/**
 * Core Framework - Models
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Models {

    // Properties
    private $Path = null;
    private array $Models = []; // store them here

    public function __construct() {

        // Import Global Variables
        global $CONFIG;

        // Set Path
        $this->Path = $CONFIG->root() . "/Model";

        // Check if the Model directory exists
        if(is_dir($this->Path)){

            // Loop through all the files in the directory
            foreach(scandir($this->Path) as $model){

                // Check if the file is a Model
                if (!preg_match('/(.+)Model\.php$/i', $model, $matches)) {
                    continue;
                }

                // Include the Model
                require_once $this->Path . "/" . $model;

                // Get the Model Base Name and Class Name
                $baseName = $matches[1];
                $className      = $baseName . 'Model';

                // Check if the class exists
                if (class_exists($className)) {

                    // Create the Model
                    $this->Models[$baseName] = new $className();
                }
            }
        }

        // Set Path
        $this->Path = $CONFIG->root() . "/lib/plugins";

        // Check if the plugins directory exists
        if(is_dir($this->Path)){

            // Loop through all the files in the directory
            foreach(array_diff(scandir($this->Path), array('..', '.')) as $plugin){

                // Check if a Model already exist
                if(!isset($this->Models[ucfirst($plugin)])){

                    // Set Plugin path
                    $path = $this->Path . "/" . $plugin . "/Model.php";

                    // Check if the plugin includes a Model
                    if(is_file($path)){

                        // Get the Model Base Name and Class Name
                        $baseName = ucfirst($plugin);
                        $className = $baseName . 'Model';

                        // Check if the class exists
                        if (class_exists($className)) {

                            // Create the Model
                            $this->Models[$baseName] = new $className();
                        }
                    }
                }
            }
        }
    }

    // Magic getter to retrieve a Model
    public function __get($name) {
        return $this->Models[$name] ?? null;
    }

    // Magic setter to add a Model
    public function __set($name, $value) {
        $this->Models[$name] = $value;
    }
}
