<?php

/**
 * Core Framework - CoreCommand
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Command;

class CoreCommand extends Command {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Initialize the framework
     */
    public function initAction()
    {
        // Initialize the framework
        $this->Helper->Core->init();
    }

    /**
     * Add changes to the migration config file
     */
    public function changeAction()
    {
        // Load the current version
        $current = $this->Config->get('migration', $this->Config->version()) ?? [];

        // Retrieve the type of change
        $type = $this->Request->getArguments(3);

        // Check if the type is valid
        if(!in_array($type, ['add', 'delete', 'update', 'rename', 'convert', 'filter'])){

            // Output the error message
            $this->Output->print("Invalid type of change. Valid types are: add, delete, update, rename, convert, filter");
            $this->Output->print("Usage: ./cli change <type> <table> <column> <object> [<value>]");
            $this->Output->print("Example: ./cli change rename users name table auth_users");
            return;
        }

        // Retrieve the table
        $table = $this->Request->getArguments(4);

        // Check if the table is valid
        if(empty($table)){

            // Output the error message
            $this->Output->print("Invalid table name. Please provide a valid table name.");
            return;
        }

        // Retrieve the column
        $column = $this->Request->getArguments(5);

        // Check if the column is valid
        if(empty($column)){

            // Output the error message
            $this->Output->print("Invalid column name. Please provide a valid column name.");
            return;
        }

        // Retrieve object
        $object = $this->Request->getArguments(6);

        // Check if the object is valid
        if(!in_array($object, ['table', 'column', 'data'])){

            // Output the error message
            $this->Output->print("Invalid object name. Valid objects are: table, column, data");
            return;
        }

        // Retrieve the value
        $value = $this->Request->getArguments(7);

        // Append the change to the migration config file
        $current[] = [
            'type' => $type,
            'table' => $table,
            'column' => $column,
            'object' => $object,
            'value' => $value
        ];

        // Save the changes
        $this->Config->set('migration', $this->Config->version(), $current);
    }

    /**
     * Compile the application
     */
    public function compileAction()
    {
        // Import Global Variables
        global $BOOTSTRAP, $DATABASE, $CONFIG, $REQUEST;

        // Create an Update directory
        $path = $CONFIG->root() . DIRECTORY_SEPARATOR . "Install";

        // Check if the Update directory exists
        if(!is_dir($path)){

            // Create the Update directory recursively
            mkdir($path, 0755, true);
        }

        // Check if the Update directory exists
        if(!is_dir($path . DIRECTORY_SEPARATOR . "Definition")){

            // Create the Update directory recursively
            mkdir($path . DIRECTORY_SEPARATOR . "Definition", 0755, true);
        }

        // Check if the Update directory exists
        if(!is_dir($path . DIRECTORY_SEPARATOR . "Data")){

            // Create the Update directory recursively
            mkdir($path . DIRECTORY_SEPARATOR . "Data", 0755, true);
        }

        // Check if Database is connected
        if($DATABASE->isConnected()){

            // Loop through the tables
            foreach($DATABASE->schema()->tables() as $table){

                // Output the name of the table
                $this->Output->print("Compiling {$table}...");

                // Check if we compile the schema
                if(is_null($REQUEST->getArguments(3)) || in_array("--schema",$REQUEST->getArguments())){

                    // Create a Schema
                    $Schema = $DATABASE->schema()
                        ->define($table)
                        ->save();

                    // Move the Schema to the Update directory
                    rename($CONFIG->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map", $path . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map");

                    // Output the Definition path
                    $this->Output->print("Definition: " . $path . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map");

                }

                // Check if we compile the required data
                if(is_null($REQUEST->getArguments(3)) || in_array("--required",$REQUEST->getArguments())){

                    // Create a Query
                    $Query = $DATABASE->query()
                        ->table($table)
                        ->select('*')
                        ->where('id', 5000, '<', 'OR')
                        ->where('id', 9999, '=', 'OR');

                    // Retrieve the data
                    $data = $Query->fetch();

                    // Add some sanitizing of some tables
                    if(in_array($table, ['groups', 'roles', 'organizations'])){

                        // Loop through the data
                        foreach($data as $key => $value){

                            // Check if the key users exists
                            if(array_key_exists('users', $value)){

                                // Set the value of users to null
                                $data[$key]['users'] = null;
                            }
                        }
                    }

                    // Output the number of records
                    $this->Output->print("Records [required]: " . count($data));

                    // Save the data as JSON
                    file_put_contents($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $table . ".required", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }

                // Check if we compile the sample data
                if(is_null($REQUEST->getArguments(3)) || in_array("--sample",$REQUEST->getArguments())){

                    // Create a Query
                    $Query = $DATABASE->query()
                        ->table($table)
                        ->select('*')
                        ->where('id', 5000, '>=', 'AND')
                        ->where('id', 9999, '<', 'AND');

                    // Retrieve the data
                    $data = $Query->fetch();

                    // Output the number of records
                    $this->Output->print("Records [sample]: " . count($data));

                    // Save the data as JSON
                    file_put_contents($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $table . ".sample", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            }
        }

        // Check if we compile the installer
        if(is_null($REQUEST->getArguments(3)) || in_array("--installer",$REQUEST->getArguments())){

            // Load/Create the installer configuration
            $CONFIG->add('installer');

            // Modules
            $modules = $CONFIG->get('installer', 'modules');

            // Check if modules are defined
            if(is_null($modules)){

                // Set default modules list to empty
                $modules = [];

                // Save the modules list
                $CONFIG->set('installer', 'modules', $modules);
            }
        }
    }
}
