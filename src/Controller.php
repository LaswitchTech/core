<?php

/**
 * Core Framework - Controller
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Controller {

    // Global Properties
    protected $Auth;
    protected $Input;
    protected $Output;
    protected $Request;

    // Auth Properties
    protected $Public = true; // Control if authentication is required
    protected $Permission = false; // Control if the method requires a permission
    protected $Level = 1; // Control the permission level required
    protected $Namespace = "Namespace>"; // Contains the namespace of the method
    protected $Access = 1; // Control the permission level required

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $AUTH, $INPUT, $OUTPUT, $REQUEST;

        // Initialize Properties
        $this->Auth = $AUTH;
        $this->Input = $INPUT;
        $this->Output = $OUTPUT;
        $this->Request = $REQUEST;

        // Add URI segments to the namespace
        foreach($this->Request->getUriSegments() as $Segment){
            $this->Namespace .= "/{$Segment}";
        }

        // Check if the controller is public
        if($this->Auth){
            if(!$this->Public){

                // Check if the user is authenticated
                if(!$this->Auth->Authentication->isAuthenticated()){

                    // Send the output
                    $this->output('Unauthorized', array('HTTP/1.1 401 Unauthorized'));
                }

                // Check if the method requires a permission and if the user has the required permission
                if($this->Permission && !$this->Auth->Authorization->hasPermission($this->Namespace,$this->Level)){

                    // Send the output
                    $this->output('Forbidden', array('HTTP/1.1 403 Forbidden'));
                }
            }
        }
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
