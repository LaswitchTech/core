<?php

/**
 * Core Framework - SMTP
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects\Message;
use Exception;

class SMTP {

	// Constants
	const SMTP_OK = '250';
	const SMTP_DATA_OK = '354';
	const SMTP_AUTH_OK = '334';
	const SMTP_USERNAME_OK = '334';
	const SMTP_PASSWORD_OK = '235';

    // Global Properties
    private $Config;
    private $Log;

    // Properties
    private $connection;
    private $status = false;
	private $host = "localhost";
	private $port = 465;
	private $encryption = "ssl";
	private $username;
	private $password;
	private $encoding = 'base64'; // quoted-printable or base64
	private $illegal = false;
	private $templates = [];
	private $template;
	private $path;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $CONFIG, $LOG;

        // Initialize Properties
        $this->Config = $CONFIG;
        $this->Log = $LOG;
        $this->path = $this->Config->root() . '/Template/Mail';

        // Import smtp
        $this->Config->add('smtp');
        $this->Log->add('smtp');

        // Retrieve SMTP Settings
        $this->username = $this->Config->get('smtp', 'username') ?: $this->username;
        $this->password = $this->Config->get('smtp', 'password') ?: $this->password;
        $this->host = $this->Config->get('smtp', 'host') ?: $this->host;
        $this->port = $this->Config->get('smtp', 'port') ?: $this->port;
        $this->encryption = $this->Config->get('smtp', 'encryption') ?: $this->encryption;
        $this->encoding = $this->Config->get('smtp', 'encoding') ?: $this->encoding;
        $this->illegal = $this->Config->get('smtp', 'illegal') ?: $this->illegal;

        // Add Template directory if missing
        if(!is_dir($this->path)){
            mkdir($this->path, 0755, true);
        }

        // Add default template
        if(is_file($this->path . '/default.html')){
            if($this->add('default',$this->path . '/default.html')){
                $this->set('default');
            }
        } else {
            if(is_file($this->path . '/default.txt')){
                if($this->add('default',$this->path . '/default.txt')){
                    $this->set('default');
                }
            }
        }
    }

    /**
     * This method closes the SMTP connection when the object is destroyed.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * This method closes the SMTP connection.
     *
     * @return self
     */
    public function close(): self
    {
        // Check if a connection exist
        if($this->connection){

            // Close the active connection
            fclose($this->connection);

            // Clear the connection
            $this->connection = null;

            // Clear the status
            $this->status = false;

            // Log the closing
            $this->Log->set('smtp')->success("SMTP connection closed");
        }

        return $this;
    }

    /**
     * This method connects to an SMTP server using the specified credentials and encryption type.
     *
     * @param  string  $host
     * @param  int  $port
     * @param  string  $encryption
     * @return self
     * @throws Exception
     */
    public function connect(?string $host = null,?int $port = null,?string $encryption = null): self
    {
        // Setup the connection parameters
        $host = $host ?: $this->host;
        $port = $port ?: $this->port;
        $encryption = $encryption ?: $this->encryption;

        // If a connection is already established return it
        if (!is_null($this->connection)) {
            return $this;
        }

        // Attempt to connect to the SMTP server
        try {

            // Set encryption
            $ssl = in_array($encryption, ['SSL', 'ssl']);
            if($ssl){
                $host = 'ssl://' . $host;
            }

            // Connect to an SMTP server
            $this->Log->set('smtp')->info("Establishing connection to SMTP server.");
            $smtp = stream_socket_client($host . ':' . $port, $errno, $errstr, 30);
            if (!$smtp) {
                throw new Exception("Could not connect to SMTP server: {$errstr}");
            }
            $this->connection = $smtp;
            $this->Log->set('smtp')->success("SMTP server connected.");

            // Greeting
            $greeting = fgets($this->connection, 1024);
            if (!$greeting) {
                throw new Exception("No greeting received from SMTP server");
            }
            if (substr($greeting, 0, 3) != '220') {
                throw new Exception("{$greeting}");
            }

            // EHLO
            fputs($this->connection, "EHLO {$host}" . PHP_EOL);
            $ehlo_response = '';
            while ($line = fgets($this->connection, 1024)) {
                $ehlo_response .= $line;
                if (substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            if (substr($ehlo_response, 0, 3) != self::SMTP_OK) {
                throw new Exception("{$ehlo_response}");
            }

            // TLS
            if ($ssl && strpos($ehlo_response, 'STARTTLS') !== false) {
                fputs($this->connection, "STARTTLS" . PHP_EOL);
                $tls_response = fgets($this->connection, 1024);
                if (substr($tls_response, 0, 3) != '220') {
                    throw new Exception("{$tls_response}");
                }
                if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new Exception("Could not start TLS encryption.");
                }
                // Re-issue EHLO
                fputs($this->connection, "EHLO {$_SERVER['HTTP_HOST']}" . PHP_EOL);
                $ehlo_response = '';
                while ($line = fgets($this->connection, 1024)) {
                    $ehlo_response .= $line;
                    if (substr($line, 3, 1) === ' ') {
                        break;
                    }
                }
                if (substr($ehlo_response, 0, 3) != self::SMTP_OK) {
                    throw new Exception("{$ehlo_response}");
                }
            }
        } catch (Exception $e) {

            // Log the error
            $this->Log->set('smtp')->error('SMTP Error: '.$e->getMessage());

            // Close the connection
            $this->close();
        }

        return $this;
    }

    /**
     * This method authenticates the connection to the SMTP server using the specified username and password.
     *
     * @param  string  $username
     * @param  string  $password
     * @return self
     * @throws Exception
     */
    public function authenticate(?string $username = null,?string $password = null): self
    {

        // Setup the connection parameters
        $username = $username ?: $this->username;
        $password = $password ?: $this->password;

        // Check if the connection is already authenticated
        if($this->isAuthenticated()){ return $this; }

        // Attempt to authenticate on the SMTP server
        try{

            // Check if a connection exist
            if(!$this->isConnected()){
                throw new Exception("No connection to SMTP server.");
            }

            // Authenticate
            $this->Log->set('smtp')->info("Authenticating on SMTP server.");
            fputs($this->connection, "AUTH LOGIN" . PHP_EOL);
            $out = fgets($this->connection, 1024);
            if (substr($out, 0, 3) != self::SMTP_AUTH_OK) {
                throw new Exception("{$out}");
            }

            // Send username
            fputs($this->connection, base64_encode($username) . PHP_EOL);
            $out = fgets($this->connection, 1024);
            if (substr($out, 0, 3) != self::SMTP_USERNAME_OK) {
                throw new Exception("{$out}");
            }

            // Send password
            fputs($this->connection, base64_encode($password) . PHP_EOL);
            $out = fgets($this->connection, 1024);
            if (substr($out, 0, 3) != self::SMTP_PASSWORD_OK) {
                throw new Exception("{$out}");
            }

            // If we've got this far, authentication was successful
            $this->status = true;
            $this->Log->set('smtp')->success("Authenticated on SMTP server");
        } catch (Exception $e) {

            // Log the error
            $this->Log->set('smtp')->error('SMTP Error: '.$e->getMessage());

            // Close the connection
            $this->close();
        }

        return $this;
    }

    /**
     * Check if the connection is established.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return !is_null($this->connection);
    }

    /**
     * Check if the connection is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->status;
    }

    /**
     * This method is used to add template files.
     *
     * @param  string  $name
     * @param  string  $file
     * @return self
     * @throws Exception
     */
    public function add(string $name,?string $file = null): self
    {
        // Set File
        $file = is_null($file) ? $this->path . '/' . $name . '.html' : $file;

        // Attempt to add the template
        try {

            // Check if the file exist
            if(is_file($file)){

                // Check if the template does not already exist
                if(!isset($this->templates[$name])){

                    // Add the template
                    $this->templates[$name] = $file;
                } else {

                    // Throw an exception
                    throw new Exception("This template already exist.");
                }
            } else {

                // Throw an exception
                throw new Exception("Could not find the following template file: {$file}.");
            }
        } catch (Exception $e) {

            // Log error
            $this->Log->set('smtp')->error('SMTP template error: '.$e->getMessage());
        }

        return $this;
    }

    /**
     * This method is used to select a template file.
     *
     * @param  string  $name
     * @return self
     * @throws Exception
     */
    public function set(string $name): self
    {
        // Attempt to set the template
        try {

            // Check if the template exist
            if(isset($this->templates[$name])){

                // Set the template
                $this->template = $name;
            } else {

                // Throw an exception
                throw new Exception("Could not find the requested template.");
            }
        } catch (Exception $e) {

            // Log error
            $this->Log->set('smtp')->error('SMTP template error: '.$e->getMessage());
        }

        return $this;
    }

    public function message(): Message
    {
        if(!$this->isAuthenticated()){ $this->authenticate(); }
        $message = new Message($this->connection);
        $message->template(file_get_contents($this->templates[$this->template]));
        $message->from($this->username);
        return $message;
    }

    /**
     * Check if the module is installed
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        // Check if the module is currently installed
        return $this->isConnected() && $this->Config->reload('auth')->get('smtp', 'installed') === true;
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
        if(isset($config['encryption'],$config['host'],$config['port'],$config['username'],$config['password'])){

            // Save the settings
            $this->Config->set('smtp', 'encryption', $config['encryption']);
            $this->Config->set('smtp', 'username', $config['username']);
            $this->Config->set('smtp', 'password', $config['password']);
            $this->Config->set('smtp', 'host', $config['host']);
            $this->Config->set('smtp', 'port', $config['port']);

            // Retrieve SMTP Settings
            $this->encryption = $this->Config->get('smtp', 'encryption') ?: $this->encryption;
            $this->username = $this->Config->get('smtp', 'username') ?: $this->username;
            $this->password = $this->Config->get('smtp', 'password') ?: $this->password;
            $this->host = $this->Config->get('smtp', 'host') ?: $this->host;
            $this->port = $this->Config->get('smtp', 'port') ?: $this->port;

            // Connect to the smtp server
            $this->connect();

            // Check if the smtp server is connected
            if($this->isConnected()){

                // Authenticate to the SMTP Server
                $this->authenticate();

                // Check if the SMTP Server is authenticated
                if($this->isAuthenticated()){

                    // Create a new message
                    $message = $this->message()
                        ->to($this->username)
                        ->subject('Test Email from %HOST%')
                        ->body('This is a test email')
                        ->var('logo', 'data:image/png;base64,' . base64_encode(file_get_contents($this->Config->root() . '/src/icons/icon.png')))
                        ->var('brand', 'Core Framework')
                        ->var('greetings', "Sincerely");

                    // Send the message
                    $message->send();

                    // Check if the message was sent
                    if($message->status()){

                        // Save the message
                        $message->save();

                        // Set the smtp module as installed
                        $this->Config->set('smtp', 'installed', true);

                        // Add a true status
                        $status[] = true;
                    } else {
                        $status[] = "Could not send the test email";
                    }
                } else {
                    $status[] = "Could not authenticate to the smtp server";
                }
            } else {
                $this->Config->delete('smtp');
                $status[] = "Could not connect to the smtp server";
            }
        } else {
            $status[] = "Missing required fields";
        }

        // Return the statuses
        return $status;
    }
}
