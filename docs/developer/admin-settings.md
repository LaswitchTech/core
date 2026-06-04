# Admin Settings System (Phase 1.3)

## Overview

The Application Settings System provides a `/admin` landing page with a plugin-provided **Settings Registry** for configuring the application at runtime. Settings are persisted to existing `.cfg` files via `$CONFIG->set()`.

## Pages

| Route | Template | View | Purpose |
|-------|----------|------|---------|
| `/admin` | `panel.php` | `index.php` | Admin dashboard with quick-access cards |
| `/admin/settings` | `panel.php` | `settings.php` | Settings registry (all registered sections) |
| `/admin/security` | `panel.php` | `security.php` | Password policy, session, and auth settings |
| `/admin/maintenance` | `panel.php` | `maintenance.php` | App config, theme, SMTP, maintenance mode |

All pages use the `panel.php` template for admin layout with sidebar navigation.

## Settings Registry API

### Registration

Plugins register sections at bootstrap time:

```php
use LaswitchTech\Core\Objects\SettingsField;
use LaswitchTech\Core\Objects\SettingsSection;
use LaswitchTech\Core\SettingsRegistry;

SettingsRegistry::getInstance()->register(new SettingsSection(
    'application',              // keyPrefix (maps to application.cfg)
    'Application Settings',     // label
    'gear',                     // icon
    'General configuration.',   // description
    [
        SettingsField::text('name', 'App Name', 'Display name.'),
        SettingsField::boolean('feature_x', 'Feature X'),
        SettingsField::select('theme', 'Theme', ['dark' => 'Dark', 'light' => 'Light']),
    ]
));
```

### Rendering

The `/admin/settings` page iterates over `SettingsRegistry::getInstance()->sections()` and renders each section's fields as Bootstrap form controls.

### Saving

POST to `/admin/settings` saves all changed fields from the rendered sections back to their respective `.cfg` files via `$CONFIG->set()`. On success, the page redirects back with flash messages:

```
/admin/settings?flash=success&message=Settings+updated+successfully
/admin/settings?flash=warning&message=2+saved,+1+error(s)
```

### Access Control

Sections can declare permissions for display gating:

```php
new SettingsSection('application', 'Admin Only', 'gear', null, $fields, ['Administrator' => 1]);
```

`SettingsRegistry::accessible($role, $level)` filters sections by required role/level.

## Field Types

| Type | Bootstrap Control | Save Value |
|------|-------------------|------------|
| `text` | `<input type="text" class="form-control">` | string |
| `textarea` | `<textarea class="form-control">` (rows=3) | string |
| `boolean` | `.form-switch` checkbox | `true`/`false` (on=checked) |
| `select` | `<select class="form-select">` | string (validates against options) |

## Architecture Notes

- **Config persistence**: Uses existing `$CONFIG->set()` / `$CONFIG->get()` — no new config service
- **Section registration**: Happens at bootstrap; any plugin can call `SettingsRegistry::register()` before first render
- **View resolution**: Plugin views resolve via the old routing path's `Route::Directory` parameter (carried through View engine)
- **Flash messages**: Stored as query parameters after redirect-after-save (no session dependency)
