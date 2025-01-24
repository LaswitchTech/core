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
    public function print($string) {
        print_r($string . PHP_EOL);
    }

    /**
     * Output a string with color
     * @param string $string
     * @param string $color
     * @return void
     */
    public function set($string, $color = 'default'){
        if(isset($this->Colors[$color])){
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
        $this->output($this->set($string, 'red'));
    }

    /**
     * Output a string with green color
     * @param string $string
     * @return void
     */
    public function success($string) {
        $this->output($this->set($string, 'green'));
    }

    /**
     * Output a string with yellow color
     * @param string $string
     * @return void
     */
    public function warning($string) {
        $this->output($this->set($string, 'yellow'));
    }

    /**
     * Output a string with blue color
     * @param string $string
     * @return void
     */
    public function info($string) {
        $this->output($this->set($string, 'cyan'));
    }

    /**
     * Output the data
     * @param mixed $data
     * @param array $httpHeaders
     * @return void
     */
    public function json($data, $httpHeaders=array()) {

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

            // Check if the data is an array or object
            if(is_array($data) || is_object($data)){

                // Convert the data to JSON
                $data = json_encode($data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Send the output
            echo $data;

            // Exit the script
            exit;
        }
    }
}
