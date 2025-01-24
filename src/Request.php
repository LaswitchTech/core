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
    private $Post;
    private $Files;
    private $Server;
    private $Cookie;
    private $Request;

    /**
     * Constructor
     */
    public function __construct(){
        $this->Get = $_GET;
        $this->Post = $_POST;
        $this->Files = $_FILES;
        $this->Server = $_SERVER;
        $this->Cookie = $_COOKIE;
        $this->Request = $_REQUEST;
        unset($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $_REQUEST);
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
    public function getParams($type, $key = null){
        switch(strtoupper($type)){
            case 'GET':
                $array = $this->Get;
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
     * Decode the REQUEST data
     * @param string $string
     * @return string
     */
    public function decode($string){
        return urldecode(base64_decode($string));
    }
}
