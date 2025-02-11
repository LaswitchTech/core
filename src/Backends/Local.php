<?php

/**
 * Core Framework - Local
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Backends;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Backend;
use Exception;

class Local extends Backend {

    /**
     * Set the Backend Password
     *
     * @param string $password
     * @return self
     */
    public function set(?string $password = null): self
    {
        $this->backend['password'] = password_hash($password, PASSWORD_DEFAULT);
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
        return password_verify($password, $this->backend['password']);
    }
}
