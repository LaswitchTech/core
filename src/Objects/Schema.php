<?php

/**
 * Core Framework - Schema
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects\Definition;
use LaswitchTech\Core\Abstracts\Connector;
use Exception;

class Schema {

    /**
     * @var Connector
     */
    private $connector;

    /**
     * Definition path
     *
     * @var string
     */
    private $path;

    /**
     * Table name
     *
     * @var string
     */
    private $table;

    /**
     * Definitions
     *
     * @var array
     */
    private $definitions = [];

    /**
     * Table engine
     *
     * @var string
     */
    private $engine = 'InnoDB';

    /**
     * Table charset
     *
     * @var string
     */
    private $charset = 'utf8mb4';

    /**
     * Table collation
     *
     * @var string
     */
    private $collation = 'general_ci';

    /**
     * Constructor
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        global $CONFIG;
        $this->path = $CONFIG->root() . '/Definition';

        $this->connector = $connector;
    }

    /**
     * Describe a table
     *
     * @return mixed
     */
    public function describe()
    {
        $definitions = [];
        foreach ($this->definitions as $column => $definition) {
            $definitions[] = $definition->define();
        }
        return $definitions;
    }

    /**
     * DEFINE the table
     *
     * @param string $table
     * @return self
     */
    public function define(string $table): self
    {
        $this->table = $table;
        $path = $this->path . DIRECTORY_SEPARATOR . $table . '.map';

        // If the definition file does not exist, generate the definition
        if (!file_exists($path)) {

            // Load the table definition
            $definitions = $this->connector->describe($table);

            // Retrieve the table engine, charset and collation
            $sql = "SHOW TABLE STATUS LIKE '{$table}'";
            $result = $this->connector->query($sql);
            $row = $result->fetch_assoc();

            // Set the table engine
            $this->engine = $row['Engine'];

            // Set the table charset and collation
            $Collation = explode('_', $row['Collation']);
            $this->charset = $Collation[0];
            $this->collation = str_replace($this->charset.'_','',$row['Collation']);
        } else {

            // Retrieve the definition from the file
            $definitions = json_decode(file_get_contents($path), true);

            // Loop through the definition
            foreach ($definitions as $key => $definition) {
                if(in_array($key, ['engine', 'charset', 'collation'])) {
                    $this->{$key} = $definition;
                    unset($definitions[$key]);
                }
            }
        }

        // Load the definition
        foreach ($definitions as $key => $definition) {
            $this->column($definition['Field'], $definition);
        }

        // If the definition file does not exist, create it
        if (!file_exists($path)) {

            // Save the definition
            $this->save();
        }

        return $this;
    }

    /**
     * Get a column's Definition
     *
     * @param string $column
     * @param array $definition
     * @return Definition
     */
    public function column(string $column, array $definition = []): Definition
    {
        if(!isset($this->definitions[$column])) {
            $this->definitions[$column] = new Definition($column, $definition);
        }
        return $this->definitions[$column];
    }

    /**
     * Rename a column
     *
     * @param string $column
     * @param string $newName
     * @return self
     */
    public function rename(string $column, string $newName): self
    {
        if(isset($this->definitions[$column])) {
            $this->definitions[$newName] = $this->definitions[$column];
            unset($this->definitions[$column]);
            $this->definitions[$newName]->rename($newName);
        }
        // $sql = "ALTER TABLE `{$this->table}` CHANGE `{$column}` `{$newName}` {$this->definitions[$newName]->define()['Type']}";
        // $this->connector->query($sql);
        return $this;
    }

    /**
     * Remove a column
     *
     * @param string $column
     * @return self
     */
    public function remove(string $column): self
    {
        if(isset($this->definitions[$column])) {
            unset($this->definitions[$column]);
        }
        return $this;
    }

    /**
     * Save the definition to a file
     *
     * @return self
     */
    public function save(): self
    {
        $definitions = [];
        foreach ($this->definitions as $definition) {
            $definitions[] = $definition->define();
        }
        $definitions['engine'] = $this->engine;
        $definitions['charset'] = $this->charset;
        $definitions['collation'] = $this->collation;
        $path = $this->path . DIRECTORY_SEPARATOR . $this->table . '.map';
        if(!is_dir($this->path)) { mkdir($this->path, 0777, true); }
        file_put_contents($path, json_encode($definitions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $this;
    }

    /**
     * Compare the in-memory definition with the actual DB structure.
     *
     * If $asQueries = false, return a descriptive array of differences.
     * If $asQueries = true, return an array of SQL statements that would
     * apply the changes needed to sync the DB to the in-memory definitions.
     *
     * @param bool $asQueries
     * @return array
     */
    public function compare(bool $asQueries = false): array
    {
        // Make sure we have a table name
        if (empty($this->table)) {
            return [];
        }

        // Get columns from DB
        $dbCols = $this->connector->describe($this->table);
        $dbMap = [];
        foreach ($dbCols as $col) {
            $dbMap[$col['Field']] = $col;
        }

        // Get columns from memory
        $memoryMap = [];
        foreach ($this->definitions as $definitionObj) {
            $arr = $definitionObj->define();
            $memoryMap[$arr['Field']] = $arr;
        }

        // We will accumulate a list of changes (descriptive or queries)
        $changes = [];

        // 1) Handle Renamed Columns
        //    If in-memory column has "Previously" => we interpret that as "rename oldName -> newName"
        //    if oldName actually exists in $dbMap, we know it's a rename.
        //    Then we can remove oldName from $dbMap to avoid also dropping it below.
        foreach ($memoryMap as $newName => $memDef) {
            if (!empty($memDef['Previously']) && isset($dbMap[$memDef['Previously']])) {
                $oldName = $memDef['Previously'];
                if (isset($dbMap[$oldName]) && !isset($changes[$newName])) {
                    // It's truly a rename from $oldName to $newName
                    if ($asQueries) {
                        $changes[$newName] = $this->buildRenameQuery($oldName, $newName, $memDef);
                    } else {
                        $changes[$newName] = [
                            'action'   => 'rename',
                            'oldName'  => $oldName,
                            'newName'  => $newName,
                            'details'  => $memDef
                        ];
                    }
                    // We handle the rename by removing the oldName from $dbMap
                    // and also removing $newName from $memoryMap so we don't handle it again
                    unset($dbMap[$oldName]);
                    // (We won't remove from $memoryMap here, because we need it for next steps—some prefer to do so, but you can also mark it somehow.)
                }
            }
        }

        // 2) Handle Added Columns
        //    If a column is in memoryMap but not in $dbMap => it's a new column
        foreach ($memoryMap as $colName => $memDef) {
            if (!isset($dbMap[$colName]) && !isset($changes[$colName])) {
                // We skip columns that are flagged as a rename
                // if $memDef['Previously'] was set but didn't match a DB col, this might be a rename from a non-existing col
                // We'll treat it as "add" anyway
                if ($asQueries) {
                    $changes[$colName] = $this->buildAddColumnQuery($colName, $memDef);
                } else {
                    $changes[$colName] = [
                        'action'  => 'add',
                        'column'  => $colName,
                        'details' => $memDef
                    ];
                }
            }
        }

        // 3) Handle Dropped Columns
        //    If a column is in DB but not in memory => it's removed
        //    (unless it's renamed, which we handled earlier by unsetting from $dbMap)
        foreach ($dbMap as $colName => $dbDef) {
            if (!isset($memoryMap[$colName]) && !isset($changes[$colName])) {
                if ($asQueries) {
                    $changes[$colName] = "ALTER TABLE `{$this->table}` DROP COLUMN `{$colName}`";
                } else {
                    $changes[$colName] = [
                        'action' => 'drop',
                        'column' => $colName,
                        'dbDef'  => $dbDef
                    ];
                }
            }
        }

        // 4) Handle Modified Columns
        //    If a column is both in DB and in memory (and not renamed), check differences (type, null, default, etc.)
        //    Then build a "modify" or "change" query if there's a difference
        foreach ($memoryMap as $colName => $memDef) {
            if (isset($dbMap[$colName]) && !isset($changes[$colName])) {
                // Compare details
                $dbDef = $dbMap[$colName];
                // We'll do a simple comparison on "Type", "Null", "Default", "Extra", "Key"
                $diff = $this->compareColumnDefs($dbDef, $memDef);
                if (!empty($diff)) {
                    // We have differences
                    if ($asQueries) {
                        // A "MODIFY COLUMN" or "CHANGE COLUMN" statement can be used.
                        // Typically use "MODIFY" if we keep the same col name
                        // We'll build a "MODIFY" statement
                        $changes[$colName] = $this->buildModifyColumnQuery($colName, $memDef);
                    } else {
                        $changes[$colName] = [
                            'action' => 'modify',
                            'column' => $colName,
                            'dbDef'  => $dbDef,
                            'memDef' => $memDef,
                            'diff'   => $diff
                        ];
                    }
                }
            }
        }

        return $changes;
    }

    /**
     * Compare two column definitions (DB vs memory).
     * Return an array of differences or an empty array if none.
     *
     * For simplicity, we only compare keys we care about:
     * 'Type', 'Null', 'Default', 'Extra', 'Key'.
     */
    private function compareColumnDefs(array $dbDef, array $memDef): array
    {
        $keysToCheck = array_keys($dbDef);
        $diff = [];
        foreach ($keysToCheck as $key) {
            // Normalize or parse if needed, e.g. 'int(11)' vs 'int(10)'
            // For now, let's do a plain string compare
            $dbVal  = isset($dbDef[$key])  ? $dbDef[$key]  : null;
            $memVal = isset($memDef[$key]) ? $memDef[$key] : null;
            if ($dbVal != $memVal) {
                $diff[$key] = ["db"=>$dbVal, "mem"=>$memVal];
            }
        }
        return $diff;
    }

    /**
     * Build the SQL to rename a column.
     * e.g. ALTER TABLE tableName CHANGE oldName newName newDefinition
     */
    private function buildRenameQuery(string $oldName, string $newName, array $memDef): string
    {
        $colDefSQL = $this->buildColumnSQL($memDef, $newName);
        return "ALTER TABLE `{$this->table}` CHANGE `{$oldName}` `{$newName}` {$colDefSQL}";
    }

    /**
     * Build the SQL to add a new column.
     * e.g. ALTER TABLE tableName ADD COLUMN colName colDefinition
     */
    private function buildAddColumnQuery(string $colName, array $memDef): string
    {
        $colDefSQL = $this->buildColumnSQL($memDef, $colName);
        return "ALTER TABLE `{$this->table}` ADD COLUMN {$colDefSQL}";
    }

    /**
     * Build the SQL to modify an existing column (same name, changed type, etc).
     * e.g. ALTER TABLE tableName MODIFY COLUMN colName colDefinition
     */
    private function buildModifyColumnQuery(string $colName, array $memDef): string
    {
        $colDefSQL = $this->buildColumnSQL($memDef, $colName);
        return "ALTER TABLE `{$this->table}` MODIFY COLUMN {$colDefSQL}";
    }

    /**
     * Build the column definition snippet, e.g.
     *   `age` int(11) NOT NULL DEFAULT '0' AUTO_INCREMENT
     *
     * If the column is 'NULL' then we do "NULL" instead of "NOT NULL".
     */
    private function buildColumnSQL(array $memDef, string $colName): string
    {
        // We'll need:
        // - column name
        // - type
        // - null vs not null
        // - default
        // - extra (auto_increment / on update CURRENT_TIMESTAMP)
        // Possibly also Key, but in MySQL you'd typically do PK or unique in separate statements,
        // or in the create statement. For an "ALTER" approach, we might skip 'Key' here.

        $type = $memDef['Type'] ?? 'varchar(255)';

        // Null / not null
        $null = (isset($memDef['Null']) && $memDef['Null'] === 'YES') ? "NULL" : "NOT NULL";

        // Default
        $defaultSQL = "";
        if(!in_array($type, ['tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob'])) {
            if (array_key_exists('Default', $memDef)) {
                $defaultVal = $memDef['Default'];
                if ($defaultVal === 'CURRENT_TIMESTAMP') {
                    $defaultSQL = "DEFAULT CURRENT_TIMESTAMP";
                } else {
                    if ($defaultVal === null && $null === "NULL") {
                        $defaultSQL = "DEFAULT NULL";
                    } elseif ($defaultVal !== null) {
                        $escaped = addslashes($defaultVal);
                        $defaultSQL = "DEFAULT '{$escaped}'";
                    }
                }
            }
        }

        // Extra
        $extra = "";
        if (!empty($memDef['Extra'])) {
            // e.g. "auto_increment" or "on update CURRENT_TIMESTAMP"
            $extra = strtoupper($memDef['Extra']);
        }

        return "`{$colName}` {$type} {$null}"
               . (empty($defaultSQL) ? "" : " {$defaultSQL}")
               . (empty($extra) ? "" : " {$extra}");
    }

    /**
     * Build the CREATE TABLE SQL, e.g.
     *  CREATE TABLE `tableName` (
     *
     * @return string
     */
    private function buildCreate(): string
    {
        $columnLines = [];
        foreach ($this->definitions as $definition) {
            $columnLines[] = $this->buildColumnSQL($definition->define(), $definition->define()['Field']);
        }

        // Check if we have primary keys
        $primaryKeys = [];
        foreach ($this->definitions as $definition) {
            if (isset($definition->define()['Key']) && $definition->define()['Key'] === 'PRI') {
                $primaryKeys[] = $definition->define()['Field'];
            }
        }

        // If we have primary keys, add a line for them
        if (!empty($primaryKeys)) {
            $pk = "`" . implode("`,`", $primaryKeys) . "`";
            $columnLines[] = "PRIMARY KEY ($pk)";
        }

        // For demonstration, assume InnoDB engine
        // In real usage, you might store the engine in definition or config
        $columnsSQL = implode(",\n  ", $columnLines);
        $sql = "CREATE TABLE `{$this->table}` (\n  {$columnsSQL}\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->charset}_{$this->collation}";
        return $sql;
    }

    /**
     * Drop the table if it exists
     *
     * @return self
     * @throws Exception
     */
    public function drop(): self
    {
        if (empty($this->table)) {
            throw new Exception("No table defined.");
        }
        $sql = "DROP TABLE IF EXISTS `{$this->table}`";
        $this->connector->query($sql);

        return $this;
    }

    /**
     * Truncate the table
     *
     * @return self
     * @throws Exception
     */
    public function truncate(): self
    {
        if (empty($this->table)) {
            throw new Exception("No table defined.");
        }
        $sql = "TRUNCATE TABLE `{$this->table}`";
        $this->connector->query($sql);

        return $this;
    }

    /**
     * Update the table
     *
     * @return self
     */
    public function update(): self
    {
        $changes = $this->compare(true);
        foreach ($changes as $change) {
            $this->connector->query($change);
        }
        return $this;
    }

    /**
     * Create the table
     *
     * @return self
     */
    public function create(): self
    {
        $sql = $this->buildCreate();
        $this->connector->query($sql);
        return $this;
    }

    /**
     * Return if the table exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        $sql = "SHOW TABLES LIKE '{$this->table}'";
        $result = $this->connector->query($sql);
        return $result->num_rows > 0;
    }
}
