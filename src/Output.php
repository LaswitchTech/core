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
    public function print($string, $httpHeaders=array())
    {

        // Check if the script is running in CLI mode
        if(defined('STDIN')){

            // Print to the console
            print_r($string . PHP_EOL);
        } else {

            // Check if the string is an array or object
            if(is_array($string) || is_object($string)){

                // Convert the string to JSON
                $string = json_encode($string,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Send the output
            echo $string;

            // Check if headers are present
            if(!empty($httpHeaders)){

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

                    // Exit the script
                    exit;
                }
            }
        }
    }

    /**
     * Print the backtrace result from debug_backtrace() in a readable format
     *
     * @param array $array
     * @return void
     */
    public function trace($array,$error=false)
    {
        $string = "<strong>Stack Trace</strong>:".PHP_EOL;
        foreach($array as $key => $value){
            $file = $value['file'] ?? null;
            $line = $value['line'] ?? null;
            $in = "{$file}({$line})";
            $class = $value['class'] ?? null;
            $type = $value['type'] ?? null;
            $function = $value['function'] ?? null;
            $by = $class ? "{$class}{$type}{$function}" : $function;
            $args = '';
            foreach($value['args'] as $arg){
                $args .= gettype($arg);
                switch(gettype($arg)){
                    case 'string':
                        $args .= ' "'.$arg.'"';
                        break;
                    case 'array':
                        $args .= "(Array)";
                        break;
                    case 'object':
                        $args .= "(Object)";
                        break;
                    default:
                        $args .= "{$arg}";
                        break;
                }
                $args .= ', ';
            }
            $args = trim($args, ', ');
            $string .= "  <strong>#$key</strong> {$in}: {$by}({$args})".PHP_EOL;
        }
        $trace = debug_backtrace()[0];
        $file = $trace['file'] ?? null;
        $line = $trace['line'] ?? null;
        $string .= "    <strong>thrown in</strong> {$file} on line <strong>{$line}</strong>".PHP_EOL;
        if($error){
            $this->error($string);
        } else {
            $this->print($string);
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
        error_log(strip_tags($string));
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
        if($string){ $this->error($string); }

        // Check if the command is valid
        if($command){
            $path = $CONFIG->root() . "/Command/" . ucfirst($command) . "Command" . ".php";
            if(!is_file($path)){
                $path = $CONFIG->root() . "/lib/plugins/" . ucfirst($command) . "/Command.php";
                if(!is_file($path)){
                    $command = null;
                }
            }
        }
        if($command){
            if(!class_exists(ucfirst($command) . "Command")){

                // Load Command File
                require_once $path;

                // Check if command class exist
                if(!class_exists(ucfirst($command) . "Command")){
                    $command = null;
                }
            }
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

            // List available commands
            foreach(scandir($CONFIG->root() . "/lib/plugins/") as $command){
                if(is_file($CONFIG->root() . "/lib/plugins/" . $command . "/Command.php")){
                    $this->print(" - " . strtolower(str_replace('Command.php','',$command)));
                }
            }
        }

        // Stop execution and Exit the script
        exit();
    }
}
