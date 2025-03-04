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
        // Global Variables
        global $CONFIG;

        // Initialize the framework
        $this->Output->print("Initializing...");

        // Path to file
        $htaccess = $CONFIG->root() . DIRECTORY_SEPARATOR . ".htaccess";

        // Check if file exists
        if(!is_dir($htaccess) && !is_file($htaccess) && !is_link($htaccess)) {

            // Output
            $this->Output->print("Creating {$htaccess} file...");

            // Create content
            $content = "AddType application/javascript .mjs" . PHP_EOL . PHP_EOL;
            $content .= "<IfModule mod_headers.c>" . PHP_EOL;
            $content .= "    RequestHeader unset Proxy" . PHP_EOL;
            $content .= "</IfModule>" . PHP_EOL . PHP_EOL;
            $content .= "<IfModule mod_rewrite.c>" . PHP_EOL;
            $content .= "    RewriteEngine on" . PHP_EOL;
            $content .= "    RewriteRule ^(\.well-known/.*)$ $1 [L]" . PHP_EOL;
            $content .= "    RewriteRule ^$ webroot/ [L]" . PHP_EOL;
            $content .= "    RewriteRule (.*) webroot/$1 [L]" . PHP_EOL;
            $content .= "</IfModule>";

            // Create file
            file_put_contents($htaccess, $content);
        }

        // Path to file
        $webroot = $CONFIG->root() . DIRECTORY_SEPARATOR . "webroot";

        // Check if directory exists
        if(!is_dir($webroot) && !is_file($webroot) && !is_link($webroot)) {

            // Output
            $this->Output->print("Creating {$webroot} directory...");

            // Create directory
            mkdir($webroot, 0755, true);
        }

        // Path to file
        $htaccess = $webroot . DIRECTORY_SEPARATOR . ".htaccess";

        // Check if file exists
        if(!is_dir($htaccess) && !is_file($htaccess) && !is_link($htaccess)) {

            // Output
            $this->Output->print("Creating {$htaccess} file...");

            // Create content
            $content = "Options +FollowSymLinks" . PHP_EOL . PHP_EOL;
            $content = "AddType application/javascript .mjs" . PHP_EOL . PHP_EOL;
            $content .= "<IfModule mod_php8.c>" . PHP_EOL;
            $content .= "    php_value session.cookie_samesite Strict" . PHP_EOL;
            $content .= "    php_value session.cookie_secure On" . PHP_EOL;
            $content .= "    php_value memory_limit 1024M" . PHP_EOL;
            $content .= "    php_value upload_max_filesize 8192M" . PHP_EOL;
            $content .= "    php_value post_max_size 8192M" . PHP_EOL;
            $content .= "    php_value max_input_time -1" . PHP_EOL;
            $content .= "    php_value max_execution_time 600" . PHP_EOL;
            $content .= "    php_value max_file_uploads 100" . PHP_EOL;
            $content .= "</IfModule>" . PHP_EOL . PHP_EOL;
            $content .= "<IfModule mod_headers.c>" . PHP_EOL;
            $content .= "    RequestHeader unset Proxy" . PHP_EOL;
            $content .= "</IfModule>" . PHP_EOL . PHP_EOL;
            $content .= "<IfModule mod_rewrite.c>" . PHP_EOL;
            $content .= "    RewriteEngine on" . PHP_EOL;
            $content .= "    RewriteBase /" . PHP_EOL . PHP_EOL;
            $content .= "    # Forbid any direct .php file access in plugins or themes" . PHP_EOL;
            $content .= "    RewriteRule ^(plugins|themes)/.*\.php$ - [F,L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Forbid direct access to certain files" . PHP_EOL;
            $content .= "    RewriteRule ^(cli|\.htaccess)$ - [F,L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Serve existing files, directories, or symlinks directly" . PHP_EOL;
            $content .= "    RewriteCond %{REQUEST_FILENAME} -f [OR]" . PHP_EOL;
            $content .= "    RewriteCond %{REQUEST_FILENAME} -d [OR]" . PHP_EOL;
            $content .= "    RewriteCond %{REQUEST_FILENAME} -l" . PHP_EOL;
            $content .= "    RewriteRule ^.*$ - [L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Route endpoint.php/anything to endpoint.php" . PHP_EOL;
            $content .= "    RewriteRule ^endpoint\.php(.*)$ endpoint.php [QSA,L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Everything else goes to index.php" . PHP_EOL;
            $content .= "    RewriteRule ^.*$ index.php [QSA,L]" . PHP_EOL;
            $content .= "</IfModule>";

            // Create file
            file_put_contents($htaccess, $content);
        }

        // Path to file
        $index = $webroot . DIRECTORY_SEPARATOR . "index.php";

        // Check if file exists
        if(!is_dir($index) && !is_file($index) && !is_link($index)) {

            // Output
            $this->Output->print("Creating {$index} file...");

            // Create content
            $content = '<?php' . PHP_EOL;
            $content .= '// Load Composer\'s autoloader' . PHP_EOL;
            $content .= 'require_once dirname(__DIR__) . "/vendor/autoload.php";' . PHP_EOL . PHP_EOL;
            $content .= '// Initiate Bootstrap' . PHP_EOL;
            $content .= '$BOOTSTRAP = new LaswitchTech\Core\Bootstrap("ROUTER");' . PHP_EOL;

            // Create file
            file_put_contents($index, $content);
        }

        // Path to file
        $endpoint = $webroot . DIRECTORY_SEPARATOR . "endpoint.php";

        // Check if file exists
        if(!is_dir($endpoint) && !is_file($endpoint) && !is_link($endpoint)) {

            // Output
            $this->Output->print("Creating {$endpoint} file...");

            // Create content
            $content = '<?php' . PHP_EOL;
            $content .= '// Load Composer\'s autoloader' . PHP_EOL;
            $content .= 'require_once dirname(__DIR__) . "/vendor/autoload.php";' . PHP_EOL . PHP_EOL;
            $content .= '// Initiate Bootstrap' . PHP_EOL;
            $content .= '$BOOTSTRAP = new LaswitchTech\Core\Bootstrap("API");' . PHP_EOL;

            // Create file
            file_put_contents($endpoint, $content);
        }

        // Path to file
        $favicon = $webroot . DIRECTORY_SEPARATOR . "favicon.ico";
        $icon = $CONFIG->root() . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "icons". DIRECTORY_SEPARATOR . "icon.ico";

        // Check if file exists
        if(!is_dir($favicon) && !is_file($favicon) && !is_link($favicon)) {

            // Output
            $this->Output->print("Creating {$favicon} file...");

            // Create symbolic link
            symlink($icon, $favicon);
        }

        // Path to directory
        $css = $webroot . DIRECTORY_SEPARATOR . "css";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "css";

        // Check if directory exists
        if(!is_dir($css) && !is_file($css) && !is_link($css)) {

            // Output
            $this->Output->print("Creating {$css} directory...");

            // Create symbolic link
            symlink($dist, $css);
        }

        // Path to directory
        $js = $webroot . DIRECTORY_SEPARATOR . "js";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "js";

        // Check if directory exists
        if(!is_dir($js) && !is_file($js) && !is_link($js)) {

            // Output
            $this->Output->print("Creating {$js} directory...");

            // Create symbolic link
            symlink($dist, $js);
        }

        // Path to directory
        $img = $webroot . DIRECTORY_SEPARATOR . "img";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "img";

        // Check if directory exists
        if(!is_dir($img) && !is_file($img) && !is_link($img)) {

            // Output
            $this->Output->print("Creating {$img} directory...");

            // Create symbolic link
            symlink($dist, $img);
        }

        // Path to directory
        $plugins = $webroot . DIRECTORY_SEPARATOR . "plugins";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "plugins";

        // Check if directory exists
        if(!is_dir($plugins) && !is_file($plugins) && !is_link($plugins)) {

            // Output
            $this->Output->print("Creating {$plugins} directory...");

            // Create symbolic link
            symlink($dist, $plugins);
        }

        // Path to directory
        $themes = $webroot . DIRECTORY_SEPARATOR . "themes";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "themes";

        // Check if directory exists
        if(!is_dir($themes) && !is_file($themes) && !is_link($themes)) {

            // Output
            $this->Output->print("Creating {$themes} directory...");

            // Create symbolic link
            symlink($dist, $themes);
        }
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
