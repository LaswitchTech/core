<?php

/**
 * Core Framework - Locale
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Locale {

    // Constants
    const Charset = 'UTF-8';

    // Global Properties
    private $Config;

    // Properties
    private $Path;
    private $Locale;
    private $Translation = [];
    private $Language;
    private $Region;
    private $Charset = self::Charset;
    private $Direction = "ltr";

    /**
     * Constructor
     *
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        // Global Variables
        global $CONFIG;

        // Set Global Properties
        $this->Config = $CONFIG;

        // Set Properties
        $this->Path = $this->Config->root() . DIRECTORY_SEPARATOR . 'Locale';
        $this->Locale = $locale;

        // Load Locale
        $this->load();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Save Locale
        $this->save();
    }

    /**
     * Get Locale.
     *
     * @return string
     */
    public function locale(): string
    {
        return $this->Locale;
    }

    /**
     * Get Language.
     *
     * @param  string|null  $language
     * @return string
     */
    public function language(?string $language = null): string
    {
        if($language){
            $this->Language = $language;
        }
        return $this->Language ?? '';
    }

    /**
     * Get Region.
     *
     * @param  string|null  $region
     * @return string
     */
    public function region(?string $region = null): string
    {
        if($region){
            $this->Region = $region;
        }
        return $this->Region ?? '';
    }

    /**
     * Get Charset.
     *
     * @param  string|null  $charset
     * @return string
     */
    public function charset(?string $charset = null): string
    {
        if($charset){
            $this->Charset = $charset;
        }
        return $this->Charset ?? '';
    }

    /**
     * Get Direction.
     *
     * @param  string|null  $direction
     * @return string
     */
    public function direction(?string $direction = null): string
    {
        if($direction){
            $this->Direction = $direction;
        }
        return $this->Direction ?? '';
    }

    /**
     * Load Locale.
     *
     * @return self
     */
    private function load(): self
    {
        // Set Locale Path(s)
        $path = $this->Path . DIRECTORY_SEPARATOR . $this->Locale;
        $config = $path . DIRECTORY_SEPARATOR . 'locale.cfg';
        $translation = $path . DIRECTORY_SEPARATOR . 'translation.cfg';

        // Create Locale Directory if it does not exist
        if(!is_dir($path)){
            mkdir($path, 0755, true);
        }

        // Create Locale Configuration File if it does not exist
        if(!is_file($config)){
            file_put_contents($config, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Create Locale Translation File if it does not exist
        if(!is_file($translation)){
            file_put_contents($translation, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Load Locale Configuration
        foreach(json_decode(file_get_contents($config), true) as $key => $value){
            switch($key){
                case 'name':
                    $this->Locale = $value;
                    break;
                case 'language':
                    $this->Language = $value;
                    break;
                case 'region':
                    $this->Region = $value;
                    break;
                case 'charset':
                    $this->Charset = $value;
                    break;
                case 'direction':
                    $this->Direction = $value;
                    break;
            }
        }

        // Load Locale Translation
        $this->Translation = json_decode(file_get_contents($translation), true);

        return $this;
    }

    /**
     * Get Locale string.
     *
     * @param  string  $key
     * @return string
     */
    public function get(string $key): string
    {
        return $this->Translation[$key] ?? $this->add($key);
    }

    /**
     * Add Locale string.
     *
     * @param  string  $key
     * @param  string|null  $value
     * @return string
     */
    public function add(string $key, ?string $value = null): string
    {
        $this->Translation[$key] = $value ?? $key;
        return $value ?? $key;
    }

    /**
     * Save Locale Translation.
     *
     * @return self
     */
    public function save(): self
    {
        // Save Locale Configuration
        file_put_contents($this->Path . DIRECTORY_SEPARATOR . $this->Locale . DIRECTORY_SEPARATOR . 'locale.cfg', json_encode([
            "name" => $this->Locale,
            "language" => $this->Language,
            "region" => $this->Region,
            "charset" => $this->Charset,
            "direction" => $this->Direction
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Save Locale Translation
        file_put_contents($this->Path . DIRECTORY_SEPARATOR . $this->Locale . DIRECTORY_SEPARATOR . 'translation.cfg', json_encode($this->Translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $this;
    }
}
