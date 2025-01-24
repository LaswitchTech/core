<?php

/**
 * Core Framework - Log
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal classes into the global namespace
use DateTime;
use Exception;
use ReflectionClass;

class Log {

    const DEBUG_LABEL = 'DEBUG';
    const INFO_LABEL = 'INFO';
    const SUCCESS_LABEL = 'SUCCESS';
    const WARNING_LABEL = 'WARNING';
    const ERROR_LABEL = 'ERROR';
    const DEBUG_LEVEL = 5;
    const INFO_LEVEL = 4;
    const SUCCESS_LEVEL = 3;
    const WARNING_LEVEL = 2;
    const ERROR_LEVEL = 1;
    const Extension = '.log';
    const Dir = '/log';

    // Properties
    private $Path = null;
    private $Levels = [];
    private $Level = 0;
    private $IP = false;
    private $Rotation = false;
    private $Files = [];
    private $File = null;
    private $Message = null;

    /**
     * Create a new Logger instance.
     *
     * @param  string|array|null  $logFile
     * @return void
     * @throws Exception
     */
    public function __construct(){

        // Global Variables
        global $CONFIG;

        // Add a log configuration file
        $CONFIG->add('log');

        // Set Properties
        $this->Path = $CONFIG->root();
        $this->Levels[self::DEBUG_LEVEL] = self::DEBUG_LABEL;
        $this->Levels[self::INFO_LEVEL] = self::INFO_LABEL;
        $this->Levels[self::SUCCESS_LEVEL] = self::SUCCESS_LABEL;
        $this->Levels[self::WARNING_LEVEL] = self::WARNING_LABEL;
        $this->Levels[self::ERROR_LEVEL] = self::ERROR_LABEL;
        $this->Level = $CONFIG->get('log', 'level') ?: $this->Level;
        $this->Rotation = $CONFIG->get('log', 'rotation') ?: $this->Rotation;
        $this->IP = $CONFIG->get('log', 'ip') ?: $this->Rotation;

        // Configure PHP
        $this->config();
    }

    /**
     * Configure PHP.
     *
     * @param  string  $option
     * @param  bool|int  $value
     * @return void
     * @throws Exception
     */
    public function config($level = null){

        // Set Level
        $this->Level = $level ?: $this->Level;

        // Configure PHP based on the log level
        switch($this->Level){
            case self::DEBUG_LEVEL:
                error_reporting(E_ALL);
                ini_set("display_errors", 1);
                break;
            case self::INFO_LEVEL:
                error_reporting(E_ALL ^ E_NOTICE);
                ini_set("display_errors", 0);
                break;
            case self::SUCCESS_LEVEL:
                error_reporting(E_ALL ^ E_NOTICE);
                ini_set("display_errors", 0);
                break;
            case self::WARNING_LEVEL:
                error_reporting(E_ALL ^ E_NOTICE);
                ini_set("display_errors", 0);
                break;
            case self::ERROR_LEVEL:
                error_reporting(E_ALL ^ E_NOTICE);
                ini_set("display_errors", 0);
                break;
            default:
                error_reporting(E_ALL ^ E_NOTICE);
                ini_set("display_errors", 0);
                break;
        }

        // Return
        return $this;
    }

    /**
     * Retrieve the client IP address.
     *
     * @return string $ip
     */
	public function ip(){
        $ip = '';
        if(getenv('HTTP_CLIENT_IP')){
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')){
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('HTTP_X_FORWARDED')){
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif(getenv('HTTP_FORWARDED_FOR')){
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif(getenv('HTTP_FORWARDED')){
            $ip = getenv('HTTP_FORWARDED');
        } elseif(getenv('REMOTE_ADDR')){
            $ip = getenv('REMOTE_ADDR');
        } elseif(defined('STDIN')){
            $ip = 'LOCALHOST';
        } else {
            $ip = 'UNKNOWN';
        }
        if(in_array($ip,['127.0.0.1','127.0.1.1','::1'])){ $ip = 'LOCALHOST'; }
	    return $ip;
	}

    /**
     * Retrieve the client User Agent.
     *
     * @return string $agent
     */
    public function agent(){

        // Retrieve the User Agent
        $agent = 'Unknown';

        // Check for a web agent
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? json_encode($_SERVER['HTTP_USER_AGENT']) : $agent;

        // Check for a command line agent
        $agent = defined('STDIN') ? json_encode(["Terminal" => $_SERVER['TERM'], "Program" => $_SERVER['TERM_PROGRAM']]) : $agent;

        // Return
        return $agent;
    }

    /**
     * Add a new log file.
     *
     * @param  string  $name
     * @param  string  $path
     * @return void
     * @throws Exception
     */
    public function add($name, $path = null){

        // If not already saved, add File in the list
        if(!isset($this->Files[$name])){

            // Check for a command line agent
            $path = ($path && is_string($path)) ? $path : $this->Path . self::Dir . '/' . $name . self::Extension;

            // Check if it doesn't exist
            if(!is_file($path)){

                // Create the directory recursively
                if(!is_dir(dirname($path))){
                    mkdir(dirname($path), 0777, true);
                }

                // Create File
                file_put_contents($path, PHP_EOL);
            }

            // Save File
            $this->Files[$name] = $path;
        }

        // Return
        return $this;
    }

    /**
     * Set the current log file.
     *
     * @param  string  $name
     * @return void
     * @throws Exception
     */
    public function set($name){

        // Set Log File
        $this->File = is_string($name) && isset($this->Files[$name]) ? $name : $this->File;

        // Return
        return $this;
    }

    /**
     * Clear a log file.
     *
     * @param  string  $Name
     * @return void
     * @throws Exception
     */
    public function clear($name = null){

        // Set name
        $name = $name ?: $this->File;

        // If not already saved, add File in the list
        if(isset($this->Files[$name])){

            // Clear File
            file_put_contents($this->Files[$name], '');
        }

        // Return
        return $this;
    }

    /**
     * Read a log file.
     *
     * @param  string  $name
     * @return array
     */
    public function read($name = null) {

        // Set name
        $name = $name ?: $this->File;

        // Check if the log file exists
        if(isset($this->Files[$name]) && file_exists($this->Files[$name])){
            return file($this->Files[$name], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        } else {
            return [];
        }
    }

    /**
     * Get an array of all added log files.
     *
     * @return array
     */
    public function list(){
        return array_keys($this->Files);
    }

    /**
     * Write a log message to the current log file.
     *
     * @param  mixed  $message
     * @param  string  $level
     * @param  string|null  $name
     * @return void
     */
    public function log($message, $level = self::LEVEL_INFO, $name = null){

        // Validate log level
        if(isset($this->Levels[$level]) && $level <= $this->Level){

            // Sanitize message
            if(!is_string($message)){
                $message = '[JSON] ' . PHP_EOL . json_encode($message, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            }

            // Set name
            $name = $name ?: $this->File;

            // Check if the log file exists
            if(isset($this->Files[$name])){

                // Backtrace
                $trace = debug_backtrace();
                $caller = count($trace) > 1 ? $trace[1] : $trace[0];
                $file = isset($caller['file']) ? $caller['file'] : '';
                $line = isset($caller['line']) ? $caller['line'] : '';
                $caller = count($trace) > 1 ? end($trace) : current($trace);
                $class = isset($caller['class']) && count($trace) > 1 ? $caller['class'] : '';
                $function = isset($caller['function']) && count($trace) > 1 ? $caller['function'] : '';
                $classTrace = '[' . $class ? $class . '::' : $class . $function . ']';
                $timestamp = "[" . date("Y-m-d H:i:s") . "]";
                $ip = $this->IP ? '[' . $this->ip() . ']' : '';

                // Format Line
                $logLine = $timestamp . $ip . '[' . $this->Levels[$level] . '][' . $classTrace . '](' . $file . ':' . $line . ') Message: ' . $message;

                // Check if File should be rotated
                if($this->Rotation && is_file($this->Files[$name])){

                    // Get dates
                    $today = new DateTime();
                    $logDate = new DateTime();
                    $logDate->setTimestamp(filemtime($this->Files[$name]));

                    // Evaluate Dates
                    if($today->format("Y-m-d") > $logDate->format("Y-m-d")){
                        $fileName = $this->Files[$name] . '.' . strtotime($logDate->format("Y-m-d"));
                        rename($this->Files[$name], $fileName);
                    }
                }

                // Create the directory recursively
                if(!is_dir(dirname($this->Files[$name]))){
                    mkdir(dirname($this->Files[$name]), 0777, true);
                }

                // Write Line to logFile
                file_put_contents($this->Files[$name], trim($logLine) . PHP_EOL, FILE_APPEND);

                // Save the message
                $this->Message = $logLine;
            }
        }

        // Return
        return $this;
    }

    /**
     * Write a level DEBUG log message to the current log file.
     *
     * @param  mixed  $message
     * @param  string|null  $name
     * @return void
     */
    public function debug($message, $name = null){
        return $this->log($message, $level = self::DEBUG_LEVEL, $name);
    }

    /**
     * Write a level INFO log message to the current log file.
     *
     * @param  mixed  $message
     * @param  string|null  $name
     * @return void
     */
    public function info($message, $name = null){
        return $this->log($message, $level = self::INFO_LEVEL, $name);
    }

    /**
     * Write a level SUCCESS log message to the current log file.
     *
     * @param  mixed  $message
     * @param  string|null  $name
     * @return void
     */
    public function success($message, $name = null){
        return $this->log($message, $level = self::SUCCESS_LEVEL, $name);
    }

    /**
     * Write a level WARNING log message to the current log file.
     *
     * @param  mixed  $message
     * @param  string|null  $name
     * @return void
     */
    public function warning($message, $name = null){
        return $this->log($message, $level = self::WARNING_LEVEL, $name);
    }

    /**
     * Write a level ERROR log message to the current log file.
     *
     * @param  mixed  $message
     * @param  string|null  $name
     * @return void
     */
    public function error($message, $name = null){
        return $this->log($message, $level = self::ERROR_LEVEL, $name);
    }

    public function fatal(){

        // Store the last message in error log
        error_log($this->Message);

        // Print the last message
        echo $this->Message;

        // Exit the script
        exit;
    }
}
