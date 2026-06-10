# Core-Web — Design Notes

## 1. Project Overview

Core-Web is a PHP framework kernel for building modular web applications. The repository is organized around a reusable core in `src/`, application-level entry points, configuration files, templates, helpers, endpoints, models, commands, and extension folders under `lib/`.

The framework appears designed to support multiple runtime scopes:

- Web page routing through `Router`.
- API dispatching through `API`.
- CLI command execution through `CLI`.
- Installation flow through `Installer`.

Core-Web favors convention-based discovery. Endpoints, models, helpers, commands, routes, plugins, modules, themes, and configuration files are loaded by naming and directory conventions rather than through a large static application registry.

## 2. Core Principles

Observed design principles:

1. **Kernel first** — common services live under `src/` and are loaded by `Bootstrap`.
2. **Scope-gated services** — services are loaded depending on the active runtime scope such as `ROUTER`, `API`, or `CLI`.
3. **Global service access** — bootstrapped services are injected into `$GLOBALS` using uppercase service names such as `$CONFIG`, `$REQUEST`, `$DATABASE`, `$AUTH`, and `$ROUTER`.
4. **Convention over explicit registration** — many components are discovered by path and naming convention.
5. **Configuration-driven behavior** — `.cfg` JSON files under `config/` define application, routes, bootstrap, auth, database, CSS, JavaScript, logging, locale, and other runtime behavior.
6. **Extensions over kernel changes** — plugins, modules, and themes live under `lib/` and are intended to extend behavior without modifying the kernel directly.
7. **Shared abstractions** — base and abstract classes provide common behavior for endpoints, models, helpers, commands, connectors, and backends.

## 3. Directory Structure

Observed top-level structure:

```text
core/
├── src/              # Framework kernel classes under LaswitchTech\Core
├── Endpoint/         # Route/API endpoint classes
├── Model/            # Application model classes
├── Helper/           # Application/helper classes
├── Command/          # CLI command classes
├── View/             # HTTP error views and route views
├── Template/View/    # Layout templates such as website, panel, internal, fullscreen, error
├── config/           # JSON configuration files using .cfg extension
├── webroot/          # Web-accessible entry points and rewrite target
├── assets/           # Static assets
├── lib/              # Extension hub: plugins, themes, modules, skeleton
├── install.php       # Installer entry point
├── cli               # CLI entry point
├── composer.json     # Composer package definition
└── .github/          # GitHub workflows and templates
```

Important extension folders under `lib/`:

```text
lib/
├── plugins/          # Plugin folders
├── themes/           # Theme folders
├── modules/          # Module bundles
└── skeleton/         # Scaffolding templates
```

## 4. Request Lifecycle

Observed web lifecycle:

```text
HTTP request
  → webroot/index.php or root index.php
  → Bootstrap('ROUTER')
  → service loading
  → Router::start()
  → route matching
  → authorization / maintenance checks
  → Route object
  → template + view rendering
```

Observed API lifecycle:

```text
HTTP API request
  → webroot/endpoint.php or install.php
  → Bootstrap('API')
  → service loading
  → API::start()
  → endpoint/action parsing
  → endpoint class resolution
  → authorization checks
  → action execution
  → JSON response
```

Observed CLI lifecycle:

```text
CLI command
  → cli
  → Bootstrap('CLI')
  → service loading
  → CLI::start()
  → command/action parsing
  → command class resolution
  → action execution
  → terminal output
```

## 5. Bootstrap and Service Globals

`src/Bootstrap.php` acts as the service loader and runtime bootstrapper.

Observed responsibilities:

- Start PHP session for web scopes when needed.
- Load bootstrap and application configuration.
- Load services from a static default service map.
- Apply runtime scope rules.
- Instantiate services by class name.
- Expose services through global variables.
- Start the active runtime dispatcher.

Services observed in the bootstrap map include:

- `UUID`
- `ENCRYPTION`
- `REQUEST`
- `OUTPUT`
- `LOG`
- `LOCALE`
- `NET`
- `DATABASE`
- `SMS`
- `SMTP`
- `AUTH`
- `CSRF`
- `STYLE`
- `BUILDER`
- `HELPER`
- `MODEL`
- `IMAP`
- `SLS`
- `INSTALLER`
- `UPDATER`

If a service cannot be loaded, the framework appears to fall back to a `Module` stub object.

Design constraint: code should use bootstrapped services rather than manually instantiating kernel services when possible.

The framework uses a global service pattern where all bootstrapped services are available in the global namespace using uppercase names. The bootstrap process determines which services to load based on the current execution scope (ROUTER, API, or CLI), and each scope loads its appropriate set of services.

## 5. Bootstrap and Service Globals

`src/Bootstrap.php` acts as the service loader and runtime bootstrapper.

Observed responsibilities:

- Start PHP session for web scopes when needed.
- Load bootstrap and application configuration.
- Load services from a static default service map.
- Apply runtime scope rules.
- Instantiate services by class name.
- Expose services through global variables.
- Start the active runtime dispatcher.

Services observed in the bootstrap map include:

- `UUID`
- `ENCRYPTION`
- `REQUEST`
- `OUTPUT`
- `LOG`
- `LOCALE`
- `NET`
- `DATABASE`
- `SMS`
- `SMTP`
- `AUTH`
- `CSRF`
- `STYLE`
- `BUILDER`
- `HELPER`
- `MODEL`
- `IMAP`
- `SLS`
- `INSTALLER`
- `UPDATER`

If a service cannot be loaded, the framework appears to fall back to a `Module` stub object.

Design constraint: code should use bootstrapped services rather than manually instantiating kernel services when possible.

## 6. Routing

`src/Router.php` handles web routing.

Observed responsibilities:

- Load route definitions.
- Register error routes and views.
- Register core routes.
- Register configured routes from `config/routes.cfg`.
- Register plugin routes from plugin route files.
- Apply route-level authorization.
- Apply maintenance mode checks.
- Render the matched route.

Routes are represented by `src/Objects/Route.php`.

Observed route metadata includes:

- `label`
- `icon`
- `color`
- `level`
- `public`
- `parent`
- `location`
- `template`
- `view`
- `action`

Routes can contribute to navigation through route locations such as sidebar or menu areas. Navigation rendering is handled through builder/helper logic rather than direct hardcoded menu HTML.

## 7. Endpoints, Models, Helpers

### Endpoints

Endpoints are used for API-style actions and convention-based dispatching.

Observed endpoint layers:

- `src/Abstracts/Endpoint.php`
- `src/Base/BaseEndpoint.php`
- Application or plugin endpoint classes such as `Endpoint/CoreEndpoint.php`

`BaseEndpoint` provides common CRUD-style actions such as:

- `countAction`
- `fetchAllAction`
- `fetchAction`
- `createAction`
- `updateAction`
- `deleteAction`
- `archiveAction`
- `recoverAction`
- `describeAction`

### Models

Models provide database access and common CRUD abstractions.

Observed model layers:

- `src/Abstracts/Model.php`
- `src/Base/BaseModel.php`
- Application or plugin model classes under `Model/` or plugin folders

`BaseModel` appears to provide table initialization, schema introspection, CRUD operations, data processing, type mapping, and sanitization.

### Helpers

Helpers are loaded by convention through `src/Helpers.php`.

Observed helper sources:

- Core helper folder.
- Application helper folder.
- Plugin helper files.

Helpers should encapsulate reusable rendering or utility behavior rather than duplicating logic in views or controllers.

## 8. Views and Templates

The framework separates layout templates from page views.

Observed template folder:

```text
Template/View/
```

Observed templates include:

- `website.php`
- `panel.php`
- `internal.php`
- `fullscreen.php`
- `error.php`

Observed error views are stored under:

```text
View/
```

The route object appears to coordinate template selection, view selection, and rendering.

Views should remain presentation-focused. Database access and business logic should be kept in models, endpoints, controllers, helpers, or services.

## 9. Plugin / Extension Architecture

Plugins live under:

```text
lib/plugins/{plugin}/
```

Observed plugin concepts:

- Plugin route files are loaded into the router.
- Plugin endpoint classes can be discovered by convention.
- Plugin helpers can be discovered by convention.
- Plugin assets can be included in the asset pipeline.
- Plugin command classes can be discovered by the CLI layer.

A typical plugin may include:

```text
lib/plugins/{plugin}/
├── routes.cfg
├── Endpoint.php
├── Helper.php
├── Command.php
├── Model.php
├── assets/
└── docs/
```

Only files required by the plugin should be created. Empty placeholder files should be avoided.

Extension development should prefer existing framework extension points before modifying the kernel.

## 10. Configuration

Configuration files live under:

```text
config/
```

Configuration files use the `.cfg` extension and appear to contain JSON.

Observed configuration files include:

- `bootstrap.cfg`
- `application.cfg`
- `auth.cfg`
- `database.cfg`
- `migration.cfg`
- `routes.cfg`
- `csrf.cfg`
- `css.cfg`
- `style.cfg`
- `js.cfg`
- `log.cfg`
- `smtp.cfg`
- `locale.cfg`
- `installer.cfg`
- `requirement.cfg`
- `breadcrumbs.cfg`

`src/Config.php` manages configuration loading and writing.

Observed behavior:

- Missing config files may be initialized as empty JSON objects.
- Values can be read by file/key convention.
- Values can be added, set, reloaded, deleted, or versioned.

Configuration should be preferred over hardcoding runtime behavior.

## 11. Database Layer

`src/Database.php` acts as the database wrapper and connector factory.

Observed concepts:

- Connector abstraction through `src/Abstracts/Connector.php`.
- MySQL connector under `src/Connectors/MySQL.php`.
- Query object creation through `$DATABASE->query()`.
- Schema object creation through `$DATABASE->schema()`.
- Installer-driven schema creation from definition files.

Observed installer/database behavior:

- Table definitions are loaded from definition files.
- Schema objects can create or update database structure.
- Required/sample data can be loaded during installation.

To verify: the exact current support level for SQLite, PostgreSQL, and other connectors in this branch.

## 12. Authentication and Authorization

`src/Auth.php` manages authentication and route authorization.

Observed authentication mechanisms include:

- Bearer token.
- Basic HTTP authentication.
- Session-based authentication.
- Cookie-based authentication.
- Request parameter authentication.

Authorization appears role-based, with permission levels and hierarchy checks.

`src/Objects/User.php` represents authenticated users and loads related data such as backends, sessions, vcards, organizations, pins, tokens, roles, and groups.

Observed account-related concepts:

- Local backend authentication.
- API tokens.
- PIN or reset-flow support.
- Role membership.
- Organization relationship.

To verify: the exact behavior of 2FA, email verification, password reset, remember-me, SMS, and IMAP support in this branch.

## 13. Assets, Styles, and Themes

`src/Builder.php` and `src/Style.php` participate in asset rendering and style compilation.

Observed asset behavior:

- Core CSS and JavaScript can be loaded globally.
- Plugin CSS and JavaScript can be loaded by convention.
- Theme CSS and JavaScript can be loaded by active theme.
- JavaScript globals can expose CSRF, auth, user, and application metadata.
- LESS compilation is handled through `wikimedia/less.php`.

Themes live under:

```text
lib/themes/{theme}/
```

Observed theme concepts:

- Theme metadata through `info.cfg`.
- Theme styles.
- Theme library/script files.
- Runtime compilation through the `Style` service.

## 14. CLI

The executable `cli` file bootstraps the framework in `CLI` scope.

`src/CLI.php` dispatches command/action pairs by convention.

Observed command sources:

- Core command classes.
- Application command classes.
- Plugin command classes.

CLI output is handled through `Output`, which supports terminal-oriented formatting and colors.

Commands should follow the existing command/action convention instead of adding separate standalone scripts when possible.

## 15. Testing and CI

Observed CI and repository automation live under:

```text
.github/
```

Observed files include release workflow and GitHub templates.

The repository uses Composer and PHP tooling. Expected local validation commands include:

```sh
composer install
composer test
vendor/bin/phpunit
php -l path/to/file.php
git diff --check
```

To verify: the exact current test suite coverage and whether CI runs PHPUnit, syntax checks, route smoke tests, or release-only checks in this branch.

## 16. Known Constraints

Observed constraints and design cautions:

1. Bootstrapped services are exposed globally; avoid bypassing this pattern without a clear reason.
2. Runtime behavior is heavily configuration-driven; avoid hardcoded routes, assets, permissions, or paths.
3. Plugins, themes, and modules should extend the system without modifying the kernel whenever possible.
4. Views should not contain database access or business logic.
5. Kernel changes should be small and justified by framework-level needs.
6. Missing or incomplete services may be represented by stubs or fallback modules.
7. Local generated files, ignored extension folders, vendor dependencies, and environment-specific config should not be committed unless intentionally tracked.

## 17. Request Lifecycle

### Web Request Flow

After inspection, the HTTP request lifecycle is as follows:

1. **HTTP Request** arrives at web server
2. **.htaccess rewrite rules** direct request to `webroot/index.php`
3. **`webroot/index.php`** bootstraps the framework using `Bootstrap("ROUTER")`
4. **Bootstrap process**: 
   - Starts PHP session if needed (for web scopes)
   - Loads configuration files
   - Instantiates services based on scope (`ROUTER`)
   - Exposes services globally via `$GLOBALS`
   - Starts the active runtime dispatcher (`Router::start()`)
5. **`Router::start()`**:
   - Initializes Core helper through `Helper->Core->init()`
   - Determines requested route from URL (via `$REQUEST` service)
   - If application is installed, renders the matched route
   - If not installed, redirects to `/install` route

6. **Route Processing**:
   - Routes loaded from:
     - Core error routes
     - Configured routes from `config/routes.cfg`
     - Plugin routes from `lib/plugins/{plugin}/routes.cfg`
   - Authorization checks applied based on route properties
   - Route rendering via Template/View system

### Bootstrap Services (ROUTER Scope)

The following services are loaded during ROUTER scope:

- UUID, REQUEST, OUTPUT, LOG, LOCALE, NET, DATABASE, SMS, SMTP
- AUTH, CSRF, STYLE, BUILDER, HELPER, MODEL, IMAP, SLS
- INSTALLER, UPDATER, ROUTER

Each service is initialized and available globally as uppercase variable names in the `$GLOBALS` array.

### Route Authorization

Routes are checked for authorization against user permissions using the `AUTH` service:
- Unauthenticated users get 430 "Unauthenticated"
- Unauthorized access gets 403 "Forbidden"
- Deleted/banned users get specific errors
- Role-based permission checking via `isAuthorized('Route>' . $this->Routes[$route]->namespace(), intval($this->Routes[$route]->level()))`

### Configuration Dependencies

The routing process depends on:
- `config/bootstrap.cfg` - Bootstrap configuration
- `config/application.cfg` - Application settings
- `config/routes.cfg` - Route definitions
- `config/auth.cfg` - Authentication parameters
- Plugin route files in `lib/plugins/{plugin}/routes.cfg`

## 18. To Verify

The following areas require a later verification pass:

- Current plugin manifest format and required plugin files.
- Current module packaging format under `lib/modules/`.
- Current theme metadata and asset-loading contract.
- Exact database connector support in this branch.
- Exact authentication feature completeness in this branch.
- Exact CI workflow behavior in `.github/workflows/`.
- Whether route smoke tests exist and how they should be run.
- Whether documentation plugin support exists in this branch.
- Whether extension installation, enable, disable, uninstall, and dependency handling are implemented in this branch.

## 18. API Endpoint Flow Verification Findings

Based on analysis of `src/API.php`:

The API endpoint flow works as follows:
1. Entry point is via `/webroot/endpoint.php`, which initializes Bootstrap with 'API' scope
2. The API class's `start()` method is called
3. It parses the namespace from request using `$REQUEST->getNamespace()`
4. It parses the namespace into endpoint and action (method) parts
5. It determines the endpoint class path by checking:
   - Core default location at vendor/laswitchtech/core/Endpoint/
   - Application specific location at Endpoint/
   - Plugin specific location at lib/plugins/pluginname/Endpoint.php
6. Loads the endpoint class
7. Verifies that endpoint class and action method exist
8. Performs authorization checks against:
   - Authentication state (user loaded, not deleted/banned, verified)
   - Authorization level based on endpoint path
   - Maintenance mode if configured
9. Executes the corresponding `Action()` method from the endpoint class
10. Returns JSON response via `$OUTPUT->print()`

BaseEndpoint provides common CRUD actions:
- countAction
- fetchAllAction
- fetchAction
- createAction
- updateAction
- deleteAction
- archiveAction
- recoverAction
- describeAction

This matches the documented API lifecycle in DESIGN.md section 7.1 which was previously incomplete.
- Whether `ROADMAP.md`, `KANBAN.md`, and `NEXT.md` are populated and authoritative.
