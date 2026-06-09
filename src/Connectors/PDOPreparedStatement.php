<?php

namespace LaswitchTech\Core\Connectors;

use PDOStatement;

/**
 * Adapter wrapping a PDOStatement to match mysqli_stmt interface.
 *
 * Exposes bind_param(type-characters, ...$values), execute(), get_result()
 * — the exact surface Query.php expects when using prepared statements.
 */
class PDOPreparedStatement
{
    private ?PDOStatement $stmt;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Bind parameters matching mysqli_stmt::bind_param signature.
     *
     * @param string $types Type string: 'i' (int), 'd' (float), 's' (string), 'b' (blob/other)
     * @param mixed ...$params Values to bind
     */
    public function bind_param(string $types, mixed &...$params): void
    {
        foreach (str_split($types) as $i => $type) {
            $value = &$params[$i];
            switch ($type) {
                case 'i': $value = (int) $value; break;
                case 'd': $value = (float) $value; break;
                case 'b': $value = strval($value); break; // blob → string for SQLite
                default:  $value = strval($value); break; // 's' and fallback
            }
        }

        foreach ($params as $i => $value) {
            $this->stmt->bindValue($i + 1, $value);
        }
    }

    /** Execute the prepared statement. */
    public function execute(): bool
    {
        try {
            // Ensure execution happens without any transaction issues
            $result = $this->stmt->execute();
            
            return $result;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage(), (int) $e->getCode());
        }
    }

    /** Return a PDOResult adapter for the executed statement. */
    public function get_result(): PDOResult
    {
        return new PDOResult($this->stmt);
    }

    public function __destruct()
    {
        $this->stmt = null;
    }
}
