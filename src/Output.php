<?php

/**
 * Core Framework - Output
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Output {

    // Properties
    protected $Colors = [
        "default" => "\033[39m",
        "black" => "\033[30m",
        "red" => "\033[31m",
        "green" => "\033[32m",
        "yellow" => "\033[33m",
        "blue" => "\033[34m",
        "magenta" => "\033[35m",
        "cyan" => "\033[36m",
        "light-gray" => "\033[37m",
        "dark-gray" => "\033[90m",
        "light-red" => "\033[91m",
        "light-green" => "\033[92m",
        "light-yellow" => "\033[93m",
        "light-blue" => "\033[94m",
        "light-magenta" => "\033[95m",
        "light-cyan" => "\033[96m",
        "white" => "\033[97m",
    ];

    /**
     * Constructor
     */
    public function __construct(){}

    /**
     * Output a string
     * @param string $string
     * @return void
     */
    public function print($string, $httpHeaders=array()) {

        // Check if the script is running in CLI mode
        if(defined('STDIN')){

            // Print to the console
            print_r($string . PHP_EOL);
        } else {

            // Check if header information can be sent
            if (!headers_sent()) {

                // Remove the default Set-Cookie header
                header_remove('Set-Cookie');

                // Add the custom headers
                if (is_array($httpHeaders) && count($httpHeaders)) {

                    // Add the headers
                    foreach ($httpHeaders as $httpHeader) {

                        // Add the header
                        header($httpHeader);
                    }
                }

                // Check if the string is an array or object
                if(is_array($string) || is_object($string)){

                    // Convert the string to JSON
                    $string = json_encode($string,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                }

                // Send the output
                echo $string;

                // Exit the script
                exit;
            }
        }
    }

    /**
     * Output a string with color
     * @param string $string
     * @param string $color
     * @return void
     */
    public function set($string, $color = 'default'){
        if(defined('STDIN') && isset($this->Colors[$color])){
            return $this->Colors[$color] . $string . $this->Colors['default'];
        } else {
            return $string;
        }
    }

    /**
     * Output a string with red color
     * @param string $string
     * @return void
     */
    public function error($string) {
        $this->print($this->set($string, 'red'));
    }

    /**
     * Output a string with green color
     * @param string $string
     * @return void
     */
    public function success($string) {
        $this->print($this->set($string, 'green'));
    }

    /**
     * Output a string with yellow color
     * @param string $string
     * @return void
     */
    public function warning($string) {
        $this->print($this->set($string, 'yellow'));
    }

    /**
     * Output a string with blue color
     * @param string $string
     * @return void
     */
    public function info($string) {
        $this->print($this->set($string, 'cyan'));
    }

    /**
     * Output command-line help
     * @param string $string
     * @return void
     */
    public function help($string = null) {

        // Check if the script is running in CLI mode
        if(!defined('STDIN')){ return; }

        // Retrieve Global Variables
        global $REQUEST, $CONFIG;

        // Retrieve the arguments
        $arguments = $REQUEST->getArguments();

        // Initialize Variables
        $file = $arguments[0] ?? null;
        $command = $arguments[1] ?? null;
        $action = $arguments[2] ?? null;

        // Check if a string is provided
        if($string){ $this->print($string); }

        // Check if the command is valid
        if($command && !is_file($CONFIG->root() . "/Command/" . ucfirst($command . "Command" . ".php"))){
            $command = null;
        }
        if($command && !class_exists(ucfirst($command) . "Command")){
            $command = null;
        }

        // Check if the command is valid
        if($command){

            // Initialize the class
            $class = ucfirst($command) . "Command";
            $class = new $class();

            // Check if the action is valid
            if(!method_exists($class, $action . "Action")){
                $action = null;
            }

            // Output Usage
            $this->print("Usage: $file $command [action] [options]");

            // List available actions
            $this->print("Available Actions:");
            foreach(get_class_methods($class) as $method){
                if(substr($method,-6) == 'Action'){
                    $this->print(" - " . strtolower(str_replace('Action','',$method)));
                }
            }
        } else {

            // Output Usage
            $this->print("Usage: $file [command] [action] [options]");

            // List available commands
            $this->print("Available Commands:");
            foreach(scandir($CONFIG->root() . "/Command/") as $command){
                if(str_contains($command, 'Command.php')){
                    $this->print(" - " . strtolower(str_replace('Command.php','',$command)));
                }
            }
        }

        // Stop execution and Exit the script
        exit();
    }
}
