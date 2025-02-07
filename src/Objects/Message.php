<?php

/**
 * Core Framework - Message
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use DOMDocument;
use Exception;

class Message {

	// Constants
	const SMTP_OK = '250';
	const SMTP_DATA_OK = '354';
	const SMTP_DATA_DIRECTORY = 'data';

    // Global Properties
    private $Request;
    private $Config;
    private $Log;

    // Properties
    private $connection;
    private $from;
    private $replyTo;
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $attachment = [];
    private $subject;
    private $body;
    private $headers = [];
    private $vars = [];
    private $template;
	private $encoding = 'base64'; // quoted-printable or base64
    private $illegal = false;
    private $status = false;
	private $eml;
    private $path;

    /**
     * Constructor
     *
     * @param $connection
     * @param string $template
     */
    public function __construct($connection)
    {
        // Global Variables
        global $REQUEST, $CONFIG, $LOG;

        // Initialize Properties
        $this->connection = $connection;
        $this->Request = $REQUEST;
        $this->Config = $CONFIG;
        $this->Log = $LOG;

        // Import smtp
        $this->Config->add('smtp');

        // Retrieve SMTP Settings
        $this->illegal = $this->Config->get('smtp', 'illegal') ?: $this->illegal;
        $this->encoding = $this->Config->get('smtp', 'encoding') ?: $this->encoding;
    }

    /**
     * This method evaluate if a string contains HTML tags.
     *
     * @param  string  $string
     * @return bool
     */
    private function hasHTML($string): bool
    {
        return ($string !== strip_tags($string));
    }

    /**
     * This method converts a string that may contain HTML into plain text.
     *
     * @param  string  $html
     * @return string
     */
    private function toText($html): string
    {

        // Check if content contains HTML
        if($this->hasHTML($html)){

            // Initialise $text
            $text = '';

            // Create an empty DOMDocument
            $dom = new DOMDocument();

            // Suppress any parsing errors
            libxml_use_internal_errors(true);

            // Load Content
            $dom->loadHTML($html);

            // Retrieve the body
            $content = $dom->textContent;

            // Remove spaces between new lines
            $content = preg_replace('/\n\s+\n/', "\n\n", $content);

            // Convert to array based on lines
            $lines = explode(PHP_EOL,$content);

            // Trim each lines of extra spaces add them to the $text string
            foreach($lines as $line){
                $text .= trim($line) . PHP_EOL;
            }

            // Return $text string
            return trim($text);
        }

        // Otherwise return the original content
        return $html;
    }

    /**
     * Sanitize string to remove illegal characters.
     *
     * @param string $string
     * @return string
     */
    private function sanitize(string $string): string
    {
        // Return original string if illegal characters are allowed
        if ($this->illegal) { return $string; }

        // Array of French accented characters and their ASCII equivalents
        $accents = array(
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ç' => 'c', 'é' => 'e',
            'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ÿ' => 'y', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ç' => 'C',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Î' => 'I',
            'Ï' => 'I', 'Ô' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Û' => 'U',
            'Ü' => 'U', 'Ÿ' => 'Y'
        );

        // Replace accented characters with their non-accented counterparts
        $string = strtr($string, $accents);

        // Define illegal characters
        $illegalChars = array(
            '\n' => '',
            '\r\n' => '',
            '<' => '',
            '>' => '',
            '{' => '',
            '}' => '',
            '|' => '/',
            '\\' => '/',
        );

        // Replace illegal characters with a safe alternative or remove them
        $string = strtr($string, $illegalChars);

        return $string;
    }

    /**
     * Parse string to replace variables with provided values.
     *
     * @param  string $string
     * @return string
     * @throws Exception
     */
    private function parse(string $string): string
    {
        // Replace System Variables
        $string = str_replace('%IP%',$this->Log->ip(),$string);
        $string = str_replace('%AGENT%',$this->Log->agent(),$string);
        $string = str_replace('%HOST%',$this->Request->getHost(),$string);
        $string = str_replace('%HOSTADDRESS%',$this->Request->getHostAddress(),$string);
        $string = str_replace('%NAMESPACE%',$this->Request->getNamespace(),$string);
        $string = str_replace('%YEAR%',date('Y'),$string);
        $string = str_replace('%MONTH%',date('m'),$string);
        $string = str_replace('%DAY%',date('d'),$string);
        $string = str_replace('%DATE%',date('Y-m-d'),$string);
        $string = str_replace('%TIME%',date('H:i:s'),$string);
        $string = str_replace('%DATETIME%',date('Y-m-d H:i:s'),$string);
        $string = str_replace('%FROM%',$this->from,$string);
        $string = str_replace('%TO%',implode(', ',$this->to),$string);
        $string = str_replace('%CC%',implode(', ',$this->cc),$string);
        $string = str_replace('%BCC%',implode(', ',$this->bcc),$string);
        $string = str_replace('%SUBJECT%',$this->subject,$string);
        $string = str_replace('%BODY%',$this->body,$string);

        // Replace Template Variables
        foreach($this->vars as $key => $value){
            $string = str_replace('%' . strtoupper($key) . '%',$value,$string);
        }

        // Replace Server Variables
        foreach($this->Request->getParams('SERVER') as $key => $value){
            $string = str_replace('%' . strtoupper($key) . '%',$value,$string);
        }

        // Replace Env Variables
        foreach($this->Request->getParams('ENV') as $key => $value){
            $string = str_replace('%' . strtoupper($key) . '%',$value,$string);
        }

        return $string;
    }

    /**
     * Set the message template.
     *
     * @param  string $template
     * @return self
     */
    public function template(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Set the email address of the sender.
     *
     * @param  string $from
     * @return self
     */
    public function from(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Set the email address to reply to.
     *
     * @param  string $replyTo
     * @return self
     */
    public function replyTo(string $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * Set the email address of the recipient.
     *
     * @param  string $to
     * @return self
     */
    public function to(string $to): self
    {
        $this->to[] = $to;
        return $this;
    }

    /**
     * Set the email address of the carbon copy recipient.
     *
     * @param  string $cc
     * @return self
     */
    public function cc(string $cc): self
    {
        $this->cc[] = $cc;
        return $this;
    }

    /**
     * Set the email address of the blind carbon copy recipient.
     *
     * @param  string $bcc
     * @return self
     */
    public function bcc(string $bcc): self
    {
        $this->bcc[] = $bcc;
        return $this;
    }

    /**
     * Set the email subject.
     *
     * @param  string $attachment
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the email body.
     *
     * @param  string $body
     * @return self
     */
    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set the email attachment.
     *
     * @param  string $attachment
     * @return self
     */
    public function attachment(string $attachment): self
    {
        $this->attachment[] = $attachment;
        return $this;
    }

    /**
     * Set a variable to be replaced in the template.
     *
     * @param  string $key
     * @param  mixed $value
     * @return self
     */
    public function var(string $key, mixed $value): self
    {
        $this->vars[$key] = $value;
        return $this;
    }

    /**
     * Set a header to be included in the email.
     *
     * @param  mixed $header
     * @return self
     */
    public function header(mixed $header): self
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Send the email.
     *
     * @return self
     */
    public function send(): self
    {
        // Check if the message has already been sent
        if($this->status){
            return $this;
        }

        // Attempt to send the email
        try {

            // Sender
            fputs($this->connection, "MAIL FROM:<{$this->from}>" . PHP_EOL);
            $out = fgets($this->connection, 1024);
            $this->Log->set('smtp')->debug("SMTP Sender: {$out}");
            if (substr($out, 0, 3) != self::SMTP_OK) {
                throw new Exception("{$out}");
            }

            // Recipients To handling
            if(!empty($this->to)){
                foreach ($this->to as $recipient) {
                    $this->Log->set('smtp')->debug("Sending RCPT TO for TO: {$recipient}");
                    fputs($this->connection, "RCPT TO:<{$recipient}>" . PHP_EOL);
                    $out = fgets($this->connection, 1024);
                    $this->Log->set('smtp')->debug("SMTP TO Recipient: {$out}");
                    if (substr($out, 0, 3) != self::SMTP_OK) {
                        throw new Exception("{$out}");
                    }
                }
            }

            // Recipients CC handling
            if(!empty($this->cc)){
                foreach ($this->cc as $recipient) {
                    $this->Log->set('smtp')->debug("Sending RCPT TO for CC: {$recipient}");
                    fputs($this->connection, "RCPT TO:<{$recipient}>" . PHP_EOL);
                    $out = fgets($this->connection, 1024);
                    $this->Log->set('smtp')->debug("SMTP CC Recipient: {$out}");
                    if (substr($out, 0, 3) != self::SMTP_OK) {
                        throw new Exception("{$out}");
                    }
                }
            }

            // Recipients BCC handling
            if(!empty($this->bcc)){
                foreach ($this->bcc as $recipient) {
                    $this->Log->set('smtp')->debug("Sending RCPT TO for BCC: {$recipient}");
                    fputs($this->connection, "RCPT TO:<{$recipient}>" . PHP_EOL);
                    $out = fgets($this->connection, 1024);
                    $this->Log->set('smtp')->debug("SMTP BCC Recipient: {$out}");
                    if (substr($out, 0, 3) != self::SMTP_OK) {
                        throw new Exception("{$out}");
                    }
                }
            }

            // Data
            fputs($this->connection, "DATA" . PHP_EOL);
            $out = fgets($this->connection, 1024);
            $this->Log->set('smtp')->debug("SMTP Data: {$out}");
            if (substr($out, 0, 3) != self::SMTP_DATA_OK) {
                throw new Exception("{$out}");
            }

            // Subject preparation
            $this->subject = $this->sanitize($this->parse($this->subject));

            // Body preparation
            $html = $this->parse($this->template);
            if(!$html){
                throw new Exception("Unable to retreive the body for the message.");
            }
            $text = $this->toText($html);

            // Boundary for multipart emails
            $boundary = strtoupper(uniqid(time() . '-'));

            // Headers setup
            $headers = "From: {$this->from}" . PHP_EOL;
            $headers .= "To: " . implode(',', $this->to) . PHP_EOL;
            if (!empty($this->replyTo)) {
                $headers .= "Reply-To: " . implode(',', $this->replyTo) . PHP_EOL;
            }
            if (!empty($this->cc)) {
                $headers .= "Cc: " . implode(',', $this->cc) . PHP_EOL;
            }
            $headers .= "Subject: " . $this->subject . PHP_EOL;
            $headers .= "Date: " . date('r') . PHP_EOL;
            $headers .= "MIME-Version: 1.0" . PHP_EOL;

            // Add custom headers (including JSON if needed)
            foreach ($this->headers as $header) {
                if (is_array($header)) {
                    // JSON-encode the header value if it is an array
                    $header_value = json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $headers .= "X-Custom-Header: {$header_value}" . PHP_EOL;
                } else {
                    $headers .= $header . PHP_EOL;
                }
            }

            // Check encoding and apply it to the message body
            if ($this->encoding === 'base64') {
                $encodedTextBody = chunk_split(base64_encode($text));
                $encodedHtmlBody = chunk_split(base64_encode($html));
            } else {
                $encodedTextBody = quoted_printable_encode($text);
                $encodedHtmlBody = quoted_printable_encode($html);
            }

            // Determine if multipart
            $multipart = (count($this->attachment) > 0 || $this->hasHTML($html));
            if ($multipart) {
                $headers .= "Content-Type: multipart/alternative; boundary={$boundary}" . PHP_EOL . PHP_EOL;
            }

            // Message preparation
            $message = '';
            if ($multipart) {
                $message .= "--{$boundary}" . PHP_EOL;
            }

            // Insert plain text part
            $message .= "Content-Type: text/plain; charset=UTF-8" . PHP_EOL;
            $message .= "Content-Transfer-Encoding: " . $this->encoding . PHP_EOL . PHP_EOL;
            $message .= $encodedTextBody . PHP_EOL;

            // Insert HTML part if present
            if ($this->hasHTML($html)) {
                $message .= "--{$boundary}" . PHP_EOL;
                $message .= "Content-Type: text/html; charset=UTF-8" . PHP_EOL;
                $message .= "Content-Transfer-Encoding: " . $this->encoding . PHP_EOL . PHP_EOL;
                $message .= $encodedHtmlBody . PHP_EOL;
            }

            // Handle attachments
            foreach ($this->attachment as $attachment) {
                $file_path = $attachment;
                $file_name = basename($file_path);
                $file_mime_type = mime_content_type($file_path);
                $file_content = chunk_split(base64_encode(file_get_contents($file_path)));
                $message .= "--{$boundary}" . PHP_EOL;
                $message .= "Content-Type: $file_mime_type; name=\"$file_name\"" . PHP_EOL;
                $message .= "Content-Transfer-Encoding: base64" . PHP_EOL;
                $message .= "Content-Disposition: attachment; filename=\"$file_name\"" . PHP_EOL . PHP_EOL;
                $message .= $file_content . PHP_EOL;
            }

            // Finalize message
            if ($multipart) {
                $message .= "--{$boundary}--" . PHP_EOL;
            }
            $message .= "." . PHP_EOL;

            // Log the message
            $this->Log->set('smtp')->debug("SMTP Message: " . PHP_EOL . $headers . $message);

            // Send message
            fputs($this->connection, $headers . $message);
            $out = fgets($this->connection, 1024);
            $this->Log->set('smtp')->debug("SMTP Message: {$out}");

            // Check if the message was sent successfully
            if (substr($out, 0, 3) != self::SMTP_OK) {
                throw new Exception("{$out}");
            }

            // Extract the Message-ID if present in the response
            if (preg_match('/<(.+)>/', $out, $matches)) {
                $messageId = $matches[1];
                $this->Log->set('smtp')->success("Message-ID: {$messageId}");
            }

            // return the message
            $this->eml = $headers . $message;
            $this->status = true;
            $this->Log->set('smtp')->success("Email sent successfully");
        } catch (Exception $e) {

            // Log error
            $this->Log->set('smtp')->error('SMTP Error: '.$e->getMessage());
        }

        return $this;
    }

    /**
     * Return the message status.
     *
     * @return bool
     */
    public function status(): bool
    {
        return $this->status;
    }

    /**
     * Return the eml file path.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Save the sent email as a .eml file in the data directory.
     *
     * @return self
     * @throws Exception
     */
    public function save(): self
    {
        try{
            // Global Variables
            global $UUID;

            // Generate a unique file name
            $filename = $UUID->toString(time()).'.eml';
            $path = $this->Config->root() . DIRECTORY_SEPARATOR . self::SMTP_DATA_DIRECTORY;

            // Check if path exists and create it if it doesn't
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new Exception("Unable to create directory {$path}");
                }
            }

            // Validate the directory
            if (is_dir($path) && is_writable($path)) {

                // Create the file
                if (file_put_contents($path . DIRECTORY_SEPARATOR . $filename, trim($this->eml))) {

                    // Save the path
                    $this->path = $path . DIRECTORY_SEPARATOR . $filename;

                    // Log success
                    $this->Log->set('smtp')->success("Message Saved: ".$path . DIRECTORY_SEPARATOR . $filename);
                } else {

                    // Throw an exception if the file cannot be created
                    throw new Exception("Unable to create ".$path . DIRECTORY_SEPARATOR . $filename);
                }
            } else {

                // Throw an exception if the directory is invalid
                throw new Exception("Invalid directory specified: {$path}");
            }
        } catch (Exception $e) {

            // Log error
            $this->Log->set('smtp')->error('SMTP Error: '.$e->getMessage());
        }

        return $this;
    }
}
