<?php

/**
 * Core Framework - Query
 *
 * @author     LaswitchTech <support@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Connector;
use Exception;

class Query {

    /**
     * List of valid SQL operators
     *
     * @var array
     */
    const operators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IS NULL', 'IS NOT NULL', 'CONTAINS'];

    /**
     * List of valid SQL conjunctions
     *
     * @var array
     */
    const conjunctions = ['AND', 'OR'];

    /**
     * List of valid SQL directions
     *
     * @var array
     */
    const directions = ['ASC', 'DESC'];

    /**
     * List of valid SQL join types
     *
     * @var array
     */
    const types = ['LEFT', 'RIGHT', 'INNER', 'FULL', 'SELF'];

    /**
     * @var Connector
     */
    private $connector;

    /**
     * Query type: 'select', 'insert', 'update', 'delete'
     *
     * @var string
     */
    private $type;

    /**
     * Fields to select (for SELECT queries)
     *
     * @var string
     */
    private $fields = '*';

    /**
     * Table name
     *
     * @var string
     */
    private $table;

    /**
     * Index column
     *
     * @var string
     */
    private $index;

    /**
     * Limit of rows to return
     *
     * @var int
     */
    private $limit;

    /**
     * Filter key
     *
     * @var int
     */
    private $filter;

    /**
     * Array of WHERE clauses
     *
     * @var array
     */
    private $where = [];

    /**
     * Array of JOIN clauses
     *
     * @var array
     */
    private $join = [];

    /**
     * Array of ORDER BY clauses
     *
     * @var array
     */
    private $order = [];

    /**
     * Key/value pairs for INSERT/UPDATE
     *
     * @var array
     */
    private $values = [];

    /**
     * Parameters for INSERT/UPDATE
     *
     * @var array
     */
    private $params = [];

    /**
     * Count distinct flag
     *
     * @var bool
     */
    private $countDistinct = false;

    /**
     * Constructor
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Return the SQL query string
     */
    public function __toString()
    {
        return $this->buildQuery();
    }

    /**
     * FROM clause
     *
     * @param string $table
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * INSERT query
     *
     * @param array  $data
     * @return self
     */
    public function insert(array $data): self
    {
        $this->type = 'insert';
        $this->values = $data;
        return $this;
    }

    /**
     * SELECT query
     *
     * @param string $fields
     * @return self
     */
    public function select(string $fields = '*'): self
    {
        $this->type = 'select';
        $this->fields = $fields;
        return $this;
    }

    /**
     * COUNT query
     *
     * @param string $column
     * @param bool   $distinct
     * @return self
     */
    public function count(string $column = '*', bool $distinct = false): self
    {
        $this->type = 'count';
        $this->fields = $column;
        $this->countDistinct = $distinct;
        return $this;
    }

    /**
     * UPDATE query
     *
     * @param array  $data
     * @return self
     */
    public function update(array $data): self
    {
        $this->type = 'update';
        $this->values = $data;
        return $this;
    }

    /**
     * DELETE query
     *
     * @return self
     */
    public function delete(): self
    {
        $this->type = 'delete';
        return $this;
    }

    /**
     * JOIN clause
     *
     * @param string $table
     * @param string $on
     * @param string $type
     * @return self
     */
    public function join(string $key, string $table, string $column, string $operator = '=', string $type = 'LEFT'): self
    {
        $operator = (in_array($operator,self::operators)) ? $operator : '=';
        $type = (in_array($type,self::types)) ? $type : 'LEFT';

        // Check if there is a parent key
        $parent = null;
        if (strpos($key, '.') !== false) {
            [$parent, $key] = explode('.', $key, 2);
        }

        $this->join[] = [
            'parent' => $parent,
            'table' => $table,
            'column' => $column,
            'key' => $key,
            'operator' => $operator,
            'type' => $type
        ];
        return $this;
    }

    /**
     * WHERE initiator
     *
     * @param string $conjunction
     * @return self
     */
    public function filter(string $conjunction = 'AND'): self
    {
        $conjunction = strtoupper($conjunction);
        $conjunction = (in_array($conjunction,self::conjunctions)) ? $conjunction : 'AND';

        $this->where[] = [
            'conjunction' => $conjunction,
            'clauses' => []
        ];
        $this->filter = count($this->where) - 1;
        return $this;
    }

    /**
     * WHERE clause
     *
     * @param string $column
     * @param mixed  $value
     * @param string $operator
     * @param string $conjunction
     * @return self
     */
    public function where(string $column, $value, ?string $operator = null, string $conjunction = 'AND'): self
    {
        $operator = (in_array($operator,self::operators)) ? $operator : '=';
        $conjunction = (in_array($conjunction,self::conjunctions)) ? $conjunction : 'AND';

        if(empty($this->where)){
            $this->filter();
        }
        $this->where[$this->filter]['clauses'][] = [
            'conjunction' => $conjunction,
            'operator' => $operator,
            'column' => $column,
            'value' => $value
        ];
        return $this;
    }

    /**
     * ORDER BY clause
     *
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function order(string $column, string $direction = 'ASC'): self
    {
        $direction = (in_array($direction,self::directions)) ? $direction : 'ASC';

        $this->order[] = [
            'column' => $column,
            'direction' => $direction
        ];
        return $this;
    }

    /**
     * INDEX clause
     *
     * @param string $column
     * @return self
     */
    public function index(string $column): self
    {
        $this->index = $column;
        return $this;
    }

    /**
     * LIMIT clause
     *
     * @param int $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Execute the query and return the result
     *
     * For a SELECT, returns an array of rows.
     * For INSERT, UPDATE, DELETE, returns the number of affected rows.
     *
     * @return mixed
     * @throws Exception
     */
    public function result()
    {
        $sql = $this->buildQuery();
        $statement = $this->connector->prepare($sql, $this->params);
        if ($this->type === 'select') {
            $result = $statement->get_result();
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $this->buildRows($rows);
        }
        if ($this->type === 'count') {
            $result = $statement->get_result();
            $row = $result->fetch_assoc();
            return (int) ($row['t__count'] ?? 0);
        }
        return $this->connector->affectedRows();
    }

    /**
     * Execute the query and return the result
     *
     * Alias for result()
     *
     * For a SELECT, returns an array of rows.
     * For INSERT, UPDATE, DELETE, returns the number of affected rows.
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->result();
    }

    /**
     * Execute the query and return the result
     *
     * Alias for result()
     *
     * For a SELECT, returns an array of rows.
     * For INSERT, UPDATE, DELETE, returns the number of affected rows.
     *
     * @return mixed
     */
    public function fetch()
    {
        return $this->result();
    }

    /**
     * Resolve column specification
     *
     * @param string $spec
     * @return string
     */
    private function resolveColumn(string $spec): string
    {
        if ($spec === '*') return '*';

        if (strpos($spec, '.') === false) {
            return "t.`{$spec}`";
        }

        $parts = explode('.', $spec);
        if ($parts[0] === 't' && count($parts) === 2) {
            return "t.`{$parts[1]}`";
        }

        $column = array_pop($parts);
        $alias  = 'j__' . implode('__', $parts);
        return "`{$alias}`.`{$column}`";
    }

    /**
     * Build the rows array
     *
     * @param array $results
     * @return array
     */
    private function buildRows($results)
    {
        $rows = [];
        foreach ($results as $result){
            $row = [];
            foreach ($result as $key => $value){
                if (strpos($key, 't__') === 0) {
                    if(!isset($row[substr($key, 3)])){
                        $row[substr($key, 3)] = $value;
                    }
                } else {
                    $parts = explode('__', substr($key, 3));
                    $column = array_pop($parts);
                    $key    = implode('__', $parts);
                    // Create the nested structure step-by-step
                    $ref = &$row;
                    foreach ($parts as $segment) {
                        if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                            $ref[$segment] = [];
                        }
                        $ref = &$ref[$segment];
                    }
                    $ref[$column] = $value;
                    unset($ref);
                }
            }
            if($this->index && isset($result['t__'.$this->index])){
                $rows[$result['t__'.$this->index]] = $row;
            } else {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Return the ID of the last inserted record
     *
     * @return int
     */
    public function lastId(): int
    {
        return $this->connector->lastId();
    }

    /**
     * Build the SQL query from the given parameters
     *
     * @return string
     */
    private function buildQuery(): string
    {
        $this->params = [];
        switch ($this->type) {
            case 'select':
                $sql = "SELECT {$this->buildFields()} FROM `{$this->table}` AS t";
                if (!empty($this->join)) {
                    $sql .= ' ' . $this->buildJoin();
                }
                if (!empty($this->where)) {
                    $sql .= ' WHERE ' . $this->buildWhere();
                }
                if (!empty($this->order)) {
                    $sql .= ' ORDER BY ' . $this->buildOrder();
                }
                if ($this->limit) {
                    $sql .= " LIMIT {$this->limit}";
                }
                return $sql;

            case 'count':
                $col = $this->resolveColumn($this->fields);
                $countExpr = ($this->countDistinct && $col !== '*') ? "DISTINCT {$col}" : $col;
                $sql = "SELECT COUNT({$countExpr}) AS `t__count` FROM `{$this->table}` AS t";
                if (!empty($this->join)) {
                    $sql .= ' ' . $this->buildJoin();
                }
                if (!empty($this->where)) {
                    $sql .= ' WHERE ' . $this->buildWhere();
                }
                return $sql;

            case 'insert':
                $columns = array_keys($this->values);
                foreach ($columns as $key => $column) {
                    $columns[$key] = "`{$column}`";
                }
                $columns = implode(',', $columns);
                $values = implode(',', array_map([$this, 'addParam'], array_values($this->values)));
                return "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$values})";

            case 'update':
                $sets = [];
                foreach ($this->values as $col => $val) {
                    $sets[] = "`{$col}` = " . $this->addParam($val);
                }
                $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets);
                if (!empty($this->where)) {
                    $sql .= ' WHERE ' . $this->buildWhere();
                }
                return $sql;

            case 'delete':
                $sql = "DELETE FROM `{$this->table}`";
                if (!empty($this->where)) {
                    $sql .= ' WHERE ' . $this->buildWhere();
                }
                return $sql;
        }

        throw new Exception("Invalid query type: {$this->type}");
    }

    /**
     * Build the Fields clause
     *
     * @return string
     */
    private function buildFields(): string
    {
        if($this->fields == '*'){
            $fields = [];
            $columns = $this->connector->describe($this->table);
            foreach ($columns as $column) {
                $fields[] = "t.`{$column['Field']}` AS `t__{$column['Field']}`";
            }
            foreach ($this->join as $join) {
                $alias = $join['parent'] ? "j__{$join['parent']}__{$join['key']}" : "j__{$join['key']}";
                $columns = $this->connector->describe($join['table']);
                foreach ($columns as $column) {
                    $fields[] = "{$alias}.`{$column['Field']}` AS `{$alias}__{$column['Field']}`";
                }
            }
        } else {
            $fields = explode(',', $this->fields);
            foreach ($fields as $key => $field) {
                $field = trim($field);
                if(strpos($field, '.') !== false){
                    $parts = explode('.', $field);
                    $joint = $parts[0];
                    $column = $parts[1];
                    $fields[$key] = "`j__{$joint}`.`{$column}` AS `j__{$joint}__{$column}`";
                } else {
                    $fields[$key] = "t.`{$field}` AS `t__{$field}`";
                }
            }
        }
        return implode(', ', $fields);
    }

    /**
     * Build the JOIN clause
     *
     * @return string
     */
    private function buildJoin(): string
    {
        $clauses = [];
        foreach ($this->join as $join) {
            $leftAlias = $join['parent'] ? "`j__{$join['parent']}`" : 't';
            $alias = $join['parent'] ? "j__{$join['parent']}__{$join['key']}" : "j__{$join['key']}";
            $clauses[] = "{$join['type']} JOIN `{$join['table']}` AS `{$alias}` " . "ON {$leftAlias}.`{$join['key']}` {$join['operator']} " . "`{$alias}`.`{$join['column']}`";
        }
        return implode(' ', $clauses);
    }

    /**
     * Build the WHERE clause
     *
     * @return string
     */
    private function buildWhere(): string
    {
        $filters = '';
        foreach ($this->where as $filter => $where) {
            $clauses = [];
            foreach ($where['clauses'] as $i => $condition) {
                $conjunction = $condition['conjunction'];
                if (in_array($this->type, ['select','count'], true)) {
                    $spec = $condition['column'];
                    if (strpos($spec, '.') === false) {
                        $col = "t.`{$spec}`";
                    } else {
                        $parts = explode('.', $spec);
                        if ($parts[0] === 't' && count($parts) === 2) {
                            $col = "t.`{$parts[1]}`";
                        } else {
                            $column = array_pop($parts);
                            $alias  = 'j__' . implode('__', $parts);
                            $col    = "`{$alias}`.`{$column}`";
                        }
                    }
                } else {
                    $col = "`{$condition['column']}`";
                }
                $op = $condition['operator'];
                $condition['value'] = ($op === 'CONTAINS') ? json_encode($condition['value']) : $condition['value'];
                $val = (in_array($op, ['IS NULL', 'IS NOT NULL'])) ? '' : $this->addParam($condition['value']);
                $clause = ($op == 'CONTAINS') ? "JSON_CONTAINS({$col}, {$val})" : "{$col} {$op} {$val}";
                $clauses[] = ($i > 0) ? "{$conjunction} {$clause}" : $clause;
            }
            $filters .= ($filter > 0) ? " {$where['conjunction']} " : '';
            $filters .= "(".implode(' ', $clauses).")";
        }
        return $filters;
    }

    /**
     * Build the ORDER BY clause
     *
     * @return string
     */
    private function buildOrder(): string
    {
        $clauses = [];
        foreach ($this->order as $order) {
            $spec = $order['column'];
            if (strpos($spec, '.') === false) {
                $clauses[] = "t.`{$spec}` {$order['direction']}";
            } else {
                $parts = explode('.', $spec);
                if ($parts[0] === 't' && count($parts) === 2) {
                    $clauses[] = "t.`{$parts[1]}` {$order['direction']}";
                } else {
                    $column = array_pop($parts);
                    $alias  = 'j__' . implode('__', $parts);
                    $clauses[] = "`{$alias}`.`{$column}` {$order['direction']}";
                }
            }
        }
        return implode(', ', $clauses);
    }

    /**
     * Add a parameter to the list and return a placeholder
     *
     * @param mixed $value
     * @return string
     */
    private function addParam($value): string
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
        $this->params[] = $value;

        return '?';
    }

    /**
     * Set the auto increment value
     *
     * @param int $int
     */
    public function autoIncrement(int $int): void
    {
        if (method_exists($this->connector, 'autoIncrementSQL')) {
            $sql = $this->connector->autoIncrementSQL($this->table, $int);
        } else {
            // MySQL default
            $sql = "ALTER TABLE `{$this->table}` AUTO_INCREMENT = {$int}";
        }
        $this->connector->query($sql);
    }
}
