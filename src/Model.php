<?php

/**
 * Core Framework - Model
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Model {

    // Properties
    protected $Database;

    /**
     * Constructor
     */
    public function __construct(){

        // Retrieve the global Database
        global $DATABASE;

        // Initialize Database
        $this->Database = $DATABASE;
    }
}
