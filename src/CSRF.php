<?php

/**
 * Core Framework - CSRF
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class CSRF {

    // Constants
    const FIELD = '%UUID%';
    const LENGTH = 32;
    const ROTATION = true;

    // Global Properties
    protected $Request;
    protected $Output;
	protected $Config;
    protected $UUID;

    // Properties
    protected $Token = null;
    protected $Field = self::FIELD;
    protected $Length = self::LENGTH;
    protected $Rotate = self::ROTATION;

    /**
     * Constructor
     */
    public function __construct()
    {

        // Import Global Variables
        global $REQUEST, $OUTPUT, $CONFIG, $UUID;

        // Initialize Properties
        $this->Request = $REQUEST;
        $this->Output = $OUTPUT;
        $this->Config = $CONFIG;
        $this->UUID = $UUID;

        // Add the csrf config file
        $this->Config->add('csrf');

        // Retrieve and Set CSRF Settings
        $this->Field = $this->Config->get('csrf', 'field') ?? $this->Field;
        $this->Length = $this->Config->get('csrf', 'length') ?? $this->Length;
        $this->Rotate = $this->Config->get('csrf', 'rotate') ?? $this->Rotate;

        // Check if the method used should be validated
        if(!defined('STDIN') && in_array($this->Request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])){
            if(!$this->validate($this->Request->getParams('POST', $this->Field) ?? null)){
                $this->Output->print(
                    'Invalid CSRF Token',
                    array('Content-Type: application/json', 'HTTP/1.1 403 Forbidden'),
                );
            }
        }
    }

    /**
     * Parse a string
     */
    protected function parse(string $string): string
    {
        $string = str_replace('%SESSION%', session_id(), $string);
        $string = str_replace('%UUID%', $this->UUID->toString("csrf-" . session_id()), $string);
        return $string;
    }

    /**
     * Generate token.
     * @return $this
     */
    protected function generate(){

        // Retrieve the existing Token
        $token = $this->Request->getParams('SESSION', $this->Field) ?? $this->UUID->toString();

        // Store the token
        $this->Token = $token;
        $this->Request->setParams('SESSION', $this->Field, $this->Token);

        // Return
        return $this;
    }

    /**
     * Clear token.
     * @return $this
     */
    protected function clear(){

        // Check if rotate is enabled
        if($this->Rotate){

            // Clear the token
            $this->Token = null;
            $this->Request->setParams('SESSION', $this->Field, null);
        }

        // Return
        return $this;
    }

    /**
     * Get token.
     * @return string $this->Token
     */
    public function token(){
        return $this->generate()->Token;
    }

    /**
     * Validate a token.
     * @param  string  $token
     * @return boolean
     */
    public function validate(?string $token){
        $this->generate();
        if(!empty($token) && hash_equals($token, $this->Token)){
            $this->clear()->generate();
            return true;
        }
        return false;
    }

    /**
     * Generate a hidden input field.
     */
    public function field(){
        return '<input type="hidden" name="' . $this->Field . '" value="' . $this->token() . '">';
    }
}
