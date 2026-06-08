<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Hash;

class Pin {

    protected $Database;
    protected $Pin;
    protected $isTotp = false;

    /**
     * Constructor
     */
    public function __construct(?int $id = null)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // If an ID is provided, load the pin
        if ($id !== null) {
            $query = $this->Database->query();
            $pin = $query->table('pins')
                ->select('*')
                ->where('id', $id)
                ->limit(1)
                ->result();

            if (count($pin) > 0) {
                $this->Pin = $pin[0];
                // Check if this is a TOTP pin
                $this->isTotp = isset($this->Pin['type']) && $this->Pin['type'] === 'totp';
            } else {
                throw new Exception("Pin not found");
            }
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Clean up expired pins
        $this->clean();
    }

    /**
     * Generate a random numeric pin
     *
     * @param int $length
     * @return string
     */
    public function generate(int $length = 6): string
    {
        // Define possible characters
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $pin = '';

        // Generate random pin
        for ($i = 0; $i < $length; $i++) {
            $pin .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $pin;
    }
    
    /**
     * Generate a TOTP (Time-based One-Time Password)
     *
     * @param string $secret
     * @param int $window
     * @return string
     */
    public function generateTotp(string $secret, int $window = 30): string
    {
        // Use phpseclib to generate TOTP
        $timestamp = floor(time() / $window);
        
        // Convert timestamp to bytes
        $counter = pack('N*', $timestamp);
        
        // Create HMAC-SHA1 hash (or SHA256 if preferred)
        $hash = hash_hmac('sha1', $counter, $secret, true);
        
        // Dynamic truncation - get 4 bytes from the hash 
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24) |
                  ((ord($hash[$offset + 1]) & 0xFF) << 16) |
                  ((ord($hash[$offset + 2]) & 0xFF) << 8) |
                  (ord($hash[$offset + 3]) & 0xFF);
        
        // Generate 6-digit TOTP
        return str_pad($binary % 1000000, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify a TOTP code
     *
     * @param string $secret
     * @param string $code
     * @param int $window
     * @return bool
     */
    public function verifyTotp(string $secret, string $code, int $window = 30): bool
    {
        // Check if the code is valid for current time window
        $currentCode = $this->generateTotp($secret, $window);
        if (hash_equals($currentCode, $code)) {
            return true;
        }
        
        // Try a few time windows back to handle sync issues
        for ($i = 1; $i <= 2; $i++) {
            $pastTimestamp = floor((time() - $i * $window) / $window);
            $pastCode = $this->generateTotp($secret, $window);
            if (hash_equals($pastCode, $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Save the pin to the database
     *
     * @param int $userId
     * @param string $pin
     * @param int $expiry (in minutes)
     * @param string $type (numeric, totp)
     * @return bool
     */
    public function save(int $userId, string $pin, int $expiry = 15, string $type = 'numeric'): bool
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('pins')
            ->insert([
                'user' => $userId,
                'hash' => password_hash($pin, PASSWORD_DEFAULT),
                'expiry' => date('Y-m-d H:i:s', strtotime("+$expiry minutes")),
                'type' => $type
            ]);

        // Execute the Query
        $affectedRows = $Query->execute();

        // Execute the Query
        $id = $Query->lastId();

        // Check if the pin was created
        if ($affectedRows > 0) {

            // Load the pin
            $this->__construct($id);

            // Update the user with the new pin ID
            $Query = $this->Database->query()
                ->table('users')
                ->update([
                    'pin' => $id
                ])
                ->where('id', $userId);

            return $Query->execute() > 0;
        }

        return false;
    }

    /**
     * Verify the pin
     *
     * @param string $code
     * @return bool
     */
    public function verify(string $code): bool
    {
        // Check if the pin is loaded
        if (!isset($this->Pin)) {
            return false;
        }

        // Check if the pin is expired
        if (strtotime($this->Pin['expiry']) < time()) {
            return false;
        }

        // For TOTP pins, we need special handling
        if ($this->isTotp) {
            // We would normally need the secret here - for now using legacy verification
            return password_verify($code, $this->Pin['hash']);
        }

        // Verify the pin
        return password_verify($code, $this->Pin['hash']);
    }

    /**
     * Notify the user with the pin via email
     *
     * @param object $user
     * @param string $pin
     * @return bool
     */
    public function notify(object $user, string $pin, callable $fn): bool
    {
        // Import Global Variables
        global $SMTP, $CONFIG, $REQUEST;

        // Initialize the status
        $status = false;

        // Connect to the smtp server
        $SMTP->connect();

        // Check if the smtp server is connected
        if($SMTP->isConnected()){

            // Authenticate to the SMTP Server
            $SMTP->authenticate();

            // Check if the SMTP Server is authenticated
            if($SMTP->isAuthenticated()){

                // Create a new message
                $eml = $fn($user,$pin);

                // Configure the message
                $eml->to($user->username)
                    ->from($user->organization()->email ?? $CONFIG->get('smtp','username'))
                    ->var('greetings', "Sincerely,<br>".$user->organization()->name."'s Team");

                // Send the message
                $eml->send();

                // Check if the message was sent
                if($status = $eml->status()){

                    // Save the message
                    $eml->save();
                }
            }
        }

        return $status;
    }

    /**
     * Clean up expired pins
     *
     * @return void
     */
    private function clean(): void
    {
        // Delete expired pins
        $this->Database->query()
            ->table('pins')
            ->delete()
            ->where('expiry', date('Y-m-d H:i:s'), '<')
            ->execute();
    }
}
