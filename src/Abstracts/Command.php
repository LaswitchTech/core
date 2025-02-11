<?php

/**
 * Core Framework - Command
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Command {

    // Global Properties
    protected $Model;
    protected $Helper;
    protected $Output;
    protected $Request;

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $MODEL, $HELPER, $OUTPUT, $REQUEST;

        // Initialize Properties
        $this->Model = $MODEL;
        $this->Helper = $HELPER;
        $this->Output = $OUTPUT;
        $this->Request = $REQUEST;
    }

    /**
     * Magic Method to catch all undefined methods
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call($name, $arguments) {

        // Output Usage
        $this->Output->help();
    }
}
