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
        // Check if the database has been initialized
        if (is_null($this->Database)) {
            return false;
        }

        // Check if the database is currently installed
        if (!$this->Database->isInstalled()) {
            return false;
        }

        // Check if the database is currently connected
        if (!$this->Database->isConnected()) {
            return false;
        }

        // Check if the user is already authenticated
        if ($this->status) {
            return $this->status;
        }

        // Check Bearer Token
        if ($this->authenticateBearer()) {
            $this->method = 'bearer';
            $this->status = true;
            return $this->status;
        }

        // Check Basic Authentication
        if ($this->authenticateBasic()) {
            $this->method = 'basic';
            $this->status = true;
            return $this->status;
        }

        // Check Session Authentication
        if ($this->authenticateSession()) {
            $this->method = 'session';
            $this->status = true;
            return $this->status;
        }

        // Check Cookie Authentication
        if ($this->authenticateCookie()) {
            $this->method = 'cookie';
            $this->status = true;
            return $this->status;
        }

        // Check Request Authentication
        if ($this->authenticateRequest()) {
            $this->method = 'request';
            $this->status = true;
            return $this->status;
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
    {
        if($this->isAuthenticated()){
            $roles = $this->user->roles(true);
            foreach($roles as $role){
                if($role->permissions($permission) >= $level){
                    return true;
                }
            }
        }
        return false;
    }

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
                ->where('expires', date('Y-m-d H:i:s'), '>')
                ->limit(1)
                ->result();

            // Check if the token is valid
            if (!empty($result)) {
                return $this->authenticate(intval($result[0]['user']));
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
            if (!is_null($REQUEST->getParams('REQUEST','login')) || !is_null($REQUEST->getParams('REQUEST','signin'))) {
                return $this->authenticate($REQUEST->getParams('REQUEST','username'), $REQUEST->getParams('REQUEST','password'));
            }
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

                // Check if the user's organization is active
                $status = ($status && $this->user->organization['isActive'] > 0);

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
    public function user(string|int|null $user = null): ?Objects\User
    {
        return is_null($user) ? $this->user : new Objects\User($user);
    }

    /**
     * Install the module
     *
     * @param array $config
     * @return array
     */
    public function install(array $config): array
    {
        // Import Global Variables
        global $UUID;

        // Initialize the status
        $status = [];

        // Check if the config includes all the required fields
        if(isset($config['organization'],$config['username'],$config['password'])){

            // Check if the database has been initialized
            if (!is_null($this->Database)) {

                // Connect to the database
                $this->Database->connect();

                // Check if the database is currently connected
                if ($this->Database->isConnected()) {

                    // Create the organization
                    $Query = $this->Database->query()
                        ->table('organizations')
                        ->insert([
                            'owner' => $config['username']
                        ]);
                    $affected = $Query->execute();
                    $organizationId = $Query->lastId();

                    // Create the organization vCard
                    $Query = $this->Database->query()
                        ->table('vcards')
                        ->insert([
                            'owner' => $config['username'],
                            'category' => 'Organization',
                            'name' => $config['organization'],
                            'organization' => $organizationId
                        ]);
                    $affected += $Query->execute();
                    $organizationVcardId = $Query->lastId();

                    // Create the user vCard
                    $Query = $this->Database->query()
                        ->table('vcards')
                        ->insert([
                            'owner' => $config['username'],
                            'category' => 'User',
                            'email' => $config['username'],
                            'organization' => $organizationId
                        ]);
                    $affected += $Query->execute();
                    $userVcardId = $Query->lastId();

                    // Create the user backend
                    $Query = $this->Database->query()
                        ->table('backends')
                        ->insert([
                            'owner' => $config['username'],
                            'type' => 'local',
                            'password' => password_hash($config['password'], PASSWORD_DEFAULT),
                            'organization' => $organizationId
                        ]);
                    $affected += $Query->execute();
                    $userBackendId = $Query->lastId();

                    // Create the user api token
                    $Query = $this->Database->query()
                        ->table('tokens')
                        ->insert([
                            'owner' => $config['username'],
                            'token' => $UUID->toString($config['username'])
                        ]);
                    $affected += $Query->execute();
                    $userTokenId = $Query->lastId();

                    // Create the user
                    $Query = $this->Database->query()
                        ->table('users')
                        ->insert([
                            'owner' => $config['username'],
                            'username' => $config['username'],
                            'backend' => $userBackendId,
                            'vcard' => $userVcardId,
                            'organization' => $organizationId,
                            'token' => $userTokenId,
                            'isVerified' => 1
                        ]);
                    $affected += $Query->execute();
                    $userId = $Query->lastId();

                    // Update the organization
                    $Query = $this->Database->query()
                        ->table('organizations')
                        ->update([
                            'users' => json_encode([$userId], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                            'vcard' => $organizationVcardId,
                            'isActive' => 1
                        ])
                        ->where('id', $organizationId);
                    $affected += $Query->execute();

                    // Update the user token
                    $Query = $this->Database->query()
                        ->table('tokens')
                        ->update([
                            'user' => $userId
                        ])
                        ->where('id', $userTokenId);
                    $affected += $Query->execute();

                    // Update the group membership
                    $Query = $this->Database->query()
                        ->table('groups')
                        ->update([
                            'users' => json_encode([$userId], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
                        ])
                        ->where('name', 'Administrator');
                    $affected += $Query->execute();

                    // Check if the database records were created
                    if($affected >= 9){

                        // Add a true status
                        $status[] = true;
                    } else {
                        $status[] = "Failed to install";
                    }
                } else {
                    $status[] = "Database not connected";
                }
            } else {
                $status[] = "Database not initialized";
            }
        } else {
            $status[] = "Missing required fields";
        }

        // Return the statuses
        return $status;
    }
}
