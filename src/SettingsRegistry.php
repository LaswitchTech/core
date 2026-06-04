<?php

namespace LaswitchTech\Core;

use LaswitchTech\Core\Objects\SettingsSection;

/**
 * SettingsRegistry — singleton for plugin-registered settings sections.
 *
 * Sections are registered at bootstrap time and rendered by the ConfigEndpoint.
 * Usage:
 *   SettingsRegistry::getInstance()->register(new SettingsSection('application', 'Application'));
 *   // Then iterate: foreach (SettingsRegistry::getInstance()->sections() as $section) { ... }
 */
class SettingsRegistry
{
    private static ?self $instance = null;

    /** @var SettingsSection[] */
    private array $sections = [];

    private function __construct() {}

    /** Prevent cloning */
    private function __clone() {}

    /** Prevent unserializing */
    public function __wakeup(): void
    {
        $this->sections = [];
    }

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset the singleton (for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Register a settings section.
     */
    public function register(SettingsSection $section): self
    {
        // Allow re-registration: overwrite existing section with same keyPrefix
        $this->sections[$section->keyPrefix] = $section;
        return $this;
    }

    /**
     * Check if a section with the given keyPrefix exists.
     */
    public function has(string $keyPrefix): bool
    {
        return isset($this->sections[$keyPrefix]);
    }

    /**
     * Get a registered section by keyPrefix.
     */
    public function get(string $keyPrefix): ?SettingsSection
    {
        return $this->sections[$keyPrefix] ?? null;
    }

    /**
     * Get all registered sections, ordered by registration.
     *
     * @return SettingsSection[]
     */
    public function sections(): array
    {
        return array_values($this->sections);
    }

    /**
     * Get sections filtered by a required role and level.
     *
     * @param string $role
     * @param int $level
     * @return SettingsSection[]
     */
    public function accessible(string $role, int $level): array
    {
        return array_values(array_filter($this->sections(), function (SettingsSection $section) use ($role, $level) {
            if (empty($section->permissions)) {
                return true;  // No permissions required → accessible to all
            }
            foreach ($section->permissions as $reqRole => $reqLevel) {
                if ($role === $reqRole && $level >= $reqLevel) {
                    return true;  // User meets this permission requirement
                }
            }
            return false;
        }));
    }
}
