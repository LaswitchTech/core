<?php

namespace LaswitchTech\Core\Connectors;

use PDOStatement;

/**
 * Adapter wrapping a PDOStatement to match mysqli_result interface.
 */
class PDOResult
{
    private ?PDOStatement $stmt;
    /** @var int cached row count so numRows() works even after fetch/fetchAll */
    private int $rowCount = -1;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /** @return array<string, mixed>|null PDO returns false at EOF; coalesce to null for mysqli compatibility. */
    public function fetch_assoc(): ?array
    {
        $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /** @return array<int, mixed>|null */
    public function fetch_row(): ?array
    {
        $row = $this->stmt->fetch(\PDO::FETCH_NUM);
        return $row !== false ? $row : null;
    }

    /** @return array<int, mixed[]> */
    public function fetch_all(int $mode = \PDO::FETCH_ASSOC): array
    {
        return $this->stmt->fetchAll($mode);
    }

    /**
     * Return number of rows.
     * rowCount() on SQLite SELECT may return -1; we use it as-is (positive) or treat as 0. */
    public function numRows(): int
    {
        if ($this->rowCount < 0) {
            $rc = (int) $this->stmt->rowCount();
            $this->rowCount = $rc > 0 ? $rc : 0;
        }
        return $this->rowCount;
    }

    public function __destruct()
    {
        $this->stmt = null;
    }
}
