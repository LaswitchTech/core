<?php

/**
 * Core Framework - UUID
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class UUID {

    /**
     * Return the uuid string
     */
    public function __toString()
    {
        return $this->generate();
    }

    /**
     * Return the UUID string
     */
    public function toString(?string $origin = null): string
    {
        return $this->generate($origin);
    }

    /**
     * Generate a new UUID
     *
     * @param string|null $origin If provided, generates a deterministic UUID from the string.
     * @return string
     */
    protected function generate(?string $origin = null): string
    {
        if ($origin !== null) {
            // Generate deterministic bytes from a hash
            $hash = md5($origin, true); // 16 bytes output
        } else {
            // Generate random bytes
            $hash = random_bytes(16);
        }

        // Modify bytes to conform to UUID v4 standard
        $hash[6] = chr((ord($hash[6]) & 0x0F) | 0x40); // Set version to 4
        $hash[8] = chr((ord($hash[8]) & 0x3F) | 0x80); // Set variant to RFC 4122

        return strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($hash), 4)));
    }
}
