<?php

/**
 * Core Framework - Config
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal classes into the global namespace
use ReflectionClass;
use Exception;

class Config {

    const Extension = '.cfg';
    const ConfigDir = '/config';

    private $Files = [];
    private $Configurations = [];
    private $Path = null;

    /**
     * Constructor.
     */
    public function __construct($File = null){

        // Set Path according to this file
        $this->Path = realpath(getcwd());

        // If server document_root is available, use it instead
        if(isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])){
            $this->Path = $_SERVER['DOCUMENT_ROOT'];
        }

        // If constant ROOT_PATH has been set
        if(defined("ROOT_PATH")){
            $this->Path = ROOT_PATH;
        }

        // Check if configuration files were provided
        if($File){
            if(is_string($File)){
                $this->add($File);
            } else {
                if(is_array($File)){
                    if($this->isAssociative($File)){
                        foreach($File as $Name => $Path){
                            $this->add($Name, $Path);
                        }
                    } else {
                        foreach($File as $Name){
                            $this->add($Name);
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if an array is associative.
     *
     * @param  array  $array
     * @return boolean
     */
    protected function isAssociative($array) {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * Add a configuration file.
     *
     * @param  string  $File
     * @param  string  $Path
     * @return object $this
     */
    public function add($File, $Path = null){

        // If not already saved, add File in the list
        if(!array_key_exists($File, $this->Files)){

            // Set Path
            if(!is_string($Path)){
                $Path = $this->Path . self::ConfigDir . '/' . $File . self::Extension;
            }

            // Check if it doesn't exist
            if(!is_file($Path)){

                // Create the directory recursively
                if(!is_dir(dirname($Path))){
                    mkdir(dirname($Path), 0777, true);
                }

                // Create File
                file_put_contents($Path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }

            // Retrieve File and Load it
            $this->Configurations[$File] = json_decode(file_get_contents($Path),true);

            // Save File
            $this->Files[$File] = $Path;
        }

        // Return
        return $this;
    }

    /**
     * Reload a configuration file.
     *
     * @param  string  $File
     * @return object $this
     */
    public function reload($File): object
    {

        // If not already saved, add File in the list
        if(isset($this->Files[$File])){

            // Retrieve File and Load it
            $this->Configurations[$File] = json_decode(file_get_contents($this->Files[$File]),true);
        }

        // Return
        return $this;
    }

    /**
     * Get a Setting.
     *
     * @param  string  $File
     * @param  string  $Setting
     * @return mixed
     */
    public function get($File, $Setting = null): mixed
    {
        // Check if file exist and load it.
        if(!array_key_exists($File, $this->Files)){
            $this->add($File);
        }

        // Check if setting exist and return it.
        if($Setting){
            if(isset($this->Configurations[$File][$Setting])){
                // Return
                return $this->Configurations[$File][$Setting];
            }
        } else {
            // Return
            return $this->Configurations[$File];
        }

        return null;
    }

    /**
     * List all configuration files.
     *
     * @return object $this
     */
    public function list($byFiles = false){

        // Check if we should scan for files in the configuration directory or return the list of loaded files
        if($byFiles){

            // Scan for files in the configuration directory
            $Files = scandir($this->Path . self::ConfigDir);

            // Remove . and .. from the list
            $Files = array_diff($Files, array('.', '..'));

            // Only return files with the configuration extension
            $Files = array_filter($Files, function($File){
                return strpos($File, self::Extension) !== false;
            });

            // Remove the extension from the file names
            $Files = array_map(function($File){
                return str_replace(self::Extension, '', $File);
            }, $Files);

            // Convert to a standard array
            $Files = array_values($Files);

            // Return
            return $Files;
        } else {

            // Return
            return array_keys($this->Files);
        }
    }

    /**
     * Set a Setting.
     *
     * @param  string  $File
     * @param  string  $Setting
     * @param  string|array|int|boolean  $Value
     * @return object $this
     */
    public function set($File, $Setting, $Value){

        // Check if file exist and return it.
        if(isset($this->Configurations[$File])){

            // Set Value
            $this->Configurations[$File][$Setting] = $Value;

            // Save File
            file_put_contents($this->Files[$File], json_encode($this->Configurations[$File], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Return
        return $this;
    }

    /**
     * Delete a or all configuration file(s).
     *
     * @param  string|null  $File
     * @return object $this
     */
    public function delete($File = null){

        // If not already saved, add File in the list
        if(isset($this->Files[$File])){

            // Delete Configuration File
            unlink($this->Files[$File]);

            // Unset File
            unset($this->Files[$File]);
        }

        // Return
        return $this;
    }

    /**
     * Check if a configuration file exist.
     *
     * @param  string  $File
     * @return boolean
     */
    public function check($File){

        // Check if configuration file without loading it
        return is_file($this->Path . self::ConfigDir . '/' . $File . self::Extension);
    }

    /**
     * Get a Root Path.
     *
     * @return string $this->Path
     */
    public function root(){

        // Return
        return $this->Path;
    }

    /**
     * Get a Class File Path.
     *
     * @param  string  $className
     * @return string $filePath
     */
    public function path($className) {

        // Check if the Composer autoload file is included
        $autoloadPath = $this->root() . '/vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            throw new Exception("Composer autoload file not found.");
        }

        // Include the Composer autoload file
        require_once $autoloadPath;

        // Check if the class exists
        if (!class_exists($className)) {

            // Throw an exception
            throw new Exception("Class $className does not exist.");
        }

        // Use ReflectionClass to get the file path
        $reflector = new ReflectionClass($className);
        $filePath = $reflector->getFileName();

        return $filePath;
    }

    /**
     * Get/Set a Version.
     *
     * @param  string|null  $version
     * @return string $version
     */
    public function version(?string $version = null): string
    {

        // Check if VERSION file exist and create it if not
        if(!is_file($this->root() . DIRECTORY_SEPARATOR . "VERSION")){

            // Create the VERSION file
            file_put_contents($this->root() . DIRECTORY_SEPARATOR . "VERSION", "v0.0.0");
        }

        if($version){

            // Save Version
            file_put_contents($this->root() . DIRECTORY_SEPARATOR . "VERSION", $version);
        } else {

            // Retrieve Current Version
            $version = file_get_contents($this->root() . DIRECTORY_SEPARATOR . "VERSION");
            $version = preg_replace('/\s+/', '', $version);
        }

        return $version;
    }
}
