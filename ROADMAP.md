# Core-Web — Project Roadmap

> **Version**: v0.0.92
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
| **Database Abstraction** | Partially implemented | `Database.php` + `Objects\Query` (fluent builder) + `Objects\Schema` (DDL); MySQL connector only; Schema heavily MySQL-specific (InnoDB, utf8mb4, DESCRIBE, SHOW TABLES, ENUM, MODIFY COLUMN); SQLite and PostgreSQL connectors are stubs |
| **Auth** | Partially implemented | `Auth.php` (bearer/basic/session), database-backed sessions, remember-me cookie; 2FA/TOTP and registration not yet implemented |
| **Helpers** | Implemented | Auto-loads from core, vendor, and plugin directories via `$HELPER-><name>` magic getter |
| **Config** | Partially implemented | `Config.php` for JSON `.cfg` files; only 4 config files exist (`css.cfg`, `extensions.cfg`, `js.cfg`, `requirement.cfg`) |
| **CSRF** | Implemented | Automatic validation on POST/PUT/PATCH/DELETE, timing-safe comparison, token rotation |
| **Output** | Implemented | CLI colorized + HTTP JSON output |
| **Logging** | Implemented | `Log.php` with 5 levels, file rotation |
| **i18n** | Partially implemented | `Locales.php` supports en-ca/fr-ca, timezone management; Locale files not populated |
| **Installer** | Partially implemented | `Installer.php` + `Database::install()` for schema/data bootstrapping |
| **SMTP** | Implemented | `SMTP.php` email sender, used in password reset flows |
| **Scaffold** | Partially implemented | `lib/skeleton/` boilerplate exists; `init.sh` initializer |
| **Modules** | Implemented | `lib/modules/core/` as self-contained submodule |

### What's Missing (Gaps)

| Area | Severity | Notes |
|------|----------|-------|
| **Testing** | P0 | No `tests/` directory, no test framework, no PHPUnit config. Zero test coverage. |
| **ConfigOverrideService** | P1 | No config override layer (no `config/local.php` pattern). Admin settings can't persist. |
| **Settings Registry** | P1 | No application-wide settings system. `/admin/settings` doesn't exist. |
| **Profile Modal** | P1 | No modal-based profile UI with section registry. |
| **Debug Audit Logger** | P1 | No audit trail or debug logging service. |
| **Global View Context** | P1 | No `ViewGlobals` class; view context is inconsistent across layouts. |
| **Dependency Resolver** | P2 | No service dependency resolution; all services are loaded flat. |
| **Migration Runner** | P2 | No database migration system (only install-time schema creation). |
| **Documentation Plugin** | P2 | No docs generation plugin for the kernel. |
| **VersionProvider** | P2 | Static `VERSION` file only; no version resolution API. |
| **Developer Mode** | P2 | No `/admin/developer` page or scaffold generator. |
| **Encryption Service** | P2 | `src/Encryption.php` is a 0-byte stub. |
| **SMS / IMAP / SLS Services** | P3 | `SMS.php`, `IMAP.php`, `SLS.php` are 0-byte stubs. |
| **PostgreSQL / SQLite Connectors** | P2 | Database connectors exist as stubs. MySQL-only connector blocks local development. |
| **Menu Registry** | P2 | Menu is handled by `Builder->menu()` but no explicit `MenuRegistry` class. |
| **Datatables Standardization** | P2 | Assets exist (`lib/plugins/datatables/`) but no standardized usage pattern. |
| **Organization System** | P2 | `src/Objects/Organization.php` exists but no full plugin with tables/repositories. |

---

## Phase 1: Stabilization (Now — Core Safety)

Foundational work that prevents bugs and enables safe development.

### 1.1 Testing Infrastructure (P0)

**Why**: Zero test coverage means every change risks silent regressions. The codebase has 57 plugins, 30+ core classes, and a complex global injection system — testing is essential.

**Tasks:**
- [ ] Add PHPUnit (or lightweight alternative) to `composer.json` dev dependencies
- [ ] Create `tests/` directory with bootstrap file
- [ ] Test `Bootstrap.php` service loading (ROUTER/API/CLI scopes)
- [ ] Test `Config.php` CRUD operations (add, get, set, delete, list)
- [ ] Test `CSRF.php` token generation and validation
- [ ] Test `Router.php` route registration and dispatch
- [ ] Test `Database.php` connection handling
- [ ] Test `Query.php` fluent builder (select, insert, update, delete, joins, filters)
- [ ] Test `Style.php` LESS compilation
- [ ] Test `Auth.php` authentication flow (bearer, basic, session)
- [ ] Test plugin auto-discovery (helpers, routes, menus)
- [ ] Add PHPUnit CI workflow to `.github/workflows/`

### 1.2 Configuration Override Layer (P1)

**Why**: Without a config override layer, there's no way to persist application-specific settings. All config lives in checked-in `.cfg` files, making deployment impossible.

**Tasks:**
- [ ] Create `ConfigOverrideService` that loads from `config/local.php` (if exists) and merges with `config/*.cfg`
- [ ] Support dot-notation key access: `get('app.name')`, `get('database.host')`
- [ ] Support write-through: `set('app.name', 'MyApp')` writes to `config/local.php`
- [ ] Atomic file writes (write to temp + rename) to prevent corruption
- [ ] Add `config/local.php.example` (gitignored)
- [ ] Test config merge priority (local > base)

### 1.3 Application Settings System (P1)

**Why**: No `/admin/settings` page means administrators can't configure the application at runtime. This blocks deployment of any real application.

**Tasks:**
- [ ] Create `SettingsRegistry` class (plugin-provided settings sections)
- [ ] Create `SettingsSection` value object (key prefix, label, icon, fields)
- [ ] Build `/admin/settings` page (GET renders registry, POST saves via ConfigOverrideService)
- [ ] Build UI: card-based sections, field types (text, boolean, select), save confirmation
- [ ] Provide `SettingsSection::register()` hook for plugins
- [ ] Test settings save/load round-trip
- [ ] Test plugin-registered sections appear correctly

### 1.4 Global View Context (P1)

**Why**: Views/partials access globals inconsistently across the 5 layouts. Missing context causes silent errors. A guaranteed context layer is essential.

**Tasks:**
- [ ] Create `ViewGlobals` class with `contextFromScope()` and `contextFromContainer()`
- [ ] Define guaranteed globals: `$auth`, `$currentUser`, `$menu`, `$breadcrumbs`, `$locale`, `$csrf`, `$config`, `$app`
- [ ] Update all 5 layouts to call `ViewGlobals::contextFromScope()` at entry point
- [ ] Guest-safe defaults for unauthenticated users
- [ ] Test each layout renders without undefined variable errors
- [ ] Document the contract in `/docs/developer/view-context.md`

---

## Phase 2: Core Services (Unblocking Feature Work)

Services that unlock plugin and application development.

### 2.1 Auth Feature Completion (P1)

**Why**: Auth is incomplete — 2FA/TOTP and user registration are stubs. Any production application needs full auth.

**Tasks:**
- [ ] Implement 2FA/TOTP (`Objects\Pin` refactor to TOTP using `phpseclib3/phpseclib`)
- [ ] Implement 2FA recovery codes (UUID format, one per user, single-use)
- [ ] Implement user registration (config-gated, disabled by default)
- [ ] Implement email verification flow (single-use token, 24h expiry)
- [ ] Implement forgot password flow (single-use token, 60min expiry)
- [ ] Implement remember-me (selector/validator token pair with rotation)
- [ ] Test all auth flows with browser tests or PHPUnit
- [ ] Document auth flow in `/docs/developer/authentication.md`

### 2.2 Profile Modal System (P1)

**Why**: Users need a way to manage their profile, security settings, and 2FA. No modal UI exists.

**Tasks:**
- [ ] Create `ProfileModal` class with section registry
- [ ] Create `ProfileSection` value object (tab, content callback, permissions)
- [ ] Build modal UI in topbar (Bootstrap modal, tabbed interface)
- [ ] Register core sections: Overview, Security (2FA), API Tokens
- [ ] Support plugin-provided sections via hook
- [ ] Test tab rendering, permission gating, AJAX content loading
- [ ] Document in `/docs/developer/profile-modal.md`

### 2.3 Debug & Audit Logging (P1)

**Why**: No audit trail means no way to track admin actions, security events, or debug issues in production.

**Tasks:**
- [ ] Create `DebugAuditLogger` class (APP_DEBUG-gated)
- [ ] Log to `admin_audit_log` table (type: debug/audit, sanitized payloads, IP, user)
- [ ] Create `/admin/audit` page with type filtering (`?type=all|debug|audit`)
- [ ] Create `AuditLogger` class for production audit trail (non-debug)
- [ ] Log auth events (login, logout, 2FA, permission changes)
- [ ] Add debug badges to /admin pages (request ID, global variables, CSRF status)
- [ ] Test audit log write/filter/render

### 2.4 Version Provider (P2)

**Why**: No way to check kernel vs application version, or compare extension compatibility.

**Tasks:**
- [ ] Create `VersionProvider` class with kernel and application version resolution
- [ ] Support semver comparison (`>=`, `<=`, `^`, `~`, exact)
- [ ] Add admin overview card showing kernel version, app version, update status
- [ ] Test version resolution and comparison logic

### 2.5 Dependency Resolver (P2)

**Why**: Plugin install/enable/disable needs to resolve dependencies and block incompatible operations.

**Tasks:**
- [ ] Create `DependencyResolver` class
- [ ] Support dependency format: `plugin-name >=1.0.0`, `plugin-name <2.0.0`
- [ ] Check install/enable/disable/uninstall for blocked operations
- [ ] Add to extension catalog UI (block reason display)
- [ ] Test dependency resolution with conflicting versions

### 2.6 Migration Runner (P2)

**Why**: Schema changes between versions require migration support. Currently only `Database::install()` handles schema creation.

**Tasks:**
- [ ] Create `MigrationRunner` class
- [ ] Support versioned migrations (`migrations/001_create_users.php`, etc.)
- [ ] Track applied migrations in `migrations` table
- [ ] Integrate with plugin lifecycle (install → run migrations, uninstall → reverse migrations)
- [ ] Test migration apply/reverse
- [ ] Document migration format in `/docs/developer/migrations.md`

### 2.7 Database Connector Expansion (MySQL + SQLite) (P1)

**Why**: MySQL-only blocks local development (PHP 8.1+ on macOS has no MySQL socket by default; SQLite works out-of-the-box). The Connector pattern is already in place — SQLite just needs implementation. MySQL's Schema/Query also has heavy SQL-dialect assumptions that need to be abstracted.

**Scope**: Implement SQLite connector, abstract MySQL-specific SQL in Schema/Query, add adapter layer for dialect differences. PostgreSQL is out of scope for this phase.

**Files affected**:
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

---

## Phase 3: Feature Completeness

Features that make the kernel production-ready.

### 3.1 Developer Tools (P2)

**Why**: Developers need tooling for scaffolding, debugging, and testing.

**Tasks:**
- [ ] Create `/admin/developer` page
- [ ] Scaffold generator (plugins, endpoints, models, controllers, layouts)
- [ ] Templates stored in `resources/scaffolds/`
- [ ] Developer mode toggle (APP_DEBUG equivalent for admin)
- [ ] Test tool availability and scaffold output

### 3.2 Documentation Plugin (P2)

**Why**: No way to render documentation from within the application.

**Tasks:**
- [ ] Create `documentation` plugin with lightweight markdown renderer
- [ ] Panel layout with sidebar TOC + prev/next navigation
- [ ] Support headers, bold, italic, code, lists, links, images
- [ ] Edit on GitHub link for admin users
- [ ] Plugin self-registration with routes
- [ ] Test rendering of common markdown patterns

### 3.3 DataTables Standardization (P2)

**Why**: DataTables assets exist but no standardized usage pattern across admin pages.

**Tasks:**
- [ ] Create `DataTable` helper class for consistent initialization
- [ ] Define standard configuration options
- [ ] Update admin pages to use standardized pattern
- [ ] Document in `/docs/developer/datatables.md`

### 3.4 Menu Registry (P2)

**Why**: Menu system works via `Builder->menu()` but no explicit registry makes it hard for plugins to register menu items.

**Tasks:**
- [ ] Create `MenuRegistry` class with plugin hooks
- [ ] Support menu item registration with permissions, icons, order
- [ ] Replace `Builder->menu()` with registry-backed menu building
- [ ] Document menu plugin API

### 3.5 Organization System (P2)

**Why**: Multi-tenant data scoping requires organization support.

**Tasks:**
- [ ] Complete `lib/plugins/organizations/` plugin with tables, repositories
- [ ] `organizations` + `organization_users` pivot tables
- [ ] `OrganizationRepository` and `OrganizationMemberRepository`
- [ ] Profile Modal integration (switch/create/list organizations)
- [ ] Data scoping middleware (auto-filter queries by organization)
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

### 4.3 Config Migration Completion (P2)

**Tasks:**
- [ ] Migrate all DB-backed config to `ConfigOverrideService`
- [ ] Plugin config (SMTP, Telico) non-sensitive keys to `config/local.php`
- [ ] Sensitive credentials written directly to DB by plugins
- [ ] Migrate remaining config sections

### 4.4 Theme/Runtime Management (P3)

**Tasks:**
- [ ] Theme switch UI in admin settings
- [ ] Preview before applying
- [ ] Layout runtime management (switch without manual file operations)

---

## Phase 5: V1.0 Scope

Features required for V1.0 release (target: 2026-08-15).

### Required for V1.0

- [ ] Complete testing infrastructure (Phase 1.1)
- [ ] Config override layer (Phase 1.2)
- [ ] Settings registry (Phase 1.3)
- [ ] Global view context (Phase 1.4)
- [ ] Complete auth features (Phase 2.1)
- [ ] Profile modal (Phase 2.2)
- [ ] Debug/audit logging (Phase 2.3)
- [ ] Version provider (Phase 2.4)
- [ ] Dependency resolver (Phase 2.5)
- [ ] Migration runner (Phase 2.6)
- [ ] Developer tools (Phase 3.1)
- [ ] Documentation plugin (Phase 3.2)
- [ ] Organization system (Phase 3.5)
- [ ] Data scoping middleware
- [ ] Database connector expansion (MySQL + SQLite) (Phase 2.7)

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
| SMS service (`SMS.php`) | Empty stub — deferred pending provider selection |
| IMAP service (`IMAP.php`) | Empty stub — deferred pending use case |
| SLS service (`SLS.php`) | Empty stub — deferred pending licensing design |
| SQLite connector | Phase 2.7 — In progress |
| PostgreSQL connector | Empty stub — MySQL + SQLite sufficient for V1.0 |

---

## How to Use This Roadmap

1. **Start with Phase 1** — these are prerequisites for safe development
2. **Pick the highest-priority unchecked item** in the earliest unblocked phase
3. **Check dependencies** — some tasks unlock others (e.g., config override → settings registry)
4. **Update this file** when a task is completed or when scope changes
5. **Move tasks between phases** as new information becomes available

## Priority Legend

| Priority | Meaning |
|----------|--------|
| **P0** | Blocker — nothing else can proceed safely |
| **P1** | Critical — needed for any production deployment |
| **P2** | Important — needed for feature-complete kernel |
| **P3** | Nice-to-have — nice before V1.0, fine after |

---

## Current State

| Area | Status | Next Step |
|------|--------|-----------|
| Plugin system | Implemented | Stabilize with tests |
| Bootstrap / Services | Implemented | Test scope-based loading |
| Routing | Implemented | Test route registration |
| Layout System | Implemented | Add ViewGlobals context layer |
| Theme / LESS | Implemented | Add theme runtime management |
| Auth | Partial | Complete 2FA, registration, email verification |
| Config | Partial | Add ConfigOverrideService |
| Settings | Not started | Build SettingsRegistry + /admin/settings |
| Testing | Not started | Add PHPUnit + first 10 tests |
| Database Connectors | Partial | MySQL only; SQLite in Phase 2.7 |
| Profile Modal | Not started | Build with section registry |
| Debug/Audit | Not started | Build DebugAuditLogger + audit page |
| Version Provider | Not started | Build with semver comparison |
| Dependency Resolver | Not started | Build for plugin lifecycle |
| Migration Runner | Not started | Build versioned migration system |
| Developer Tools | Not started | Build scaffold generator |
| Documentation | Not started | Build lightweight markdown plugin |
| Organizations | Partial | Complete tables, repos, middleware |
| V1.0 Target | **2026-08-15** | Scope defined above |
