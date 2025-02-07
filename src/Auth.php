<?php

/**
 * Core Framework - Auth
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects;
use Exception;

class Auth {

    // Global Properties
    protected $Config;
    protected $Database;

    // Properties
    protected $user;
    protected $method;
    protected $status = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $CONFIG, $DATABASE;

        // Initialize Properties
        $this->Config = $CONFIG;
        $this->Database = $DATABASE;

        // Import Auth Configurations
        $this->Config->add('auth');
    }

    /**
     * Check if the user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        // Check if the user is already authenticated
        if ($this->status) {
            return $this->status;
        }

        // Check Bearer Token
        if ($this->authenticateBearer()) {
            $this->method = 'bearer';
        }

        // Check Basic Authentication
        if ($this->authenticateBasic()) {
            $this->method = 'basic';
        }

        // Check Session Authentication
        if ($this->authenticateSession()) {
            $this->method = 'session';
        }

        // Check Cookie Authentication
        if ($this->authenticateCookie()) {
            $this->method = 'cookie';
        }

        // Check Request Authentication
        if ($this->authenticateRequest()) {
            $this->method = 'request';
        }

        if($this->method){
            $this->status = true;
        }

        return $this->status;
    }

    /**
     * Check if the user is loaded
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
        if(is_null($this->user)){
            $this->isAuthenticated();
        }
        return !is_null($this->user);
    }

    /**
     * Check if the user is authorized
     *
     * @param string $permission
     * @param int $level
     * @return bool
     */
    public function isAuthorized(string $permission, int $level): bool
    {}

    /**
     * Get the authentication method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Authenticate using Bearer Token
     *
     * @return bool
     */
    protected function authenticateBearer(): bool
    {
        // Import Global Variables
        global $REQUEST;

        // Get Authorization Header
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }

        // Check if the Authorization Header is set
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1];

            // Validate Token
            $query = $this->Database->query();
            $result = $query->table('tokens')
                ->select('user')
                ->where('token', $token)
                ->where('expires', '>', date('Y-m-d H:i:s'))
                ->limit(1)
                ->result();

            // Check if the token is valid
            if (!empty($result)) {
                return $this->authenticate($result[0]['user']);
            }
        }

        return false;
    }

    /**
     * Authenticate using Basic Authentication
     *
     * @return bool
     */
    protected function authenticateBasic(): bool
    {
        // Import Global Variables
        global $REQUEST;

        // Check if the Authorization Header is set
        if (is_null($REQUEST->getParams('SERVER','PHP_AUTH_USER')) || is_null($REQUEST->getParams('SERVER','PHP_AUTH_PW'))) {
            return false;
        }

        $username = $REQUEST->getParams('SERVER','PHP_AUTH_USER');
        $password = $REQUEST->getParams('SERVER','PHP_AUTH_PW');

        // Validate user
        $query = $this->Database->query();
        $user = $query->table('users')
            ->select('id, password')
            ->where('username', $username)
            ->limit(1)
            ->result();

        // Check if the user exists and the password is valid
        if (!empty($user)){
            return $this->authenticate($user[0]['id'], $password);
        }

        return false;
    }

    /**
     * Authenticate using Cookie
     *
     * @return bool
     */
    protected function authenticateCookie(): bool
    {
        // Import Global Variables
        global $REQUEST;

        if ($REQUEST->getParams('COOKIE',session_id())) {
            return $this->authenticate($REQUEST->getParams('COOKIE',session_id()));
        }

        return false;
    }

    /**
     * Authenticate using Session
     *
     * @return bool
     */
    protected function authenticateSession(): bool
    {
        // Import Global Variables
        global $REQUEST, $UUID;

        if ($REQUEST->getParams('SESSION',$UUID->toString("auth-" . session_id()))) {

            // Create the Query
            $Query = $this->Database->query()
                ->table('sessions')
                ->select('*')
                ->filter()
                ->where('id', 9999, '<>')
                ->filter()
                ->where('uuid', $UUID->toString("auth-" . session_id()), '=', 'OR')
                ->where('user', $REQUEST->getParams('SESSION',$UUID->toString("auth-" . session_id())), '=', 'OR');

            // Retrieve any existing session
            $Sessions = $Query->result();

            if(count($Sessions) > 0){
                return $this->authenticate($Sessions[0]['user']);
            }
        }

        return false;
    }

    /**
     * Authenticate using Request
     *
     * @return bool
     */
    protected function authenticateRequest(): bool
    {
        // Import Global Variables
        global $REQUEST;

        if ($REQUEST->getParams('REQUEST','username') && $REQUEST->getParams('REQUEST','password')) {
            return $this->authenticate($REQUEST->getParams('REQUEST','username'), $REQUEST->getParams('REQUEST','password'));
        }

        return false;
    }

    /**
     * Authenticate the user
     *
     * @param int|string $user
     * @param string|null $password
     * @return bool
     */
    protected function authenticate(int|string $user, ?string $password = null): bool
    {
        // Import Global Variables
        global $REQUEST;

        // Retrieve User
        $user = $this->user($user);

        // Check if the user is logging out
        if(!is_null($REQUEST->getParams('GET','logout') ?? $REQUEST->getParams('GET','signout'))){

            // Destroy Session
            $user->session()->clear();
        } else {

            // Validate Password
            $load = ($password) ? (bool) $user->backend()->validate($password) : true;

            // Check if the user can be loaded
            if($load){

                // Load User
                $this->user = $user;

                // Check if the user is deleted
                $status = !$this->user->deleted();

                // Check if the user is banned
                $status = ($status && !$this->user->banned());

                // Check if the user is verified
                $status = ($status && $this->user->verified());

                // Check the user status
                if($status){

                    // Set Session
                    $this->user->session()->create();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create a new Group object
     *
     * @param string $name
     * @return Objects\Group
     */
    public function group(string $name): Objects\Group
    {
        return new Objects\Group($name);
    }

    /**
     * Create a new Role object
     *
     * @param string $name
     * @return Objects\Role
     */
    public function role(string $name): Objects\Role
    {
        return new Objects\Role($name);
    }

    /**
     * Create a new User object
     *
     * @param string|int|null $user
     * @return Objects\User
     */
    public function user(string|int|null $user = null): Objects\User
    {
        return is_null($user) ? $this->user : new Objects\User($user);
    }
}
