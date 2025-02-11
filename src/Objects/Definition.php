<?php

/**
 * Core Framework - Definition
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;

class Definition {

    /**
     * List of default length for types
     *
     * @var array
     */
    const lengths = [
        // Numeric
        'int' => 11,
        'tinyint' => 4,
        'smallint' => 6,
        'mediumint' => 9,
        'bigint' => 20,
        // -
        'decimal' => 10,
        'float' => 12,
        'double' => 22,
        'real' => 12,
        // -
        'bit' => 1,
        'boolean' => 1,
        'serial' => 10,

        // Date and Time
        'date' => 0,
        'datetime' => 0,
        'timestamp' => 0,
        'time' => 0,
        'year' => 4,

        // String
        'char' => 255,
        'varchar' => 255,
        // -
        'tinytext' => 0,
        'text' => 0,
        'mediumtext' => 0,
        'longtext' => 0,
        // -
        'binary' => 255,
        'varbinary' => 255,
        // -
        'tinyblob' => 0,
        'blob' => 0,
        'mediumblob' => 0,
        'longblob' => 0,
        // -
        'enum' => 0,
        'set' => 0,

        // Spatial
        'geometry' => 0,
        'point' => 0,
        'linestring' => 0,
        'polygon' => 0,
        'multipoint' => 0,
        'multilinestring' => 0,
        'multipolygon' => 0,
        'geometrycollection' => 0,

        // JSON
        'json' => 0,
    ];

    /**
     * Column name
     *
     * @var string
     */
    private $column;

    /**
     * Table definition
     *
     * @var array
     */
    private $definition = [
        "Field" => null,
        "Type" => "varchar(255)",
        "Null" => "YES",
        "Key" => "",
        "Default" => null,
        "Extra" => ""
    ];

    /**
     * Constructor
     *
     * @param string $column
     * @param array $definition
     */
    public function __construct(string $column, array $definition = [])
    {
        $this->column = $column;
        foreach($definition as $key => $value) {
            $this->definition[$key] = $value;
        }
        $this->definition['Field'] = $column;
    }

    /**
     * Define the column
     *
     * @return array
     */
    public function define(): array
    {
        return $this->definition;
    }

    /**
     * Set the column type
     *
     * @param string $type
     * @param int $length
     * @return self
     */
    public function type(string $type, int $length = 0): self
    {
        $length = ($length > 0) ? $length : self::lengths[$type];
        $this->definition['Type'] = $type . ($length > 0 ? "($length)" : "");
        return $this;
    }

    /**
     * Set the column as nullable
     *
     * @param bool $isNull
     * @return self
     */
    public function nullable(bool $isNull = true): self
    {
        $this->definition['Null'] = ($isNull) ? "YES" : "NO";
        return $this;
    }

    /**
     * Set the column default value
     *
     * @param string $value
     * @return self
     */
    public function default(string $value = null): self
    {
        $this->definition['Default'] = $value;
        return $this;
    }

    /**
     * Set the column as primary key
     *
     * @param bool $isPrimary
     * @return self
     */
    public function primary(bool $isPrimary = true): self
    {
        $this->definition['Key'] = ($isPrimary) ? "PRI" : "";
        return $this;
    }

    /**
     * Set the column as unique
     *
     * @param bool $isUnique
     * @return self
     */
    public function unique(bool $isUnique = true): self
    {
        $this->definition['Key'] = ($isUnique) ? "UNI" : "";
        return $this;
    }

    /**
     * Set the column as auto increment
     *
     * @param bool $isAutoIncrement
     * @return self
     */
    public function autoIncrement(bool $isAutoIncrement = true): self
    {
        $this->definition['Extra'] = ($isAutoIncrement) ? "auto_increment" : "";
        return $this;
    }

    /**
     * Set on update current timestamp
     *
     * @param bool $isOnUpdate
     * @return self
     */
    public function onUpdate(bool $isOnUpdate = true): self
    {
        $this->definition['Extra'] = ($isOnUpdate) ? "on update CURRENT_TIMESTAMP" : "";
        return $this;
    }

    /**
     * Rename the column
     *
     * @param string $name
     * @return self
     */
    public function rename(string $name): self
    {
        $this->definition['Field'] = $name;
        $this->definition['Previously'] = $this->column;
        $this->column = $name;
        return $this;
    }
}
