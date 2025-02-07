<?php

/**
 * Core Framework - Module
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Module {

    /**
     * Call
     */
    public function __call($name, $arguments){

        // Set a warning message to notify the user that the method is not available and that the class/module is not installed
        $message = "Warning: Method " . $name . " is not available. The class/module is not installed." . PHP_EOL;

        // Output the warning message
        echo $message;

        // Log the warning message
        error_log($message);

        // Exit the script and stop the execution
        exit;
    }
}
