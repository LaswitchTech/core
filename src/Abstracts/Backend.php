<?php

/**
 * Core Framework - Backend
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Backend {

    // Global Properties
    protected $Database;

    // Properties
    protected $backend;

    /**
     * Constructor
     *
     * @param string $backend
     * @param array $data
     * @throws Exception
     */
    public function __construct(string $backend = null, ?array $data = null)
    {
        // Import Global Variables
        global $DATABASE;

        // Initialize Properties
        $this->Database = $DATABASE;

        // Check if backend data is provided
        if ($data) {

            // Set Backend
            $this->backend = $data;
        } else {

            // Retrieve Backend
            $query = $this->Database->query();
            $backend = $query->table('backends')
                ->select('*')
                ->where('id', $backend)
                ->limit(1)
                ->result();

            // Check if user exists
            if (count($backend) == 0) {
                throw new Exception('Backend not found');
            }

            // Set Backend
            $this->backend = $backend[0];
        }
    }

    /**
     * Set the Backend Password
     *
     * @param string $password
     * @return self
     */
    public function set(?string $password = null): self
    {
        // Implement in child class
        return $this;
    }

    /**
     * Validate the Backend Password
     *
     * @param string $password
     * @return bool
     */
    public function validate(?string $password = null): bool
    {
        // Implement in child class
        return false;
    }

    /**
     * Save the Backend
     *
     * @return self
     */
    public function save(): self
    {
        // Create the Query
        $query = $this->Database->query();

        // Update the Backend
        $query->table('backends')
            ->update($this->backend)
            ->where('id', $this->backend['id'])
            ->result();

        return $this;
    }
}
