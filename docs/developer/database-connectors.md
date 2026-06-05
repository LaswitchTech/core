# Database Connectors

Core-Web supports two database backends through a pluggable connector interface (`Connectors\Connector`): **MySQL** and **SQLite**.

## Connector Interface

All connectors extend `LaswitchTech\Core\Abstracts\Connector` which defines these dialect-independence methods:

| Method | Purpose |
|---|---|
| `getDefaultEngine()` | Return `'InnoDB'` or `'SQLite'` |
| `getDefaultCharset()` | Return `'utf8mb4'`, `''` for SQLite (no charset support) |
| `getDefaultCollation()` | Return collation suffix, `''` for SQLite |
| `supportsModifyColumn()` | Whether ALTER TABLE MODIFY COLUMN works (`false` for SQLite) |
| `showTablesSQL(?like)` | Generate SQL to list tables (dialect-specific) |
| `tableExistsSQL(string)` | Generate SQL to check a specific table's existence |
| `autoIncrementSQL(string, int)` | GENERATE SQL to set AUTO_INCREMENT start value |
| `defineColumn(array)` | Transform column definition for dialect (ENUM→TEXT, etc.) |

## MySQL Connector

**Config (`config/database.cfg`):**
```json
{
    "connector": "mysql",
    "host": "localhost",
    "port": 3306,
    "database": "demo",
    "username": "root",
    "password": "",
    "socket": null
}
```

- Uses `mysqli` extension with prepared statements.
- Supports full table engine/charset/collation configuration via `CREATE TABLE` suffixes.

## SQLite Connector

**Config (`config/database.cfg`):**
```json
{
    "connector": "sqlite",
    "path": "data/database.sqlite"
}
```

- Uses `PDO_SQLITE` extension.
- Enables WAL journal mode and foreign key enforcement automatically on connect.
- File is created/directory auto-created if not present.
- **No support for**: ENUM/SET types, AUTO_INCREMENT (maps to AUTOINCREMENT), MODIFY COLUMN.

### SQLite Configuration Notes

| Key | Required | Description |
|---|---|---|
| `connector` | Yes | Must be `"sqlite"` |
| `path` | Yes | Path to the SQLite database file (relative to project root) |

The path is auto-created: if `data/database.sqlite` is specified and `/data/` doesn't exist, it is created with mode 0755. The database file permissions are set to 0644 by PDO.

### Type Mappings

Core-Web automatically maps MySQL column types to SQLite-appropriate equivalents:

| MySQL Type | SQLite Equivalent | Notes |
|---|---|---|
| `ENUM(...)` / `SET(...)` | `TEXT` | Enum values preserved in Extra comment field |
| `tinyint(1)` | `BOOLEAN` | Numeric-only storage in SQLite |
| `AUTO_INCREMENT` | `AUTOINCREMENT` | Note: no underscore |
| `on update CURRENT_TIMESTAMP` | (warned) | Not supported; silently skipped |

## Database Install Flow

The installer (`Database.php::install()`) automatically detects which config format to expect based on connector type:

### MySQL install config:
```php
[$config] = [
    'connector' => 'mysql',
    'host'      => 'localhost',
    'database'  => 'myapp',
    'username'  => 'root',
    'password'  => '',
    'sample'    => true,  // optional
];
$Database->install($config);
```

### SQLite install config:
```php
$config = [
    'connector' => 'sqlite',
    'path'      => 'data/app.sqlite',
    'sample'    => true,  // optional
];
$Database->install($config);
```

## Limitations and Workarounds

### MODIFY COLUMN not supported (SQLite)

SQLite does not support `ALTER TABLE ... MODIFY COLUMN`. The core framework detects this via `$connector->supportsModifyColumn()` and skips the modification step when running schema updates. If you need to change a column type, Schema.php will:

1. Detect the unsupported operation (`modify`)
2. Emit a comment placeholder (not an executable SQL statement)
3. Continue without modifying that column

**Workaround**: For SQLite deployments, define your Schema in its final form from the start, and only use `ADD COLUMN` operations via normal DDL.

### JSON_CONTAINS compatibility

When using MySQL's `CONTAINS` operator on JSON columns, this maps to native `JSON_CONTAINS()`. On SQLite, JSON data is stored as TEXT with `json()` functions available at query time (SQLite 3.38+).

## Requirement Catalog

Connector support status is documented in `config/requirement.cfg` under `database_connectors`. The `core` service list includes `DATABASE`, which loads whichever connector is configured.
