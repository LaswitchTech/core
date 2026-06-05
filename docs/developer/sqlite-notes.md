# SQLite Known Limitations and Notes

Core-Web supports SQLite out of the box with automatic type mappings, but several MySQL features don't have direct equivalents. This file documents them for developers and operators.

## DDL Limitations

### MODIFY COLUMN not supported

SQLite's ALTER TABLE only supports RENAME TABLE and ADD COLUMN. It does **not** support MODIFY or CHANGE COLUMN.

When you run `$Schema->define('table')->update()` on a SQLite database:
- Columns can be safely **added** (ADD COLUMN works)
- Columns cannot be **modified** in place (type changes are silently skipped with a comment placeholder)
- Columns can be **dropped** (DROP COLUMN is supported in SQLite 3.35+)
- Renaming follows the framework's rename path via `CHANGE` which may also need manual intervention

### AUTO_INCREMENT syntax

SQLite uses the keyword `AUTOINCREMENT` (no underscore), not MySQL's `AUTO_INCREMENT`. The framework maps these automatically at schema definition time. Additionally, you must specify an actual column in SQLite:

**MySQL:**
```sql
CREATE TABLE t (id INT AUTO_INCREMENT PRIMARY KEY) AUTO_INCREMENT = 1000;
```

**SQLite:**
```sql
CREATE TABLE t (id INTEGER PRIMARY KEY AUTOINCREMENT);
-- AUTO_INCREMENT value is set via sqlite_sequence table:
INSERT INTO sqlite_sequence (name, seq) VALUES ('t', 1000) ON CONFLICT(name) DO UPDATE SET seq = 1000;
```

## Type Limitations

### ENUM and SET are TEXT

SQLite has no ENUM or SET types. All enums/sets are stored as TEXT columns. Enum values (e.g., `enum('active','inactive')`) are preserved in the column definition's Extra comment field for reference, but enforcement is not possible at the storage level. You must handle validation in application code.

### tinyint(1) maps to BOOLEAN

`tinyint(1)` → `BOOLEAN` (which SQLite treats as numeric: 0 or 1). No loss of information since BOOLEAN values are always integer-compatible.

## JSON Compatibility

For MySQL's `JSON_CONTAINS()` used by the `CONTAINS` operator — on SQLite, store JSON data as TEXT with `json()` function for parsing. If you need full JSON query support, ensure at least SQLite 3.38+. Core-Web queries using prepared parameters will work transparently; raw `JSON_CONTAINS()` SQL will only execute on MySQL connectors.

## Foreign Keys

SQLite enables foreign key enforcement in the framework via `PRAGMA foreign_keys=ON` on connect. This must be re-enabled on every connection since it's session-scoped. It is not globally enabled by default in SQLite (unlike MySQL).

## Performance Tips

1. **WAL journal mode** is enabled automatically on connect for better concurrent read performance
2. For bulk inserts, use transactions around your operations (`BEGIN;` / `COMMIT;`)
3. The framework uses `PRAGMA journal_mode=WAL` which allows multiple concurrent readers (no write locking issues with standard read-heavy workloads)

## Migration from MySQL to SQLite

When migrating an existing MySQL-backed project:

1. Update `config/database.cfg` to the SQLite format with a valid path
2. Run `$Database->query()->table('users')->autoIncrement(10000);` — this now correctly uses `sqlite_sequence` instead of `ALTER TABLE ... AUTO_INCREMENT`
3. For ENUM columns in Definition/map files: change them to TEXT before re-running schema comparison, or manually update the .map definitions
4. Verify that your existing data loads correctly (SQLite is more strict about type coercions)

## Testing Your SQLite Setup

A smoke test suite exists at `tests/Unit/SQLiteConnectorTest.php` covering:
- Connection via PDO and SQLite backend
- Table describe via PRAGMA table_info
- Query builder integration with SELECT, INSERT, UPDATE, DELETE
- Prepared statement binding (PDOPreparedStatement adapter)
- Last-insert-id and affected-rows
