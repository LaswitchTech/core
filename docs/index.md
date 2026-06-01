# Core-Web — User Manual

The manual is the central reference for using, configuring, and extending the Core-Web framework.
It covers setup, administration, customization, and development.

If you can't find what you're looking for here, check the [DESIGN.md](../DESIGN.md) for
architecture details or [ROADMAP.md](../ROADMAP.md) for planned development.

---

## 1. General

- [What is Core-Web?](./01-general/what-is-core-web.md)
- [Architecture Overview](./01-general/architecture.md)
- [Project Structure](./01-general/project-structure.md)
- [Stack & Dependencies](./01-general/stack-dependencies.md)
- [Frequently Asked Questions](./01-general/faq.md)

## 2. Installing Core-Web

- [Requirements](./02-installing/requirements.md)
- [Installation Guide](./02-installing/installation.md)
- [Setup Script](./02-installing/setup.md)
- [Configuration](./02-installing/configuration.md)
- [First Run & Installer](./02-installing/first-run.md)
- [Upgrading Core-Web](./02-installing/upgrading.md)

## 3. Using Core-Web

### 3.1 Configuration

- [Config Files (.cfg)](./03-using/configuration.md)
- [Config Override Layer](./03-using/config-override.md)
- [Bootstrap Configuration](./03-using/bootstrap-config.md)
- [Application Settings](./03-using/app-settings.md)

### 3.2 Services

- [Overview of Services](./03-using/services-overview.md)
- [Database Service](./03-using/database.md)
- [Query Builder](./03-using/query-builder.md)
- [Schema Builder](./03-using/schema-builder.md)
- [Auth Service](./03-using/auth.md)
- [Session Management](./03-using/session-management.md)
- [CSRF Protection](./03-using/csrf.md)
- [SMTP / Email](./03-using/smtp.md)
- [Locale / i18n](./03-using/locale.md)
- [Logging](./03-using/logging.md)
- [Output Formatting](./03-using/output.md)
- [Request Handling](./03-using/request.md)
- [UUID Generation](./03-using/uuid.md)
- [Style / LESS Compilation](./03-using/style-less.md)
- [Helper Loader](./03-using/helpers.md)
- [Installer](./03-using/installer.md)

### 3.3 Routing

- [Route Registration](./03-using/routing.md)
- [Route Patterns](./03-using/route-patterns.md)
- [Error Pages](./03-using/error-pages.md)
- [Custom Status Codes](./03-using/custom-status-codes.md)

### 3.4 Layouts

- [Overview of Layouts](./03-using/layouts-overview.md)
- [panel.php — Admin Layout](./03-using/layout-panel.md)
- [website.php — App Layout](./03-using/layout-website.md)
- [fullscreen.php — Fullscreen Layout](./03-using/layout-fullscreen.md)
- [internal.php — Auth Layout](./03-using/layout-internal.md)
- [index.php — Blank Layout](./03-using/layout-blank.md)
- [Layout Hooks](./03-using/layout-hooks.md)
- [Widget System](./03-using/widgets.md)

### 3.5 Menus

- [Menu Registry](./03-using/menus.md)
- [Adding Menu Items](./03-using/adding-menu-items.md)
- [Menu Locations](./03-using/menu-locations.md)
- [Breadcrumbs](./03-using/breadcrumbs.md)

### 3.6 Authentication

- [Auth Flow Overview](./03-using/auth-flow.md)
- [Session Authentication](./03-using/auth-session.md)
- [Bearer Token Authentication](./03-using/auth-bearer.md)
- [Basic Auth](./03-using/auth-basic.md)
- [Remember Me](./03-using/auth-remember-me.md)
- [Forgot Password](./03-using/auth-forgot-password.md)
- [Email Verification](./03-using/auth-email-verification.md)
- [2FA / TOTP](./03-using/auth-2fa.md)
- [User Registration](./03-using/auth-registration.md)
- [User Object](./03-using/auth-user-object.md)
- [Backends](./03-using/auth-backends.md)

## 4. Administering Core-Web

### 4.1 Administration Pages

- [Admin Panel Overview](./04-administering/admin-panel.md)
- [User Management](./04-administering/admin-users.md)
- [Organization Management](./04-administering/admin-organizations.md)
- [Settings Page](./04-administering/admin-settings.md)
- [Security Settings](./04-administering/admin-security.md)

### 4.2 Extensions

- [Extension Catalog](./04-administering/extension-catalog.md)
- [Installing Extensions](./04-administering/extension-install.md)
- [Uninstalling Extensions](./04-administering/extension-uninstall.md)
- [Enabling / Disabling Extensions](./04-administering/extension-enable-disable.md)
- [Extension Manifest Format](./04-administering/extension-manifest.md)
- [Dependency Resolution](./04-administering/extension-dependencies.md)
- [Extension Updates](./04-administering/extension-updates.md)
- [Publishing Extensions](./04-administering/extension-publish.md)

### 4.3 Developer Tools

- [Developer Mode](./04-administering/developer-mode.md)
- [Scaffold Generator](./04-administering/scaffold-generator.md)
- [Debug Tools](./04-administering/debug-tools.md)
- [Debug Audit Logging](./04-administering/debug-audit-logger.md)

### 4.4 Maintenance

- [Backups](./04-administering/backups.md)
- [Database Maintenance](./04-administering/db-maintenance.md)
- [Theme Preview](./04-administering/theme-preview.md)
- [Log Rotation](./04-administering/log-rotation.md)
- [Server Migration](./04-administering/server-migration.md)
- [Backup & Restore](./04-administering/backup-restore.md)

## 5. Adapting Core-Web

### 5.1 Plugins

- [Plugin Development](./05-adapting/plugin-development.md)
- [Plugin Directory Structure](./05-adapting/plugin-structure.md)
- [Plugin Manifest (info.cfg)](./05-adapting/plugin-manifest.md)
- [Creating a Plugin Endpoint](./05-adapting/plugin-endpoint.md)
- [Creating a Plugin Model](./05-adapting/plugin-model.md)
- [Creating a Plugin Helper](./05-adapting/plugin-helper.md)
- [Creating a CLI Command](./05-adapting/plugin-command.md)
- [Plugin Routing](./05-adapting/plugin-routing.md)
- [Plugin Styles & Assets](./05-adapting/plugin-assets.md)
- [Plugin Installation Files](./05-adapting/plugin-install-files.md)
- [Plugin Migrations](./05-adapting/plugin-migrations.md)
- [Plugin Lifecycle Hooks](./05-adapting/plugin-lifecycle.md)
- [Plugin Registry](./05-adapting/plugin-registry.md)
- [Plugin Example — Hello World](./05-adapting/plugin-hello-world.md)

### 5.2 Themes

- [Theme Development](./05-adapting/theme-development.md)
- [Theme Directory Structure](./05-adapting/theme-structure.md)
- [LESS Compilation Pipeline](./05-adapting/theme-less.md)
- [styles.cfg Format](./05-adapting/theme-styles-cfg.md)
- [Creating a Theme](./05-adapting/theme-create.md)
- [Available Themes](./05-adapting/available-themes.md)
- [Dark / Light Mode](./05-adapting/theme-dark-light.md)
- [Switching Themes](./05-adapting/theme-switching.md)

### 5.3 Layouts

- [Creating a Custom Layout](./05-adapting/layout-custom.md)
- [Layout Template Format](./05-adapting/layout-template.md)
- [Layout Variables](./05-adapting/layout-variables.md)
- [Layout Overrides](./05-adapting/layout-overrides.md)

### 5.4 i18n / Localization

- [Locale Files](./05-adapting/locale-files.md)
- [Adding a New Locale](./05-adapting/adding-locale.md)
- [Locale Resolution](./05-adapting/locale-resolution.md)

## 6. Developing Core-Web

### 6.1 Architecture

- [Kernel Architecture](./06-developing/kernel-architecture.md)
- [Bootstrap System](./06-developing/bootstrap-system.md)
- [Service Container](./06-developing/service-container.md)
- [Config System](./06-developing/config-system.md)
- [Database Architecture](./06-developing/database-architecture.md)
- [Auth Architecture](./06-developing/auth-architecture.md)
- [Routing Architecture](./06-developing/routing-architecture.md)
- [Query Builder Internals](./06-developing/query-builder-internals.md)
- [Schema Builder Internals](./06-developing/schema-builder-internals.md)
- [Domain Objects Reference](./06-developing/domain-objects.md)

### 6.2 For Hands-on Developers

- [Development Setup](./06-developing/dev-setup.md)
- [Coding Conventions](./06-developing/coding-conventions.md)
- [Contribution Guide](../CONTRIBUTING.md)
- [Code of Conduct](../CODE_OF_CONDUCT.md)
- [Testing](./06-developing/testing.md)
- [Debugging Tips](./06-developing/debugging-tips.md)

### 6.3 Core Classes Reference

- [Bootstrap](./06-developing/ref-bootstrap.md)
- [Router](./06-developing/ref-router.md)
- [API](./06-developing/ref-api.md)
- [Auth](./06-developing/ref-auth.md)
- [Database](./06-developing/ref-database.md)
- [Query](./06-developing/ref-query.md)
- [Schema](./06-developing/ref-schema.md)
- [Session](./06-developing/ref-session.md)
- [User](./06-developing/ref-user.md)
- [Config](./06-developing/ref-config.md)
- [CSRF](./06-developing/ref-csrf.md)
- [Output](./06-developing/ref-output.md)
- [Request](./06-developing/ref-request.md)
- [SMTP](./06-developing/ref-smtp.md)
- [Message](./06-developing/ref-message.md)
- [Style](./06-developing/ref-style.md)
- [Log](./06-developing/ref-log.md)
- [Locale](./06-developing/ref-locale.md)
- [Helper Loader](./06-developing/ref-helpers.md)
- [Installer](./06-developing/ref-installer.md)
- [Builder](./06-developing/ref-builder.md)
- [UUID](./06-developing/ref-uuid.md)
- [BaseModel](./06-developing/ref-base-model.md)
- [BaseEndpoint](./06-developing/ref-base-endpoint.md)
- [Abstracts Reference](./06-developing/ref-abstracts.md)
- [Objects Reference](./06-developing/ref-objects.md)

### 6.4 Abstract Classes Reference

- [BaseModel](./06-developing/ref-base-model.md)
- [BaseEndpoint](./06-developing/ref-base-endpoint.md)
- [Backend](./06-developing/ref-backend.md)
- [Controller](./06-developing/ref-controller.md)
- [Endpoint](./06-developing/ref-endpoint.md)
- [Helper](./06-developing/ref-helper.md)
- [Model](./06-developing/ref-model.md)
- [Connector](./06-developing/ref-connector.md)
- [Command](./06-developing/ref-command.md)

### 6.5 Versioning

- [Versioning Model](./06-developing/versioning.md)
- [VersionProvider](./06-developing/version-provider.md)

### 6.6 Changelog

- [Core-Web Releases](./06-developing/changelog.md)
- [Migration Guides](./06-developing/migration-guides.md)

### 6.7 See Also

- [DESIGN.md — Architecture & Decisions](../DESIGN.md)
- [ROADMAP.md — Project Priorities](../ROADMAP.md)
- [GitHub Repository](https://github.com/LaswitchTech/core)
- [Release Downloads](https://github.com/LaswitchTech/core/releases/latest)

### 6.8 Contributing

- [How to Contribute](./06-developing/contribute.md)
- [Security Policy](../SECURITY.md)
- [Bug Reports & Feature Requests](./06-developing/issues.md)
