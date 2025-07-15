<?php

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Command;

class CoreCommand extends Command {

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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validate Cron Schedule
     */
    private function validateCronSchedule(string $expr): bool
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
    private function getCronSchedule(string $expr): array
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

    /**
     * Compare a parsed cron *schedule* (minute, hour, day, month, dow)
     * with the current time held in $now (same 5 keys).
     */
    private function compareSchedule(array $schedule, array $now): bool
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
        global $DATABASE, $REQUEST;

        // Check if Database is connected
        if($DATABASE->isConnected()){

            // Loop through the tables
            foreach($DATABASE->schema()->tables() as $table){

                // Output the name of the table
                $this->Output->print("Compiling {$table}...");

                // Set the path
                $path = $this->Config->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . $table;

                // Check if the directory exists
                if(!is_dir($path)){

                    // Set the path
                    $path = $this->Config->root();
                }

                // Set the path
                $path = $path . DIRECTORY_SEPARATOR . "Install";

                // Check if the directory exists
                if(!is_dir($path)){

                    // Create the directory recursively
                    mkdir($path, 0755, true);
                }

                // Check if the directory exists
                if(!is_dir($path . DIRECTORY_SEPARATOR . "Definition")){

                    // Create the directory recursively
                    mkdir($path . DIRECTORY_SEPARATOR . "Definition", 0755, true);
                }

                // Check if the directory exists
                if(!is_dir($path . DIRECTORY_SEPARATOR . "Data")){

                    // Create the directory recursively
                    mkdir($path . DIRECTORY_SEPARATOR . "Data", 0755, true);
                }

                // Check if we compile the schema
                if(is_null($REQUEST->getArguments(3)) || in_array("--schema",$REQUEST->getArguments())){

                    // Create a Schema
                    $Schema = $DATABASE->schema()
                        ->define($table)
                        ->save();

                    // Move the Schema to the Update directory
                    rename($this->Config->root() . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map", $path . DIRECTORY_SEPARATOR . "Definition" . DIRECTORY_SEPARATOR . $table . ".map");

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

    /**
     * Handle extension-related actions
     */
    public function extensionAction()
    {
        // Configure extended listing
        $listing = $this->Helper->Core->loadExtensionsMeta();

        // Set main path
        $path = $this->Config->root() . DIRECTORY_SEPARATOR . "lib";

        // Handle the request
        switch($this->Request->getArguments(3)){
            case 'list':
                switch($this->Request->getArguments(4)){
                    default:
                        if(!array_key_exists($this->Request->getArguments(4), $listing)){
                            $this->Output->print("Invalid type: " . $this->Request->getArguments(4));
                            $this->Output->print("Available types: modules, plugins, themes");
                            return;
                        }
                        $this->Output->print("Type: " . $this->Request->getArguments(4));
                        foreach($listing[$this->Request->getArguments(4)] as $base => $extension){
                            $this->Output->print(" - " . $base . ($extension['installed'] ? " (installed)" : "") . ($extension['git'] ? " (dev)" : ""));
                        }
                        return;
                        return;
                    case null:
                        $this->Output->print("Usage: ./cli core extension ".$this->Request->getArguments(3)." <type>");
                        $this->Output->print("Types: modules, plugins, themes");
                        return;
                }
                return;
            case 'info':
            case 'import':
            case 'update':
            case 'install':
            case 'uninstall':
                switch($this->Request->getArguments(4)){
                    case 'modules':
                    case 'plugins':
                    case 'themes':
                        switch($this->Request->getArguments(5)){
                            default:
                                if($this->Request->getArguments(3) != 'import'){
                                    $extension = $listing[$this->Request->getArguments(4)][$this->Request->getArguments(5)] ?? null;
                                    if(is_null($extension)){
                                        $this->Output->print("Extension not found: " . $this->Request->getArguments(5));
                                        return;
                                    }
                                }
                                switch($this->Request->getArguments(3)){
                                    case 'info':
                                        foreach($extension as $key => $value){
                                            if(is_array($value)){
                                                $this->Output->print(ucwords($key) . ": " . implode(", ", $value));
                                            } else {
                                                $this->Output->print(ucwords($key) . ": " . $value);
                                            }
                                        }
                                        return;
                                    case 'import':
                                        switch($this->Request->getArguments(6)){
                                            default:
                                                $url = $this->Helper->Core->getRepo($this->Request->getArguments(6))['url'];
                                                if($url){
                                                    $listings = $this->Config->get('extensions');
                                                    $listings[$this->Request->getArguments(4)][$this->Request->getArguments(5)] = ['url' => $this->Helper->Core->getRepo($this->Request->getArguments(6))['url']];
                                                    if($this->Request->getArguments(7)){
                                                        $listings[$this->Request->getArguments(4)][$this->Request->getArguments(5)]['token'] = $this->Request->getArguments(7);
                                                    }
                                                    $this->Config->set('extensions', $this->Request->getArguments(4), $listings[$this->Request->getArguments(4)]);
                                                    $this->Output->print("Extension {$this->Request->getArguments(5)} imported successfully.");
                                                } else {
                                                    $this->Output->print("Invalid repository URL: " . $this->Request->getArguments(6));
                                                    return;
                                                }
                                                return;
                                            case null:
                                                $this->Output->print("Usage: ./cli core extension ".$this->Request->getArguments(3)." ".$this->Request->getArguments(4)." ".$this->Request->getArguments(5)." <repository> <token>");
                                                $this->Output->print("Repository: URL of the repository to import the extension from");
                                                $this->Output->print("Token: Optional token for authentication");
                                                return;
                                        }
                                        return;
                                    case 'update':
                                        if(is_dir($extension['path'])){
                                            $tmpPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'tmp';
                                            $archivePath = $tmpPath . DIRECTORY_SEPARATOR . $extension['base'] . '.zip';
                                            if($this->Helper->Core->download($extension['download'], $archivePath, $extension['token'] ?? null)){
                                                if($this->Helper->Core->unpack($archivePath, $extension['path'])){
                                                    if($this->Model->Core->import($extension['path'] . DIRECTORY_SEPARATOR . "Install", file_get_contents($extension['path'] . DIRECTORY_SEPARATOR . 'VERSION'))){
                                                        $this->Output->print("Extension {$extension['base']} updated successfully.");
                                                    } else {
                                                        $this->Output->print("Failed to create the database.");
                                                    }
                                                } else {
                                                    $this->Output->print("Failed to unpack the extension {$extension['base']}.");
                                                }
                                            } else {
                                                $this->Output->print("Failed to download the extension {$extension['base']}.");
                                            }
                                        } else {
                                            $this->Output->print("Extension not installed: " . $this->Request->getArguments(5));
                                        }
                                        return;
                                    case 'install':
                                        if(!is_dir($extension['path'])){
                                            $tmpPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'tmp';
                                            $archivePath = $tmpPath . DIRECTORY_SEPARATOR . $extension['base'] . '.zip';
                                            if($this->Helper->Core->download($extension['download'], $archivePath, $extension['token'] ?? null)){
                                                if($this->Helper->Core->unpack($archivePath, $extension['path'])){
                                                    if($this->Model->Core->import($extension['path'] . DIRECTORY_SEPARATOR . "Install")){
                                                        $this->Output->print("Extension {$extension['base']} installed successfully.");
                                                    } else {
                                                        $this->Output->print("Failed to create the database.");
                                                    }
                                                } else {
                                                    $this->Output->print("Failed to unpack the extension {$extension['base']}.");
                                                }
                                            } else {
                                                $this->Output->print("Failed to download the extension {$extension['base']}.");
                                            }
                                        } else {
                                            $this->Output->print("Extension already installed: " . $this->Request->getArguments(5));
                                        }
                                        return;
                                    case 'uninstall':
                                        if(is_dir($extension['path'])){
                                            if($this->Helper->Core->delete($extension['path'])){
                                                $this->Output->print("Extension uninstalled successfully.");
                                            } else {
                                                $this->Output->print("Failed to uninstall the extension.");
                                            }
                                        } else {
                                            $this->Output->print("Extension not installed: " . $this->Request->getArguments(5));
                                        }
                                        return;
                                }
                            case null:
                                $this->Output->print("Usage: ./cli core extension ".$this->Request->getArguments(3)." ".$this->Request->getArguments(4)." <base>");
                                $this->Output->print("Base: name of the extension");
                                return;
                        }
                        return;
                    default:
                        $this->Output->print("Usage: ./cli core extension ".$this->Request->getArguments(3)." <type> <base>");
                        $this->Output->print("Types: modules, plugins, themes");
                        $this->Output->print("Base: name of the extension");
                        return;
                }
                return;
            default:
                $this->Output->print("Usage: ./cli core extension <sub-command> <type> <base>");
                $this->Output->print("Sub-commands: list, info, import, update, install, uninstall");
                $this->Output->print("Types: modules, plugins, themes");
                $this->Output->print("Base: name of the extension");
                return;
        }
    }
}
