<?php

/**
 * Core Framework - Command
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Output;
use Exception;

class Command {

    // Global Properties
    protected $Input;
    protected $Output;

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $INPUT, $OUTPUT;

        // Initialize Properties
        $this->Input = $INPUT;
        $this->Output = $OUTPUT;
    }

    /**
     * Magic Method to catch all undefined methods
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call($name, $arguments) {

        // Output Usage
        $this->Output->print("Usage: ./cli " . strtolower(str_replace('Command','',__CLASS__)) . " " . $name . " [options]");

        // List available methods that end with 'Action'
        $this->Output->print("Available Methods:");
        foreach(get_class_methods($this) as $method){
            if(substr($method,-6) == 'Action'){
                $this->Output->print(" - " . str_replace('Action','',$method));
            }
        }
    }
}
