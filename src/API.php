<?php

/**
 * Core Framework - API
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class API {

    // Global Properties
    protected $Output;
    protected $Request;
	protected $Config;
	protected $Auth;

    /**
     * Constructor
     */
    public function __construct() {

        // Import Global Variables
        global $OUTPUT, $REQUEST, $CONFIG, $AUTH;

        // Initialize Properties
        $this->Output = $OUTPUT;
        $this->Request = $REQUEST;
        $this->Config = $CONFIG;
        $this->Auth = $AUTH;
    }

    /**
     * Execute the API Request
     */
    public function request(){

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Parse the namespace
        $parts = explode("/", trim($namespace, "/"));

        // Check if the namespace is valid
        if(count($parts) > 1){

            // Set the endpoint and method
            $endpoint = $parts[0];
            $class = ucfirst($endpoint) . "Endpoint";
            $method = $parts[1] . 'Action';

            // Set the endpoint path
            $path = $this->Config->root() . "/Endpoint/" . ucfirst($endpoint) . "Endpoint.php";

            // Check if the endpoint exists
            if(!is_file($path)){

                // Set the path to the plugin path
                $path = $this->Config->root() . "/lib/plugins/" . $endpoint . "/Endpoint.php";
            }

            // Check if the endpoint exists
            if(is_file($path)){

                // Load the endpoint
                require_once $path;

                // Check if the class exists
                if(class_exists($class)){

                    // Initialize the endpoint
                    $object = new $class();

                    // Check if the method exists
                    if(method_exists($object, $method)){

                        // Retrieve Auth Properties
                        $public = $object->getPublic();
                        $level = $object->getLevel();
                        $permission = "Endpoint>" . $namespace;

                        // Check if Auth is available and if the endpoint is public
                        if(get_class($this->Auth) !== "Strap" && !$public){

                            // Check if the user is authenticated
                            if(!$this->Auth->isAuthenticated()){

                                // Send unauthorized
                                $this->Output->print('Unauthorized', array('HTTP/1.1 401 Unauthorized'));
                            }

                            // Check if the user has the required permission
                            if(!$this->Auth->hasPermission($permission, $level)){

                                // Send forbidden
                                $this->Output->print('Forbidden', array('HTTP/1.1 403 Forbidden'));
                            }
                        }

                        // Call the method
                        $results = $object->{$method}();

                        // Send the output
                        $this->Output->print($results['data'] ?? [], array('HTTP/1.1 ' . $results['status'] ?? 500 . ' '. $results['message'] ?? 'Internal Server Error'));
                    } else {

                        // Could not find the method, send not implemented
                        $this->Output->print('Could not find the method', array('HTTP/1.1 501 Not Implemented'));
                    }
                } else {

                    // Could not find the class, send not implemented
                    $this->Output->print('Could not find the endpoint', array('HTTP/1.1 501 Not Implemented'));
                }
            } else {

                // Could not find the endpoint
                $this->Output->print('Could not find the endpoint', array('HTTP/1.1 404 Not Found'));
            }
        } else {

            // Could not identify the Controller and/or Method, send bad request
            $this->Output->print('Could not identify the Controller and/or Action', array('HTTP/1.1 400 Bad Request'));
        }
    }
}
