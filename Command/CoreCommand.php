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
    }
}
