<?php

/**
 * Core Framework - CLI
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class CLI {

    // Global Properties
    protected $Output;
    protected $Request;
	protected $Config;

    // Properties
    protected $Arguments;
    protected $Class;
    protected $Path;
    protected $Command;
    protected $Action;

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $OUTPUT, $REQUEST, $CONFIG;

        // Initialize Properties
        $this->Output = $OUTPUT;
        $this->Request = $REQUEST;
        $this->Config = $CONFIG;

        // Retrieve Arguments
        $this->Arguments = $this->Request->getArguments();
    }

    /**
     * Run the CLI
     * @return void
     */
    public function run(){

        // Parse Standard Input
        if(count($this->Arguments) > 0){

            // Remove the first argument
            unset($this->Arguments[0]);

            // Identify the Command File
            if(count($this->Arguments) > 0){

                // Identify the Command
                $this->Command = ucfirst($this->Arguments[1] . "Command");
                unset($this->Arguments[1]);

                // Set Path
                $this->Path = $this->Config->root() . "/Command/" . $this->Command . ".php";

                // Check if the file exists
                if(!is_file($this->Path)){

                    // Set Plugin Path
                    $this->Path = $this->Config->root() . "/lib/plugins/" . strtolower(str_replace('Command','',$this->Command)) . "/Command.php";
                }

                // Check if the required command is available
                if(is_file($this->Path)){

                    // Load Command File
                    require_once $this->Path;

                    // Check if command class exist
                    if(class_exists($this->Command)){

                        // Create Command
                        $this->Class = new $this->Command();

                        // Identify the Action
                        if(count($this->Arguments) > 0){

                            // Identify the Action
                            $this->Action = $this->Arguments[2] . "Action";
                            unset($this->Arguments[2]);

                            // Check if the required action is available
                            if(method_exists($this->Class, $this->Action)){

                                // Execute Action
                                $this->Class->{$this->Action}();
                            } else {

                                // Display help
                                $this->Output->help("The action " . strtolower(str_replace('Action','',$this->Action)) . " is not available");
                            }
                        } else {

                            // Display help
                            $this->Output->help();
                        }
                    } else {

                        // Display help
                        $this->Output->help("Could not initialize the command " . strtolower(str_replace('Command','',$this->Command)));
                    }
                } else {

                    // Display help
                    $this->Output->help("The command " . strtolower(str_replace('Command','',$this->Command)) . " is not available");
                }
            } else {

                // Display help
                $this->Output->help();
            }
        } else {

            // Display help
            $this->Output->help("Internal Error");
        }
    }
}
