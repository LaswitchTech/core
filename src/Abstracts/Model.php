<?php

/**
 * Core Framework - Model
 *
 * @author     LaswitchTech <support@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Model {

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
