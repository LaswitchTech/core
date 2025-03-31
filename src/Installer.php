<?php

/**
 * Core Framework - Installer
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Installer {

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
        $this->Config->add('installer');
    }

    /**
     * Check if the module is installed
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        return filter_var($this->Config->reload('application')->get('application', 'installed'), FILTER_VALIDATE_BOOLEAN);
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
        if(isset($config['owner'],$config['name'],$config['copyright'])){

            // Save the settings
            $this->Config->set('application', 'owner', $config['owner']);
            $this->Config->set('application', 'name', $config['name']);
            $this->Config->set('application', 'copyright', intval($config['copyright']));
            $this->Config->set('application', 'theme', $this->Config->get('installer', 'theme'));

            // Set the database as installed
            $this->Config->set('application', 'installed', true);

            // Check if the data file exists
            $status[] = true;
        } else {
            $status[] = "Missing required fields";
        }

        // Return the statuses
        return $status;
    }
}
