<?php

// Declaring namespace
namespace LaswitchTech\Core\Backends;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Backend;
use Exception;

class Local extends Backend {

    /** @var array Default password policy (overridden by auth.cfg) */
    const DEFAULT_POLICY = [
        'min_length'        => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_digit'     => true,
        'require_special'   => false,
    ];

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
        if ($password === null || empty($this->backend['password'])) {
            return false;
        }
        return password_verify($password, $this->backend['password']);
    }

    /**
     * Validate a password against the configured policy.
     *
     * Returns an array of error strings — empty means the password is valid.
     * Policy is read from auth.cfg → fallback to constants above.
     *
     * @param string $password
     * @return string[] Error messages (empty if valid).
     */
    public function validatePolicy(string $password): array {
        global $CONFIG;
        $policy = $CONFIG->get('auth', 'password_policy') ?? self::DEFAULT_POLICY;

        // Get min_length with fallback
        $minLength = $policy['min_length'] ?? 8;
        if (strlen($password) < $minLength) {
            return ['Password must be at least ' . $minLength . ' characters long.'];
        }

        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain an uppercase letter.';
        }
        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain a lowercase letter.';
        }
        if ($policy['require_digit'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain a digit.';
        }
        if ($policy['require_special'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain a special character.';
        }

        return $errors ?? [];
    }
}
