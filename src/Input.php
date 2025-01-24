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

class Input {

    /**
     * Constructor
     */
    public function __construct(){}

    /**
     * Get the request parameters
     * @param string $Type
     * @param string $Key
     * @return mixed
     */
    public function getParams($Type, $Key = null){

        // Check the type
        switch(strtoupper($Type)){
            case 'GET':
                return $this->getGetParams($Key);
            case 'POST':
                return $this->getPostParams($Key);
            case 'REQUEST':
                return $this->getRequestParams($Key);
            case 'QUERY':
                return $this->getQueryStringParams($Key);
            default:
                return $this->getRequestParams($Key);
        }
    }

    /**
     * Get the query string parameters
     * @param string $Key
     * @return mixed
     */
    public function getQueryStringParams($Key = null) {

        if($this->QueryString === null){

            // Parse the query string
            parse_str($_SERVER['QUERY_STRING'], $this->QueryString);
        }

        // Check if a key was provided
        if($Key){

            // Check if the key exists
            if(isset($this->QueryString[$Key])){

                // Return the query string value
                return $this->QueryString[$Key];
            } else {

                // Return null
                return null;
            }
        } else {

            // Return the query string
            return $this->QueryString;
        }
    }

    /**
     * Get the GET parameters
     * @param string $Key
     * @return mixed
     */
    public function getGetParams($Key = null) {

        if($this->GET === null){

            // Initiate the GET array
            $this->GET = array();

            // Decode the GET data
            foreach($_GET as $arrayKey => $arrayValue){

                // Add the decoded data to the GET array
                $this->GET[$arrayKey] = urldecode(base64_decode($arrayValue));
            }
        }

        // Check if a key was provided
        if($Key){

            // Check if the key exists
            if(isset($this->GET[$Key])){

                // Return the GET value
                return $this->GET[$Key];
            } else {

                // Return null
                return null;
            }
        } else {

            // Return the GET
            return $this->GET;
        }
    }

    /**
     * Get the POST parameters
     * @param string $Key
     * @return mixed
     */
    public function getPostParams($Key = null) {

        if($this->POST === null){

            // Initiate the POST array
            $this->POST = array();

            // Decode the POST data
            foreach($_POST as $arrayKey => $arrayValue){

                // Add the decoded data to the POST array
                $this->POST[$arrayKey] = urldecode(base64_decode($arrayValue));
            }
        }

        // Check if a key was provided
        if($Key){

            // Check if the key exists
            if(isset($this->POST[$Key])){

                // Return the POST value
                return $this->POST[$Key];
            } else {

                // Return null
                return null;
            }
        } else {

            // Return the POST
            return $this->POST;
        }
    }

    /**
     * Get the REQUEST parameters
     * @param string $Key
     * @return mixed
     */
    public function getRequestParams($Key = null) {

        if($this->REQUEST === null){

            // Initiate the REQUEST array
            $this->REQUEST = array();

            // Decode the REQUEST data
            foreach($_REQUEST as $arrayKey => $arrayValue){

                // Add the decoded data to the REQUEST array
                $this->REQUEST[$arrayKey] = urldecode(base64_decode($arrayValue));
            }
        }

        // Check if a key was provided
        if($Key){

            // Check if the key exists
            if(isset($this->REQUEST[$Key])){

                // Return the REQUEST value
                return $this->REQUEST[$Key];
            } else {

                // Return null
                return null;
            }
        } else {

            // Return the REQUEST
            return $this->REQUEST;
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
