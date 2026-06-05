# Core-Web — Agent Quick Reference

## Setup

```sh
composer install       # required before anything else
composer test          # runs PHPUnit (tests/ Unit/*.php)
vendor/bin/phpunit     # or run directly with config from phpunit.xml.dist
```

CI uses `PHP 8.2`. This is the minimum compatible version for dev work.

## Structure at a glance

- **`src/`** — Kernel framework classes (Bootstrap, Router, Database, Auth, etc.). 74 files total.
- **`lib/plugins/`** — Domain feature plugins (auto-discovered via `info.cfg` manifests). ~57 plugins with lifecycle hooks.
- **`lib/themes/`** — UI themes (default, gentelella, glass). LESS compilation via `Style.php` + `wikimedia/less.php`.
- **`lib/modules/`** — Self-contained module packages.
- **`config/*.cfg`** — JSON config files committed as defaults; instance-specific `.cfg` files are gitignored.
- **`webroot/`** — Document root for advanced setups (gitignored per `.gitignore`).
- **`cli`** — CLI entry point.
- **`lib/skeleton/` + `lib/init.sh`** — Project scaffold boilerplate.

## Bootstrap / Service Container

```php
new \LaswitchTech\Core\Bootstrap('ROUTER'); // loads globals: $DATABASE, $AUTH, $ROUTER, etc.
new \LaswitchTech\Core\Bootstrap('API');    // routes + REST dispatching
new \LaswitchTech\Core\Bootstrap('CLI');    // CLI runner
```

Services are injected into `$GLOBALS`. **Never instantiate services manually** outside Bootstrap — use the global instances the same way `index.php` does.

## Routing & Entry Points

- `index.php` — shared hosting entry point (loads `.env` inline for compatibility).
- `webroot/index.php` — advanced deployment entry point (document root here).
- `install.php` — web-based installer.
- Routes are registered through `Router.php` and dispatched to controllers/plugins.

## Key Constraints

- **`.env` contains secrets** — never commit it. Use `.env.example` or documented environment variables instead.
- **`vendor/`, `lib/` (except `.sh` files and `skeleton/`), `/Definition/`, `.DS_Store` are gitignored.** Plugin scaffolding lives in `lib/skeleton/`.
- **`src/SMSP.php`, `src/IMAP.php`, `src/SLS.php`, etc. may be 0-byte stubs** — check before using; they are deferred features tracked in ROADMAP.md.
- **PHP lint all files before committing** — CI runs `php -l` on every PHP file outside vendor/.
- **PSR-12 is enforced** by CI via `git diff --check`. Follow it manually.

## Testing

```sh
vendor/bin/phpunit                    # full suite (tests/Unit/*Test.php)
vendor/bin/phpunit tests/Unit/RouterTest.php  # a single test file
php -l src/SomeClass.php              # syntax check
```

Tests use `tests/bootstrap.php` which loads the Composer autoloader and defines `ROOT_PATH`. Unit tests have trait `tests/Traits/MockGlobals.php` for mocking globals.

## Documentation Sources (read in this order)

1. **ROADMAP.md** — current priorities, gaps, V1.0 scope
2. **DESIGN.md** — architecture decisions, service map, plugin/theme/layout contracts
3. **CLAUDE.md** — workflow rules (commit discipline, refactoring guidelines)
4. **docs/** — implemented behavior reference material

## Repo-Specific Gotchas

- `lib/plugins/` plugins use an auto-discovery lifecycle with `info.cfg` manifests. Don't hardcode plugin paths.
- `Bootstrap.php` loads 25+ service globals scoped to ROUTER/API/CLI. Adding a new kernel service requires registering it in the relevant scope(s).
- LESS/CSS build happens at runtime via `Style.php` — no build step or asset pipeline needed.
- The CLI tool (`cli`) can create projects: `php cli core init`.
