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
     * Validate Cron Schedule
     */
    public function validateCronSchedule(string $expr): bool
    {
        $cron5 = '~^
            (\*|[0-5]?\d)(/([1-5]?\d))?       # minute
            \s+
            (\*|1?\d|2[0-3])(/([1-5]?\d))?    # hour
            \s+
            (\*|0?[1-9]|[12]\d|3[01])(/([1-9]|[12]\d|3[01]))? # dom
            \s+
            (\*|0?[1-9]|1[0-2])(/([1-9]|1[0-2]))?            # month
            \s+
            (\*|[0-7])(/([0-7]))?                            # dow (0/7 = Sun)
            $~x';

        return (bool)preg_match($cron5, trim($expr));
    }

    /**
     * Retrieve the cron schedule
     */
    public function getCronSchedule(string $expr): array
    {
        // Check if the schedule is valid
        if(!$this->validateCronSchedule($expr)){
            throw new Exception("Invalid cron schedule: $expr");
        }

        // Split the expression into parts
        $parts = preg_split('/\s+/', $expr);

        // Construct the schedule array
        $schedule = [
            'minute' => $parts[0],
            'hour' => $parts[1],
            'day' => $parts[2],
            'month' => $parts[3],
            'dow' => $parts[4]
        ];

        return $schedule;
    }

    // /**
    //  * Compare the schedule with the current date
    //  */
    // public function compareSchedule(array $schedule, array $now): bool
    // {
    //     // Check if the schedule matches the current date
    //     return (
    //         ($schedule['minute'] == '*' || $schedule['minute'] == $now['minute']) &&
    //         ($schedule['hour'] == '*' || $schedule['hour'] == $now['hour']) &&
    //         ($schedule['day'] == '*' || $schedule['day'] == $now['day']) &&
    //         ($schedule['month'] == '*' || $schedule['month'] == $now['month']) &&
    //         ($schedule['dow'] == '*' || $schedule['dow'] == $now['dow'])
    //     );
    // }

    /**
     * Compare a parsed cron *schedule* (minute, hour, day, month, dow)
     * with the current time held in $now (same 5 keys).
     */
    public function compareSchedule(array $schedule, array $now): bool
    {
        return
            $this->matchCronField($schedule['minute'], $now['minute'], 0, 59) &&
            $this->matchCronField($schedule['hour'],   $now['hour'],   0, 23) &&
            $this->matchCronField($schedule['day'],    $now['day'],    1, 31) &&
            $this->matchCronField($schedule['month'],  $now['month'],  1, 12, self::$monthNames) &&
            $this->matchCronField($schedule['dow'],    $now['dow'],    0, 7,  self::$dowNames, true);
    }

    /**
     * Decide whether $value matches a single cron field expression.
     *
     * @param string      $expr
     * @param int|string  $value
     * @param int         $min
     * @param int         $max
     * @param array|null  $names
     * @param bool        $wrap7
    */
    private function matchCronField(string $expr, $value, int $min, int $max, ?array $names = null, bool $wrap7 = false): bool
    {
        $value = (int)$value;
        if ($wrap7 && $value === 7) {
            $value = 0; // Sun may be 0 or 7
        }

        // Fast path: '*' means "always"
        if ($expr === '*') {
            return true;
        }

        // Split lists: 1,5,10-15,*/10  …
        foreach (explode(',', $expr) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            // Convert month/dow names to numbers if needed
            if ($names) {
                $part = preg_replace_callback('/[A-Za-z]+/', function ($m) use ($names) {
                    $up = strtoupper($m[0]);
                    return $names[$up] ?? $m[0];   // leave untouched if unknown
                }, $part);
            }

            // Step syntax (*/15 or 5-55/10)
            if (strpos($part, '/') !== false) {
                [$range, $step] = explode('/', $part, 2);
                $step = max(1, (int)$step);

                // '*' → full range; otherwise a-b
                [$start, $end] = ($range === '*')
                    ? [$min, $max]
                    : array_pad(explode('-', $range, 2), 2, null);

                $start = (int)($start ?? $min);
                $end   = (int)($end   ?? $max);

                if ($value >= $start && $value <= $end &&
                    (($value - $start) % $step) === 0) {
                    return true;
                }
                continue;
            }

            // Simple range a-b
            if (strpos($part, '-') !== false) {
                [$start, $end] = explode('-', $part, 2);
                if ($value >= (int)$start && $value <= (int)$end) {
                    return true;
                }
                continue;
            }

            // Single literal number
            if ($value === (int)$part) {
                return true;
            }
        }

        // No match in any list element
        return false;
    }

    /**
     * Lookup tables for names → numbers
     */
    private static array $monthNames = [
        'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
        'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12,
    ];
    private static array $dowNames = [
        'SUN'=>0,'MON'=>1,'TUE'=>2,'WED'=>3,'THU'=>4,'FRI'=>5,'SAT'=>6,
    ];

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

    /**
     * Execute CRON jobs
     */
    public function cronAction()
    {
        // Set Current Date and Time
        $now = [
            "minute" => date('i'),
            "hour" => date('H'),
            "day" => date('d'),
            "month" => date('m'),
            "dow" => date('w')
        ];
        var_dump($now);

        // Set Path
        $path = $this->Config->root() . "/lib/plugins";

        // Check if the Model directory exists
        if(is_dir($path)){

            // Loop through all the files in the directory
            foreach(array_diff(scandir($path), ['..', '.','.DS_Store']) as $plugin){

                // Set Command path
                $commandPath = $path . "/" . $plugin . "/Command.php";

                // Check if the command file exists
                if(is_file($commandPath)){

                    // Include the Command
                    require_once $commandPath;

                    // Get the Command Base Name and Class Name
                    $baseName = ucfirst($plugin);
                    $className = $baseName . 'Command';

                    // Check if the class exists
                    if (class_exists($className)) {

                        // Create the Command
                        $Command = new $className();

                        // Check if the command contains a cron method & schedule method
                        if (method_exists($Command, 'cron') && method_exists($Command, 'schedule')) {

                            // Retrieve the schedule
                            $schedule = $Command->schedule();

                            // Check if the schedule is valid (using the cron expression)
                            if($this->validateCronSchedule($schedule)){

                                // Parse the schedule
                                $schedule = $this->getCronSchedule($schedule);

                                // Check if the schedule matches the current date
                                if($this->compareSchedule($schedule, $now)){

                                    // Execute the command
                                    $Command->cron();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
