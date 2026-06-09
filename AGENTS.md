# Core-Web — Agent Instructions

## Mission

Core-Web is a PHP kernel for building modular web applications through extensions/plugins.

The goal is to keep the kernel stable and develop new functionality gradually through extensions whenever possible.

## Current Workflow

Work in small, verifiable increments.

1. Read `NEXT.md`.
2. Read relevant docs under `docs/`.
3. Review similar existing code before editing.
4. Implement one small change.
5. Run focused tests.
6. Run broader tests when practical.
7. Run `git diff --check`.
8. Commit and push every successful change.

Do not stop after editing files without committing and pushing unless tests fail, unrelated user changes are present, or git push fails.

## Branch Discipline

The `dev` branch should stay clean and gradual.

Avoid large speculative rewrites.

If a task requires experimentation, document the risk before changing code.

## Development Strategy

Prefer this order:

1. Documentation
2. Extension implementation
3. Tests
4. Small kernel changes only when required

Do not modify the kernel unless:
- an extension cannot reasonably implement the feature,
- a framework bug exists,
- or the user explicitly asks for a kernel change.

## CI Failure Handling

When given GitHub Actions logs:

- `/home/runner/work/core/core` means the current repository.
- Find the first meaningful failure.
- Patch the smallest correct fix.
- Run the closest local equivalent.
- Run `git diff --check`.
- Commit and push.

Do not claim that files cannot be edited unless an actual command fails.

## Testing

Use these commands:

```sh
composer install
composer test
vendor/bin/phpunit
php -l src/SomeClass.php
git diff --check
```

For small changes, run the most focused test first.

## Documentation Sources

Read in this order:

1. NEXT.md
2. AGENTS.md
3. ROADMAP.md
4. DESIGN.md
5. docs/

If instructions conflict, follow the newest and most specific instruction.

## Extension Development Rules

New features should usually be built as extensions/plugins.

Before creating or changing an extension:

1. Review similar plugins in lib/plugins/.
2. Follow existing route, repository, controller, view, and definition patterns.
3. Avoid hardcoded paths.
4. Avoid custom authorization systems.
5. Document the extension.

## Git Rules

Before editing:

```sh
git status
```

Before committing:

```sh
git diff --check
git status
```

Commit only task-related files.

Push after every successful commit.
