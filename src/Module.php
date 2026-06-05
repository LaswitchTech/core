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
     * Magic method for undefined methods.
     *
     * Emits a warning but does NOT exit — callers should check return values.
     * This is intentional: Module is a generic container, not a final authority.
     */
    public function __call($name, $arguments){

        // Emit a warning message and log it
        $message = "Warning: Method " . $name . " is not available. The class/module is not installed." . PHP_EOL;
        trigger_error($message, E_USER_WARNING);
        error_log(trim($message));

        // Return false (safe fallback) instead of exiting
        return false;
    }
}
