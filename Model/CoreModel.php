<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Model;

class coreModel extends Model {

    // Properties
    private $Table;

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->Table;
    }

    /**
     * Create a new record and return the id
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    public function create(string $table, array $data): int
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->insert($data);

        // Execute the Query
        $affectedRows = $Query->execute();

        // Execute the Query
        return $Query->lastId();
    }

    /**
     * Read a record(s)
     *
     * @param string $table
     * @param int $id
     * @return array
     */
    public function read(string $table, ?int $id = null): array
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->select('*');

        // Check if id is provided
        if($id){
            $Query->where('id', $id)->limit(1);
        }

        // Execute the Query
        return $Query->result();
    }

    /**
     * Update a record
     *
     * @param string $table
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(string $table, int $id, array $data): int
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->update($data)
            ->where('id', $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Delete a record
     *
     * @param string $table
     * @param int $id
     * @return int
     */
    public function delete(string $table, int $id): int
    {
        // Set the table
        $this->Table = $table;

        // Create the Query
        $Query = $this->Database->query()
            ->table($this->Table)
            ->delete()
            ->where('id', $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Import or Update a module's definition and data
     *
     * @param string $path
     * @param string|null $current
     */
    public function import(string $path, ?string $current = null): bool
    {
        // Import Global Variables
        global $CONFIG;

        // Check if the directory exists
        if(is_dir($path)) {

            try {

                // Set Definition directory
                $definitionDir = $path . DIRECTORY_SEPARATOR . "Definition";

                // Set Data directory
                $dataDir = $path . DIRECTORY_SEPARATOR . "Data";

                // Set Migration File
                $migrationFile = $path . DIRECTORY_SEPARATOR . "migration.cfg";

                // Retrieve the migrations
                $migrations = $CONFIG->add('migration', $migrationFile)->get('migration');

                // Retrieve the new version
                $latest = $CONFIG->version();

                // Check if the current version is set
                $current = $current ?? $CONFIG->version();

                // Initialize a dictionary
                $dictionary = [];

                // Check if the directory exists
                if(is_dir($definitionDir)) {

                    // Retrive the list of definition files
                    $definitions = array_diff(scandir($definitionDir), array('..', '.'));

                    // Loop through the definition files
                    foreach($definitions as $definition) {

                        // Remove the .map extension
                        $definition = str_replace('.map', '', $definition);

                        // Check if the definition file already exists and delete it
                        if(is_file($CONFIG->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map")){
                            unlink($CONFIG->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map");
                        }

                        // Copy the definition file to the Definition directory
                        copy($definitionDir . DIRECTORY_SEPARATOR . $definition . ".map", $CONFIG->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map");

                        // Create the Schema
                        $Schema = $this->Database->schema()->define($definition);

                        // Check if the Schema is already exists
                        if($Schema->exists()){

                            // Save any existing data
                            $dictionary[$definition] = $this->read($definition);

                            // Drop the Schema
                            $Schema->drop();
                        }

                        // Import the Schema in the Database
                        $Schema->create();
                    }

                    // Loop through the migrations
                    foreach($migrations as $version => $migration){

                        // Check if version is greater than the current version and if version is lower or equal to the latest version
                        if(version_compare($current, $version, '<') && version_compare($version, $latest, '<=')){

                            // Loop through the migration steps
                            foreach($migration as $step){

                                // Run the migration step
                                $dictionary = $this->migrate($dictionary, $step['type'], $step['table'], $step['object'], $step['column'], $step['value']);
                            }
                        }
                    }

                    // Loop through the definition files
                    foreach($definitions as $definition) {

                        // Remove the .map extension
                        $definition = str_replace('.map', '', $definition);

                        // Check if the data file exists
                        if(is_file($dataDir . DIRECTORY_SEPARATOR . $definition . ".required")){

                            // Retrieve the content of the data file
                            $records = json_decode(file_get_contents($dataDir . DIRECTORY_SEPARATOR . $definition . ".required"),true);

                            // Loop through the records
                            foreach($records as $record){

                                // Set Record
                                $record['owner'] = $CONFIG->get('database','username');
                                $record['created'] = date('Y-m-d H:i:s');
                                $record['modified'] = date('Y-m-d H:i:s');

                                // Create the Query
                                $Query = $this->Database->query()
                                    ->table($definition)
                                    ->insert($record)
                                    ->execute();
                            }
                        }

                        // Check if the dictionary has the definition
                        if(array_key_exists($definition, $dictionary) && !empty($dictionary[$definition])){

                            // Loop through the dictionary
                            foreach($dictionary[$definition] as $record){

                                // Check if the record ID is higher than 9999
                                if($record['id'] > 9999){

                                    // Create the Query
                                    $Query = $this->Database->query()
                                        ->table($definition)
                                        ->insert($record)
                                        ->execute();
                                } else {

                                    // Create the Query
                                    $Query = $this->Database->query()
                                        ->table($definition)
                                        ->update($record)
                                        ->where('id', $record['id'])
                                        ->execute();
                                }
                            }
                        }

                        // Retrieve the last inserted id
                        $lastId = 0;
                        $lastRecord = $this->Database->query()->table($definition)->select()->order('id', 'DESC')->limit(1)->execute();
                        if(!empty($lastRecord)){
                            $lastId = $lastRecord[0]['id'];
                        }

                        // Set default auto increment
                        $autoIncrement = ($lastId >= 10000) ? $lastId + 1 : 10000;

                        // Set auto increment on the table
                        $this->Database->query()->table($definition)->autoIncrement($autoIncrement);
                    }
                }
            } catch (\Exception $e) {
                // Log the error
                var_dump("Failed to update the extension: " . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Migrate the database
     *
     * @param array $dictionary
     * @param string $type
     * @param string $table
     * @param string $object
     * @param string|null $column
     * @param string|null $value
     * @return array
     */
    protected function migrate(array $dictionary, string $type, string $table, string $object, ?string $column, ?string $value): array
    {
        // Check if the table exists
        if(isset($dictionary[$table])){

            // Check if the step is a table rename
            if($type == "rename"){

                // Check the object to apply on
                if($object == "table"){

                    // Load the table data
                    $data = $dictionary[$table];

                    // Add the table to the dictionary
                    $dictionary[$value] = $data;

                    // Remove the old table from the dictionary
                    unset($dictionary[$table]);
                } elseif ($object == "column") {

                    // Load the table data
                    $data = $dictionary[$table];

                    // Loop through the data
                    foreach($data as $key => $value){

                        // Check if the column exists
                        if(array_key_exists($column, $value)){

                            // Rename the column
                            $data[$key][$value] = $data[$key][$column];

                            // Unset the old column
                            unset($data[$key][$column]);
                        }
                    }

                    // Save the table data
                    $dictionary[$table] = $data;
                }
            } elseif ($type == "add") {

                // Check the object to apply on
                if($object == "table"){
                } elseif ($object == "column") {
                } elseif ($object == "data") {
                }
            } elseif ($type == "delete") {

                // Check the object to apply on
                if($object == "table"){

                    // Remove the table from the dictionary
                    unset($dictionary[$table]);
                } elseif ($object == "column") {

                    // Load the table data
                    $data = $dictionary[$table];

                    // Loop through the data
                    foreach($data as $key => $value){

                        // Check if the column exists
                        if(array_key_exists($column, $value)){

                            // Unset the column
                            unset($data[$key][$column]);
                        }
                    }

                    // Save the table data
                    $dictionary[$table] = $data;
                } elseif ($object == "data") {

                    // Load the table data
                    $data = $dictionary[$table];

                    // Loop through the data
                    foreach($data as $key => $value){

                        // Check if the column is a wildcard *
                        if($column == "*"){

                            // Unset the column
                            unset($data[$key]);
                        }
                    }

                    // Save the table data
                    $dictionary[$table] = $data;
                }
            } elseif ($type == "update") {

                // Check the object to apply on
                if($object == "table"){
                } elseif ($object == "column") {
                } elseif ($object == "data") {
                }
            } elseif ($type == "convert") {

                // Check the object to apply on
                if($object == "table"){
                } elseif ($object == "column") {
                } elseif ($object == "data") {
                }
            } elseif ($type == "filter") {

                // Check the object to apply on
                if($object == "table"){
                } elseif ($object == "column") {
                } elseif ($object == "data") {
                }
            }
        }

        return $dictionary;
    }
}
