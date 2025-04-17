<?php

/**
 * Core Framework - CoreHelper
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Helper;

class CoreHelper extends Helper {

    // Constants
    const HttpCodes = [400,401,403,404,405,422,423,427,428,429,430,432,500,501,503];

    /**
     * Init function
     *
     * @param bool $force Force the creation of the files
     * @return bool
     */
    public function init(bool $force = false): bool
    {
        // Global Variables
        global $CONFIG;

        // Init status
        $status = true;

        // Path to file
        $htaccess = $CONFIG->root() . DIRECTORY_SEPARATOR . ".htaccess";

        // Check if the file exist
        if($force && is_file($htaccess)) {

            // Delete file
            unlink($htaccess);
        }

        // Check if file exists
        if(!is_dir($htaccess) && !is_file($htaccess) && !is_link($htaccess)) {

            // Initialize content
            $content = "";

            // Loop through the HTTP codes
            foreach (self::HttpCodes as $code) {

                // Set the error document path
                $path = $CONFIG->root() . DIRECTORY_SEPARATOR . "View" . DIRECTORY_SEPARATOR . $code . ".php";

                // Check if the file exists
                if(is_file($path)) {

                    // Add the ErrorDocument line
                    $content .= "ErrorDocument $code $path" . PHP_EOL;
                }
            }

            // Check if the content is empty
            if(!empty($content)) {

                // Add a new line
                $content .= PHP_EOL;
            }

            // Create content
            $content .= "Options +FollowSymLinks" . PHP_EOL . PHP_EOL;
            $content .= "AddType application/javascript .mjs" . PHP_EOL . PHP_EOL;
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

        // Update the status
        $status = $status && is_file($htaccess);

        // Path to file
        $webroot = $CONFIG->root() . DIRECTORY_SEPARATOR . "webroot";

        // Check if directory exists
        if(!is_dir($webroot) && !is_file($webroot) && !is_link($webroot)) {

            // Create directory
            mkdir($webroot, 0755, true);
        }

        // Update the status
        $status = $status && is_dir($webroot);

        // Path to file
        $htaccess = $webroot . DIRECTORY_SEPARATOR . ".htaccess";

        // Check if the file exist
        if($force && is_file($htaccess)) {

            // Delete file
            unlink($htaccess);
        }

        // Check if file exists
        if(!is_dir($htaccess) && !is_file($htaccess) && !is_link($htaccess)) {

            // Initialize content
            $content = "";

            // Loop through the HTTP codes
            foreach (self::HttpCodes as $code) {

                // Set the error document path
                $path = $CONFIG->root() . DIRECTORY_SEPARATOR . "View" . DIRECTORY_SEPARATOR . $code . ".php";

                // Check if the file exists
                if(is_file($path)) {

                    // Add the ErrorDocument line
                    $content .= "ErrorDocument $code $path" . PHP_EOL;
                }
            }

            // Check if the content is empty
            if(!empty($content)) {

                // Add a new line
                $content .= PHP_EOL;
            }

            // Create content
            $content .= "Options +FollowSymLinks" . PHP_EOL . PHP_EOL;
            $content .= "AddType application/javascript .mjs" . PHP_EOL . PHP_EOL;
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

        // Update the status
        $status = $status && is_file($htaccess);

        // Path to file
        $index = $webroot . DIRECTORY_SEPARATOR . "index.php";

        // Check if the file exist
        if($force && is_file($index)) {

            // Delete file
            unlink($index);
        }

        // Check if file exists
        if(!is_dir($index) && !is_file($index) && !is_link($index)) {

            // Create content
            $content = '<?php' . PHP_EOL;
            $content .= '// Load Composer\'s autoloader' . PHP_EOL;
            $content .= 'require_once dirname(__DIR__) . "/vendor/autoload.php";' . PHP_EOL . PHP_EOL;
            $content .= '// Initiate Bootstrap' . PHP_EOL;
            $content .= '$BOOTSTRAP = new LaswitchTech\Core\Bootstrap("ROUTER");' . PHP_EOL;

            // Create file
            file_put_contents($index, $content);
        }

        // Update the status
        $status = $status && is_file($index);

        // Path to file
        $endpoint = $webroot . DIRECTORY_SEPARATOR . "endpoint.php";

        // Check if the file exist
        if($force && is_file($endpoint)) {

            // Delete file
            unlink($endpoint);
        }

        // Check if file exists
        if(!is_dir($endpoint) && !is_file($endpoint) && !is_link($endpoint)) {

            // Create content
            $content = '<?php' . PHP_EOL;
            $content .= '// Load Composer\'s autoloader' . PHP_EOL;
            $content .= 'require_once dirname(__DIR__) . "/vendor/autoload.php";' . PHP_EOL . PHP_EOL;
            $content .= '// Initiate Bootstrap' . PHP_EOL;
            $content .= '$BOOTSTRAP = new LaswitchTech\Core\Bootstrap("API");' . PHP_EOL;

            // Create file
            file_put_contents($endpoint, $content);
        }

        // Update the status
        $status = $status && is_file($endpoint);

        // Path to file
        $favicon = $webroot . DIRECTORY_SEPARATOR . "favicon.ico";
        $icon = $CONFIG->root() . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "icons". DIRECTORY_SEPARATOR . "icon.ico";

        // Check if the file exist
        if($force && is_file($favicon)) {

            // Delete file
            unlink($favicon);
        }

        // Check if file exists
        if(!is_dir($favicon) && !is_file($favicon) && !is_link($favicon)) {

            // Create symbolic link
            symlink($icon, $favicon);
        }

        // Update the status
        $status = $status && is_link($favicon);

        // Path to directory
        $css = $webroot . DIRECTORY_SEPARATOR . "css";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "css";

        // Check if directory exists
        if(is_dir($dist) && !is_dir($css) && !is_file($css) && !is_link($css)) {

            // Create symbolic link
            symlink($dist, $css);
        }

        // Update the status
        $status = $status && is_link($css);

        // Path to directory
        $js = $webroot . DIRECTORY_SEPARATOR . "js";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "js";

        // Check if directory exists
        if(is_dir($dist) && !is_dir($js) && !is_file($js) && !is_link($js)) {

            // Create symbolic link
            symlink($dist, $js);
        }

        // Update the status
        $status = $status && is_link($js);

        // Path to directory
        $img = $webroot . DIRECTORY_SEPARATOR . "img";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "img";

        // Check if directory exists
        if(is_dir($dist) && !is_dir($img) && !is_file($img) && !is_link($img)) {

            // Create symbolic link
            symlink($dist, $img);
        }

        // Update the status
        $status = $status && is_link($img);

        // Path to directory
        $plugins = $webroot . DIRECTORY_SEPARATOR . "plugins";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "plugins";

        // Check if directory exists
        if(is_dir($dist) && !is_dir($plugins) && !is_file($plugins) && !is_link($plugins)) {

            // Create symbolic link
            symlink($dist, $plugins);
        }

        // Update the status
        $status = $status && is_link($plugins);

        // Path to directory
        $themes = $webroot . DIRECTORY_SEPARATOR . "themes";
        $dist = $CONFIG->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "themes";

        // Check if directory exists
        if(is_dir($dist) && !is_dir($themes) && !is_file($themes) && !is_link($themes)) {

            // Create symbolic link
            symlink($dist, $themes);
        }

        // Update the status
        $status = $status && is_link($themes);

        // Return
        return $status;
    }

    /**
     * Retrieve the list of releases from the repository
     *
     * @return array
     */
    public function releases(): array
    {
        // Retrieve the git configuration
        $git = $this->Config->get('installer','git');

        // Check if git is set
        if (is_null($git) || empty($git)) {
            return [];
        }

        // Retrieve the repository
        $repository = $git['repository'] ?? null;

        // Retrieve the token
        $token = $git['token'] ?? null;

        // Retrieve the name
        $name = $this->Config->get('installer','name');

        // Check if repository is set
        if (is_null($repository) || empty($repository)) {
            return [];
        }

        // Initialize the url
        $url = "https://api.github.com/repos/{$repository}/releases";

        // Initialize curl
        $cURL = curl_init($url);

        // Set Headers
        $headers = [
            'User-Agent: ' . $name,
            'Accept: application/vnd.github.v3+json'
        ];

        // Check if a token is set
        if (!is_null($token) && !empty($token)) {
            $headers[] = 'Authorization: token ' . $token;
        }

        // Set cURL options
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, $headers);

        // Execute the request
        $response = curl_exec($cURL);
        $status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);

        // Close the cURL session
        curl_close($cURL);

        // Check if the response is valid
        if($status == 200){

            // Decode the response
            return json_decode($response, true);
        }

        return [];
    }
}
