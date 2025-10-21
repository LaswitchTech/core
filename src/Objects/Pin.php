<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Pin {

    protected $Database;
    protected $Pin;

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
            $pin .= $characters[rand(0, $charactersLength - 1)];
        }

        return $pin;
    }

    /**
     * Save the pin to the database
     *
     * @param int $userId
     * @param string $pin
     * @param int $expiry (in minutes)
     * @return bool
     */
    public function save(int $userId, string $pin, int $expiry = 15): bool
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('pins')
            ->insert([
                'user' => $userId,
                'hash' => password_hash($pin, PASSWORD_DEFAULT),
                'expiry' => date('Y-m-d H:i:s', strtotime("+$expiry minutes"))
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
                    ->var('logo', 'data:'.mime_content_type($CONFIG->root() . '/webroot' . $this->logo()).';base64,' . base64_encode(file_get_contents($CONFIG->root() . '/webroot' . $this->logo())))
                    ->var('brand', $CONFIG->get('application','name'))
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
     * Get the logo path
     *
     * @return string
     */
    private function logo(): string
    {
        // Import Global Variables
        global $CONFIG;

        $src = '/assets/img/logo.svg';
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.jpg';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.gif';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.webp';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.png';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.svg';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.jpg';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.gif';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.webp';
        }
        if(!is_file($CONFIG->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.png';
        }
        return $src;
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
