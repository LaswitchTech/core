# Bootstrap Configuration Loading

> **Section**: 3.1 — Configuration  
> **File**: `src/Bootstrap.php`  

## How Bootstrap Loads Config

When you create a new Bootstrap instance, it loads config files in a specific order:

```php
$BOOTSTRAP = new Bootstrap('ROUTER');
```

### Step 1 — Load Bootstrap Config

```php
$CONFIG = new Config('bootstrap');   // Loads config/bootstrap.cfg
$this->Config = $CONFIG;             // Stores as bootstrap.cfg
$config->add('application');          // Also loads application.cfg early (used during init)
```

The `bootstrap.cfg` file is the first config loaded. It controls **service container overrides** — which classes replace the defaults, and which scopes they apply to.

### Step 2 — Merge Config Overrides

Bootstrap merges user-defined overrides into its internal service map:

```php
$straps = $this->Config->get('bootstrap');   // Read bootstrap.cfg overrides

foreach(self::Default as $name => $config) {
    foreach($config as $key => $value) {
        if (isset($straps[$name][$key])) {
            $config[$key] = $straps[$name][$key];  // Override default with config value
        }
    }
}
```

This means `bootstrap.cfg` can override any service's class or scope at runtime.

### Step 3 — Load Services by Scope

After merging, Bootstrap instantiates each service based on the scope (`ROUTER`, `API`, or `CLI`):

```php
foreach($config as $name => $config) {
    if(!in_array($this->scope, $config['scope'])) continue;  // Skip non-matching scopes
    $class = $config['class'];
    // ... instantiate and assign to $GLOBALS[$name]
}
```

## Config Loading Order Summary

| Step | Config File | Purpose |
|------|-------------|---------|
| 1 | `bootstrap.cfg` | Service container overrides (classes, scopes) |
| 2 | `application.cfg` | App metadata — loaded early because the app name/theme may be needed during bootstrap |
| 3+ | Other configs | Loaded lazily via `$CONFIG->add()` when first accessed |

Other config files (database, auth, css, js, locale, smtp, etc.) are **not loaded automatically** during bootstrap. They are loaded on demand:

```php
$config->add('database');   // First time accessing database settings
$settings = $config->get('database', 'host');  // Returns: 'localhost'
```

## Bootstrap Config Override Format

To override a default service in `bootstrap.cfg`:

```json
{
    "ENCRYPTION": {
        "class": "\\Custom\\Encryption",
        "scope": ["ROUTER", "API"]
    }
}
```

- `class` — Fully qualified class name (must exist)
- `scope` — Which scopes the service belongs to (subset or superset of the default)

If the alternate class doesn't exist, the default is kept. No validation happens at override time — invalid classes fail at instantiation.

## The $CONFIG Global

After bootstrap completes:

```php
$GLOBALS['CONFIG']  // Config instance with 'bootstrap' and 'application' loaded
```

Access any config file through it:

```php
$CONFIG->get('css', 'theme');            // Get nested value
$CONFIG->set('application', 'name', 'My App');  // Set + persist
$CONFIG->list();                         // Loaded files: ['bootstrap', 'application']
$CONFIG->list(true);                     // All files on disk: ['application', 'auth', ...]
```

## Config Precedence (When Multiple Sources Define the Same Key)

1. **Config file override** (`bootstrap.cfg`) — highest priority for service classes/scopes
2. **Default in `Bootstrap::Default`** — fallback if no override exists
3. **On-disk config file contents** — loaded lazily, values depend on what's written

For application settings (not services), the precedence is simpler:
- The **last value written to disk** wins (no multi-source merging)
- Admin UI writes to instance-specific configs (`application.cfg`)
- Committed configs (`requirement.cfg`, `extensions.cfg`) are shared defaults

## Key Takeaway

`bootstrap.cfg` controls **which services load and with what class**.  
Other config files control **what values those services use** (database credentials, app name, etc.).
