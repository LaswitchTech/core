<?php

namespace LaswitchTech\Core\Connectors;

use PDOStatement;

/**
 * Adapter wrapping a PDOStatement to match mysqli_result interface.
 */
class PDOResult
{
    private ?PDOStatement $stmt;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /** @return array<string, mixed>|null */
    public function fetch_assoc(): ?array
    {
        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /** @return array<int, mixed>|null */
    public function fetch_row(): ?array
    {
        return $this->stmt->fetch(\PDO::FETCH_NUM);
    }

    /** @return array<int, mixed[]> */
    public function fetch_all(int $mode = \PDO::FETCH_ASSOC): array
    {
        return $this->stmt->fetchAll($mode);
    }

    public function numRows(): int
    {
        // PDOStatement cannot be rewound after fetch(), so we cache
        // by fetching all into memory the first time accessed.
        return (int) $this->stmt->rowCount();
    }

    public function __destruct()
    {
        $this->stmt = null;
    }
}
