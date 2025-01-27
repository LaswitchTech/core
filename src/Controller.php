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
    protected $Output;
    protected $Request;

    // // Auth Properties
    // protected $Public = true; // Control if authentication is required
    // protected $Permission = false; // Control if the method requires a permission
    // protected $Level = 1; // Control the permission level required

    // Properties
    protected $Namespace = null; // Contains the namespace of the method

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $AUTH, $OUTPUT, $REQUEST;

        // Initialize Properties
        $this->Auth = $AUTH;
        $this->Output = $OUTPUT;
        $this->Request = $REQUEST;

        // // Check if the controller is public
        // if($this->Auth){
        //     if(!$this->Public){

        //         // Check if the user is authenticated
        //         if(!$this->Auth->Authentication->isAuthenticated()){

        //             // Send the output
        //             $this->output('Unauthorized', array('HTTP/1.1 401 Unauthorized'));
        //         }

        //         // Check if the method requires a permission and if the user has the required permission
        //         if($this->Permission && !$this->Auth->Authorization->hasPermission($this->Namespace,$this->Level)){

        //             // Send the output
        //             $this->output('Forbidden', array('HTTP/1.1 403 Forbidden'));
        //         }
        //     }
        // }
    }

    // /**
    //  * Return the Public property
    //  */
    // public function getPublic(){
    //     return $this->Public;
    // }

    // /**
    //  * Return the Permission property
    //  */
    // public function getPermission(){
    //     return $this->Permission;
    // }

    // /**
    //  * Return the Level property
    //  */
    // public function getLevel(){
    //     return $this->Level;
    // }

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
