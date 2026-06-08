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

    /** @var bool Set to true when password auth succeeded but TOTP verification is pending */
    public $needs_2fa = false;

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
     * Authenticate using Remember-Token (selector + validator pair)
     *
     * @return bool
     */
    protected function byRememberToken(): bool {
        // Import Global Variables
        global $REQUEST;

        if (is_null($REQUEST->getParams('COOKIE', session_id()))) {
            return false;
        }

        $value = $REQUEST->getParams('COOKIE', session_id());

        // Split selector:validator — reject invalid format
        if (!str_contains($value, ':')) {
            return false;
        }

        [$selector, $rawValidator] = explode(':', $value, 2);

        if (strlen($selector) < 4 || strlen($rawValidator) < 32) {
            return false;
        }

        // Query non-expired remember_tokens entry for this selector
        $result = $this->Database->query()
            ->table('remember_tokens')
            ->select('*')
            ->where('selector', $selector)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->limit(1)
            ->result();

        if (empty($result)) {
            return false;
        }

        $entry = $result[0];

        // Timing-safe validator verification
        if (!password_verify($rawValidator, $entry['validator_hash'])) {
            return false;
        }

        // Rotate token on successful authentication
        $newSelector   = bin2hex(random_bytes(16));  // 32-char hex
        $newValidator  = bin2hex(random_bytes(32));   // 64-char hex
        $expires       = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->Database->query()
            ->table('remember_tokens')
            ->update([
                'selector'         => $newSelector,
                'validator_hash'   => password_hash($newValidator, PASSWORD_DEFAULT),
                'expires'          => $expires,
                'last_rotated'     => date('Y-m-d H:i:s'),
            ])
            ->where('id', $entry['id'])
            ->result();

        // Reissue cookie with new selector:validator
        setcookie(
            session_id(),
            $newSelector . ':' . $newValidator,
            time() + 60 * 60 * 24 * 7,
            '/',
            '',
            true,   // secure
            true    // httponly
        );

        // Authenticate the user
        return $this->authenticate((int) $entry['user']);
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

        // Check Remember-Token Authentication (selector + validator pair)
        if ($this->byRememberToken()) {
            $this->method = 'remember_token';
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

                        // Check if TOTP is enabled for this user and not yet verified in this session
                        if ($this->user->setting('totp_enabled') === true) {
                            $twoFaVerified = $REQUEST->getParams('SESSION', 'auth-2fa-' . session_id());
                            if (!$twoFaVerified) {
                                $this->needs_2fa = true;
                                return true;  // password OK — middleware intercepts after this returns
                            }
                        }

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
     * Set up 2FA for a user
     *
     * @param int $userId
     * @param string $secret
     * @return bool
     */
    public function setupTotp(int $userId, string $secret): bool
    {
        // Update the user's TOTP secret in the database
        return $this->Database->query()
            ->table('users')
            ->update([
                'totp_secret' => $secret
            ])
            ->where('id', $userId)
            ->execute() > 0;
    }

    /**
     * Verify a TOTP code
     *
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function verifyTotp(int $userId, string $code): bool
    {
        // Retrieve the user's TOTP secret
        $user = $this->Database->query()
            ->table('users')
            ->select('totp_secret')
            ->where('id', $userId)
            ->limit(1)
            ->result();

        if (empty($user)) {
            return false;
        }

        // Use the TOTP library to verify
        $secret = $user[0]['totp_secret'];
        
        // Create a new pin with TOTP support
        $Pin = new Objects\Pin();
        return $Pin->verifyTotp($secret, $code);
    }
    
    /**
     * Generate a TOTP secret for a user
     *
     * @param int $userId
     * @return string|null
     */
    public function generateTotpSecret(int $userId): ?string
    {
        // Generate a random secret for TOTP using the phpseclib library
        $secret = '';
        for ($i = 0; $i < 20; $i++) {
            $secret .= chr(random_int(33, 126)); // Printable ASCII characters
        }
        
        // Store it in the user's profile
        if ($this->setupTotp($userId, $secret)) {
            return $secret;
        }
        
        return null;
    }

    /**
     * Generate recovery codes for a user
     *
     * @param int $userId
     * @return array|null
     */
    public function generateRecoveryCodes(int $userId): ?array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            // Generate UUID-like recovery codes  
            $code = bin2hex(random_bytes(8));
            $codes[] = strtoupper($code);
        }
        
        // Store the codes in a special table or as part of user profile
        $recoveryCodesJson = json_encode($codes, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        
        return $this->Database->query()
            ->table('users')
            ->update([
                'recovery_codes' => $recoveryCodesJson
            ])
            ->where('id', $userId)
            ->execute() > 0 ? $codes : null;
    }
    
    /**
     * Check if recovery codes are valid for a user
     *
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function verifyRecoveryCode(int $userId, string $code): bool
    {
        // Retrieve the user's recovery codes
        $user = $this->Database->query()
            ->table('users')
            ->select('recovery_codes')
            ->where('id', $userId)
            ->limit(1)
            ->result();
            
        if (empty($user)) {
            return false;
        }
        
        $recoveryCodes = json_decode($user[0]['recovery_codes'] ?? '[]', true);
        
        if (!is_array($recoveryCodes) || empty($recoveryCodes)) {
            return false;
        }
        
        // Check if the provided code matches one of the recovery codes
        $index = array_search(strtoupper($code), $recoveryCodes);
        
        if ($index !== false) {
            // Remove used recovery code
            unset($recoveryCodes[$index]);
            
            // Update user with remaining recovery codes 
            $this->Database->query()
                ->table('users')
                ->update([
                    'recovery_codes' => json_encode(array_values($recoveryCodes), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
                ])
                ->where('id', $userId)
                ->execute();
                
            return true;
        }
        
        return false;
    }
    
    /**
     * Set up a remember token for a user (selector/validator pair)
     *
     * @param int $userId
     * @return bool
     */
    public function setRememberToken(int $userId): bool
    {
        // Generate a random selector and validator
        $selector = bin2hex(random_bytes(16));  // 32-char hex 
        $validator = bin2hex(random_bytes(32));   // 64-char hex
        
        // Hash the validator for storage
        $validatorHash = password_hash($validator, PASSWORD_DEFAULT);
        
        // Set expiration (7 days)
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        // Store in database
        return $this->Database->query()
            ->table('remember_tokens')
            ->insert([
                'user' => $userId,
                'selector' => $selector,
                'validator_hash' => $validatorHash,
                'expires' => $expires,
                'created' => date('Y-m-d H:i:s')
            ])
            ->execute() > 0;
    }
    
    /**
     * Clear remember tokens for a user (e.g., on logout)
     *
     * @param int $userId
     * @return bool
     */
    public function clearRememberTokens(int $userId): bool
    {
        return $this->Database->query()
            ->table('remember_tokens')
            ->delete()
            ->where('user', $userId)
            ->execute() > 0;
    }
    
    /**
     * Get the remember token for a user based on selector
     *
     * @param string $selector
     * @return array|null
     */
    public function getRememberToken(string $selector): ?array
    {
        $result = $this->Database->query()
            ->table('remember_tokens')
            ->select('*')
            ->where('selector', $selector)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->limit(1)
            ->result();
            
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Generate password policy enforcement (not actually implemented yet)
     * 
     * @return void
     */
    public function enforcePasswordPolicy(): void
    {
        // This method would contain policy enforcement logic
        // Currently stubbed - will be expanded in future implementation
    }
}
