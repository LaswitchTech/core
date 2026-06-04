# Config Override Layer

> **Section**: 3.1 — Configuration  
> **Purpose**: Explain which config files are committed vs gitignored and why  

## Why Both Committed and Gitignored Config Files?

Core-Web's design splits config files into two categories:

| Category | Examples | Who Owns | Git Status |
|----------|----------|----------|------------|
| **Committed** — shared defaults | `requirement.cfg`, `extensions.cfg`, `css.cfg`, `js.cfg` | The kernel repository | Tracked in `.gitignore` via `!` negation rules |
| **Gitignored** — instance-specific | `database.cfg`, `auth.cfg`, `application.cfg`, `smtp.cfg`, `csrf.cfg` | Each deployment | Ignored by default (instance-specific secrets) |

## Committed Config Files (Tracked)

These files define the kernel's defaults and are part of the distributed package. Deployments receive them automatically when they clone or composer-install Core-Web.

### `requirement.cfg`

System requirements that must be satisfied before deployment:

```json
{
    "core": ["DATABASE", "SMTP", "AUTH", "INSTALLER"],
    "modules": ["core"],
    "plugins": ["bootstrap", "composer", "extensions", ...],
    "themes": ["default"]
}
```

This file should **never** be modified per-instance — it defines what the kernel needs.

### `extensions.cfg`

Default extension registry:

```json
{
    "modules": {
        "core": {
            "url": "https://raw.githubusercontent.com/LaswitchTech/core-module-core/refs/heads/stable/info.cfg"
        }
    },
    "plugins": [],
    "themes": []
}
```

Installed extensions are appended at runtime, but the file itself is committed with defaults.

### `css.cfg` and `js.cfg`

Frontend compilation defaults (theme, paths, source files). These are stable across deployments and define the build pipeline.

## Gitignored Config Files (Instance-Specific)

These files contain deployment-specific or sensitive data:

| File | What It Contains | Example Values |
|------|-----------------|----------------|
| `database.cfg` | DB connector, host, credentials | `connector`, `host`, `database`, `username`, `password` |
| `auth.cfg` | Authentication settings | Enabled backends, session config |
| `application.cfg` | App metadata and settings | App name, theme, owner |
| `smtp.cfg` | Email server connection | Host, port, credentials |
| `csrf.cfg` | CSRF token policy | Rotation interval, expiry |
| `installer.cfg` | Installer defaults | Pre-filled form values |
| `locale.cfg` | Language and timezone | Default locale, time zone |
| `log.cfg` | Logging configuration | Log levels, file rotation |
| `migration.cfg` | Migration system settings | Migration directory, tracking table |

## Deploying: From Example to Config

Since instance-specific config files are gitignored, deployments use a copy pattern:

```bash
# Copy examples and edit for your environment
cp config/database.cfg.example config/database.cfg
nano config/database.cfg    # Edit credentials, connector type, etc.

cp config/smtp.cfg.example config/smtp.cfg
nano config/smtp.cfg        # Fill in SMTP server details
```

**Never commit the resulting `.cfg` files** — they contain secrets and instance-specific paths.

## Admin Settings Override

The admin settings page (`/admin/settings`) modifies gitignored instance configs at runtime via `$CONFIG->set()`. When an admin changes "App Name" or switches themes, that change is persisted to the instance-specific config file (e.g., `application.cfg`), not the committed default.

This keeps the kernel's defaults clean while allowing per-instance customization.

## Summary

- **Committed files** (`requirement.cfg`, `extensions.cfg`, `css.cfg`, `js.cfg`) — shared, version-controlled defaults from the kernel package
- **Gitignored files** (database, auth, application, smtp, etc.) — instance-specific, contain secrets and deployment choices
- **Admin UI** modifies gitignored configs at runtime; committed defaults remain untouched
- Use `.cfg.example` files to document what each config file expects
