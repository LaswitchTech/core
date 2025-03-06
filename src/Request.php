<?php

/**
 * Core Framework - Request
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Request {

    // Properties
    private $Get;
    private $Env;
    private $Post;
    private $Files;
    private $Server;
    private $Cookie;
    private $Session;
    private $Request;
    private $Arguments;

    /**
     * Constructor
     */
    public function __construct(){

        // Import Global Variables
        global $_GET, $_ENV, $_POST, $_FILES, $_SERVER, $_COOKIE, $_SESSION, $_REQUEST, $argv, $argc;

        // Set the properties
        $this->Get = $_GET;
        $this->Env = $_ENV;
        $this->Post = $_POST;
        $this->Files = $_FILES;
        $this->Server = $_SERVER;
        $this->Cookie = $_COOKIE;
        $this->Session = $_SESSION;
        $this->Request = $_REQUEST;
        $this->Arguments = $argv ?? [];

        // Unset the global variables
        unset($_SERVER, $_GET, $_ENV, $_POST, $_FILES, $_REQUEST, $argv, $argc);
    }

    /**
     * Get the host
     */
    public function getHost() {
        return $this->Server['HTTP_HOST'] ?? '';
    }

    /**
     * Get the host
     */
    public function getHostSSL() {
        return $this->Server['HTTPS'] == 'on' ? 'https://' : 'http://';
    }

    /**
     * Get the host address
     */
    public function getHostAddress() {
        return defined('STDIN') ? 'localhost' : $this->getHostSSL() . $this->getHost();
    }

    /**
     * Get the URI
     * @return string
     */
    public function getUri() {
        return parse_url($this->Server['REQUEST_URI'] ?? '', PHP_URL_PATH);
    }

    /**
     * Get the URI segments
     * @return array
     */
    public function getUriSegments() {
        return array_filter(explode( '/', $this->getUri()));
    }

    /**
     * Set and Return the Namespace property
     */
    public function getNamespace(){
        $namespace = "";
        foreach($this->getUriSegments() as $Segment){
            if(strpos($Segment, '.') === false){
                $namespace .= "/{$Segment}";
            }
        };
        return empty($namespace) ? '/' : $namespace;
    }

    /**
     * Get the request method
     * @return string
     */
    public function getMethod() {
        return $this->Server["REQUEST_METHOD"] ?? 'GET';
    }

    /**
     * Get the query string parameters
     * @return string
     */
    public function getQueryString() {
        return $this->Server['QUERY_STRING'] ?? '';
    }

    /**
     * Get the parameters
     * @param string $type
     * @param string $key
     * @return mixed
     */
    public function getParams(string $type,?string $key = null)
    {
        switch(strtoupper($type)){
            case 'GET':
                $array = $this->Get;
                break;
            case 'ENV':
                $array = $this->Env;
                break;
            case 'POST':
                $array = $this->Post;
                break;
            case 'FILES':
                $array = $this->Files;
                break;
            case 'COOKIE':
                $array = $this->Cookie;
                break;
            case 'SESSION':
                $array = $this->Session;
                break;
            case 'SERVER':
                $array = $this->Server;
                break;
            case 'QUERY':
                parse_str($this->Server['QUERY_STRING'], $array);
                break;
            case 'REQUEST':
                $array = $this->Request;
                break;
            default:
                return null;
        }
        return !is_null($key) ? ($array[$key] ?? null) : $array;
    }

    /**
     * Set the parameters
     * @param string $type
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function setParams(string $type, string $key,?string $value)
    {
        switch(strtoupper($type)){
            case 'COOKIE':
                $_COOKIE[$key] = $value;
                break;
            case 'SESSION':
                $_SESSION[$key] = $value;
                break;
            default:
                return null;
        }
        return $value;
    }

    /**
     * Set the parameters
     * @param string $type
     * @param string $key
     * @return mixed
     */
    public function clearParams(string $type, string $key): bool
    {
        switch(strtoupper($type)){
            case 'ENV':
                if(isset($this->Env[$key])){ unset($this->Env[$key]); return true; }
                break;
            case 'SERVER':
                if(isset($this->Server[$key])){ unset($this->Server[$key]); return true; }
                break;
            case 'GET':
                if(isset($this->Get[$key])){ unset($this->Get[$key]); return true; }
                break;
            case 'POST':
                if(isset($this->Post[$key])){ unset($this->Post[$key]); return true; }
                break;
            case 'FILES':
                if(isset($this->Files[$key])){ unset($this->Files[$key]); return true; }
                break;
            case 'REQUEST':
                if(isset($this->Request[$key])){ unset($this->Request[$key]); return true; }
                break;
            case 'COOKIE':
                if(isset($this->Cookie[$key])){
                    unset($this->Cookie[$key]);
                    setcookie($key, '', time() - 3600, '/');
                    return true;
                }
                break;
            case 'SESSION':
                if(isset($this->Session[$key])){
                    unset($this->Session[$key]);
                    unset($_SESSION[$key]);
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Decode the REQUEST data
     * @param string $string
     * @return string
     */
    public function decode(string $string){
        return urldecode(base64_decode($string));
    }

    /**
     * Get the arguments
     * @param int $index
     * @return mixed
     */
    public function getArguments($index = null){
        return !is_null($index) ? ($this->Arguments[$index] ?? null) : $this->Arguments;
    }

    /**
     * Request for input
     * @param string $string
     * @param array|int|string $options
     * @param string $default
     * @return string
     */
    public function request(string $string, $options = null, $default = null){
        if(defined('STDIN')){
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
}
