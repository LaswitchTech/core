<?php

/**
 * Core Framework - Database
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Objects;
use LaswitchTech\Core\Connectors;
use Exception;

class Database {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * Constructor
     */
    public function __construct()
    {

        // Import Global Variables
        global $CONFIG;

        // Initialize Properties
        $this->Config = $CONFIG;

        // Load the Database Configuration
        $this->Config->add('database')->add('migration');

        // Initiate the Connector
        $this->init();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Initialize the database connector
     */
    private function init(): void
    {
        // Instantiate the appropriate connector
        switch($this->Config->get('database', 'connector')) {
            case 'mysql':
                // Connect to the database
                $this->connector = new Connectors\MySQL();
                $this->connector->connect();
                break;
            default:
                // throw new Exception('Invalid database connector');
        }
    }

    /**
     * Check if the database is connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->connector === null) {
            return false;
        }
        return $this->connector->isConnected();
    }

    /**
     * Connect to the database
     */
    public function connect(): void
    {
        if ($this->connector === null) {
            $this->init();
        }
        if ($this->connector !== null) {
            $this->connector->connect();
        }
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        if ($this->connector !== null) {
            $this->connector->close();
        }
    }

    /**
     * Create a new Query object
     *
     * @return Objects\Query
     */
    public function query(): Objects\Query
    {
        return new Objects\Query($this->connector);
    }

    /**
     * Create a new Schema object
     *
     * @return Objects\Schema
     */
    public function schema(): Objects\Schema
    {
        return new Objects\Schema($this->connector);
    }

    /**
     * Install the module
     *
     * @param array $config
     * @return array
     */
    public function install(array $config): array
    {
        // Initialize the status
        $status = [];

        // Check if the config includes all the required fields
        if(isset($config['connector'],$config['host'],$config['database'],$config['username'],$config['password'])){

            // Save the settings
            $this->Config->set('database', 'connector', $config['connector']);
            $this->Config->set('database', 'host', $config['host']);
            $this->Config->set('database', 'database', $config['database']);
            $this->Config->set('database', 'username', $config['username']);
            $this->Config->set('database', 'password', $config['password']);

            // Connect to the database server
            $this->connect();

            // Check if the database server is connected
            if($this->isConnected()){

                // Retrieve the path of the version's directory
                $path = $this->Config->root() . DIRECTORY_SEPARATOR . "Install";

                // Check if the directory exists
                if(is_dir($path)) {

                    // Retrive the list of definition files
                    $definitions = array_diff(scandir($path . DIRECTORY_SEPARATOR . "Definition"), array('..', '.'));

                    // Check if the Definition directory exists if not create it
                    if(!is_dir($this->Config->root() . DIRECTORY_SEPARATOR . "Definition")){
                        mkdir($this->Config->root() . DIRECTORY_SEPARATOR . "Definition", 0755, true);
                    }

                    // Loop through the definition files
                    foreach($definitions as $definition) {

                        // Remove the .map extension
                        $definition = str_replace('.map', '', $definition);

                        // Check if the definition file already exists and delete it
                        if(is_file($this->Config->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map")){
                            unlink($this->Config->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map");
                        }

                        // Copy the definition file to the Definition directory
                        copy($path . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map", $this->Config->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $definition . ".map");

                        // Create the Schema
                        $Schema = $this->schema()->define($definition);

                        // Check if the Schema is already exists
                        if($Schema->exists()){

                            // Drop the Schema
                            $Schema->drop();
                        }

                        // Import the Schema in the Database
                        $Schema->create();

                        // Check if the data file exists
                        if(is_file($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $definition . ".required")){

                            // Retrieve the content of the data file
                            $records = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $definition . ".required"),true);

                            // Loop through the records
                            foreach($records as $record){

                                // Set Record
                                $record['owner'] = $config['username'];
                                $record['created'] = date('Y-m-d H:i:s');
                                $record['modified'] = date('Y-m-d H:i:s');

                                // Create the Query
                                $Query = $this->query()
                                    ->table($definition)
                                    ->insert($record)
                                    ->execute();
                            }

                            // Check if the data file exists
                            $status[] = true;

                            // Check if sample data should be loaded
                            if(isset($config['sample']) && filter_var($config['sample'], FILTER_VALIDATE_BOOLEAN)){

                                // Check if the data file exists
                                if(is_file($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $definition . ".sample")){

                                    // Retrieve the content of the data file
                                    $records = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $definition . ".sample"),true);

                                    // Loop through the records
                                    foreach($records as $record){

                                        // Set Record
                                        $record['owner'] = $config['username'];
                                        $record['created'] = date('Y-m-d H:i:s');
                                        $record['modified'] = date('Y-m-d H:i:s');

                                        // Create the Query
                                        $Query = $this->query()
                                            ->table($definition)
                                            ->insert($record)
                                            ->execute();
                                    }

                                    // Check if the data file exists
                                    $status[] = true;
                                } else {
                                    $status[] = "Could not find the sample data file";
                                }
                            }

                            // Set default auto increment
                            $autoIncrement = 10000;

                            // Set auto increment on the table
                            $this->query()->table($definition)->autoIncrement($autoIncrement);

                            // Add a true status
                            $status[] = true;
                        } else {
                            $status[] = "Could not find the data file";
                        }
                    }
                } else {
                    $status[] = "Could not find the update directory";
                }
            } else {
                $this->Config->delete('database');
                $status[] = "Could not connect to the database server";
            }
        } else {
            $status[] = "Missing required fields";
        }

        // Return the statuses
        return $status;
    }
}
