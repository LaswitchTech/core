# Application Settings System (Phase 1.3)

> **Section**: 3.1 — Configuration  
> **Built on**: `Config` class (low-level JSON file manager)

## Overview

The Application Settings System provides an admin-facing API for configuring the application at runtime. It consists of three layers:

| Layer | Class | Responsibility |
|-------|-------|----------------|
| **Registry** | `SettingsRegistry` | Stores and manages settings sections; singleton, plugin-provided |
| **Section** | `SettingsSection` | A grouped set of fields with a key prefix, label, icon, description |
| **Field** | `SettingsField` | Individual config field (text, textarea, boolean, select, hidden) |

The `/admin/settings` page iterates over all registered sections and renders them as Bootstrap form controls. On POST, values are saved back to the appropriate `.cfg` files via `$CONFIG->set()`.

## Registration API

Plugins register settings sections at bootstrap time:

```php
use LaswitchTech\Core\Objects\SettingsField;
use LaswitchTech\Core\Objects\SettingsSection;
use LaswitchTech\Core\SettingsRegistry;

SettingsRegistry::getInstance()->register(new SettingsSection(
    'application',             // keyPrefix — maps to application.cfg keys
    'Application Settings',    // label (shown in section header)
    'gear',                    // Bootstrap icon
    'General application configuration.',  // description
    [
        SettingsField::text('name', 'App Name', 'Display name shown to users.'),
        SettingsField::text('owner', 'Owner', 'Application owner/author.'),
        SettingsField::select('theme', 'Theme', [
            'default' => 'Default (Bootstrap)',
            'gentelella' => 'Gentelella',
            'glass' => 'Glass Morphism',
        ], 'Which theme to use for the UI.', 'default'),
    ]
));
```

## Available Field Types

### `SettingsField::text($name, $label, $help = null, $default = null)`

Single-line text input. Values are stored as strings in config files.

### `SettingsField::textarea($name, $label, $help = null, $default = null)`

Multi-line text area. Useful for longer free-form values like notes or raw JSON configs.

### `SettingsField::boolean($name, $label, $help = null, $default = null)`

Toggle/checkbox field. Values are stored as booleans (`true`/`false`) in the config file. Form submission sends `"true"`/`"false"` strings — automatically converted by `resolveDefault()`.

### `SettingsField::select($name, $label, $options, $help = null, $default = null)`

Dropdown with predefined options. `$options` is a key-value array: `['value' => 'Label', ...]`.

### `SettingsField::hidden($name, $label, $help = null, $default = null)`

Hidden input field. Renders an `<input type="hidden">` that submits a value but isn't visible in the UI. Useful for locking a key that admins shouldn't change.

## Field Properties (Value Object)

Each `SettingsField` exposes readonly properties:

| Property | Type | Description |
|----------|------|-------------|
| `$name` | string | Config key name within the section's file |
| `$label` | string | Human-readable label for the UI |
| `$type` | string | One of: `text`, `textarea`, `boolean`, `select`, `hidden` |
| `$help` | ?string | Help text shown below the input (tooltip-style) |
| `$options` | ?array | For select fields: `['value' => 'label', ...]` |
| `$default` | mixed | Default value if not set in config file |
| `$keyPrefix` | ?string | Override section's prefix for this specific field |

### Key Resolution

Fields compute their config key as `prefix/name` (section prefix + field name), unless overridden by the field's own `$keyPrefix`. The key is used with `$CONFIG->set($file, $key, $value)` to persist changes.

## Rendering on /admin/settings

The admin settings page works like this:

1. **GET** — iterate `SettingsRegistry::getInstance()->sections()`, render each section's fields as Bootstrap form controls
2. **POST** — read all submitted form values, call `$CONFIG->set($file, $key, $value)` for each changed field
3. On success, **redirect back** with flash messages (redirect-after-save pattern)

Default core settings are registered during bootstrap and seeded into `application.cfg` if the file is empty:
- App name, owner, theme, navigation titles

## Plugin Registration Flow

```
Bootstrap loads                          ← config/bootstrap.cfg for service overrides
    ↓
SettingsRegistry::getInstance()          ← singleton ensures one shared registry
    ↓
Plugin registers SettingsSection         ← via its plugin bootstrap.php or init hook
    ↓
/admin/settings page renders sections    ← GET request iterates registry
    ↓
POST saves to .cfg files                ← $CONFIG->set(file, key, value)
```

## Summary

- `SettingsRegistry` is a singleton — all plugins register into the same shared store
- Each section maps to a `.cfg` file via its key prefix (e.g., `application` → `application.cfg`)
- `SettingsField` supports 5 types: text, textarea, boolean, select, hidden
- Admin UI uses redirect-after-save with flash messages for persistence feedback
- Default core settings are seeded during bootstrap
