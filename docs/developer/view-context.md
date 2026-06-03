# Global View Context

> **Status**: Implemented — all 5 layouts updated

## Overview

`ViewGlobals` provides a **guaranteed set of global variables** in the template scope. Every layout template that uses `View::render()` automatically receives these variables.

## Guaranteed Variables

| Variable | Type | Description |
|----------|------|-------------|
| `$config` | `Config` | Configuration object (bootstrap + application settings) |
| `$auth` | `Auth` | Auth module instance |
| `$currentUser` | `User\|null` | Current authenticated user (null if guest) |
| `$menu` | `Builder\|null` | Menu/UI builder instance |
| `$breadcrumbs` | `array` | Breadcrumb data from current route |
| `$locale` | `Locales` | Localization/i18n instance |
| `$csrf` | `CSRF` | CSRF token generator/validator |
| `$request` | `Request` | HTTP request abstraction |
| `$output` | `Output` | Response/output formatter |
| `$app` | `array` | Application metadata (`name`, `theme`, `installed`) |

## Usage

### In Layout Templates

Call `ViewGlobals::apply()` at the entry point of any layout:

```php
<?php
use LaswitchTech\Core\ViewGlobals;
ViewGlobals::apply();
?>
<?php if (!$config->get('application', 'installed')): ?>
    <!-- Setup page -->
<?php else: ?>
    <!-- Main page -->
<?php endif; ?>
```

### Direct Variable Access

After `apply()`, use variables directly instead of `$this->`:

```php
<!-- Before (via Route facade) -->
<?= $this->Config->get('application', 'name') ?>
<?= $this->Auth->isAuthorized('Administrator', 1) ?>
<?= $this->Locale->get('Welcome') ?>

<!-- After (via ViewGlobals) -->
<?= $app['name'] ?>
<?= $auth->isAuthorized('Administrator', 1) ?>
<?= $locale->get('Welcome') ?>
```

### Preserved Route Methods

`$this` (the Route object) is still available for route-specific methods:

```php
<!-- These still work -->
<?= $this->label() ?>
<?php require_once $this->view(); ?>
<?= $this->interrupt()->Router->render('404') ?>
<?= $this->Helper->Core->crumbs() ?>
```

## How It Works

1. `View::render()` calls `ViewGlobals::apply()` before loading any template
2. `apply()` calls `context()` which imports request-scope globals (`$CONFIG`, `$AUTH`, etc.)
3. `extract()` places variables into the template scope
4. Template files receive `$config`, `$auth`, etc. as regular PHP variables

## Guest Safety

All variables are **safe for guest users**:
- `$currentUser` is `null` for guests
- `$auth->isAuthorized()` returns `false` for guests
- No null-pointer errors on any guaranteed variable

## Migration Path

Existing layouts using `$this->Config`, `$this->Auth`, etc. will continue to work.
Gradually replace with ViewGlobals variables:

1. Add `ViewGlobals::apply()` at layout entry point
2. Replace `$this->Config` → `$config`
3. Replace `$this->Auth` → `$auth`
4. Replace `$this->Locale` → `$locale`
5. Replace `$this->Builder` → `$menu`
6. Replace `$this->Request` → `$request`

Preserve `$this->` for route methods (`label()`, `view()`, `interrupt()`).
