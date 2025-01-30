<?php

/**
 * Core Framework - Connector
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Connector {

    /**
     * @var mixed
     */
    protected $Config;

    /**
     * Constructor
     *
     * Here you might load global config or do other setup tasks.
     * For this example, we assume there's a $CONFIG global object
     * that stores DB credentials, etc.
     */
    public function __construct()
    {
        global $CONFIG;
        $this->Config = $CONFIG;

        // If you have a method $CONFIG->add('database') to load DB config, do that here.
        if (method_exists($this->Config, 'add')) {
            $this->Config->add('database');
        }
    }

    /**
     * Connect method to be overridden by child classes
     */
    public function connect()
    {
        // Implementation in child classes
    }

    /**
     * Close method to be overridden by child classes
     */
    public function close()
    {
        // Implementation in child classes
    }

    /**
     * Check if connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        // Implementation in child classes
        return false;
    }

    /**
     * Execute a query
     *
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql)
    {
        // Implementation in child classes
    }

    /**
     * Describe a table
     *
     * @param string $table
     * @return mixed
     */
    public function describe(string $table)
    {
        // Implementation in child classes
    }

    /**
     * Get the ID of the last inserted row
     *
     * @return int
     */
    public function lastId(): int
    {
        // Implementation in child classes
        return 0;
    }

    /**
     * Get the number of affected rows
     *
     * @return int
     */
    public function affectedRows(): int
    {
        // Implementation in child classes
        return 0;
    }

    /**
     * Prepare a query
     *
     * @param string $sql
     */
    public function prepare(string $sql, array $params = [])
    {
        // Implementation in child classes
        return false;
    }
}
