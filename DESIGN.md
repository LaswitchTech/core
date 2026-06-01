# Core-Web — Design Architecture & Decisions

> **Status**: Early architecture / skeleton phase. APIs and internals may change between commits.
> **Version**: v0.0.92

---

## 1. Project Overview

**Core-Web** is a reusable PHP application kernel (framework) by LaswitchTech. It provides the foundational infrastructure for building multiple web applications through a modular system of plugins, themes, layouts, and modules.

It is **not** a framework itself — it's a kernel. It provides the services, routing, database layer, and lifecycle. Domain logic lives in `lib/plugins/`.

---

## 2. Directory Structure

```
core/
├── src/                    # Kernel source code (core framework classes)
│   ├── Abstracts/          # Base classes plugins extend
│   ├── Backends/           # Pluggable authentication backends
│   ├── Connectors/         # Pluggable database connectors
│   ├── Objects/            # Domain objects & fluent builders
│   ├── icons/              # App icons & branding assets
│   ├── Bootstrap.php       # Service container & global loader
│   ├── Config.php          # JSON config file management
│   ├── Database.php        # DB abstraction + installer
│   ├── Auth.php            # Authentication orchestrator
│   ├── Router.php          # HTTP route registry & dispatcher
│   ├── API.php             # REST API dispatcher
│   ├── CLI.php             # CLI entry-point runner
│   ├── Output.php          # Console + HTTP output formatter
│   ├── Request.php         # HTTP request abstraction
│   ├── Style.php           # LESS/CSS compiler
│   ├── Locales.php         # i18n/localization manager
│   ├── Log.php             # Application logger (level-based rotation)
│   ├── Builder.php         # Config + form/UI builder
│   ├── CSRF.php            # CSRF token generation & validation
│   ├── Net.php             # TCP/UDP port reference table
│   ├── UUID.php            # UUID generator
│   ├── SMTP.php            # SMTP email sender
│   ├── Installer.php       # Application installer
│   ├── Module.php          # Fallback stub class
│   └── Helpers.php         # Plugin helper loader
├── config/                 # .cfg JSON config files (app-scoped)
├── lib/
│   ├── plugins/            # 57 domain-feature plugins
│   ├── themes/             # 3 UI themes (default, gentelella, glass)
│   ├── modules/            # Self-contained module packages
│   ├── skeleton/           # Project scaffold (boilerplate)
│   ├── init.sh             # Skeleton initializer script
│   └── publish.sh          # Module publish script
├── View/                   # Error page templates (404, 500, etc.)
├── Template/               # Email & view templates
├── Locale/                 # Translation files (en-ca, fr-ca)
├── assets/                 # Static assets (CSS/JS/icons)
├── Helper/                 # App-level helper classes
├── Command/                # CLI command definitions
├── Model/                  # App-level model definitions
├── Endpoint/               # App-level endpoint definitions
├── install.php             # Web-based installer
├── setup.sh                # Initial setup script
├── cli                     # CLI entry point
├── composer.json           # Package manifest (laswitchtech/core)
├── VERSION                 # Current version string
├── DESIGN.md               # This file
├── CLAUDE.md               # Developer workflow rules
├── ROADMAP.md              # Priorities & sequencing
└── /docs/                  # Implemented behavior reference docs
```

---

## 3. Service Architecture (Bootstrap)

### 3.1 Bootstrap Pattern

`Bootstrap.php` is the kernel's service container. It loads services as **global variables** (`$GLOBALS`) based on scope (`ROUTER`, `API`, or `CLI`).

```php
new Bootstrap('ROUTER'); // loads $DATABASE, $AUTH, $ROUTER, etc.
new Bootstrap('API');    // loads $DATABASE, $AUTH, $API, etc.
new Bootstrap('CLI');    // loads $DATABASE, $CLI, etc.
```

### 3.2 Service Map

| Global Variable | Class | Scope | Purpose |
|---|---|---|---|
| `UUID` | `UUID` | ROUTER, API, CLI | UUID generation |
| `ENCRYPTION` | `Encryption` | *(none — empty stub)* | Placeholder |
| `REQUEST` | `Request` | ROUTER, API, CLI | HTTP request parsing |
| `OUTPUT` | `Output` | ROUTER, API, CLI | Response/consoles output |
| `LOG` | `Log` | ROUTER, API, CLI | Level-based logging |
| `LOCALE` | `Locales` | ROUTER, API, CLI | i18n & timezone management |
| `NET` | `Net` | ROUTER, API, CLI | Port reference table |
| `DATABASE` | `Database` | ROUTER, API, CLI | Database abstraction |
| `SMS` | `SMS` | ROUTER, API, CLI | *(empty stub)* |
| `SMTP` | `SMTP` | ROUTER, API, CLI | Email sending |
| `AUTH` | `Auth` | ROUTER, API, CLI | Authentication |
| `CSRF` | `CSRF` | ROUTER, API | CSRF protection |
| `STYLE` | `Style` | ROUTER, API | LESS/CSS compilation |
| `BUILDER` | `Builder` | ROUTER, API | Config + builder utilities |
| `HELPER` | `Helpers` | ROUTER, API, CLI | Helper loader |
| `MODEL` | `Models` | ROUTER, API, CLI | Model loader |
| `IMAP` | `IMAP` | ROUTER, API, CLI | *(empty stub)* |
| `SLS` | `SLS` | ROUTER, API, CLI | *(empty stub)* |
| `INSTALLER` | `Installer` | ROUTER, API, CLI | Application installer |
| `UPDATER` | `Updater` | ROUTER, API, CLI | Application updater |
| `ROUTER` | `Router` | ROUTER | HTTP routing |
| `API` | `API` | API | REST API dispatch |
| `CLI` | `CLI` | CLI | CLI command dispatch |

### 3.3 Scope-Based Loading

Each service declares which scopes it belongs to. A service is only loaded if the bootstrap scope is in its list. The `MODULE` class is a fallback stub — if a class doesn't exist, a `Module` instance is injected that echoes a warning and exits on any method call.

### 3.4 Config Overriding

Services can be overridden per-scope via config:

```json
{
  "bootstrap": {
    "AUTH": {
      "class": "\\Custom\\AuthBackend",
      "scope": ["ROUTER"]
    }
  }
}
```

If the alternate class exists, it replaces the default. Otherwise the default stays.

---

## 4. Configuration System

### 4.1 Config Architecture

`Config.php` manages JSON configuration files. Each config file lives in `config/<name>.cfg`.

```php
$CONFIG = new Config('bootstrap'); // load config/bootstrap.cfg
$CONFIG->add('css');               // lazy-load config/css.cfg
$CONFIG->get('css', 'theme');      // get nested value
$CONFIG->set('css', 'theme', 'glass'); // set + persist
$CONFIG->list();                   // ['bootstrap', 'css', ...]
$CONFIG->delete('css');            // remove config file
```

### 4.2 Config Design Decisions

- **JSON-only** — all `.cfg` files are JSON
- **Lazy-loaded** — files are read from disk on first `add()` or `get()`
- **Auto-creates** missing config files (empty JSON object)
- **Path resolution** — uses `getcwd()`, `$_SERVER['DOCUMENT_ROOT']`, or `ROOT_PATH` constant
- **Extension** — always `.cfg`

### 4.3 Config Files in Use

| File | Purpose |
|---|---|
| `bootstrap.cfg` | Service container config |
| `application.cfg` | App metadata (name, theme, installed flag) |
| `database.cfg` | DB connector + credentials |
| `auth.cfg` | Authentication settings |
| `css.cfg` | CSS/LESS settings |
| `js.cfg` | JavaScript settings |
| `routes.cfg` | Route registry |
| `locale.cfg` | Language + timezone |
| `smtp.cfg` | Email server settings |
| `csrf.cfg` | CSRF token settings |
| `installer.cfg` | Installer defaults |
| `style.cfg` | Theme fallback |
| `requirement.cfg` | System requirements |
| `extensions.cfg` | Plugin extension config |

---

## 5. Database Architecture

### 5.1 Connector Pattern

`Database.php` uses a **pluggable connector pattern**:

```
Database (kernel)
  └── Connectors\MySQL ()        # Fully implemented
      ├── Connectors\PostgreSQL  # Stub
      └── Connectors\SQLite      # Stub
```

The connector is selected via `config/database.cfg` → `connector` key. Currently only `mysql` is implemented (default falls through to a switch-case with MySQL).

### 5.2 Query Builder

`Objects\Query` is a fluent query builder:

```php
$Database->query()
    ->table('users')
    ->select('*')
    ->join('backend', 'backends', 'id')
    ->where('id', 42, '=')
    ->limit(1)
    ->result();
```

Supported operators: `=`, `!=`, `<>`, `>`, `<`, `>=`, `<=`, `LIKE`, `NOT LIKE`, `IS NULL`, `IS NOT NULL`, `CONTAINS`
Supported conjunctions: `AND`, `OR`
Supported join types: `LEFT`, `RIGHT`, `INNER`, `FULL`, `SELF`

### 5.3 Schema Builder

`Objects\Schema` handles DDL operations:

```php
$Database->schema()->define('users')->create();
$Database->schema()->define('users')->drop();
$Database->schema()->define('users')->exists();
```

Schema definitions are stored in `Definition/<table>.map` files. Default engine: `InnoDB`, charset: `utf8mb4`.

### 5.4 Install Process

`Database::install()` orchestrates full application installation:
1. Validates config (connector, host, database, username, password)
2. Drops all existing tables
3. Copies `Definition/*.map` files to the Definition directory
4. Creates each schema from map files
5. Loads required data from `Data/<table>.required`
6. Optionally loads sample data from `Data/<table>.sample`
7. Sets auto-increment starting at 10000

### 5.5 Key Database Tables (inferred from code)

| Table | Purpose |
|---|---|
| `users` | User accounts (id, username, password, backend_id, session_id, organization_id, pin_id, token_id, vcard_id) |
| `backends` | Authentication backend config |
| `sessions` | Database-backed sessions (uuid, user, ip, agent, host, activity) |
| `organizations` | Organization/tenant data |
| `groups` | User groups |
| `roles` | Role definitions |
| `roles_groups` | Role-group pivot |
| `pins` | 2FA / security pins |
| `tokens` | Auth tokens |
| `vcards` | Contact/vCard data |
| `definitions` | Schema definition metadata |
| `messages` | Email/message storage |
| `logs` | Application logs |
| `tasks`, `followups`, `notes`, `events` | CRM objects |
| `contacts`, `leads`, `services`, `products` | CRM catalog |
| `industries`, `categories`, `components` | Reference data |

---

## 6. Authentication Architecture

### 6.1 Auth Orchestrator

`Auth.php` tries authentication methods in order:

1. **Bearer token** — check `Authorization: Bearer <token>` header
2. **Basic auth** — check `Authorization: Basic <credentials>` header
3. **Session** — check PHP session + database session lookup

The method used is tracked via `$this->method`.

### 6.2 Session Management

`Objects\Session` handles database-backed sessions:
- Creates/updates session records in the `sessions` table
- Sets UUID-based auth tokens in session and cookies (30-day remember me)
- Clears sessions on logout
- Ties sessions to IP, user agent, and host for security

### 6.3 Backend System (Password Validation)

`Abstracts\Backend` provides pluggable password validation:

```
Backend (abstract)
  └── Backends\Local   # Currently only implementation
```

Backends are loaded from the `backends` table (one per user). They provide:
- `set($password)` — set a password hash
- `validate($password)` — verify a password
- `reset()` — generate and send a new password via email
- `notify($user, $password)` — send reset email via SMTP

### 6.4 User Object

`Objects\User` is a rich domain object that loads a user and all their relationships in a single query:
- Joins: `backends`, `sessions`, `vcards`, `organizations`, `pins`, `tokens`
- Provides: `organization()`, `groups()`, `roles()`, `backend()`, `token`, `vcard`, `session`

---

## 7. Routing Architecture

### 7.1 HTTP Route System

`Router.php` registers routes by HTTP status code (treating codes as route groups):

```php
const HttpCodes = [330,400,401,403,404,405,422,423,427,428,429,430,432,500,501,503];
```

Standard codes (404, 500, etc.) → error pages.
Custom codes (330, 427, 430, 432) → authenticated route pages.

Each route maps to a directory + view file + optional endpoint.

### 7.2 Route Object

`Objects\Route` encapsulates a single route:
- `directory` — plugin directory
- `view` — view file
- `template` — layout template
- `public` — whether auth is required (default: true)
- `level` — minimum auth level
- `label`, `icon`, `color` — navigation metadata
- `hooks` — widget hooks

### 7.3 Plugin Route Loading

Each plugin can define `routes.cfg` in its directory. The router scans all plugin directories and registers their routes automatically.

### 7.4 Error Pages

The `View/` directory contains PHP templates for every supported HTTP status code. They are served directly by the Router.

---

## 8. API Architecture

### 8.1 REST API Dispatcher

`API.php` provides a namespace-based REST API:

```
GET /users/listAction  →  UsersEndpoint::listAction()
POST /contacts/createAction  →  ContactsEndpoint::createAction()
```

Namespace parsing: `/<endpoint>/<method>Action` → `<Endpoint>Endpoint::<method>Action`

The API dispatcher looks for endpoint classes in:
1. `vendor/laswitchtech/core/Endpoint/<Name>Endpoint.php` (core endpoints)
2. Plugin directories (plugin endpoints)

### 8.2 Endpoint vs Controller

| | Endpoint | Controller |
|---|---|---|
| **Namespace** | `LaswitchTech\Core\Abstracts` | `LaswitchTech\Core\Abstracts` |
| **Extra properties** | `$Output`, `$Request`, `$Config`, `$Locale` | `$Config` only (no `$Locale`) |
| **Usage** | API + web endpoints | Traditional MVC controllers |
| **Constructor** | Receives all globals | Receives subset of globals |

Both share: `$Auth`, `$Model`, `$Helper`, `$Level`, `$Public`, and `__call()` magic method.

---

## 9. Plugin Architecture

### 9.1 Plugin Directory Structure

Each plugin lives in `lib/plugins/<name>/` and follows this convention:

```
lib/plugins/<name>/
├── info.cfg          # Plugin metadata (name, version, author)
├── VERSION           # Plugin version string
├── Endpoint.php      # Main endpoint class
├── Model.php         # Main model class
├── Helper.php        # Helper class
├── Command.php       # CLI command (optional)
├── routes.cfg        # Route definitions (optional)
├── styles.cfg        # Stylesheet manifest (optional)
├── styles.less       # LESS source (optional)
├── library.js        # Frontend library (optional)
├── script.js         # Frontend script (optional)
├── View/             # Plugin view templates
├── Install/          # Database install files (Definition/, Data/)
└── picture.png       # Plugin icon
```

### 9.2 Plugin Conventions

- **Endpoints** extend `Abstracts\Endpoint`
- **Models** extend `Abstracts\Model`
- **Helpers** extend `Abstracts\Helper`
- **CLI commands** extend `Abstracts\Command`
- **Backends** extend `Abstracts\Backend`
- **Connectors** extend `Abstracts\Connector`

### 9.3 Helper Auto-Loading

`Helpers` class auto-loads helpers from three locations (in priority order):
1. `Helper/<Name>Helper.php` (core helpers)
2. `vendor/laswitchtech/core/Helper/<Name>Helper.php` (package helpers)
3. `lib/plugins/<name>/Helper.php` (plugin helpers)

Helpers are accessible via magic getter: `$HELPER->Core`, `$HELPER->Auth`, etc.

---

## 10. Theme Architecture

### 10.1 Theme System

`Style.php` compiles LESS/CSS from three sources in order:

1. `dist/css/` — framework base styles
2. `lib/plugins/<name>/` — plugin styles (from each plugin's `styles.cfg`)
3. `lib/themes/<theme>/` — active theme styles (from `application.cfg` → `theme`)

### 10.2 Available Themes

| Theme | Description |
|---|---|
| `default` | Default theme |
| `gentelella` | Gentelella admin theme |
| `glass` | Glass morphism theme |

### 10.3 Styles.cfg Format

```json
{
  "stylesheets": {
    "styles.less": "all"
  }
}
```

---

## 11. Module System

### 11.1 Self-Contained Modules

`lib/modules/` contains self-contained module packages. Currently:

- `lib/modules/core/` — a full module with its own git repo, containing its own `info.cfg`, `listing.cfg`, versioning, and documentation.

Modules are separate from plugins — they appear to be larger, distributable packages (possibly for the LaswitchTech marketplace or distribution system).

### 11.2 Skeleton (Project Scaffold)

`lib/skeleton/` provides a boilerplate template for new applications. The `init.sh` script clones/copies the skeleton and configures it as a new project.

The skeleton includes:
- Standard project files (`info.cfg`, `VERSION`, `LICENSE`, etc.)
- `AUTHORS`, `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `SECURITY.md`

---

## 12. Domain Objects

### 12.1 Object Summary

| Object | Purpose |
|---|---|
| `User` | User account + relationships |
| `Role` | Role definitions & permissions |
| `Group` | User groups |
| `Organization` | Organization/tenant |
| `Message` | SMTP email message builder |
| `Route` | Route metadata & rendering |
| `Query` | Fluent SQL query builder |
| `Schema` | DDL/schema management |
| `Session` | Database session management |
| `Token` | Auth token wrapper |
| `vCard` | vCard/contact wrapper |
| `Locale` | Locale translations |
| `Definition` | Schema definition metadata |
| `PDF` | PDF generation (mpdf + fpdi) |
| `Pin` | Security pin (2FA) |

### 12.2 PDF Generation

`Objects\PDF` uses `mpdf/mpdf` and `setasign/fpdi` for PDF generation. Supports:
- Formats: Letter, Legal, A4, A3, A5
- Orientations: Portrait, Landscape
- DPI: 72, 96, 150, 300
- Fonts: Helvetica, Courier, Times, Symbol, ZapfDingbats
- Encryption: 40, 128, 256-bit
- Permissions: print, modify, copy, fill-forms, assemble, etc.
- Watermarking and password protection
- PDF import (fpdi) for form filling

---

## 13. i18n / Localization

### 13.1 Locale System

`Locales.php` manages application localization:

- **Default locale**: `en-ca`
- **Supported locales**: `en-ca`, `fr-ca`
- **Default timezone**: `UTC`
- **Charset**: `UTF-8`

Locales are stored in `Locale/<locale>/` directories and loaded dynamically.

### 13.2 Locale Resolution Priority

1. Request GET parameter `?locale=`
2. Session variable (keyed by UUID)
3. Cookie `locale`
4. Browser `Accept-Language` header (first 5 chars)
5. Default (`en-ca`)

---

## 14. Logging System

### 14.1 Log Levels

| Level | Constant | Numeric |
|---|---|---|
| DEBUG | `DEBUG_LEVEL` | 5 |
| INFO | `INFO_LEVEL` | 4 |
| SUCCESS | `SUCCESS_LEVEL` | 3 |
| WARNING | `WARNING_LEVEL` | 2 |
| ERROR | `ERROR_LEVEL` | 1 |

### 14.2 Log Configuration

- Logs stored in `log/<name>.log`
- Supports file rotation
- Level-based filtering
- CLI colorized output
- HTTP JSON output for API requests

---

## 15. CSRF Protection

### 15.1 Design

`CSRF.php` runs automatically during bootstrap (for ROUTER and API scopes). It validates tokens on POST/PUT/PATCH/DELETE requests.

- **Token field**: `%UUID%` (resolved to `csrf-<session_id>`)
- **Header override**: `X-CSRF-Authorization` or `X-Csrf-Authorization` (for AJAX)
- **Rotation**: enabled by default (token cleared after each use)
- **Bypass**: authenticated via bearer/basic auth skips form token check
- **Token storage**: PHP session
- **Validation**: `hash_equals()` for timing-safe comparison

### 15.2 API

```php
$CSRF->token();    // Get current token
$CSRF->key();      // Get field name
$CSRF->field();    // Get <input> HTML
$CSRF->validate($token); // Validate a token
```

---

## 16. Entry Points

### 16.1 HTTP (Router)

```
https://example.com/
  → Bootstrap('ROUTER')
  → Router loads routes from all plugins
  → Dispatches to Endpoint or View
```

### 16.2 REST API

```
https://example.com/api/<endpoint>/<method>
  → Bootstrap('API')
  → API dispatcher routes to <Endpoint>Endpoint::<method>Action
```

### 16.3 CLI

```
php cli <command>
  → Bootstrap('CLI')
  → CLI dispatcher runs registered commands
```

### 16.4 Installer

```
https://example.com/install.php
  → Web-based application installer
  → Creates config files, runs Database::install()
```

---

## 17. Frontend Stack

- **CSS**: Bootstrap 5 + Bootstrap Icons + custom LESS compilation
- **JavaScript**: jQuery + plugin-specific `library.js` files
- **Rich text**: TinyMCE (via `tinymce` plugin)
- **Dropdowns**: Select2 (via `select2` plugin)
- **Steppers**: Custom stepper component
- **PDF viewer**: Built-in PDF viewer plugin
- **Excel**: Import/export plugin
- **vCards**: Contact vCard generation
- **Gravatar**: Avatar integration
- **Feed**: RSS/Atom feed support

---

## 18. Plugin Inventory (57 plugins)

### 18.1 Core / Infrastructure

| Plugin | Purpose |
|---|---|
| `auth` | Authentication (2FA, verification) |
| `installer` | Web installer UI |
| `updater` | Application updater |
| `maintenance` | Maintenance mode |
| `debug` | Debug toolbar |
| `logger` | Logging UI |
| `bootstrap` | Bootstrap helpers |
| `composer` | Composer integration |
| `favicon` | Favicon management |
| `feed` | RSS/Atom feeds |
| `extensions` | Extension management |
| `playground` | Development playground |
| `search` | Global search |

### 18.2 User / Access Management

| Plugin | Purpose |
|---|---|
| `users` | User CRUD + management |
| `groups` | Group management |
| `roles` | Role-based access control |
| `organizations` | Organization/tenant management |
| `profile` | User profile |
| `security` | Security settings |

### 18.3 CRM / Business

| Plugin | Purpose |
|---|---|
| `crm` | CRM dashboard |
| `contacts` | Contact management |
| `leads` | Lead management |
| `tasks` | Task management |
| `followups` | Follow-up tracking |
| `notes` | Notes |
| `events` | Event management |
| `services` | Service catalog |
| `products` | Product catalog |
| `components` | Component catalog |
| `industries` | Industry categories |
| `categories` | General categories |
| `library` | Document library |
| `documents` | Document management |
| `files` | File storage |
| `backups` | Backup management |
| `process` | Process/workflow management |
| `assessments` | Assessment/evaluation tool |
| `apps` | Application registry |

### 18.4 UI / Data

| Plugin | Purpose |
|---|---|
| `dashboard` | Dashboard layout |
| `datatables` | Server-side DataTables |
| `excel` | Excel import/export |
| `pdfviewer` | PDF viewer |
| `tinymce` | Rich text editor |
| `select2` | Advanced selects |
| `stepper` | Multi-step forms |
| `vcards` | vCard generation |
| `gravatar` | Gravatars |
| `importers` | Data import tools |
| `doctypes` | Document type registry |
| `surveys` | Survey tool |
| `telico` | Telico integration |

---

## 19. Design Decisions

### 19.1 Global Variables over Dependency Injection

**Decision**: Services are loaded as `$GLOBALS` (`$DATABASE`, `$AUTH`, etc.).

**Rationale**: Simpler for a framework without a DI container. Every class can access services without constructor parameter passing.

**Trade-off**: Makes testing harder and dependencies implicit. Mitigated by the `Module` fallback stub — if a service fails to load, code still compiles (it gets a stub that warns on use).

### 19.2 JSON Config Files over .env

**Decision**: Application config uses `config/*.cfg` JSON files, not `.env` files.

**Rationale**: Persistent, versionable, and easily editable. The `Config` class auto-creates missing files.

**Trade-off**: Config is on-disk rather than in environment. Secrets should still use `.env` or server-level config.

### 19.3 Pluggable Database Connectors

**Decision**: Database abstraction uses a connector pattern with abstract base class.

**Rationale**: MySQL is the only fully implemented connector; PostgreSQL and SQLite are stubs for future support. New connectors just extend `Abstracts\Connector`.

### 19.4 Status Codes as Route Groups

**Decision**: HTTP status codes (404, 500, etc.) double as route groups.

**Rationale**: Reuses existing error pages as route directories. Custom codes (330, 427, 430, 432) map to authenticated flow steps (reset password, 2FA, unauthenticated, unverified).

### 19.5 Thin Controllers, Focused Services

**Decision**: Controllers/Endpoints are thin — they delegate to Models, Helpers, and domain objects.

**Rationale**: Keeps the kernel generic and plugins focused. Reusable code lives in `src/Objects/` and `src/Abstracts/`.

### 19.6 No Heavy Framework Dependency

**Decision**: Only external dependency is `wikimedia/less.php` (for LESS compilation). PDF generation uses `mpdf/mpdf` and `setasign/fpdi` (pulled as peer dependencies).

**Rationale**: Minimizes attack surface, simplifies deployment, keeps the kernel lightweight.

### 19.7 Empty Stubs for Future Features

Several classes (`Encryption`, `SMS`, `IMAP`, `SLS`) are 0-byte stubs. They exist in the codebase as placeholders for future implementation.

**Decision**: Keep the stubs rather than removing them. They document planned features and allow forward-compatibility.

### 19.8 Database-Sessions over PHP-Sessions-Only

**Decision**: Sessions are persisted to the `sessions` table, not just stored in PHP's default file handler.

**Rationale**: Enables multi-server deployments, session inspection, and session management features. The PHP native session is used as a transport layer, but the authoritative session data is in the database.

### 19.9 UUID-Based Auth Tokens

**Decision**: Authentication tokens use UUIDs (via `UUID.php`) rather than random strings.

**Rationale**: UUIDs provide collision-resistant, globally-unique identifiers. The `UUID` class prefixes tokens (e.g., `csrf-`, `auth-`) for namespace separation.

---

## 20. Security Model

### 20.1 CSRF Protection

- Automatic validation on all non-GET requests
- Timing-safe token comparison (`hash_equals`)
- Token rotation after each use
- Header or form field submission

### 20.2 Authentication Methods

- Session-based (database-backed sessions)
- Bearer token (HTTP header)
- Basic auth (HTTP header)
- 2FA via `pins` table (custom status code 427)

### 20.3 Secret Handling

- No secrets in code
- `.env` files excluded from git
- Config files use `requirement.cfg` for system checks
- Installation process writes config to disk (no hardcoded defaults)

### 20.4 Session Security

- Sessions tied to IP, user agent, and host
- UUID-based auth tokens stored separately from PHP session ID
- Remember me cookies use UUID-based keys with 30-day expiry
- Session clear invalidates both DB and PHP session

---

## 21. Future Architecture Direction

### 21.1 Planned (stubbed but not implemented)

- `Encryption` — encryption/decryption utilities
- `SMS` — SMS messaging
- `IMAP` — email inbox reading
- `SLS` — Software Licensing Service

### 21.2 Planned (inferred from design)

- MySQL connector is the only implemented database connector
- OAuth support (mentioned in CLAUDE.md goals)
- Licensing system (mentioned in CLAUDE.md goals)
- PostgreSQL and SQLite database connectors

### 21.3 Architecture Principles for Future Work

- Domain logic in plugins, not kernel
- Plugins should extend abstract base classes
- Keep the kernel dependency-free (only `wikimedia/less.php`)
- New features should follow the existing stub pattern (document intent in DESIGN.md first)
- Database schema changes should include both migration and seed data paths
