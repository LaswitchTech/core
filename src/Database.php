<?php

/**
 * Core Framework - Database
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Connector\MySQL;
use LaswitchTech\Core\Connector\PostgreSQL;
use LaswitchTech\Core\Connector\SQLite;
use Exception;

class Database {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * Constructor
     */
    public function __construct()
    {

        // Import Global Variables
        global $CONFIG;

        // Initialize Properties
        $this->Config = $CONFIG;

        // Load the Database Configuration
        $this->Config->add('database');

        // Instantiate the appropriate connector
        switch($this->Config->get('database', 'connector')) {
            case 'mysql':
                $this->connector = new MySQL();
                break;
            default:
                throw new Exception('Invalid database connector');
        }

        // Connect to the database
        $this->connector->connect();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Check if the database is connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->connector === null) {
            return false;
        }
        return $this->connector->isConnected();
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        if ($this->connector !== null) {
            $this->connector->close();
        }
    }

    /**
     * Create a new Query object
     *
     * @return Query
     */
    public function query(): Query
    {
        return new Query($this->connector);
    }

    /**
     * Describe a table
     *
     * @param string $table
     * @return mixed
     */
    public function describe(string $table)
    {
        return $this->connector->describe($table);
    }

    /**
     * Create a new Schema object
     *
     * @return Schema
     */
    public function schema(): Schema
    {
        return new Schema($this->connector);
    }
}
