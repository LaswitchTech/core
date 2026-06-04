<?php

namespace LaswitchTech\Core\Objects;

/**
 * SettingsSection value object
 *
 * Wraps a group of related SettingsFields with metadata for rendering.
 * Immutable — create via constructor or factory.
 */
class SettingsSection
{
    public function __construct(
        public readonly string $keyPrefix,
        public readonly string $label,
        public readonly ?string $icon = 'gear',
        public readonly ?string $description = null,
        public readonly array $fields = [],
        public readonly array $permissions = [],  // ['role' => level] to view this section
    ) {}

    /**
     * Register a new field in this section and return self for chaining.
     */
    public function addField(SettingsField $field): self
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Get the settings registry key used to save/load this section's values.
     */
    public function configKey(): string
    {
        // Use the prefix as the config file name (e.g., "application" → application.cfg)
        return $this->keyPrefix;
    }

    /**
     * Get a field by its name.
     */
    public function getField(string $name): ?SettingsField
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }
        return null;
    }

    /**
     * Check if this section requires a specific role/level to view.
     */
    public function requiresAuth(): bool
    {
        return !empty($this->permissions);
    }
}
