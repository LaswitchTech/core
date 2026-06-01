<?php

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Helper {

    // Core Modules
    protected $Config;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $CONFIG;

        // Initialize properties
        $this->Config = $CONFIG;
    }
}
