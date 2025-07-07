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
    protected $primary;
    protected $schema;
    protected $definition;
    protected $definitions = [];

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

        // Initialize the Model
        $this->schema = $this->Database->schema()->define($this->table);

        // Describe the table
        foreach($this->schema->describe() as $column){
            $this->definition[$column['Field']] = $column;
        }
    }

    /**
     * Check if a value is a valid JSON string
     *
     * @param string $value
     * @return bool
     */
    protected function isJson($value): bool
    {
        if (function_exists('json_validate')) {
            return json_validate($value);
        }

        if ($value === '' || !is_string($value)) {
            return false;
        }

        $data = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE && is_array($data));
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
        }
        if(array_key_exists('lead', $this->definitions[$table])){
            $Query->join('lead', 'leads', 'id');
        }
        if(array_key_exists('client', $this->definitions[$table])){
            $Query->join('client', 'clients', 'id');
        }
        if(array_key_exists('vcard', $this->definitions[$table])){
            $Query->join('vcard', 'vcards', 'id');
        }
        if(array_key_exists('contact', $this->definitions[$table])){
            $Query->join('contact', 'contacts', 'id');
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
            "targetTable" => $currentTargetTable,
            "targetId" => $currentTargetId,
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
            if(is_string($value) && $this->isJson($value)){

                // Decode the JSON value
                $record[$key] = json_decode($value, true);
            }
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
                $data[$key] = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

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
                $data[$key] = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

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
        return $this->definitions;
    }
}
