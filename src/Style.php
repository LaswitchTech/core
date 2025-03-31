<?php

/**
 * Core Framework - Style
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Style {

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
        $this->Config->add('css')->add('style');
    }
}
