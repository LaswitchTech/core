<?php

// Declaring namespace
namespace LaswitchTech\Core\Connectors;

use LaswitchTech\Core\Abstracts\Connector;

/**
 * Null Connector — no-op fallback when no database is configured.
 *
 * Used during bootstrap or CLI scope when config/database.cfg is empty.
 * All methods return safe defaults (empty arrays, 0, false).
 */
class NullConnector extends Connector
{
    /**
     * @return bool Always false
     */
    public function connect(): bool
    {
        return false;
    }

    /**
     * @return bool Always false
     */
    public function isConnected(): bool
    {
        return false;
    }

    /**
     * @return bool Always false
     */
    public function close(): bool
    {
        return false;
    }

    /**
     * @return null Result object is never used with this connector
     */
    public function query(string $sql): mixed
    {
        return null;
    }

    /**
     * @return array Empty column list
     */
    public function describe(string $table): array
    {
        return [];
    }

    /**
     * @return int Always 0
     */
    public function lastId(): int
    {
        return 0;
    }

    /**
     * @return int Always 0
     */
    public function affectedRows(): int
    {
        return 0;
    }

    /**
     * @return false Never prepares a real statement
     */
    public function prepare(string $sql, array $params = []): bool
    {
        return false;
    }
}
