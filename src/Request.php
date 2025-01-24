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
use Exception;

class Request {

    // Properties
    protected $Error = null;
    protected $QueryString = null;
    protected $GET = null;
    protected $POST = null;
    protected $REQUEST = null;

    /**
     * Constructor
     */
    public function __construct(){}

    /**
     * Get the URI segments
     * @return array
     */
    public function getUriSegments() {

        // Get the URI segments
        $URI = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

        // Convert the URI to an array
        $URI = explode( '/', $URI );

        // Remove the first two segments
        array_shift($URI);
        array_shift($URI);

        // Return the URI segments
        return $URI;
    }

    /**
     * Get the request method
     * @return string
     */
    public function getMethod() {

        // Return the request method
        return $_SERVER["REQUEST_METHOD"] ?? 'GET';
    }
}
