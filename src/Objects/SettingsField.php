<?php

namespace LaswitchTech\Core\Objects;

/**
 * SettingsField value object
 *
 * Defines a single configurable field within a SettingsSection.
 * Immutable — create via the factory methods and read-only access.
 */
class SettingsField
{
    const TYPE_TEXT      = 'text';
    const TYPE_TEXTAREA  = 'textarea';
    const TYPE_BOOLEAN   = 'boolean';
    const TYPE_SELECT    = 'select';
    const TYPE_HIDDEN    = 'hidden';

    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type = self::TYPE_TEXT,
        public readonly ?string $help = null,
        public readonly ?array $options = null,  // for select: ['value' => 'label', ...]
        public readonly mixed $default = null,
        public readonly ?string $keyPrefix = null,
    ) {}

    /**
     * Create a text field.
     */
    public static function text(string $name, string $label, ?string $help = null, mixed $default = null): self
    {
        return new self($name, $label, self::TYPE_TEXT, $help, null, $default);
    }

    /**
     * Create a textarea field.
     */
    public static function textarea(string $name, string $label, ?string $help = null, mixed $default = null): self
    {
        return new self($name, $label, self::TYPE_TEXTAREA, $help, null, $default);
    }

    /**
     * Create a boolean (toggle/checkbox) field.
     */
    public static function boolean(string $name, string $label, ?string $help = null, mixed $default = null): self
    {
        return new self($name, $label, self::TYPE_BOOLEAN, $help, null, $default);
    }

    /**
     * Create a select (dropdown) field.
     */
    public static function select(string $name, string $label, array $options, ?string $help = null, mixed $default = null): self
    {
        return new self($name, $label, self::TYPE_SELECT, $help, $options, $default);
    }

    /**
     * Compute the effective config key: prefix/name or just name.
     */
    public function key(?string $sectionPrefix = null): string
    {
        $prefix = $this->keyPrefix ?: $sectionPrefix;
        return $prefix !== null ? "$prefix/$this->name" : $this->name;
    }

    /**
     * Get the resolved default value.
     */
    public function resolveDefault(?string $sectionPrefix = null): mixed
    {
        if ($this->type === self::TYPE_BOOLEAN && is_bool($this->default)) {
            return $this->default;
        }
        // For boolean, allow "true"/"false" strings from form submission
        if ($this->type === self::TYPE_BOOLEAN && in_array($this->default, ['true', 'false'], true)) {
            return $this->default === 'true';
        }
        return $this->default;
    }

    /**
     * Get the HTML id attribute for this field.
     */
    public function id(?string $sectionPrefix = null): string
    {
        return 'field_' . str_replace('/', '_', $this->key($sectionPrefix));
    }
}
