<?php

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Backend {

    // Global Properties
    protected $Database;

    // Properties
    protected $backend;
    protected $password;

    /**
     * Constructor
     *
     * @param string $backend
     * @param array $data
     * @throws Exception
     */
    public function __construct(?string $backend = null, ?array $data = null)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Check if backend data is provided
        if ($data) {

            // Set Backend
            $this->backend = $data;
        } else {

            // Retrieve Backend
            $query = $this->Database->query();
            $backend = $query->table('backends')
                ->select('*')
                ->where('id', $backend)
                ->limit(1)
                ->result();

            // Check if user exists
            if (count($backend) == 0) {
                throw new Exception('Backend not found');
            }

            // Set Backend
            $this->backend = $backend[0];
        }
    }

    /**
     * Set the Backend Password
     *
     * @param string $password
     * @return self
     */
    public function set(?string $password = null): self
    {
        // Implement in child class
        return $this;
    }

    /**
     * Validate the Backend Password
     *
     * @param string $password
     * @return bool
     */
    public function validate(?string $password = null): bool
    {
        // Implement in child class
        return false;
    }

    /**
     * Save the Backend
     *
     * @return self
     */
    public function save(): self
    {
        // Create the Query
        $query = $this->Database->query();

        // Update the Backend
        $query->table('backends')
            ->update($this->backend)
            ->where('id', $this->backend['id'])
            ->result();

        return $this;
    }

    /**
     * Generate a random string
     *
     * @param int $length
     * @param bool $onlyNumbers
     * @return string
     */
    private function generate(int $length = 8, bool $onlyNumbers = false): string
    {
        $characters = '0123456789';
        if (!$onlyNumbers) {
            $characters .= 'abcdefghijklmnopqrstuvwxyz';
            $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $characters .= '!@#$%^&*()_+{}:<>?';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Reset the Backend Password and notify the user
     *
     * @return string
     */
    public function reset(): string
    {
        // Generate a new password
        $this->password = $this->generate();

        // Set the new password
        $this->set($this->password)->save();

        // Return the new password
        return $this->password;
    }

    /**
     * Notify the user of the new password
     *
     * @param object $user
     * @param string $password
     * @return bool
     */
    public function notify(object $user, string $password): bool
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

                // Write the email
                $body = '';
                $body .= '<p>Your account password has been reset.</p>';
                $body .= '<p>Here is your new account password:</p>';
                $body .= '<pre style="background-color: #F5F5F5; font-weight: 700; font-size: 28px; text-align: center; letter-spacing: 16px; margin: 20px 20px; padding: 20px 0; font-family: Courier, monospace">'.($password ?? 'ERROR!').'</pre>';
                $body .= '<p>Please follow the link below to access %BRAND%.</p>';
                $body .= '<p style="text-align:center;margin-top: 40px;margin-bottom:40px;">';
                $body .= '<a href="'.$REQUEST->getHostAddress().'" target="_blank" style="margin-left: 6px; margin-right: 6px; text-decoration:none; background-color: #528fb3;color: #fff;font-size: 24px;padding: 20px 40px;text-align: center;margin: 20px 20px;border-radius: 8px;">%BRAND%</a>';
                $body .= '</p>';
                $body .= '<p>If you did not request this change, please contact your system administrator immediately.</p>';

                // Create a new message
                $eml = $SMTP->message()
                    ->to($user->username)
                    ->from($user->organization()->email ?? $CONFIG->get('smtp','username'))
                    ->subject('Your account password has been reset')
                    ->body($body)
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
    protected function logo()
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
}
