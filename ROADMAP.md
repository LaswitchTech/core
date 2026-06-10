# Core-Web — Roadmap

## Objective

Core-Web is being rebuilt and stabilized gradually while preserving legacy support.

The goal is not to rewrite the framework all at once. Each improvement must be implemented in small, tested steps, validated before moving to the next task, and designed to avoid breaking existing applications, plugins, themes, modules, routes, or configuration files.

## Development Rules

Every roadmap item must follow this workflow:

1. Review the current implementation.
2. Document the existing behavior if it is not already documented.
3. Identify legacy behavior that must remain supported.
4. Plan the smallest safe improvement.
5. Implement one incremental change.
6. Add or update tests.
7. Run focused tests.
8. Run broader tests when practical.
9. Run `git diff --check`.
10. Commit the completed change.
11. Move to the next task only after validation.

## Compatibility Policy

Core-Web must maintain legacy compatibility wherever possible.

Breaking changes are allowed only when:

- the current behavior is clearly defective,
- the change is required for framework stability,
- a backward-compatible adapter is not practical,
- and the migration path is documented.

When changing framework internals, preserve existing public APIs, configuration formats, route definitions, plugin conventions, and template behavior unless the roadmap explicitly says otherwise.

## Phase 1 — Foundation and Documentation

Purpose: establish a reliable understanding of the current framework before implementing major changes.

### 1.1 Document Current Architecture

- [ ] Complete `DESIGN.md` with current architecture notes.
- [ ] Verify bootstrap flow.
- [ ] Verify request lifecycle.
- [ ] Verify routing flow.
- [ ] Verify API endpoint flow.
- [ ] Verify CLI flow.
- [ ] Verify plugin, module, and theme loading behavior.
- [ ] Verify configuration loading behavior.

### 1.2 Establish Project Planning Files

- [ ] Populate `KANBAN.md` with backlog, ready, in-progress, testing, and done sections.
- [ ] Populate `NEXT.md` with the current active sprint.
- [ ] Keep `ROADMAP.md` focused on long-term direction.
- [ ] Keep `DESIGN.md` focused on stable architecture.

### 1.3 Baseline Test Strategy

- [ ] Identify currently available test commands.
- [ ] Document test commands in `docs/developer/testing.md`.
- [ ] Confirm syntax linting process.
- [ ] Confirm PHPUnit coverage.
- [ ] Confirm CI workflow behavior.
- [ ] Add missing baseline tests before large refactors.

## Phase 2 — Database Review and Optimization

Purpose: make the database layer more reliable, faster, and easier to support across connectors.

### 2.1 Review Current Database Flow

- [ ] Document how `Database` initializes.
- [ ] Document connector loading.
- [ ] Document query object creation.
- [ ] Document schema object creation.
- [ ] Document install/update behavior.
- [ ] Identify slow or duplicated logic.
- [ ] Identify legacy APIs that must remain supported.

### 2.2 Optimize Database Flow

- [ ] Simplify initialization where safe.
- [ ] Reduce repeated connector checks where safe.
- [ ] Improve error handling without changing public behavior.
- [ ] Preserve existing query and schema APIs.
- [ ] Add regression tests for existing behavior.

### 2.3 Add SQLite Support

- [ ] Review connector abstraction requirements.
- [ ] Implement SQLite connector.
- [ ] Support SQLite configuration.
- [ ] Validate basic connection flow.
- [ ] Validate query builder compatibility.
- [ ] Validate schema creation compatibility.
- [ ] Document SQLite limitations.
- [ ] Add SQLite tests.

### 2.4 Validate Database Update Process

- [ ] Review current schema update behavior.
- [ ] Validate table creation.
- [ ] Validate table alteration.
- [ ] Validate column comparison.
- [ ] Validate index handling if supported.
- [ ] Validate safe re-run behavior.
- [ ] Add tests for update idempotency.

### 2.5 Validate Database Seeding Process

- [ ] Review seed file format.
- [ ] Validate required seed data.
- [ ] Validate optional/sample seed data.
- [ ] Validate install-time seed behavior.
- [ ] Validate repeated seed behavior.
- [ ] Add tests or documented manual validation steps.

## Phase 3 — Encryption Service

Purpose: provide a reliable built-in encryption service for sensitive framework and application data.

### 3.1 Review Encryption Requirements

- [ ] Identify where encryption is needed.
- [ ] Identify key source and configuration requirements.
- [ ] Identify legacy token or encoding behavior that must remain supported.
- [ ] Document intended encryption API.

### 3.2 Implement Encryption

- [ ] Implement encryption service.
- [ ] Implement decryption service.
- [ ] Implement secure token generation if needed.
- [ ] Add key validation.
- [ ] Add error handling.
- [ ] Add tests for encryption/decryption round-trip.
- [ ] Document usage in `docs/developer/encryption.md`.

## Phase 4 — Authentication Review and Optimization

Purpose: make authentication faster, clearer, and safer while preserving existing login and authorization behavior.

### 4.1 Review Current Auth Flow

- [ ] Document authentication sources.
- [ ] Document session authentication.
- [ ] Document cookie authentication.
- [ ] Document bearer token authentication.
- [ ] Document basic authentication.
- [ ] Document request parameter authentication if still supported.
- [ ] Document authorization and role-level checks.
- [ ] Identify expensive queries or repeated user loading.

### 4.2 Optimize Auth Flow

- [ ] Reduce unnecessary repeated database lookups.
- [ ] Cache safe per-request auth state where appropriate.
- [ ] Preserve existing public methods.
- [ ] Preserve legacy authentication methods unless explicitly deprecated.
- [ ] Add regression tests for login, authorization, and guest routes.

### 4.3 Auth Safety Review

- [ ] Review session handling.
- [ ] Review token handling.
- [ ] Review password/backend behavior.
- [ ] Review authorization edge cases.
- [ ] Document known limitations.

## Phase 5 — Router Review and MVC Standardization

Purpose: move the routing layer toward a clearer MVC architecture while keeping existing route definitions working.

### 5.1 Review Current Router Flow

- [ ] Document route loading.
- [ ] Document route matching.
- [ ] Document route rendering.
- [ ] Document route authorization.
- [ ] Document maintenance mode behavior.
- [ ] Document error route behavior.
- [ ] Identify responsibilities that should move out of `Router`.

### 5.2 Define MVC Standard

- [ ] Define controller responsibilities.
- [ ] Define route object responsibilities.
- [ ] Define view/template responsibilities.
- [ ] Define middleware responsibilities if introduced.
- [ ] Define response object behavior if introduced.
- [ ] Document backward compatibility requirements.


### 5.3 Incremental MVC Migration

- [ ] Introduce MVC components without breaking old routes.
- [ ] Add adapters for legacy route behavior.
- [ ] Move rendering responsibility gradually.
- [ ] Move authorization responsibility gradually if appropriate.
- [ ] Preserve existing `routes.cfg` format.
- [ ] Add route regression tests.
- [ ] Validate existing pages and API routes.

### 5.4 Review Builder JavaScript

Purpose: simplify and optimize client-side framework behavior.

#### `assets/js/builder.js`

- [ ] Document current responsibilities.
- [ ] Document initialization flow.
- [ ] Document event registration flow.
- [ ] Identify duplicated logic.
- [ ] Identify legacy compatibility requirements.
- [ ] Measure unnecessary DOM operations.
- [ ] Reduce repeated selectors where safe.
- [ ] Reduce repeated event bindings where safe.
- [ ] Improve modularity.
- [ ] Improve maintainability.
- [ ] Preserve public JavaScript APIs.
- [ ] Add regression testing where practical.
- [ ] Document updated behavior.

## Phase 6 — SMTP Review and Email Queue

Purpose: improve email reliability by reviewing SMTP behavior and adding queue support.

### 6.1 Review Current SMTP Service

- [ ] Document current SMTP configuration.
- [ ] Document email send flow.
- [ ] Document error handling.
- [ ] Document template or message formatting behavior if present.
- [ ] Identify legacy APIs that must remain supported.

### 6.2 Implement Email Queue

- [ ] Design queue storage.
- [ ] Add queued email model/schema.
- [ ] Preserve direct-send compatibility.
- [ ] Add enqueue method.
- [ ] Add queue processor command.
- [ ] Add retry tracking.
- [ ] Add failure tracking.
- [ ] Add tests for queue behavior.
- [ ] Document queue usage.

## Phase 7 — SMS Service and SMS Queue

Purpose: implement SMS support using the same reliability pattern as email.

### 7.1 Define SMS Service Contract

- [ ] Define SMS provider abstraction.
- [ ] Define configuration format.
- [ ] Define send API.
- [ ] Define error handling behavior.
- [ ] Document provider requirements.

### 7.2 Implement SMS

- [ ] Implement base SMS service.
- [ ] Implement at least one provider or mock provider.
- [ ] Add tests using mock provider.
- [ ] Document usage.

### 7.3 Implement SMS Queue

- [ ] Design queue storage.
- [ ] Add queued SMS model/schema.
- [ ] Add enqueue method.
- [ ] Add queue processor command.
- [ ] Add retry tracking.
- [ ] Add failure tracking.
- [ ] Add tests for queue behavior.

## Phase 8 — Built-In Administration Page

Purpose: provide a built-in `/admin` area for framework and application administration.

### 8.1 Admin Foundation

- [ ] Create `/admin` route.
- [ ] Use existing layout/template patterns.
- [ ] Add access control for administrators.
- [ ] Add basic admin dashboard.
- [ ] Add navigation location for admin pages.
- [ ] Preserve existing routes and templates.

### 8.2 Admin Sections

Initial admin sections may include:

- [ ] Application overview.
- [ ] Configuration summary.
- [ ] Database status.
- [ ] Queue status.
- [ ] Auth status.
- [ ] Developer information.


### 8.3 Admin Tests and Documentation

- [ ] Add route tests for `/admin`.
- [ ] Add authorization tests.
- [ ] Document admin page extension points.

### 8.4 Developer Kanban

Purpose: provide a built-in project management interface for framework and application development.

#### `/dev/kanban`

- [ ] Create route `/dev/kanban`.
- [ ] Read `KANBAN.md` from the repository root.
- [ ] Parse Kanban sections:
  - Backlog
  - Ready
  - In Progress
  - Testing
  - Done
- [ ] Render Kanban board using framework UI components.
- [ ] Support task creation.
- [ ] Support task editing.
- [ ] Support task deletion.
- [ ] Support drag-and-drop movement between columns.
- [ ] Persist changes back to `KANBAN.md`.
- [ ] Add authorization controls.
- [ ] Add developer-only access.
- [ ] Document Kanban file format.

Future considerations:

- [ ] Support ROADMAP integration.
- [ ] Support NEXT.md integration.
- [ ] Support GitHub issue synchronization.
- [ ] Support AI-assisted task generation.

## Phase 9 — Default Developer Landing Page

Purpose: give developers a useful welcome page immediately after installing the framework.

### 9.1 Landing Page Requirements

The default landing page should:

- [ ] Confirm that Core-Web is installed.
- [ ] Show framework version if available.
- [ ] Link to documentation.
- [ ] Link to `/admin` when available.
- [ ] Show next setup steps.
- [ ] Avoid exposing secrets or sensitive environment details.

### 9.2 Implement Landing Page

- [ ] Add or update default route.
- [ ] Add default view.
- [ ] Use existing template system.
- [ ] Support maintenance mode behavior.
- [ ] Preserve legacy route behavior where required.
- [ ] Add tests or documented manual validation.

## Phase 10 — Standardization and Polish

Purpose: make the framework easier to extend and maintain after the core improvements are complete.

### 10.1 Standardize Extension Development

- [ ] Document plugin structure.
- [ ] Document route registration.
- [ ] Document endpoint conventions.
- [ ] Document model conventions.
- [ ] Document helper conventions.
- [ ] Document asset conventions.
- [ ] Document install/update conventions.

### 10.2 Standardize Testing Expectations

- [ ] Define minimum tests for kernel changes.
- [ ] Define minimum tests for extensions.
- [ ] Define minimum tests for routes.
- [ ] Define minimum tests for database changes.
- [ ] Define minimum tests for queue services.


### 10.3 Standardize Documentation Expectations

- [ ] Require docs for new public APIs.
- [ ] Require docs for new services.
- [ ] Require docs for new extensions.
- [ ] Require migration notes for compatibility changes.

### 10.4 Developer Experience Documentation

Purpose: make Core-Web easier to understand, extend, and maintain for both humans and coding agents.

- [ ] Create plugin development guide.
- [ ] Create theme development guide.
- [ ] Create module development guide.
- [ ] Create database guide.
- [ ] Create routing guide.
- [ ] Create MVC guide.
- [ ] Create authentication guide.
- [ ] Create developer tools guide.

## Deferred / Future Considerations

These items are not part of the immediate roadmap unless promoted later:

- Plugin marketplace.
- Remote extension installation.
- Licensing service.
- OAuth server/client support.
- Multi-tenant application features.
- Advanced role/permission editor.
- Background worker daemon beyond CLI queue processors.
- UI theme management.
- Distributed update system.
- Additional developer tools such as `/dev/routes`, `/dev/config`, `/dev/database`, `/dev/plugins`, and `/dev/themes`.

## Completion Definition

A roadmap task is complete only when:

- current behavior has been reviewed,
- required documentation has been updated,
- implementation is complete,
- legacy behavior remains supported or migration notes exist,
- relevant tests pass,
- `git diff --check` passes,
- and the change has been committed.
