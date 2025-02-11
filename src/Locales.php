<?php

/**
 * Core Framework - Locales
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects;
use Exception;

class Locales {

    // Constants
    const Default = 'en-ca';
    const Charset = 'UTF-8';
    const Locales = [
        'en-ca',
        'fr-ca',
    ];

    // Global Properties
    private $Config;
    private $Log;
    private $Request;
    private $UUID;

    // Properties
    private $Locale = self::Default;
    private $Locales = [];
    private $Path;
    private $Key;
    private $Timezones;
    private $Timezone = 'UTC';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Global Variables
        global $CONFIG, $LOG, $REQUEST, $UUID;

        // Set Global Properties
        $this->Config = $CONFIG;
        $this->Log = $LOG;
        $this->Request = $REQUEST;
        $this->UUID = $UUID;

        // Configure Globals
        $this->Config->add('locale');
        $this->Log->add('locale');

        // Set Properties
        $this->Path = $this->Config->root() . DIRECTORY_SEPARATOR . 'Locale';
        $this->Key = $this->UUID->toString('LOCALE' . session_id());
        $this->Timezones = $this->getTimeZones();
        $this->timezone($this->Config->get('locale','timezone') ?? 'UTC');

        // Create Locale Directory
        if(!is_dir($this->Path)){
            mkdir($this->Path, 0755, true);
        }

        // Create Locale Files
        foreach(self::Locales as $locale){

            // Create Locale Directory if it does not exist
            if(!is_dir($this->Path . DIRECTORY_SEPARATOR . $locale)){

                // Create Locale Directory
                mkdir($this->Path . DIRECTORY_SEPARATOR . $locale, 0755, true);
            }
        }

        // Load Locales
        $this->load();
    }

    /**
     * Get Timezones.
     *
     * @return array
     */
    private function getTimeZones(): array
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    /**
     * Get Locales.
     *
     * @return self
     * @throws Exception
     */
    private function load(): self
    {
        // Retrieve Locales
        $locales = array_diff(scandir($this->Path), array('..', '.'));

        // Loop through Locales
        foreach($locales as $locale){

            // Check if Locale is a directory
            if(is_dir($this->Path . DIRECTORY_SEPARATOR . $locale)){

                // Set Locale
                $this->locale($locale);
            }
        }

        // Check if Locale is being changed by Request
        if(!is_null($this->Request->getParams('GET','locale'))){

            return $this->set($this->Request->getParams('GET','locale'));
        }

        // Check if Locale was set by Session
        if(!is_null($this->Request->getParams('SESSION',$this->Key))){

            return $this->set($this->Request->getParams('SESSION',$this->Key));
        }

        // Check if Locale was set by Cookie
        if(!is_null($this->Request->getParams('COOKIE','locale'))){

            return $this->set($this->Request->getParams('COOKIE','locale'));
        }

        // Check if Locale can be set by Browser
        if(!is_null($this->Request->getParams('SERVER','HTTP_ACCEPT_LANGUAGE'))){

            return $this->set(strtolower(substr($this->Request->getParams('SERVER','HTTP_ACCEPT_LANGUAGE') ?? self::Default, 0, 5)));
        }

        // Set Default Locale
        return $this->set(self::Default);
    }

    /**
     * Get Locale.
     *
     * @param string|null $locale
     * @return Objects\Locale
     */
    public function locale(?string $locale = null): Objects\Locale
    {
        $locale = $locale ?? $this->Locale;
        if(!in_array($locale, $this->Locales)){
            $this->Locales[$locale] = new Objects\Locale($locale);
        }
        return $this->Locales[$locale];
    }

    /**
     * Set Locale.
     *
     * @param string $locale
     * @return self
     */
    public function set(string $locale): self
    {
        // Check if Locale Exists
        if(!array_key_exists($locale, $this->Locales)){
            $locale = self::Default;
        }

        // Set Locale
        $this->Locale = $locale;

        // Set Session Locale if session is started
        if(session_status() == PHP_SESSION_ACTIVE){
            $this->Request->setParams('SESSION',$this->Key,$locale);
        }

        // Set Cookie Locale
        $this->Request->setParams('COOKIE','locale',$locale);

        // Return
        return $this;
    }

    /**
     * List Locales.
     *
     * @return array
     */
    public function list(): array
    {
        $list = [];
        foreach($this->Locales as $locale => $object){
            $list[$locale] = $object->language();
        }
        return $list;
    }

    /**
     * Get Current Locale.
     *
     * @return string
     */
    public function current(): string
    {
        return $this->Locale;
    }

    /**
     * Get Timezones.
     *
     * @return array
     */
    public function timezones(): array
    {
        return $this->Timezones;
    }

    /**
     * Get Timezone.
     *
     * @param string|null $timezone
     * @return string
     */
    public function timezone(?string $timezone = null): string
    {
        if($timezone && in_array($timezone, $this->Timezones)){
            $this->Timezone = $timezone;
        }
        date_default_timezone_set($this->Timezone);
        return $this->Timezone ?? '';
    }
}
