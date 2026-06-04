# Core-Web — Project Roadmap

> **Version**: v0.0.94
> **Status**: Early architecture / skeleton phase. APIs and internals may change between commits.
> **V1.0 target**: 2026-08-15

---

## Current State Summary

Core-Web provides foundational infrastructure for building multiple web applications through a modular plugin system. The kernel is stable enough for initial development work but still has significant gaps in testing, configuration management, and several core service stubs.

### What's Working Today

| Area | Status | Notes |
|------|--------|-------|
| **Plugin system** | Implemented | 57 plugins in `lib/plugins/`, each with `info.cfg` manifest, auto-discovery, lifecycle |
| **Bootstrap / Service Container** | Implemented | `Bootstrap.php` loads 25+ services as globals (`$DATABASE`, `$AUTH`, `$ROUTER`, etc.) scoped to ROUTER/API/CLI |
| **Routing** | Implemented | `Router.php` + `Builder->menu()` with sidebar-main/admin/dev, topbar, topnav locations |
| **Layout System** | Implemented | `panel.php` (admin), `website.php` (app), `fullscreen.php`, `internal.php`, `index.php` (blank), 16 error pages |
| **Theme System** | Implemented | Bootstrap 5, LESS compilation (`Style.php` + `wikimedia/less.php`), 3 themes (default, gentelella, glass) |
| **Database Abstraction** | Implemented | `Database.php` + `Objects\Query` (fluent builder) + `Objects\Schema` (DDL with `compare()`/`update()` migration) + MySQL connector |
| **Auth** | Partially implemented | `Auth.php` (bearer/basic/session), database-backed sessions, remember-me cookie, `User->organization()`, `Objects\Pin` (numeric PIN only); 2FA/TOTP and registration not yet implemented |
| **Helpers** | Implemented | Auto-loads from core, vendor, and plugin directories via `$HELPER-><name>` magic getter |
| **Config** | Implemented | `Config.php` for JSON `.cfg` files; 4 config files committed (css.cfg, extensions.cfg, js.cfg, requirement.cfg), others gitignored for instance-specific settings |
| **CSRF** | Implemented | Automatic validation on POST/PUT/PATCH/DELETE, timing-safe comparison, token rotation |
| **Output** | Implemented | CLI colorized + HTTP JSON output |
| **Logging** | Implemented | `Log.php` with 5 levels, file rotation, IP tracking, debug_backtrace caller info |
| **i18n** | Partially implemented | `Locales.php` supports en-ca/fr-ca, timezone management; Locale files not populated |
| **Installer** | Partially implemented | `Installer.php` + `Database::install()` for schema/data bootstrapping |
| **SMTP** | Implemented | `SMTP.php` email sender, used in password reset flows |
| **Scaffold** | Partially implemented | `lib/skeleton/` boilerplate exists; `init.sh` initializer |
| **Modules** | Implemented | `lib/modules/core/` as self-contained submodule |
| **Dev Tools** | Implemented | `dev` plugin: `/dev/on`, `/dev/off`, `/dev/status` endpoints, `Developer` role, maintenance toggle, `Widget.php` (267 lines) |
| **Core Info API** | Implemented | `/api/core/info` (`CoreEndpoint::infoAction()`) — version, name, owner, copyright, changelog, logo, license, authors |
| **Organizations** | Implemented | `User->organization()` + `src/Objects/Organization.php` + `lib/plugins/organizations/` plugin with tables/repositories/admin UI |
| **UI Builder** | Implemented | `assets/js/builder.js` — UI component builder for Bootstrap components, DataTables integration, extensible via extensions |

### What's Missing (Gaps)

| Area | Severity | Notes |
|------|----------|-------|
| **Route Accessibility Tests (HTTP)** | P1 | PHPUnit unit tests for new kernel components cover Router, Response, View, Controller, Middleware, Hook, EntryPoint. HTTP accessibility layer (guzzle/browser-kit for public/private/auth simulation) deferred — CLI `testroutes` verifies route compilation. |
| **Admin Settings Page** | P1 | No `/admin/settings` page. The config system already handles app-specific `.cfg` files (some committed, some gitignored for instance-specific settings). Needs a settings UI, not a new config service. |
| **Profile Modal** | P1 | Currently `lib/plugins/profile/` is a page-based system — should be converted to a modal with section registry. |
| **Debug Audit Logger** | P1 | `Log.php` provides logging (5 levels, file rotation, IP tracking) but there's no audit trail layer — no `DebugAuditLogger` class, no admin audit page. |
| ~~**Encryption Service**~~ | ~~P1~~ | ~~`src/Encryption.php` was a 0-byte stub. Implemented with AES-256-GCM, key derivation, and token generation.~~ |
| **Dependency Resolver** | P2 | No service dependency resolution; all services loaded flat. Needed for extension install/uninstall lifecycle. |
| **Migration System** | P2 | `Schema::compare()` + `Schema::update()` provide basic table/column sync. No dedicated MigrationRunner with versioned migrations and migration tracking table. |
| **Documentation Plugin** | P2 | No docs generation plugin. Needs to read `docs/` directories at every level (kernel, app, plugins, themes, modules). |
| **SMS / IMAP Services** | P2 | `src/SMS.php` and `src/IMAP.php` are 0-byte stubs. Needed for 2FA via SMS and email verification. |
| **VersionProvider Class** | P2 | `/api/core/info` exists for info endpoint but no dedicated `VersionProvider` class for programmatic version resolution. |
| **Developer Page** | P2 | `dev` plugin has API endpoints and Widget but no `/admin/developer` page or scaffold generator. |
| **Datatables Standardization** | P2 | Assets exist (`lib/plugins/datatables/`) but no standardized usage pattern. Update to 2.3.8 + ColumnControl support needed. Standardization via `assets/js/builder.js`. |
| **UI Builder Documentation** | P2 | `assets/js/builder.js` needs documentation — it's the standard UI component builder. |
| **Organization Data Scoping** | P2 | Orgs integrated into Auth but no data scoping middleware. |
| **SLS Service** | P3 | `src/SLS.php` is a 0-byte stub. Deferred pending licensing design. |
| **PostgreSQL Connector** | P3 | Stub. MySQL + SQLite sufficient for V1.0. |
| **SQLite Connector** | P2 | Detailed plan in Phase 2.7; implementation pending. Blocks local development. |
| **Menu Registry** | P2 | Menu is handled by `Builder->menu()` but no explicit `MenuRegistry` class. Current approach is sufficient for now. |
| ~~**Config Documentation**~~ | ~~P2~~ | ~~4 docs written in `docs/03-using/` (~512 lines) covering config API, override layer, bootstrap loading, and application settings.~~ |

---

## Phase 1: Stabilization (Now — Core Safety)

Foundational work that prevents bugs and enables safe development.

### 1.1 Testing Infrastructure (P0)

**Why**: No tests exist anywhere in the codebase. This is the single highest-risk gap — every commit could silently break something.

**Approach**: Two-layer testing strategy.

**Layer 1 — Syntax validation (pre-commit / CI gate)**:
- [x] Scan all `.php` files in the root directory recursively using `php -l` (lint)
- [x] Fail CI if any file has syntax errors
- [x] Script: `tests/syntax.sh` for local use (CI in `.github/workflows/ci.yml`)
- [x] Include all PHP files in `src/`, `lib/plugins/*/`, and `lib/modules/*/`

**Layer 2 — Route accessibility tests**:
- [x] Compile all routes from every plugin (scan `routes.cfg` files in `lib/plugins/*/`) — via `php cli core testroutes`
- [ ] Test each route with **public access** (requires HTTP client)
- [ ] Test each route with **logged-in access** (requires HTTP client)
- [ ] Test each route with **unauthenticated access** (requires HTTP client)
- [ ] Validate HTTP status codes, response bodies, and headers
- [ ] Use PHPUnit for route tests (requires `guzzlehttp/guzzle` or `symfony/browser-kit`)

**CI integration**:
- [x] Add GitHub Actions workflow (`.github/workflows/ci.yml`)
- [x] Run `php -l` on all PHP files on every PR push
- [x] Run PHPUnit tests on every PR push
- [x] Add CI pre-check to `.github/workflows/release.yml` (runs syntax + PHPUnit before release)

**Tasks:**
- [x] Create `tests/` directory with bootstrap file
- [x] Create `tests/syntax.sh` — recursive `php -l` across root directories
- [x] Create `tests/Unit/` — 6 test files (86 tests) for Router, Response, View, Controller, Middleware, Hook, EntryPoint, ViewGlobals
- [x] Add PHPUnit to `composer.json` dev dependencies
- [x] Create `.github/workflows/ci.yml` (runs `php -l` + PHPUnit on every push/PR)
- [x] Update `.github/workflows/release.yml` to include CI pre-check

### 1.2 Configuration Documentation (P2)

**Why**: The config system already works — `.cfg` files in `config/` store app-specific settings. Some are committed (extensions.cfg, requirement.cfg, js.cfg, css.cfg), others are gitignored (contain instance-specific or sensitive settings). Documentation should follow the existing docs/index.md structure.

**Target docs location**: `docs/03-using/` (matching the existing docs/index.md TOC structure)

**Tasks:**
- [x] Create `docs/03-using/configuration.md` — Config class API, JSON format, path resolution, committed vs gitignored, examples
- [x] Create `docs/03-using/config-override.md` — which files are committed vs gitignored and why, deploy pattern
- [x] Create `docs/03-using/bootstrap-config.md` — bootstrap config loading order, override format, $CONFIG global
- [x] Create `docs/03-using/app-settings.md` — SettingsRegistry, SettingsSection, SettingsField (text, textarea, boolean, select, hidden)
- [x] Document which `.cfg` files are committed vs gitignored (and why)
- [x] Document config loading order and precedence
- [x] Document config file conventions (format, naming, structure)
- [x] Add examples for creating a new config file
- [ ] Link from docs/index.md TOC (already present)

### 1.3 Application Settings System (P1)

**Why**: No `/admin` or `/admin/settings` page exists. Administrators need a way to configure the application at runtime. This blocks deployment of any real application.

**Phase breakdown** — start with `/admin` as a minimal landing page, then expand the namespace:

**1.3.1 — `/admin` landing page**:
- [x] Create ConfigEndpoint (config plugin)
- [x] Use `panel.php` template for admin layout
- [x] Create admin sidebar with navigation to sub-pages
- [x] Add link to `/admin` in the user menu (added to config routes.cfg location array, not profile routes.cfg — avoids circular dependency; any plugin can add to a route's location)
- [x] Use `Builder->menu('sidebar-admin')` for the admin sidebar

**1.3.2 — Settings registry**:
- [x] Create `SettingsRegistry` class (singleton, plugin-provided sections)
- [x] Create `SettingsSection` value object (key prefix, label, icon, fields)
- [x] Create `SettingsField` value object (text, textarea, boolean, select types)
- [x] Build `/admin/settings` page (GET renders registry, POST saves to `.cfg` file)
- [x] Build UI: card-based sections, field types (text, boolean, select), save confirmation
- [x] Provide `SettingsRegistry::register()` hook for plugins
- [x] Add `/admin/security` page (2FA, password policy settings)
- [x] Add `/admin/maintenance` page (app config, SMTP, theme toggle)
- [x] Test settings save/load round-trip (redirect-after-save pattern with flash messages)
- [x] Test plugin-registered sections appear correctly
- [x] Document in `/docs/developer/admin-settings.md`

**1.3.3 — Default core settings registration**:
- [x] Add default app settings section (name, owner, theme, nav titles) via config plugin bootstrap.php
- [x] Seed application.cfg defaults if not present

**Existing references to update**:
- `docs/index.md` already lists `Admin Panel Overview`, `Settings Page`, `Security Settings`, `Debug Audit Logging`, `Developer Mode`, `Scaffold Generator` under `04-administering/` — create these docs as admin pages are built

### 1.4 Global View Context (P1)

**Why**: Views/partials access globals inconsistently across the 5 layouts. Currently handled through `Route` (`$ROUTE`/`$this`) and `Bootstrap`, but no centralized guarantee. Missing context causes silent errors.

**Tasks:**
- [x] Create `ViewGlobals` class with `apply()` and `context()` (guaranteed context from request scope)
- [x] Define guaranteed globals: `$auth`, `$currentUser`, `$menu`, `$breadcrumbs`, `$locale`, `$csrf`, `$config`, `$app`
- [x] Update all 5 layouts to call `ViewGlobals::apply()` at entry point (panel, website, fullscreen, internal, index)
- [x] Guest-safe defaults for unauthenticated users (null currentUser, safe defaults)
- [x] View engine injects ViewGlobals into every render (automatic, no controller changes)
- [x] Test each layout renders without undefined variable errors (260 files, 86 tests)
- [x] Document the contract in `/docs/developer/view-context.md`

### 1.5 Encryption Service (P1)

**Why**: `src/Encryption.php` is a 0-byte stub. No encryption/decryption utilities exist in the framework. Needed for secure data handling (PII, tokens, sensitive config values).

**Tasks:**
- [x] Implement `Encryption` class with symmetric encryption (AES-256-GCM)
- [x] Implement key derivation from passphrase
- [x] Implement data encryption/decryption methods
- [x] Implement secure key generation
- [x] Add tests for encrypt/decrypt round-trip
- [x] Document in `/docs/developer/encryption.md`

### 1.6 MVC Conversion (P0)

**Why**: The current Router/Route architecture has three structural problems: (1) Router is a monolith doing routing, auth, rendering, and error handling; (2) Route is fat — holds metadata, path resolution, controller dispatch, config persistence, and rendering; (3) Everything depends on Apache .htaccess with zero testability. This phase converts the kernel to MVC while preserving all existing functionality.

**Architecture**:

```
Router        — register() + match()  (pure routing)
RouteDTO      — pure data (namespace, template, view, public, level, action)
Middleware    — auth, maintenance (before/after controller)
Response      — controller return value (render, redirect, json, error)
View          — template + view composition (output-buffered)
Controller    — base class (bootstrap, $this->Route, $this->Helper, etc.)
EntryPoint    — coordinates Bootstrap → Router → Middleware → Controller → View
Hook          — plugin extension points (register/fire pattern)
```

**Key principles**:
- Route becomes thin DTO (no globals, no rendering, no persistence)
- Everything goes through a controller (routes without action get default ViewAction)
- Auth moves to middleware (separate from routing)
- Response object replaces implicit output
- View engine replaces require_once (output-buffered, returns string)
- Plugin hooks via Hook::register()/Hook::fire()
- Zero breaking changes for existing plugins

**Phases**:

#### Phase A — New Kernel Components (read-only, no migration)
- [x] Create `RouteDTO` (thin data object, `src/Objects/RouteDTO.php`)
- [x] Create `Response` class (`src/Response.php`)
- [x] Create `View` engine (`src/View.php`)
- [x] Create `Controller` base class (`src/Controller.php`)
- [x] Create Middleware components (`src/Middleware/`)
- [x] Create `Hook` class (`src/Hook.php`)
- [x] Create `EntryPoint` coordinator (`src/EntryPoint.php`)

#### Phase B — Migration Adapter (backward-compatible)
- [x] Refactor `Router.php` — add `register()`, `match()`, `all()`, `loadFromConfig()`, `startMVC()`
- [x] Migrate `Route.php` — delegate `render()` to `View` engine
- [x] Migrate `Bootstrap.php` — coordinate new components (add HOOK, ENTRYPOINT globals)
- [x] Verify all 57+ plugins still load correctly (62 routes verified via `php cli core testroutes`)

#### Phase C — Entry Points & Server Support
- [x] Update `CoreHelper::init()` — project root `index.php` created
- [x] Create project root `index.php` (shared hosting entry point)
- [x] Add `php cli core serve` command (PHP built-in server) via `CoreCommand::serveAction()`
- [x] Clean up `webroot/index.php` (server-agnostic front controller)
- [ ] Add `nginx.conf.example` from `CoreHelper::init()`
- [ ] Add Cloudflare-friendly headers

#### Phase D — Testing Infrastructure
- [x] Set up PHPUnit (`phpunit/phpunit` dev dep, `phpunit.xml.dist`, `tests/bootstrap.php`)
- [x] Add CoreCommand test commands (`test:routes` via `CoreCommand::testRoutesAction()`)
- [x] Create unit tests for all new components (6 test files, 86 tests: Router, Response, View, Controller, Middleware, Hook, EntryPoint, ViewGlobals)
- [x] Create test traits (`tests/Traits/MockGlobals.php`)
- [x] Run all tests: `php vendor/bin/phpunit` (86 tests, 198 assertions, all pass)

#### Phase E — Documentation & Cleanup
- [x] Update `ROADMAP.md` — add Phase 1.6, mark Complete
- [x] Create `/docs/mvc-migration.md` — plugin migration guide (endpoints → controllers, hooks, deployment)
- [ ] Update `DESIGN.md` — Section 7 (Routing), Section 16 (Entry Points), Section 18 (Testing) — **still pending**
- [ ] Update plugin documentation — deferred (no breaking changes required)

**Risk Mitigation**:
- Phase A/B are fully backward-compatible — existing plugins keep working
- Phase C/D are additive — no existing behavior changes
- Each phase is independently verifiable

**Deliverables**:
- [x] `php cli core testroutes` passes for all 62 routes
- [x] PHPUnit runs clean (86 tests, 198 assertions) for all new components
- [x] Shared hosting entry point (`index.php`, `webroot/index.php`) works
- [x] `php cli core serve` — PHP built-in server works
- [x] 57+ plugins still load without modification
- [ ] Nginx config (needs generation from `CoreHelper::init()`)
- [ ] Cloudflare-friendly headers (needs implementation in `Request.php`)

---

## Phase 2: Core Services (Unblocking Feature Work)

Services that unlock plugin and application development.

### 2.1 Auth System Security Review + Feature Completion (P1)

**Why**: Auth is incomplete — 2FA/TOTP and user registration are stubs. The Auth system (`src/Auth.php`, `src/Backends/`, `src/Objects/User.php`, `src/Objects/Organization.php`) needs a security review and completion of missing features.

**Auth system review scope:**
- Session fixation/hijacking defenses
- Token rotation and expiry handling (`Objects\Pin` — currently only numeric PIN, not TOTP)
- Password policy enforcement
- Backend extensibility (only `Local` backend exists; LDAP/ADDC/SMTP/IMAP/OAuth are commented-out stubs)
- Organization membership model (currently `User->organization()` + `src/Objects/Organization.php`)

**Tasks:**
- [ ] **Security review of `Auth.php`** — session fixation, token management, password policy, backend abstraction
- [ ] Implement 2FA/TOTP (`Objects\Pin` → TOTP using `phpseclib3/phpseclib`)
- [ ] Implement 2FA recovery codes (UUID format, one per user, single-use)
- [ ] Implement 2FA via Email (SMTP) — generate OTP, send via SMTP, verify
- [ ] Implement 2FA via SMS (SMS service) — generate OTP, send via SMS, verify
- [ ] Implement user registration (config-gated, disabled by default)
- [ ] Implement email verification flow (single-use token, 24h expiry)
- [ ] Implement forgot password flow (single-use token, 60min expiry)
- [ ] Implement remember-me (selector/validator token pair with rotation)
- [ ] Test all auth flows with browser tests or PHPUnit
- [ ] Document auth flow in `/docs/developer/authentication.md`

### 2.2 Profile Modal System (P1)

**Why**: Users need a way to manage their profile, security settings, and 2FA. Currently `lib/plugins/profile/` is a page-based system — should be converted to a modal.

**Tasks:**
- [ ] Create `ProfileModal` class with section registry
- [ ] Create `ProfileSection` value object (tab, content callback, permissions)
- [ ] Build modal UI in topbar (Bootstrap modal, tabbed interface)
- [ ] Migrate existing profile data (Overview, Security, API Tokens) to modal tabs
- [ ] Support plugin-provided sections via hook
- [ ] Test tab rendering, permission gating, AJAX content loading
- [ ] Document in `/docs/developer/profile-modal.md`

### 2.3 Debug & Audit Logging (P1)

**Why**: `Log.php` provides logging (5 levels, file rotation, IP tracking) but there's no audit trail. Admin actions, security events, and debug issues need a dedicated audit log.

**Tasks:**
- [ ] Create `DebugAuditLogger` class (APP_DEBUG-gated, reuses `Log.php` infrastructure)
- [ ] Log to `admin_audit_log` table (type: debug/audit, sanitized payloads, IP, user)
- [ ] Create `/admin/audit` page with type filtering (`?type=all|debug|audit`)
- [ ] Create `AuditLogger` class for production audit trail (non-debug)
- [ ] Log auth events (login, logout, 2FA, permission changes)
- [ ] Add debug badges to /admin pages (request ID, global variables, CSRF status)
- [ ] Test audit log write/filter/render

### 2.4 Version Provider (P2)

**Why**: `/api/core/info` (`CoreEndpoint::infoAction()`) provides version, name, owner, copyright, changelog, logo, license, authors. But no dedicated `VersionProvider` class exists for programmatic version comparison (e.g., kernel/app version resolution, extension compatibility checks).

**Tasks:**
- [ ] Create `VersionProvider` class with kernel and application version resolution
- [ ] Support semver comparison (`>=`, `<=`, `^`, `~`, exact)
- [ ] Add admin overview card showing kernel version, app version, update status
- [ ] Test version resolution and comparison logic

### 2.5 Dependency Resolver (P2)

**Why**: Plugin install/enable/disable needs to resolve dependencies and block incompatible operations. Currently services are loaded flat in `Bootstrap.php`.

**Tasks:**
- [ ] Create `DependencyResolver` class
- [ ] Support dependency format: `plugin-name >=1.0.0`, `plugin-name <2.0.0`
- [ ] Check install/enable/disable/uninstall for blocked operations
- [ ] Add to extension catalog UI (block reason display)
- [ ] Test dependency resolution with conflicting versions

### 2.6 Migration System Improvement (P2)

**Why**: `Schema::compare()` + `Schema::update()` provide basic table/column sync between definition files and DB structure. But no dedicated MigrationRunner exists for versioned migrations.

**Existing: `Schema::compare()` compares in-memory column definitions vs DB structure, returns descriptive diffs or SQL queries. `Schema::update()` executes the queries. Used by plugin installation.**

**Tasks:**
- [ ] Create `MigrationRunner` class with versioned migrations
- [ ] Support versioned migrations (`migrations/001_create_users.php`, etc.)
- [ ] Track applied migrations in `migrations` table
- [ ] Integrate with plugin lifecycle (install → run migrations, uninstall → reverse migrations)
- [ ] Test migration apply/reverse
- [ ] Document migration format in `/docs/developer/migrations.md`

### 2.7 Database Connector Expansion (MySQL + SQLite) (P1)

**Why**: MySQL-only blocks local development (PHP 8.1+ on macOS has no MySQL socket by default; SQLite works out-of-the-box). The Connector pattern is already in place — SQLite just needs implementation.

**Files affected:**
- `src/Connectors/SQLite.php` (60-80 lines, from scratch)
- `src/Database.php` (add SQLite to connector factory switch)
- `src/Objects/Schema.php` (replace MySQL-specific SQL with adapter calls)
- `src/Objects/Query.php` (replace MySQLi-specific calls with adapter calls)
- `src/Objects/Definition.php` (SQLite type mapping)
- `src/Installer.php` (SQLite config format support)
- `config/database.cfg` (add SQLite example)

**Tasks:**
- [ ] **Task 1: Implement `Connectors\SQLite.php` (60-80 lines)**
  - Use `PDO_SQLITE` driver (not `sqlite3` — PDO supports prepared statements natively)
  - `connect()`: Open DB file path from config (`database.cfg → path`), auto-create file if not exists
  - `describe()`: `PRAGMA table_info(<table>)` mapped to DESCRIBE format: `{Field, Type, Null, Key, Default, Extra}`
  - `lastId()`: `PDO::lastInsertId()`
  - `affectedRows()`: `PDO::rowCount()` (note: SELECT returns -1 in SQLite, needs `COUNT(*)` workaround)
  - `prepare()`: PDO prepared statement with `bindValue()` (no type-string workaround needed like MySQL)
  - Handle SQLite file permissions (`chmod 0644` on creation)
- [ ] **Task 2: Create `DatabaseAdapter` interface for SQL dialect differences**
  - Define `describeTable(string): array` — column introspection (SQLite: PRAGMA, MySQL: DESCRIBE)
  - Define `showTables(): array` — list tables (SQLite: sqlite_master query, MySQL: SHOW TABLES)
  - Define `buildCreateTable(string $table, string $columnsSQL): string` — dialect-specific wrapper
  - Define `buildAlterModify(string $table, string $col, string $def): string` — SQLite workaround for MODIFY
  - Define `autoIncrement(int): string` — dialect-specific AUTO_INCREMENT syntax
  - MySQL adapter: `MySQL::describeTable()` wraps existing `describe()`
  - SQLite adapter: `SQLite::describeTable()` wraps `PRAGMA table_info`
- [ ] **Task 3: Make `Schema.php` connector-aware**
  - Replace `const engine = 'InnoDB'` with `$this->engine = $connector->getDefaultEngine()`
  - Replace `const charset = 'utf8mb4'` with `$this->charset = $connector->getDefaultCharset()`
  - Replace `SHOW TABLE STATUS LIKE` with adapter's `describeTable()`
  - Replace `SHOW TABLES LIKE` with adapter's `showTables()`
  - Replace `buildColumnSQL()` hardcoded `ENGINE=... CHARSET=... COLLATE=...` with `$connector->buildCreateTable()`
  - Handle SQLite's lack of `MODIFY COLUMN` via adapter
- [ ] **Task 4: Update `Query.php` for SQLite**
  - Replace `get_result()` / `fetch_assoc()` with `$stmt->fetch(PDO::FETCH_ASSOC)` for SQLite
  - Handle `affectedRows()` for SELECT queries in SQLite (use `COUNT(*)` workaround)
  - Replace `AUTO_INCREMENT` with `AUTOINCREMENT` for SQLite
- [ ] **Task 5: Update `Installer.php` for SQLite**
  - Detect connector type
  - Use adapter-aware schema creation
  - SQLite config format: `{ "path": "data/database.sqlite" }` vs MySQL: `{ "host": "localhost", "database": "demo", "username": "root", "password": "" }`
- [ ] **Task 6: Update `Definition.php` — SQLite type mappings**
  - Map `tinyint(1)` → `BOOLEAN` for SQLite
  - Map `ENUM` → `TEXT` (SQLite doesn't support ENUM; add comment with enum values)
  - Map `AUTO_INCREMENT` → `AUTOINCREMENT`
  - Map `on update CURRENT_TIMESTAMP` → unsupported in SQLite (warn or skip)
- [ ] **Task 7: Add SQLite to `requirement.cfg` and documentation**
  - Document SQLite as valid connector (`connector: sqlite`)
  - Document SQLite system requirements (PHP 8.1+ with PDO_SQLITE extension)
  - Add SQLite config example to `config/database.cfg`
- [ ] **Task 8: Add tests**
  - SQLite connection test
  - SQLite Query builder tests (SELECT, INSERT, UPDATE, DELETE, JOIN, ORDER BY, LIMIT, INDEX)
  - SQLite Schema tests (create, describe, compare, update)
  - SQLite migration/upgrade tests (ALTER TABLE limitations, create-drop-rename workaround)
  - Config migration: MySQL → SQLite round-trip test
  - Definition type mapping tests
- [ ] **Task 9: Document SQLite support**
  - `/docs/developer/database-connectors.md` — connector interface, SQLite config, migration guide from MySQL
  - `/docs/developer/sqlite-notes.md` — known limitations (MODIFY COLUMN workaround, JSON_CONTAINS compatibility, ENUM handling)

### 2.8 SMS / IMAP Services (P2)

**Why**: `src/SMS.php` and `src/IMAP.php` are 0-byte stubs. Needed for 2FA via SMS, email verification, account recovery, and inbox integration.

**Tasks:**
- [ ] Implement `SMS.php` — send SMS via provider (Twilio, Vonage, etc.)
- [ ] Implement `IMAP.php` — connect to mail server, read/parse emails
- [ ] Add SMS as a 2FA channel (generate OTP, send via SMS, verify)
- [ ] Add IMAP for email verification (parse incoming verification emails)
- [ ] Test SMS/IMAP flows with mock providers
- [ ] Document in `/docs/developer/sms-imap.md`

### 2.9 SLS Service (P3)

**Why**: `src/SLS.php` is a 0-byte stub. Deferred pending licensing design.

---

## Phase 3: Feature Completeness

Features that make the kernel production-ready.

### 3.1 Developer Mode Completion (P2)

**Why**: The `dev` plugin exists with `/dev/on`, `/dev/off`, `/dev/status` endpoints, `Developer` role, maintenance toggle, and `Widget.php` (267 lines). But no `/admin/developer` page or scaffold generator exists.

**Current dev plugin (`lib/plugins/dev/`):**
- `Endpoint.php` — `/dev/on`, `/dev/off`, `/dev/status` endpoints
- `Widget.php` — dropdown in topbar with development/maintenance toggles
- `routes.cfg` — phpMyAdmin link under developer location
- `Developer` role defined in Auth system

**Tasks:**
- [ ] Create `/admin/developer` page
- [ ] Scaffold generator (plugins, endpoints, models, controllers, layouts)
- [ ] Templates stored in `resources/scaffolds/`
- [ ] Developer mode toggle (APP_DEBUG equivalent for admin)
- [ ] Test tool availability and scaffold output

### 3.2 Documentation Plugin (P2)

**Why**: No docs generation plugin for the kernel.

**Tasks:**
- [ ] Create `documentation` plugin with lightweight markdown renderer
- [ ] **Multi-level docs search:** Read `docs/` directories at every level:
  - Kernel: `vendor/laswitchtech/core/docs/`
  - Application: `app/docs/`
  - Plugins: `lib/plugins/*/docs/`
  - Themes: `lib/themes/*/docs/`
  - Modules: `lib/modules/*/docs/`
- [ ] Panel layout with sidebar TOC + prev/next navigation
- [ ] Support headers, bold, italic, code, lists, links, images
- [ ] Edit on GitHub link for admin users
- [ ] Plugin self-registration with routes
- [ ] Test rendering of common markdown patterns

### 3.3 Datatables Standardization (P2)

**Why**: DataTables assets exist but no standardized usage pattern. Need to update library and standardize.

**Tasks:**
- [ ] Update DataTables library to v2.3.8
- [ ] Add ColumnControl extension support
- [ ] Standardize implementation via `assets/js/builder.js` DataTables builder
- [ ] Create `DataTable` helper class for consistent initialization
- [ ] Define standard configuration options
- [ ] Update existing DataTables usage to new standard
- [ ] Document in `/docs/developer/datatables.md`

### 3.4 UI Builder Documentation (P2)

**Why**: `assets/js/builder.js` is the UI component builder used for all Bootstrap component generation. It needs documentation as the standard UI generation pattern.

**Current `builder.js`:**
- `Builder` class with component registry (cards, tables, layouts, inputs, widgets)
- DataTables integration (columnDefs, buttons, searchBuilder, exportTools, columnsVisibility, selectTools)
- Extensible via extensions (Builder.extend())
- Component lifecycle: `_init()` → `_create()` → `_load()` → `_insert()` → `_timeout()`

**Tasks:**
- [ ] Document `Builder` class API in `/docs/developer/builder.md`
- [ ] Document component registration and extension patterns
- [ ] Document DataTables builder options (columnDefs, buttons, searchBuilder, etc.)
- [ ] Document component lifecycle hooks
- [ ] Add JSDoc comments to `builder.js`
- [ ] Add examples for common patterns

### 3.5 Organization System Data Scoping (P2)

**Why**: Organizations are integrated into Auth (`User->organization()`) and the plugin exists at `lib/plugins/organizations/` (tables: organizations, users, members). But data scoping middleware is missing — queries don't automatically scope to the user's organization.

**Current state:**
- `src/Objects/Organization.php` — org CRUD, member management
- `src/Objects/User.php` → `organization()` method
- Auth checks `$user->organization['isActive']`
- Plugin tables: `organizations`, `users`, `organization_users`
- Admin UI at `/security/organizations`

**Tasks:**
- [ ] Create `OrganizationScope` middleware (auto-filter queries by organization)
- [ ] Add `OrganizationRepository` and `OrganizationMemberRepository`
- [ ] Profile Modal integration (switch/create/list organizations)
- [ ] `/admin/organizations` listing page
- [ ] Test organization creation, membership, context switching

---

## Phase 4: Pre-V1.0 Polish

Final work before V1.0 release.

### 4.1 Extension Marketplace Foundation (P2)

**Tasks:**
- [ ] Extension listing page with filtering/sorting
- [ ] Version tracking for installed extensions
- [ ] ZIP download + checksum verification
- [ ] Installation progress tracking
- [ ] Bulk operations (enable/disable multiple)

### 4.2 Kernel Update System (P2)

**Tasks:**
- [ ] Version check (local-only for now)
- [ ] Download workflow (local override patches)
- [ ] Apply workflow with rollback capability
- [ ] Admin UI for update management

### 4.3 Menu Registry (P2)

**Why**: Menu system works via `Builder->menu()` but no explicit `MenuRegistry` class makes it hard for plugins to register menu items.

**Current**: `Builder->menu('developer')` and other locations in `Widget.php`.

**Tasks:**
- [ ] Create `MenuRegistry` class with plugin hooks
- [ ] Support menu item registration with permissions, icons, order
- [ ] Replace `Builder->menu()` with registry-backed menu building
- [ ] Document menu plugin API

### 4.4 Theme/Runtime Management (P3)

**Tasks:**
- [ ] Theme switch UI in admin settings
- [ ] Preview before applying
- [ ] Layout runtime management (switch without manual file operations)

---

## Phase 5: V1.0 Scope

Features required for V1.0 release (target: 2026-08-15).

### Required for V1.0

- [x] **Complete testing infrastructure + CI (Phase 1.1)** — 86 unit tests, syntax check, CI workflow, release pre-check
- [x] **Config documentation (Phase 1.2)** — 4 docs in `docs/03-using/`: config API, override layer, bootstrap loading, app settings (~512 lines)
- [x] **Application settings registry + `/admin` (Phase 1.3)** — SettingsRegistry, config plugin with /admin dashboard, /admin/settings, /admin/security, /admin/maintenance; card-based UI with field types; redirect-after-save flash messages
- [x] **Global view context (Phase 1.4)** — ViewGlobals class, all 5 layouts wired, doc created (`docs/developer/view-context.md`)
- [x] **Encryption service (Phase 1.5)** — AES-256-GCM, PBKDF2 key derivation, token generation, HMAC utilities; 30 tests, full docs
- [x] **MVC conversion + server-agnostic deployment + testing (Phase 1.6)** — phases A-D complete; Phase E pending `DESIGN.md` update, nginx config, Cloudflare headers
- [ ] Complete auth features + security review (Phase 2.1)
- [ ] Profile modal (Phase 2.2)
- [ ] Debug/audit logging (Phase 2.3)
- [ ] Version provider (Phase 2.4)
- [ ] Dependency resolver (Phase 2.5)
- [ ] Migration system (Phase 2.6)
- [ ] Database connector expansion (MySQL + SQLite) (Phase 2.7)
- [ ] SMS / IMAP services (Phase 2.8)
- [ ] Developer mode completion (Phase 3.1)
- [ ] Documentation plugin (Phase 3.2)
- [ ] Datatables standardization (Phase 3.3)
- [ ] UI Builder documentation (Phase 3.4)
- [ ] Organization data scoping (Phase 3.5)

### Out of Scope for V1.0

- OAuth server / client integration
- Licensing server and validation system
- Extension marketplace with payment processing
- Online extension submission/review portal
- Multi-app ecosystem support
- Remote update channels (signed release distribution)
- Plugin signing / checksum verification
- Distributed authentication sharing
- AI agent orchestration system
- Business automation apps (Transport, Customs, LaswitchTech operational apps)

These systems are large enough to warrant their own design documents and development timelines.

---

## Timeline Assessment (Updated 2026-06-04)

| Metric | Value |
|--------|-------|
| Current version | v0.0.92 |
| Target date | 2026-08-15 (~70 days from today) |
| Phases complete | 1/5 (Testing, Global View Context) + partially MVC |
| Remaining major phases | 5, 6, 7, 8, 9 (Phases 2–5 in doc) |

**Status**: At current pace, V1.0 is **at risk**. The following must be accelerated or de-scoped:

- **Must have for V1.0**: Auth completion (2.1), profile modal (2.2), encryption (1.5), SQLite connector (2.7)
- **Should have**: Settings registry (1.3), config docs (1.2), debug logging (2.3)
- **Can defer**: Version provider (2.4), dependency resolver (2.5), migration system (2.6), SMS/IMAP (2.8), developer mode completion (3.1), docs plugin (3.2), DataTables standardization (3.3), UI Builder docs (3.4), org data scoping (3.5)

**Recommendation**: Prioritize Phases 1.5, 2.1, 2.7, and 1.3 for V1.0. Defer everything in Phase 3+ to post-V1.0 unless it blocks core functionality.

---

## Deferred / Explicitly Not Now

| Item | Reason |
|------|--------|
| OAuth | Pending authentication architecture design |
| Licensing | Pending licensing server design |
| Marketplace | Pending payment and review infrastructure |
| Remote ZIP install | Pending checksum/signature model |
| Multi-instance auth sharing | Pending OAuth foundation |
| Plugin signing | Pending marketplace infrastructure |
| Distributed architecture | Post-V1.0 consideration |
| SMS service (`SMS.php`) | Phase 2.8 — In progress |
| IMAP service (`IMAP.php`) | Phase 2.8 — In progress |
| SLS service (`SLS.php`) | Phase 2.9 — Deferred pending licensing design |
| PostgreSQL connector | Stub — MySQL + SQLite sufficient for V1.0 |
| SQLite connector | Phase 2.7 — In progress |

---

## Appendix: Current Phase Checklist

| Phase | Area | Status | Notes |
|-------|------|--------|-------|
| **1.1** | **Testing** | **Complete** | PHPUnit + 86 tests; `tests/syntax.sh`; CI workflow + release pre-checks; Route compilation via `testroutes` (HTTP accessibility tests pending guzzle/browser-kit) |
| **1.2** | **Config Documentation** | **Complete** | `docs/03-using/configuration.md` (Config API, JSON format), `config-override.md` (committed vs gitignored), `bootstrap-config.md` (loading order, $CONFIG global), `app-settings.md` (SettingsRegistry, SettingsSection, field types) — 4 docs, ~512 lines |
| **1.3** | **Settings Registry** | **Complete** | SettingsRegistry, SettingsSection, SettingsField; config plugin with /admin dashboard, /admin/settings, /admin/security, /admin/maintenance; card-based UI; redirect-after-save flash messages; default app settings seeded |
| **1.4** | **Global View Context** | **Complete** | `ViewGlobals` class; all 5 layouts updated; View engine injects globals; doc created; 86 tests pass |
| **1.5** | **Encryption Service** | **Complete** | AES-256-GCM, PBKDF2 key derivation (100k iterations), nonce reuse protection, AAD binding; `generateKey`, `encrypt`/`decrypt`, `encryptWithPassphrase`/`decryptWithPassphrase`, `token`, `urlToken`, `hmac`; 30 tests pass |
| **1.6** | **MVC Conversion** | **Complete (mostly)** | Phases A-D complete; Phase E: `DESIGN.md` update, nginx config generation, Cloudflare headers still pending; Bootstrap globals (HOOK, ENTRYPOINT, BUILDER, HELPER); NullConnector for CLI scope; 86 tests pass; `php cli core testroutes` verifies all 62 routes |
| 2.1 | Auth + 2FA | Partial | 2FA/TOTP, registration, email/SMS 2FA pending |
| 2.2 | Profile Modal | Not started | Currently page-based |
| 2.3 | Debug/Audit Logger | Not started | `Log.php` exists, audit layer missing |
| 2.4 | Version Provider | Not started | `/api/core/info` exists but no class |
| 2.5 | Dependency Resolver | Not started | No resolver |
| 2.6 | Migration Runner | Partial | `Schema::compare()`/`update()` exist, no versioned migrations |
| 2.7 | Database Connectors | Planned | MySQL implemented; SQLite has detailed 9-task plan in Phase 2.7 |
| 2.8 | SMS/IMAP | Not started | Both 0-byte stubs |
| 2.9 | SLS | Not started | 0-byte stub |
| 3.1 | Developer Mode | Partial | `dev` plugin exists, page/scaffold generator missing |
| 3.2 | Documentation | Not started | No docs plugin |
| 3.3 | DataTables | Not started | Standardization + update to 2.3.8 needed |
| 3.4 | UI Builder | Not started | `builder.js` needs docs |
| 3.5 | Organization | Partial | Core integration done, data scoping missing |
| **V1.0 Target** | **2026-08-15** | Scope defined above | **Timeline risk** — Phase 1 only complete; ~4 months remaining for 5 phases |
