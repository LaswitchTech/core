<?php

/**
 * Core Framework - Style
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Style {

    // Constants

    // Global Properties
    private $Config;

    // Properties

    /**
     * Constructor
     */
    public function __construct()
    {

        // Global Variables
        global $CONFIG;

        // Set Global Properties
        $this->Config = $CONFIG;

        // Configure Globals
        $this->Config->add('css')->add('style');
    }

    /**
     * Check if the module is installed
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->Config->reload('style')->get('style', 'installed') === true;
    }

    /**
     * Install the module
     *
     * @param array $config
     * @return array
     */
    public function install(array $config): array
    {
        // Initialize the status
        $status = [];

        // Check if the config includes all the required fields
        if(isset($config['entity'],$config['brand'],$config['copyright'])){

            // Save the settings
            $this->Config->set('style', 'entity', $config['entity']);
            $this->Config->set('style', 'brand', $config['brand']);
            $this->Config->set('style', 'copyright', intval($config['copyright']));

            // Check if the data file exists
            $status[] = true;

            // Set the database as installed
            $this->Config->set('style', 'installed', true);

            // Check if the data file exists
            $status[] = true;
        } else {
            var_dump($config);
            $status[] = "Missing required fields";
        }

        // Return the statuses
        return $status;
    }
}
