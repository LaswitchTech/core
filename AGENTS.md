# Core-Web — Agent Instructions

## Mission

Core-Web is a PHP kernel for building modular web applications through extensions/plugins.

The goal is to keep the kernel stable and develop new functionality gradually through extensions whenever possible.

## Current Workflow

Work in small, verifiable increments.

1. Read `NEXT.md`.
2. Read `DESIGN.md`.
3. Read relevant documentation under `docs/`.
4. Review existing implementation.
5. Update documentation if behavior is undocumented.
6. Create or update a TODO list.
7. Implement one small change.
8. Run focused tests.
9. Run broader tests when practical.
10. Run `git diff --check`.
11. Commit the change.
12. Update `KANBAN.md` when appropriate.
13. Continue to the next task.

## Documentation First

Before implementing new functionality:

1. Verify the behavior is already documented.
2. If it is not documented:
   - read the code,
   - document the current behavior,
   - update the appropriate document,
   - then continue implementation.

Documentation is considered part of development.

Do not invent behavior that is not documented or observable in code.

## Branch Discipline

The `dev` branch should stay clean and gradual.

Avoid large speculative rewrites.

If a task requires experimentation, document the risk before changing code.

## Planning Files

The repository uses the following planning documents:

### ROADMAP.md

Long-term project vision.

Contains:
- major milestones,
- future features,
- architectural goals.

Do not use ROADMAP.md as the active task list.

### KANBAN.md

Current project board.

Contains:
- Backlog
- Ready
- In Progress
- Testing
- Done

Use this file to understand project status.

### NEXT.md

Current sprint.

Contains only the tasks that should be worked on next.

Always start here.

### DESIGN.md

Architecture and design decisions.

Read before proposing structural changes.

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

## Simplicity Rule

Prefer the smallest working solution.

Do not:
- redesign existing architecture,
- refactor unrelated code,
- introduce new patterns when existing patterns already work,
- rewrite files that are not required for the task.

Small, incremental changes are preferred.

## Kernel Protection

Core-Web is a framework.

Framework stability is more important than feature velocity.

Before modifying kernel files:

1. Determine whether the feature can be implemented as an extension.
2. Determine whether an existing extension point already exists.
3. Determine whether the change benefits multiple extensions.

If the answer is no, implement the feature in an extension instead.

## CI Failure Handling

When given GitHub Actions logs:

- `/home/runner/work/core/core` means the current repository.

When fixing CI failures:

1. Identify the first failing test.
2. Understand why it fails.
3. Patch only the affected area.
4. Re-run the smallest possible test.
5. Expand testing only after the failing test passes.
6. Run `git diff --check`.
7. Commit the fix.

Avoid speculative fixes.

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
3. KANBAN.md
4. ROADMAP.md
5. DESIGN.md
6. docs/

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

Push when:
- explicitly requested,
- completing a task,
- or preparing for CI validation.
