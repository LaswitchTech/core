<?php

/**
 * Core Framework - Endpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Endpoint {

    // Global Properties
    protected $Auth;
    protected $Model;
    protected $Helper;
    protected $Output;
    protected $Request;
    protected $Config;

    // Auth Properties
    protected $Level;
    protected $Public;

    /**
     * Constructor
     */
    public function __construct()
    {

        // Import Global Variables
        global $AUTH, $MODEL, $HELPER, $OUTPUT, $REQUEST, $CONFIG;

        // Initialize Properties
        $this->Auth = $AUTH;
        $this->Model = $MODEL;
        $this->Helper = $HELPER;
        $this->Output = $OUTPUT;
        $this->Request = $REQUEST;
        $this->Config = $CONFIG;
    }

    /**
     * Magic Method to catch all undefined methods
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {

        // Send the output
        $this->Output->print('Endpoint Action['.$name.'] not Implemented', array('HTTP/1.1 501 Not Implemented'));
    }

    /**
     * Get the public status
     */
    public function getPublic()
    {
        return $this->Public;
    }

    /**
     * Get the level
     */
    public function getLevel()
    {
        return $this->Level;
    }
}
