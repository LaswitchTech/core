<?php

/**
 * PHPUnit bootstrap
 *
 * Loads the Composer autoloader. Minimal globals for unit tests.
 */

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define root path for testing
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
