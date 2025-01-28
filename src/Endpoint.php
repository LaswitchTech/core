<?php

/**
 * Core Framework - Endpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Endpoint {

    // Global Properties
    protected $Auth;
    protected $Output;
    protected $Request;

    // Properties
    protected $Namespace = null; // Contains the namespace of the method

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $AUTH, $OUTPUT, $REQUEST;

        // Initialize Properties
        $this->Auth = $AUTH;
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

        // Send the output
        $this->Output->print('Endpoint "'.str_replace("Namespace>","",$this->Namespace).'" not Implemented', array('HTTP/1.1 501 Not Implemented'));
    }
}
