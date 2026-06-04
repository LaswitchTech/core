# Config Files (.cfg)

> **Section**: 3.1 — Configuration  
> **File**: `src/Config.php`  

## Overview

Core-Web stores all application configuration as JSON files with the `.cfg` extension in the `config/` directory. The `Config` class manages lazy-loading, reading, writing, and persistence of these files at runtime.

```
config/
├── application.cfg    # App metadata (name, owner, theme)
├── auth.cfg           # Authentication settings
├── bootstrap.cfg      # Service container overrides
├── csrf.cfg           # CSRF token settings
├── css.cfg            # CSS/theme compilation settings
├── database.cfg       # Database connector + credentials
├── extensions.cfg     # Plugin/theme/module registry
├── installer.cfg      # Installer defaults
├── js.cfg             # JavaScript settings
├── locale.cfg         # Language + timezone
├── log.cfg            # Logging settings
├── migration.cfg      # Migration system settings
├── requirement.cfg    # System requirements (committed)
└── smtp.cfg           # Email server settings
```

## Config Class API

### Constructor — Load a Config File

```php
$config = new Config('application');  // Loads config/application.cfg
```

The constructor lazy-loads a single file. Files are loaded from `getcwd()/.config/`, `$_SERVER['DOCUMENT_ROOT']`, or `ROOT_PATH` (in priority order).

### Lazy Load Additional Files

```php
$config->add('css');          // Lazy-load config/css.cfg later
$config->add(['auth', 'smtp']);  // Load multiple files
$config->add(['app' => 'application']);  // Map custom name to filename
```

### Read Values

```php
// Get entire file contents as associative array
$app = $config->get('application');    // Returns: ['name' => 'My App', ...]

// Get a single key (auto-loads the file if not yet loaded)
$theme = $config->get('css', 'theme');

// Key that doesn't exist returns null
$missing = $config->get('app', 'nonexistent_key');  // Returns null
```

### Write Values

```php
$config->set('application', 'name', 'My Application');
// Persists: { "name": "My Application" } to config/application.cfg
```

The file is re-written in full on each `set()` call, formatted with `JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES`.

### List Files

```php
$config->list();                        // Returns: ['application', 'css', ...] (loaded files only)
$config->list(true);                    // Scans the config/ directory for all .cfg files
```

### Check Existence

```php
$config->check('auth');  // true if config/auth.cfg exists on disk
```

### Delete a File

```php
$config->delete('temp');   // Removes config/temp.cfg and unloads it from memory
```

### Reload (Refresh from Disk)

```php
$config->reload('application');  // Re-reads config/application.cfg from disk
```

Useful after another process has modified a config file.

### Root Path

```php
$path = $config->root();  // Returns the resolved base directory
```

## Config File Format

All `.cfg` files are JSON objects with arbitrary key-value pairs:

```json
{
    "connector": "mysql",
    "host": "localhost",
    "database": "demo1"
}
```

**No schema enforcement.** The `Config` class is a generic JSON file manager — each config file defines its own structure. Convention and the consuming code (e.g., Bootstrap, SettingsRegistry) document what keys to expect.

## Path Resolution Order

The `Config` class resolves the base directory in this order:

1. **`$_SERVER['DOCUMENT_ROOT']`** — if the script runs under Apache/Nginx
2. **`ROOT_PATH`** — if the constant is defined (e.g., by index.php)
3. **`getcwd()`** — falls back to current working directory (common with CLI)

Each config file path becomes: `{base}/config/{name}.cfg`.

## Committed vs Gitignored Files

Four `.cfg` files are committed to the repository (`!requirement.cfg`, `!extensions.cfg`, `!js.cfg`, `!css.cfg` in `.gitignore`). The rest are gitignored and expected to be instance-specific:

| File | Status | Purpose |
|------|--------|---------|
| `requirement.cfg` | **Committed** | System requirements — should never be modified per-instance |
| `extensions.cfg` | **Committed** | Default extension registry (modules, plugins, themes) |
| `css.cfg` | **Committed** | CSS/theme compilation defaults |
| `js.cfg` | **Committed** | JavaScript configuration defaults |
| All others | Gitignored | Instance-specific settings — deploy with `.cfg.example` → copy pattern |

## Config vs SettingsRegistry

The `Config` class is a **low-level JSON file manager**. For user-facing admin settings, use the **SettingsRegistry** (Phase 1.3), which builds on top of `Config`:

- `Config` → raw JSON get/set/persist
- `SettingsRegistry` → typed fields, sections, validation, UI rendering

See [Application Settings](./app-settings.md) for the admin-facing settings system.

## Examples

### Creating a New Config File

```php
$config = new Config('myplugin');  // Auto-creates config/myplugin.cfg with empty {}
$config->set('myplugin', 'feature_x', true);
$config->set('myplugin', 'limit', 100);
```

Result in `config/myplugin.cfg`:
```json
{
    "feature_x": true,
    "limit": 100
}
```

### Merging Config Changes

```php
$config = new Config('application');
$config->set('application', 'name', 'New Name');
$config->set('application', 'theme', 'glass');
// Both changes are written on the second set() call (not merged incrementally)
```

Each `set()` writes the full file, not just the changed key. This is safe for concurrent single-process use but not safe for concurrent processes without external locking.

### Scanning All Available Configs

```php
$config = new Config();
$available = $config->list(true);  // ['application', 'auth', 'bootstrap', ...]
// Use this to populate a dropdown or file list in an admin page
```
