<?php

/**
 * Core Framework - Connector
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Abstracts;

// Import additionnal class into the global namespace
use Exception;

abstract class Connector {

    /**
     * @var mixed
     */
    protected $Config;

    /**
     * Constructor
     *
     * Here you might load global config or do other setup tasks.
     * For this example, we assume there's a $CONFIG global object
     * that stores DB credentials, etc.
     */
    public function __construct()
    {
        global $CONFIG;
        $this->Config = $CONFIG;

        // If you have a method $CONFIG->add('database') to load DB config, do that here.
        if (method_exists($this->Config, 'add')) {
            $this->Config->add('database');
        }
    }

    /**
     * Connect method to be overridden by child classes
     */
    public function connect()
    {
        // Implementation in child classes
    }

    /**
     * Close method to be overridden by child classes
     */
    public function close()
    {
        // Implementation in child classes
    }

    /**
     * Check if connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        // Implementation in child classes
        return false;
    }

    /**
     * Execute a query
     *
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql)
    {
        // Implementation in child classes
    }

    /**
     * Describe a table
     *
     * @param string $table
     * @return mixed
     */
    public function describe(string $table)
    {
        // Implementation in child classes
    }

    /**
     * Get the ID of the last inserted row
     *
     * @return int
     */
    public function lastId(): int
    {
        // Implementation in child classes
        return 0;
    }

    /**
     * Get the number of affected rows
     *
     * @return int
     */
    public function affectedRows(): int
    {
        // Implementation in child classes
        return 0;
    }

    /**
     * Prepare a query
     *
     * @param string $sql
     */
    public function prepare(string $sql, array $params = [])
    {
        // Implementation in child classes
        return false;
    }

    /**
     * Default engine name for CREATE TABLE (InnoDB, SQLite, etc.).
     */
    public function getDefaultEngine(): string
    {
        return 'InnoDB';
    }

    /**
     * Default charset for tables.
     */
    public function getDefaultCharset(): string
    {
        return 'utf8mb4';
    }

    /**
     * Default collation suffix (without the charset prefix).
     */
    public function getDefaultCollation(): string
    {
        return 'general_ci';
    }

    /**
     * Whether this connector supports column ALTER/MODIFY.
     * SQLite does not support MODIFY COLUMN; it requires a create-deny-rename pattern.
     */
    public function supportsModifyColumn(): bool
    {
        return true;
    }

    /**
     * SQL to list tables. Override in connectors that use a dialect other than MySQL's SHOW TABLES.
     */
    public function showTablesSQL(?string $like = null): string
    {
        if ($like !== null) {
            return "SHOW TABLES LIKE '" . $this->escapeLike($like) . "'";
        }
        return 'SHOW TABLES';
    }

    /**
     * SQL to check if a specific table exists.
     */
    public function tableExistsSQL(string $table): string
    {
        return "SHOW TABLES LIKE '" . $this->escapeLike($table) . "'";
    }

    /**
     * Escape LIKE wildcards (% and _) in table/column names.
     */
    protected function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    /**
     * Generate the SQL to set AUTO_INCREMENT start value.
     * SQLite uses UPDATE sqlite_sequence; MySQL uses ALTER TABLE AUTO_INCREMENT.
     */
    public function autoIncrementSQL(string $table, int $int): string
    {
        return "ALTER TABLE `{$table}` AUTO_INCREMENT = {$int}";
    }

    /**
     * Transform a column definition for this connector's dialect.
     * Example: ENUM → TEXT for SQLite; tinyint(1) → BOOLEAN; AUTO_INCREMENT → AUTOINCREMENT.
     */
    public function defineColumn(array $def): array
    {
        // MySQL default — no transformations
        return $def;
    }
}
