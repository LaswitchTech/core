<?php

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Models {

    // Properties
    private array $Models = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $CONFIG;

        // Scan the Core Model directory
        $this->scan($CONFIG->root() . "/vendor/laswitchtech/core/Model");

        // Scan the App Model directory
        $this->scan($CONFIG->root() . "/Model");

        // Scan the Plugin's Model(s) directory
        $this->scan($CONFIG->root() . "/lib/plugins", true);
    }

    // Magic getter to retrieve a Model
    public function __get($name): ?object
    {
        return $this->Models[$name] ?? null;
    }

    // Magic isset to check if a Model exists
    public function __isset($name): bool
    {
        return isset($this->Models[$name]);
    }

    /*
     * Scan a directory for Models
     *
     * @param string $path The full path to the directory
     * @return void
     */
    protected function scan(string $path, bool $recursive = false): void
    {
        // Check if the path is a directory
        if(!is_dir($path)) return;

        // Loop through all the files in the directory
        foreach(array_diff(scandir($path) ?: [], array('..', '.', '.DS_Store')) as $model){

            // Create the model if possible
            $this->create($model, $path . "/" . $model);

            // Check if recursive is enabled and if the path is a directory
            if($recursive && is_dir($path . "/" . $model)){
                $this->scan($path . "/" . $model . "/Model");
            }
        }
    }

    /*
     * Create a Model dynamically
     *
     * @param string $base The base name of the Model (without 'Model' suffix)
     * @param string $path The full path to the Model file
     * @return void
     */
    protected function create(string $base, string $path): void
    {
        // Check if the path is a directory
        if(is_dir($path)) $path .= "/Model.php";

        // Only process *Model.php files
        if(!preg_match('/Model\.php$/i', $path)) return;

        // Check if the path is a file
        if(!is_file($path)) return;

        // Get the Model Base Name and Class Name
        $baseName = ucfirst(trim(str_replace(['Model','.php','-','_'],'',$base)));
        $className = $baseName . 'Model';

        // Check if a Model already exist
        if(isset($this->Models[$baseName])) return;

        // Load the path
        require_once $path;

        // Check if the class exists
        if(!class_exists($className, false)) return;

        // Create the Model
        $this->Models[$baseName] = new $className();
    }
}
