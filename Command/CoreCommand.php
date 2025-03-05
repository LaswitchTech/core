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
        $this->Helpers->Core->init();
    }

    /**
     * Compile the application
     */
    public function compileAction()
    {
        // Import Global Variables
        global $BOOTSTRAP, $DATABASE, $CONFIG;

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

        // var_dump($modules);

        // Get the current version
        $version = $CONFIG->version();

        // Create an Update directory
        $path = $CONFIG->root() . DIRECTORY_SEPARATOR . "Update" . DIRECTORY_SEPARATOR . $version;

        // Check if the Update directory exists
        if(!is_dir($path)){

            // Create the Update directory recursively
            mkdir($path, 0755, true);

            // Create the Update directory recursively
            mkdir($path . DIRECTORY_SEPARATOR . "Definition", 0755, true);

            // Create the Update directory recursively
            mkdir($path . DIRECTORY_SEPARATOR . "Data", 0755, true);
        }

        // List Tables
        $tables = $DATABASE->schema()->tables();

        // Loop through the tables
        foreach($tables as $table){

            // Output the name of the table
            $this->Output->print("Compiling {$table}...");

            // Create a Schema
            $Schema = $DATABASE->schema()
                ->define($table)
                ->save();

            // Move the Schema to the Update directory
            rename($CONFIG->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map", $path . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map");

            // Output the Definition path
            $this->Output->print("Definition: " . $path . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map");

            // Create a Query
            $Query = $DATABASE->query()
                ->table($table)
                ->select('*')
                ->where('id', 5000, '<', 'OR')
                ->where('id', 9999, '=', 'OR');

            // Retrieve the data
            $data = $Query->fetch();

            // Output the number of records
            $this->Output->print("Records [required]: " . count($data));
            // var_dump($Query->__toString());

            // Save the data as JSON
            file_put_contents($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $table . ".required", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

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

            // Create a Query
            $Query = $DATABASE->query()
                ->table($table)
                ->select('*')
                ->where('id', 9999, '>');

            // Retrieve the data
            $data = $Query->fetch();

            // Output the number of records
            $this->Output->print("Records [preload]: " . count($data));

            // Save the data as JSON
            file_put_contents($path . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $table . ".preload", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
