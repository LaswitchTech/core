<?php

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
    protected $requested = false;

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

        // Check if the database is currently connected
        if (!$this->Database->isConnected()) {
            return false;
        }

        // Check if the user is already authenticated
        if ($this->status) {
            return $this->status;
        }

        // Check Bearer Token
        if ($this->byBearerToken()) {
            $this->method = 'bearer';
            $this->status = true;
            return $this->status;
        }

        // Check Basic Authentication
        if ($this->byBasicAuth()) {
            $this->method = 'basic';
            $this->status = true;
            return $this->status;
        }

        // Check Session Authentication
        if ($this->bySession()) {
            $this->method = 'session';
            $this->status = true;
            return $this->status;
        }

        // Check Cookie Authentication
        if ($this->byCookie()) {
            $this->method = 'cookie';
            $this->status = true;
            return $this->status;
        }

        // Check Request Authentication
        if ($this->byRequest()) {
            $this->method = 'request';
            $this->status = true;
            return $this->status;
        }

        // Check if uer is authenticated
        if(!$this->status){
            $this->isResetting();
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
     * Check if the user is attempting to reset their password
     */
    protected function isResetting(): void
    {
        // Import Global Variables
        global $REQUEST;

        if($this->requested){
            return;
        }

        if(
            $REQUEST->getParams('REQUEST','code') &&
            $REQUEST->getParams('REQUEST','username') &&
            !is_null($REQUEST->getParams('REQUEST','reset')) &&
            is_null($REQUEST->getParams('REQUEST','forgot')) &&
            is_null($REQUEST->getParams('REQUEST','verify'))
        ) {
            $this->verifyPin($REQUEST->getParams('REQUEST','username'), $REQUEST->getParams('REQUEST','code'),function(object $user){

                // Reset the user's password
                $password = $user->backend()->reset();

                // Notify the user of the new password
                $this->requested = $user->backend()->notify($user,$password);
            });
        } elseif(
            $REQUEST->getParams('REQUEST','username') &&
            !is_null($REQUEST->getParams('REQUEST','forgot')) &&
            !is_null($REQUEST->getParams('REQUEST','reset'))
        ) {
            $this->setPin($REQUEST->getParams('REQUEST','username'), function(object $user, string $pin){

                // Import Global Variables
                global $SMTP, $REQUEST;

                // Write the email
                $body = '';
                $body .= '<p>Did you request a new password?</p>';
                $body .= '<p>Here is your verification code:</p>';
                $body .= '<pre style="background-color: #F5F5F5; font-weight: 700; font-size: 28px; text-align: center; letter-spacing: 16px; margin: 20px 20px; padding: 20px 0; font-family: Courier, monospace">'.($pin ?? 'ERROR!').'</pre>';
                $body .= '<p>Please follow the link below to reset your password.</p>';
                $body .= '<p style="text-align:center;margin-top: 40px;margin-bottom:40px;">';
                $body .= '<a href="'.$REQUEST->getHostAddress().'?forgot&verify='.$pin.'&username='.$user->username.'" target="_blank" style="margin-left: 6px; margin-right: 6px; text-decoration:none; background-color: #528fb3;color: #fff;font-size: 24px;padding: 20px 40px;text-align: center;margin: 20px 20px;border-radius: 8px;">Reset</a>';
                $body .= '</p>';
                $body .= '<p>If you did not request this code, please contact your system administrator immediately.</p>';

                // Create a new message
                $eml = $SMTP->message()
                    ->subject('Reset your password')
                    ->body($body);

                // Return the message
                return $eml;
            });
        }
    }

    /**
     * set a pin for password reset
     *
     * @param string $username
     * @return bool
     */
    protected function setPin(string $username, callable $fn): void
    {
        // Retrieve User
        $user = $this->user($username);

        // Check if the user is found
        if($user->found()){

            // Create a new Pin
            $Pin = new Objects\Pin();

            // Generate a new pin
            $pin = $Pin->generate();

            // Save the pin
            $Pin->save($user->id,$pin);

            // Send the pin to the user email
            $this->requested = $Pin->notify($user,$pin,$fn);
        }
    }

    /**
     * verify the pin for password reset
     *
     * @param string $username
     * @param string $code
     * @return bool
     */
    protected function verifyPin(string $username, string $code, callable $fn): void
    {
        // Retrieve User
        $user = $this->user($username);

        // Check if the user is found
        if($user->found()){

            // Create a new Pin
            $Pin = new Objects\Pin($user->pin['id']);

            // Verify the pin
            if($Pin->verify($code)){

                // Execute the callable function
                $fn($user);
            }
        }
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
    protected function byBearerToken(): bool
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
            $tokens = $matches[1];

            // Check if the string is long enough
            if (strlen($tokens) < 73) {
                return false;
            }

            // Check if the splitter character is valid '-'
            if (substr($tokens, 36, 1) !== '-') {
                return false;
            }

            // Retrieve the user
            $query = $this->Database->query();
            $user = $query->table('users')
                ->select('*')
                ->join('token', 'tokens', 'id')
                ->where('uuid', substr($tokens, 0, 36))
                ->where('id', 9999, '<>')
                ->limit(1)
                ->result();

            // Check if the token is valid
            if (!empty($user)) {

                // Select the user
                $user = $user[0];

                // Validate Token
                if(password_verify(substr($tokens, -36), $user['token']['hash'])){

                    // Check if the token is expired
                    if (!is_null($user['token']['expires']) && strtotime($user['token']['expires']) < time()) {
                        return false;
                    }

                    // Return the user id
                    return $this->authenticate(intval($user['id']));
                }
            }
        }

        return false;
    }

    /**
     * Authenticate using Basic Authentication
     *
     * @return bool
     */
    protected function byBasicAuth(): bool
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
    protected function byCookie(): bool
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
    protected function bySession(): bool
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
    protected function byRequest(): bool
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
     * @param mixed $user
     * @param string|null $password
     * @return bool
     */
    protected function authenticate(mixed $user, ?string $password = null): bool
    {
        // Import Global Variables
        global $REQUEST;

        // Retrieve User
        $user = $this->user($user);

        // Check if the user is found
        if($user->found()){

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

                    // Check if the user's organization is active
                    $status = ($status && $this->user->organization['isActive'] > 0);

                    // Check the user status
                    if($status){

                        // Set User Last Login
                        $this->user->lastLogin();

                        // Set Session
                        $this->user->session()->create();

                        // Check if the user is verified
                        if(!$this->user->verified()){

                            // Check if username is in request params
                            if(is_null($REQUEST->getParams('REQUEST','username'))){

                                // Redirect to verification page (?username='.$user->username.')
                                header('Location: ?username='.$user->username);
                            } else {

                                // Check if we should resend the verification pin
                                if(is_null($user->pin['id']) || !is_null($REQUEST->getParams('REQUEST','resend'))){

                                    // Set a new pin
                                    $this->setPin($user->username, function(object $user, string $pin){

                                        // Import Global Variables
                                        global $SMTP, $REQUEST;

                                        // Write the email
                                        $body = '';
                                        $body .= '<p>Here is your verification code:</p>';
                                        $body .= '<pre style="background-color: #F5F5F5; font-weight: 700; font-size: 28px; text-align: center; letter-spacing: 16px; margin: 20px 20px; padding: 20px 0; font-family: Courier, monospace">'.($pin ?? 'ERROR!').'</pre>';
                                        $body .= '<p>Please follow the link below to verify your acount.</p>';
                                        $body .= '<p style="text-align:center;margin-top: 40px;margin-bottom:40px;">';
                                        $body .= '<a href="'.$REQUEST->getHostAddress().'?username='.$user->username.'&verify='.$pin.'" target="_blank" style="margin-left: 6px; margin-right: 6px; text-decoration:none; background-color: #528fb3;color: #fff;font-size: 24px;padding: 20px 40px;text-align: center;margin: 20px 20px;border-radius: 8px;">Verify</a>';
                                        $body .= '</p>';
                                        $body .= '<p>If you did not request this code, please contact your system administrator immediately.</p>';

                                        // Create a new message
                                        $eml = $SMTP->message()
                                            ->subject('Account Verification')
                                            ->body($body);

                                        // Return the message
                                        return $eml;
                                    });
                                } else {

                                    // Check if code is in request params
                                    if(!is_null($REQUEST->getParams('REQUEST','code'))){

                                        // Verify the pin
                                        $this->verifyPin($user->username, $REQUEST->getParams('REQUEST','code'),function(object $user){

                                            // Import Global Variables
                                            global $REQUEST;

                                            // Verify the user
                                            $user->verify();

                                            // Redirect to original page
                                            header('Location: '.$REQUEST->getHostAddress() . $REQUEST->getUri());
                                        });
                                    }
                                }
                            }
                        }

                        return true;
                    }
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
     * @param mixed $user
     * @return Objects\User
     */
    public function user(mixed $user = null): ?Objects\User
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
                            'hash' => password_hash($UUID->toString($config['username']), PASSWORD_DEFAULT)
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

                    // Update the user
                    $Query = $this->Database->query()
                        ->table('users')
                        ->update([
                            'uuid' => $UUID->toString($userId),
                        ])
                        ->where('id', $userId);
                    $affected += $Query->execute();

                    // Check if the database records were created
                    if($affected >= 10){

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
