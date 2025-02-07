<?php

/**
 * Core Framework - DebugEndpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Endpoint;

class DebugEndpoint extends Endpoint {

    /**
     * Constructor
     */
    public function __construct(){

        // Call Parent Constructor
        parent::__construct();

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Set Properties
        switch($namespace){
            case "/debug/execute":
                $this->Public = true;
                $this->Level = 1;
                break;
        }
    }

    /**
     * Execute the command
     */
    public function executeAction(){
        $this->Output->print(["status" => 200, "message" => "Debugging...", "data" => "Lorem Ipsum"], array('HTTP/1.1 200 OK'));
    }
}
