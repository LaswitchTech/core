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

class Command {

    // Global Properties
    protected $Output;

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $OUTPUT;

        // Initialize Properties
        $this->Output = $OUTPUT;
    }

    /**
     * Magic Method to catch all undefined methods
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call($name, $arguments) {

        // Output Usage
        $this->Output->print("Usage: ./cli " . strtolower(str_replace('Command','',__CLASS__)) . " " . $name . " [options]");

        // List available methods that end with 'Action'
        $this->Output->print("Available Methods:");
        foreach(get_class_methods($this) as $method){
            if(substr($method,-6) == 'Action'){
                $this->Output->print(" - " . str_replace('Action','',$method));
            }
        }
    }

    /**
     * Request for input
     * @param string $string
     * @param array|int|string $options
     * @param string $default
     * @return string
     */
    public function request($string, $options = null, $default = null){
        $modes = ['select','text','string'];
        $mode = 'string';
        if($options != null || $options == 0){
            if(is_array($options)){
                $mode = 'select';
            } else if(is_int($options)){
                $mode = 'text';
            } else {
                if(is_string($options)){ $default = $options; }
            }
        }
        $stdin = function(){
            $handle = fopen ("php://stdin","r");
            return str_replace("\n",'',fgets($handle));
        };
        switch($mode){
            case"select":
                $answer = null;
                foreach($options as $key => $value){
                    $options[$key] = strtoupper($value);
                }
                while($answer == null || !in_array(strtoupper($answer),$options)){
                    print_r($string . ' (');
                    foreach($options as $key => $option){
                        if($key > 0){ print_r('/'); }
                        print_r($option);
                    }
                    print_r(')');
                    if($default != null){ print_r('['.$default.']'); }
                    print_r(': ');
                    $answer = $stdin();
                    if($default != null && $answer == ""){ $answer = $default; }
                }
                break;
            case"text":
                $answer = '';
                $exits = ['END','EXIT','QUIT','EOF',':Q',''];
                $count = 0;
                $max = 5;
                $print = false;
                if(is_bool($default)){ $print = $default; }
                if(is_int($options)){ $max = $options; }
                if($print){
                    print_r($string . ' type (');
                    foreach($exits as $key => $exit){
                        if($key > 0){ print_r('/'); }
                        print_r($exit);
                    }
                    print_r(') to exit' . PHP_EOL);
                } else {
                    print_r($string . PHP_EOL);
                }
                do {
                    $line = fgets(STDIN);
                    if(in_array(strtoupper(str_replace("\n",'',$line)),$exits)){
                        if($max <= 0){ $max = 1; }
                        $count = $max;
                    } else { $answer .= $line; $count++; }
                } while ($count < $max || $max <= 0);
                break;
            default:
                $answer = null;
                while($answer == null){
                    print_r($string . ' ');
                    if($default != null){ print_r('['.$default.']'); }
                    print_r(': ');
                    $answer = $stdin();
                    if($default != null && $answer == ""){ $answer = $default; }
                    if($answer == ''){ $answer = null; }
                }
                break;
        }
        $answer = trim($answer,"\n");
        if($answer == ''){ $answer = null; }
        return $answer;
    }
}
