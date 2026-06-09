<?php

namespace LaswitchTech\Core\Connectors;

use PDO;
use PDOException;
use Exception;
use LaswitchTech\Core\Abstracts\Connector;

/**
 * SQLite database connector using PDO.
 *
 * Implements the full Connector interface (8 methods) and wraps PDOStatement
 * to match mysqli_result / mysqli_stmt interfaces so that Query.php, Schema.php
 * and other clients work without modification.
 */
class SQLite extends Connector
{
    private ?PDO $pdo = null;

    // -------------------------------------------------------------------------
    // Connector interface implementation
    // -------------------------------------------------------------------------

    public function connect(): void
    {
        global $CONFIG;

        $path = '';
        if ($CONFIG && method_exists($CONFIG, 'get')) {
            $dbConfig = $CONFIG->get('database');
            if (is_array($dbConfig) && !empty($dbConfig['path'])) {
                $path = $dbConfig['path'];
            }
        }

        if (!$path) {
            throw new Exception("SQLite connector requires 'path' in database.cfg");
        }

        // Ensure the parent directory exists for the SQLite file
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->pdo = new PDO('sqlite:' . $path, '', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => false,
        ]);

        // Enable WAL mode for better concurrent read performance
        $this->pdo->exec('PRAGMA journal_mode=WAL');
        $this->pdo->exec('PRAGMA foreign_keys=ON');
    }

    public function close(): void
    {
        if ($this->pdo !== null) {
            $this->pdo = null;
        }
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    /**
     * Execute raw SQL. Returns a PDOResult adapter for SELECT queries,
     * or the rowCount for non-SELECT statements.
     *
     * @return mixed PDOStatement|PDOResult|int|null
     */
    public function query(string $sql): mixed
    {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            // Normalize: strip backticks that SQLite handles the same way as MySQL
            $stmt = $this->pdo->query($sql);
            if ($stmt === false) {
                throw new Exception('SQLite query failed');
            }

            // Return PDOResult for non-DELETE/UPDATE/INSERT so Query.php's
            // result()->fetch_assoc() chain works
            return new PDOResult($stmt);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * Describe table columns in MySQL DESCRIBE format.
     *
     * Returns indexed array where each element has:
     * Field, Type, Null, Key, Default, Extra
     */
    public function describe(string $table): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $result = $this->pdo->query("PRAGMA table_info(`$table`)");
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table doesn't exist — return empty (matches MySQL behavior)
            return [];
        }

        // Normalize to MySQL DESCRIBE column format
        $normalized = [];
        foreach ($rows as $row) {
            $normalized[] = [
                'Field' => $row['name'],
                'Type'  => $row['type'] ?? '',
                'Null'  => ($row['notnull'] === 0 || empty($row['notnull'])) ? 'YES' : 'NO',
                'Key'   => $row['pk'] > 0 ? 'PRI' : '',
                'Default' => $row['dflt_value'] !== null ? strval($row['dflt_value']) : null,
                'Extra' => ($row['pk'] > 0 && strpos(strtoupper($row['type']), 'INTEGER') !== false) ? 'auto_increment' : '',
            ];
        }

        return $normalized;
    }

    public function lastId(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }
        return (int) $this->pdo->lastInsertId();
    }

    public function affectedRows(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }
        // PDO::rowCount returns the number of rows affected by the last SQL statement.
        // For SELECT, it may return -1 in some drivers but PDO SQLite returns actual count.
        return (int) $this->pdo->query('SELECT changes()')->fetchColumn();
    }

    /** @var ?\PDOStatement reference to last-insert statement for affectedRows() */
    private ?\PDOStatement $_lastInsertStmt = null;

    /**
     * Prepare a parameterized query and return a PDOPreparedStatement adapter.
     */
    public function prepare(string $sql, array $params = []): mixed
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt === false) {
                throw new Exception('SQLite prepare failed');
            }
            // Pass params so Query builder parameters get bound on execute()
            $adapter = new PDOPreparedStatement($stmt);
            if (!empty($params)) {
                $adapter->setParams($params);
            }
            return $adapter;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage(), (int) $e->getCode());
        }
    }

    // -------------------------------------------------------------------------
    // Connector dialect introspection
    // -------------------------------------------------------------------------

    public function getDefaultEngine(): string
    {
        return 'SQLite';
    }

    public function getDefaultCharset(): string
    {
        return '';
    }

    public function getDefaultCollation(): string
    {
        return '';
    }

    public function supportsModifyColumn(): bool
    {
        return false;
    }

    public function showTablesSQL(?string $like = null): string
    {
        $sql = "SELECT name FROM sqlite_master WHERE type='table'";
        if ($like !== null) {
            // SQLite LIKE is case-insensitive for ASCII
            $escaped = addcslashes($like, '%_\\');
            $sql .= " AND name LIKE '" . $escaped . "' ESCAPE '\\'";
        }
        return $sql;
    }

    public function tableExistsSQL(string $table): string
    {
        $escaped = addcslashes($table, '%_\\');
        return "SELECT name FROM sqlite_master WHERE type='table' AND name LIKE '" . $escaped . "' ESCAPE '\\'";
    }

    public function autoIncrementSQL(string $table, int $int): string
    {
        // SQLite uses sqlite_sequence to manage AUTOINCREMENT counters
        $escaped = addcslashes($table, '\\');
        return "INSERT INTO sqlite_sequence (name, seq) VALUES ('{$escaped}', {$int}) ON CONFLICT(name) DO UPDATE SET seq = {$int}";
    }

    /**
     * Returns the list of tables directly from SQLite (not via raw SQL),
     * used by Schema.php for column introspection in SHOW TABLE STATUS.
     */
    public function describeTableStatus(): array
    {
        if (!$this->isConnected()) {
            return [];
        }
        try {
            $result = $this->pdo->query(
                "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
            );
            return (array) $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Convert a column definition for SQLite dialect.
     * Mapping rules:
     *   - ENUM(...)/SET(...) → TEXT (SQLite has no ENUM/SET; preserve values in comment)
     *   - tinyint(1) → BOOLEAN
     *   - AUTO_INCREMENT → AUTOINCREMENT (no underscore)
     */
    public function defineColumn(array $def): array
    {
        // ENUM / SET → TEXT for SQLite
        if (preg_match('/^(enum|set)\(/i', $def['Type'] ?? '')) {
            $def['Type'] = 'TEXT';
            $def['Extra'] = ($def['Extra'] === '' ? '' : $def['Extra'] . ', ') . "ENUM(" . $def['Type'] . ")";
        }

        // tinyint(1) → BOOLEAN (maps to numeric in SQLite, but clearer for tooling)
        if (preg_match('/^tinyint\(1\)$/', $def['Type'] ?? '')) {
            $def['Type'] = 'BOOLEAN';
        }

        // AUTO_INCREMENT → AUTOINCREMENT
        if (stripos($def['Extra'] ?? '', 'auto_increment') !== false) {
            $def['Extra'] = preg_replace('/auto_increment/i', 'AUTOINCREMENT', $def['Extra']);
        }

        return $def;
    }
}
