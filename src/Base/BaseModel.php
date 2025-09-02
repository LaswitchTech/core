<?php

// Declaring namespace
namespace LaswitchTech\Core\Base;

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Model;
use Exception;

abstract class BaseModel extends Model {

    // Global Properties
    protected $Auth;

    // Properties
    protected $table;
    protected $tables = [];
    protected $primary;
    protected $schema;
    protected $definition;
    protected $definitions = [];

    // Map MySQL base types -> PHP cast callable
    protected $typeMap = [
        // integers
        'int'      => 'intval',
        'integer'  => 'intval',
        'tinyint'  => 'intval',
        'smallint' => 'intval',
        'mediumint'=> 'intval',
        'bigint'   => 'intval',

        // floating point / fixed
        'float'    => 'floatval',
        'double'   => 'floatval',
        'decimal'  => 'floatval',

        // booleans (MySQL often stores them as TINYINT(1))
        'bool'     => 'boolval',
        'boolean'  => 'boolval',

        // json → decode to array / object, keep original on failure
        'json'     => [self::class, 'castJson'],

        // date/time → DateTimeImmutable (custom helper)
        'datetime' => [self::class, 'castDateTime'],
        'timestamp'=> [self::class, 'castDateTime'],
        'date'     => [self::class, 'castDateTime'],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Import Global Variables
        global $AUTH;

        // Configure the Global Properties
        $this->Auth = $AUTH;
    }

    /**
     * Initialize the Model
     *
     * @param string $table
     * @param string|null $primary
     * @return void
     */
    protected function init(string $table, ?string $primary = 'id'): void
    {
        // Set the table name
        $this->table = $table;

        // Set the primary key
        $this->primary = $primary;

        // Retrieve the tables list
        $this->tables = $this->Database->schema()->tables();

        // Initialize the Model
        $this->schema = $this->Database->schema()->define($this->table);

        // Describe the table
        foreach($this->schema->describe() as $column){
            $this->definition[$column['Field']] = $column;

            // Exclude fields
            if(in_array(strtolower($column['Field']), ['id', 'created', 'modified', 'isarchived', 'iscompleted', 'targettable', 'targetid'])) continue;

            // Set the table
            $table = in_array($column['Field'],['owner', 'assignedTo']) ? 'users' : $column['Field'] . 's';

            // Check if the field is linked to a table
            if(in_array($table, $this->tables)){

                // Initialize the Schema
                $schema = $this->Database->schema()->define($table);

                // Describe the table
                foreach($schema->describe() as $col){

                    // Add the col to the definition
                    $this->definition[$column['Field'].'.'.$col['Field']] = $col;
                }
            };
        }

        // Loop through the additional tables to join
        foreach($this->definition as $field => $col){

            // // Exclude fields
            // if(in_array(strtolower($field), ['id', 'created', 'modified', 'isarchived', 'iscompleted', 'targettable', 'targetid'])) continue;

            // // Set the fieldTable
            // $fieldTable = in_array($field,['owner', 'assignedTo']) ? 'users' : $field . 's';

            // Initialize the Schema
            $schema = $this->Database->schema()->define($fieldTable);

            // Describe the table
            foreach($schema->describe() as $column){

                // // Add the column to the definition
                // $this->definition[$field.'.'.$column['Field']] = $column;

                // // Set the fieldTable
                // $nestedTable = in_array($field,['owner', 'assignedTo']) ? 'users' : $field . 's';

                // Check if the field is a complex field
                if(in_array($nestedTable, $tables)){

                    // Initialize the Schema
                    $nestedSchema = $this->Database->schema()->define($nestedTable);

                    // Describe the table
                    foreach($nestedSchema->describe() as $nestedColumn){

                        // Add the nestedColumn to the definition
                        $this->definition[$field.'.'.$column['Field'].'.'.$nestedColumn['Field']] = $nestedColumn;
                    }
                };
            }
        }
    }

    /**
     * Check if a value is a valid JSON string
     *
     * @param string $value
     * @return bool
     */
    protected function isJson(mixed $value, ?string $key = null): bool
    {
        // Check if the function json_validate exists
        if (function_exists('json_validate')) {
            return json_validate($value);
        }

        // Check if the value is empty or not a string
        if ($value === null || $value === '' || !is_string($value)) {
            return false;
        }

        // Check if the key is set in the definition
        if ($key === null || !array_key_exists($key, $this->definition)){

            // Attempt to decode the JSON value
            $data = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($data));
        } else {

            // Retrieve the field type from the definition
            $rawType  = strtolower($this->definition[$key]['Type']);
            preg_match('/^[a-z]+/', $rawType, $m);
            $baseType = $m[0] ?? '';

            // Check if the base type is json
            if ($baseType === 'json') {
                // Attempt to decode the JSON value
                $data = json_decode($value, true);
                return (json_last_error() === JSON_ERROR_NONE && is_array($data));
            } else {
                // If the base type is not json, return false
                return false;
            }
        }
    }

    /**
     * Retrieve a single record by ID
     *
     * @param int $id
     * @return array
     */
    protected function read(string $table, int $id): array
    {
        // Check if the definition is already cached
        if(!array_key_exists($table, $this->definitions)){

            // Create the Schema
            $this->definitions[$table] = [];

            // Describe the table
            foreach($this->Database->schema()->define($table)->describe() as $column){
                $this->definitions[$table][$column['Field']] = $column;
            }
        }

        // Create the Query
        $Query = $this->Database->query()
            ->table($table)
            ->select('*')
            ->where('id', $id)
            ->limit(1);

        // Check if the table has a particular column and join if necessary
        if(array_key_exists('organization', $this->definitions[$table])){
            $Query->join('organization', 'organizations', 'id');
            $Query->join('organization.vcard', 'vcards', 'id');
        }
        if(array_key_exists('lead', $this->definitions[$table])){
            $Query->join('lead', 'leads', 'id');
            $Query->join('lead.vcard', 'vcards', 'id');
        }
        if(array_key_exists('client', $this->definitions[$table])){
            $Query->join('client', 'clients', 'id');
            $Query->join('client.vcard', 'vcards', 'id');
        }
        if(array_key_exists('vcard', $this->definitions[$table])){
            $Query->join('vcard', 'vcards', 'id');
        }
        if(array_key_exists('contact', $this->definitions[$table])){
            $Query->join('contact', 'contacts', 'id');
            $Query->join('contact.vcard', 'vcards', 'id');
        }
        if(array_key_exists('assignedTo', $this->definitions[$table])){
            $Query->join('assignedTo', 'users', 'id');
        }

        // Retrieve the Record
        $record = $Query->fetch();

        // Check if the Record exists
        if($record){

            // Set the record to the first element
            $record= $record[array_key_first($record)];

            // Check if a target is set
            if(array_key_exists('targetTable', $record) && array_key_exists('targetId', $record)){

                // Retrieve the Target
                $record['target'] = $this->read($record['targetTable'], $record['targetId']);
            }
        }

        // Return an empty array if not found
        return $record;
    }

    /**
     * Retrieve the target tree of a record
     *
     * @param array $record
     * @return array
     */
    protected function target(array $record): array
    {
        // Check if the targetTable and targetId are set
        if(array_key_exists('targetTable', $record) && array_key_exists('targetId', $record)){

            // Retrieve the Target
            $target = $this->read($record['targetTable'], $record['targetId']);

            // Check if the targetTable and targetId are set
            if(array_key_exists('targetTable', $target) && array_key_exists('targetId', $target)){

                // Retrieve the Target
                $target['target'] = $this->target($target);
            }

            // Save the target in the record
            $record['target'] = $target;
        }

        // Return the record with the target
        return $record;
    }

    /**
     * Walk to the top-most parent (“root”) and attach it to the record.
     *
     * @param  array $record
     * @return array
     */
    protected function root(array $record): array
    {
        // If the record has already been processed elsewhere and a root exists,
        // keep the existing one.
        if (array_key_exists('root', $record)) {
            return $record;
        }

        $visited  = [];          // cycle protection
        $current  = $record;
        $maxDepth = 20;          // safety guard against runaway chains
        $depth    = 0;

        while (
            $depth < $maxDepth &&
            !empty($current['targetTable']) &&
            !empty($current['targetId'])
        ) {
            $key = $current['targetTable'] . ':' . (int)$current['targetId'];

            // Circular reference ⇒ stop here.
            if (isset($visited[$key])) {
                break;
            }
            $visited[$key] = true;

            // Fetch the parent.
            $parent = $this->read($current['targetTable'], (int)$current['targetId']);

            // If not found, abort — the current node is as high as we can go.
            if (empty($parent)) {
                break;
            }

            $currentTargetTable = $current['targetTable'] ?? null;
            $currentTargetId = $current['targetId'] ?? null;
            $current = $parent;

            ++$depth;
        }

        // Save the discovered root (could be the same as the original record).
        $record['root'] = [
            "target" => $current,
            "targetTable" => $currentTargetTable ?? null,
            "targetId" => $currentTargetId ?? ($current['id'] ?? null),
        ];

        return $record;
    }

    /**
     * Process a record
     *
     * @param array $record
     * @return array
     */
    protected function process(array $record): array
    {
        // Decode JSON Fields
        foreach($record as $key => $value){

            // Check if the value is a valid JSON string
            if($this->isJson($value, $key)){

                // Decode the JSON value
                $value = json_decode($value, true);
            }

            // Set the value back to the record
            $record[$key] = $value;
        }

        // Retrieve the Target
        if(array_key_exists('targetTable', $record) && array_key_exists('targetId', $record)){
            $record = $this->target($record);
            $record = $this->root($record);
        }

        // Return the processed record
        return $record;
    }

    /**
     * Sanitize the data before inserting or updating
     *
     * @param array $data
     * @return array
     */
    protected function sanitize(array $data): array
    {

        // Loop through the data and cast types
        foreach ($data as $key => $value) {
            // ──1. Get base type («bigint(20) unsigned» ➜ «bigint»)
            if (!isset($this->definition[$key]['Type'])) {
                continue; // unknown column, leave untouched
            }
            $rawType  = strtolower($this->definition[$key]['Type']);
            preg_match('/^[a-z]+/', $rawType, $m);
            $baseType = $m[0] ?? '';

            // ──2. Cast using lookup table or fallback
            if (isset($this->typeMap[$baseType])) {
                $caster      = $this->typeMap[$baseType];
                $data[$key]  = is_callable($caster) ? $caster($value) : $value;
            } else {
                $data[$key] = (string) $value;   // sensible default
            }
        }

        return $data;
    }

    /**
     * Cast a JSON string to an array or object, or return the original string on error.
     *
     * @param string $v
     * @return mixed
     */
    protected static function castJson($v): mixed
    {
        $decoded = json_decode($v, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $v;
    }

    /**
     * Turn anything MySQL gives us into a DateTimeImmutable or null on error.
     *
     * @param string|null $value
     * @return \DateTimeImmutable|null
     */
    protected static function castDateTime($value): ?string
    {
        if ($value === null || $value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        try {
            $DateTime = new \DateTimeImmutable($value);
            return $DateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null; // or keep original string if you prefer
        }
    }

    /**
     * Create a new record and return the id
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        // Set the Owner
        if(array_key_exists('owner',$this->definition)){
            $data['owner'] = $this->Auth->user()->username;
        }

        // Set the Owner
        if(array_key_exists('organization',$this->definition)){
            $data['organization'] = $this->Auth->user()->organization()->id;
        }

        // Sanitize the Data
        foreach($data as $key => $value){

            // Check if the key exists in the definition
            if(!array_key_exists($key, $this->definition)){

                // Remove the key from the data
                unset($data[$key]);
                continue;
            }

            // Check if the value is an array and encode it as JSON
            if(is_array($value)){
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            // Set the value back to the data array
            $data[$key] = $value;
        }

        // Sanitize the data
        $data = $this->sanitize($data);

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->table)
            ->insert($data);

        // Execute the Query
        $affectedRows = $Query->execute();

        // Execute the Query
        return $Query->lastId();
    }

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        // Sanitize the Data
        foreach($data as $key => $value){

            // Check if the key exists in the definition
            if(!array_key_exists($key, $this->definition)){

                // Remove the key from the data
                unset($data[$key]);
                continue;
            }

            // Skip the owner and organization fields
            if(in_array($key, ['owner', 'organization'])){

                // Remove the key from the data
                unset($data[$key]);
                continue;
            }

            // Check if the value is an array and encode it as JSON
            if(is_array($value)){
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            // Set the value back to the data array
            $data[$key] = $value;
        }

        // Sanitize the data
        $data = $this->sanitize($data);

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->table)
            ->update($data)
            ->where($this->primary, $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Delete a record
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table($this->table)
            ->delete()
            ->where($this->primary, $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Retrieve the count of records
     *
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = [], string $conjunction = 'AND'): int
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table($this->table)
            ->select($this->primary)
            ->where('id', 9999, '<>')
            ->where('organization', $this->Auth->user()->organization()->id);

        // Add the Conditions
        foreach($conditions as $condition){
            $Query->where($condition["key"], $condition["value"], $condition["operator"], $conjunction);
        }

        // Execute the Query
        $records = $Query->fetch();

        // Return the Count
        return count($records);
    }

    /**
     * Retrieve multiple records
     *
     * @param array $conditions
     * @return array
     */
    public function fetchAll(array $conditions = [], string $conjunction = 'AND'): array
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table($this->table)
            ->select('*')
            ->join('owner', 'users', 'username')
            ->index($this->primary)
            ->filter()
            ->where('id', 9999, '<>');

        // Set the Owner
        if(array_key_exists('organization',$this->definition)){
            $Query->join('organization', 'organizations', 'id')->where('organization', $this->Auth->user()->organization()->id);
        }

        // Check if the conditions are empty
        if(!empty($conditions)){

            // Add a Filter
            $Query->filter();

            // Add the Conditions
            foreach($conditions as $key => $condition){

                // Check if the key exists in the definition
                if(!array_key_exists($condition['key'], $this->definition)){

                    // Remove the key from the data
                    unset($conditions[$key]);
                    continue;
                }

                // Add the condition to the Query
                $Query->where($condition["key"], $condition["value"], $condition["operator"], $conjunction);
            }
        }

        // Retrieve the Results
        $records = $Query->fetch();

        // Loop through the records to process them
        foreach($records as $key => $record){

            // Overwrite the record with the processed one
            $records[$key] = $this->process($record);
        }

        // Return the Results
        return $records;
    }

    /**
     * Retrieve a single record
     *
     * @param int $id
     * @return array
     */
    public function fetch(int $id): array
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table($this->table)
            ->select('*')
            ->join('owner', 'users', 'username')
            ->filter()
            ->where('id', 9999, '<>')
            ->filter()
            ->where($this->primary, $id)
            ->limit(1);

        // Set the Owner
        if(array_key_exists('organization',$this->definition)){
            $Query->join('organization', 'organizations', 'id')->where('organization', $this->Auth->user()->organization()->id);
        }

        // Retrieve the record
        $records = $Query->fetch();

        // Loop through the records to process them
        foreach($records as $key => $record){

            // Overwrite the record with the processed one
            $records[$key] = $this->process($record);
        }

        // Return the record or an empty array if not found
        return $records[array_key_first($records)] ?? [];
    }

    /**
     * Archive a record
     *
     * @param int $id
     * @return int
     */
    public function archive(int $id): int
    {
        // Execute the Query
        return $this->update($id, ['isArchived' => 1]);
    }

    /**
     * Restore a record
     *
     * @param int $id
     * @return int
     */
    public function restore(int $id): int
    {
        // Execute the Query
        return $this->update($id, ['isArchived' => 0]);
    }

    /**
     * Describe the Table
     *
     * @return array
     */
    public function describe(): array
    {
        return $this->definition;
    }
}
