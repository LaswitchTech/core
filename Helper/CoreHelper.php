<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Helper;

class CoreHelper extends Helper {

    // Constants
    const HttpCodes = [400,401,403,404,405,422,423,428,429,500,501,503];

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
        $assets = $CONFIG->root() . DIRECTORY_SEPARATOR . "webroot" . DIRECTORY_SEPARATOR . "assets";

        // Check if directory exists
        if(!is_dir($assets) && !is_file($assets) && !is_link($assets)) {

            // Create directory
            mkdir($assets, 0755, true);
        }

        // Update the status
        $status = $status && is_dir($assets);

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
            $content .= "Options +FollowSymLinks" . PHP_EOL;
            $content .= "Options -MultiViews" . PHP_EOL . PHP_EOL;
            $content .= "AcceptPathInfo On" . PHP_EOL . PHP_EOL;
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
            $content .= "    # Route endpoint.php/anything to endpoint.php" . PHP_EOL;
            $content .= "    RewriteRule ^api(.*)$ endpoint.php [QSA,L]" . PHP_EOL;
            $content .= "    RewriteRule ^endpoint\.php(.*)$ endpoint.php [QSA,L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Forbid any direct .php file access in plugins or themes" . PHP_EOL;
            $content .= "    RewriteRule ^(assets/plugins|assets/themes)/.*\.php$ - [F,L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Forbid direct access to certain files" . PHP_EOL;
            $content .= "    RewriteRule ^(cli|\.htaccess)$ - [F,L]" . PHP_EOL . PHP_EOL;
            $content .= "    # Serve existing files, directories, or symlinks directly" . PHP_EOL;
            $content .= "    RewriteCond %{REQUEST_FILENAME} -f [OR]" . PHP_EOL;
            $content .= "    RewriteCond %{REQUEST_FILENAME} -d [OR]" . PHP_EOL;
            $content .= "    RewriteCond %{REQUEST_FILENAME} -l" . PHP_EOL;
            $content .= "    RewriteRule ^.*$ - [L]" . PHP_EOL . PHP_EOL;
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
        $icon = ".." . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img". DIRECTORY_SEPARATOR . "favicon.ico";

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
        $js = $assets . DIRECTORY_SEPARATOR . "js";
        $directory = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "js";

        // Check if directory exists
        if(!is_dir($js) && !is_file($js) && !is_link($js)) {

            // Create symbolic link
            symlink($directory, $js);
        }

        // Update the status
        $status = $status && is_link($js);

        // Path to directory
        $core = $assets . DIRECTORY_SEPARATOR . "core";
        $directory = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "laswitchtech" . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "assets";

        // Check if directory exists
        if(!is_dir($core) && !is_file($core) && !is_link($core)) {

            // Create symbolic link
            symlink($directory, $core);
        }

        // Update the status
        $status = $status && is_link($core);

        // Path to directory
        $img = $assets . DIRECTORY_SEPARATOR . "img";
        $directory = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img";

        // Check if directory exists
        if(!is_dir($img) && !is_file($img) && !is_link($img)) {

            // Create symbolic link
            symlink($directory, $img);
        }

        // Update the status
        $status = $status && is_link($img);

        // Path to directory
        $plugins = $assets . DIRECTORY_SEPARATOR . "plugins";
        $lib = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "plugins";

        // Check if directory exists
        if(!is_dir($plugins) && !is_file($plugins) && !is_link($plugins)) {

            // Create symbolic link
            symlink($lib, $plugins);
        }

        // Update the status
        $status = $status && is_link($plugins);

        // Path to directory
        $themes = $assets . DIRECTORY_SEPARATOR . "themes";
        $lib = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "themes";

        // Check if directory exists
        if(!is_dir($themes) && !is_file($themes) && !is_link($themes)) {

            // Create symbolic link
            symlink($lib, $themes);
        }

        // Update the status
        $status = $status && is_link($themes);

        // Path to directory
        $cli = $CONFIG->root() . DIRECTORY_SEPARATOR . "cli";

        // Check if directory exists
        if(is_file($cli)) {

            // Set permissions to executable
            chmod($cli, 0755);

            // Update the status
            $status = $status && is_executable($cli);
        }

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

    /**
     * Fetch a JSON file (public or private) and return it as an associative array.
     *
     * @param string      $url    Full URL to the file or GitHub API endpoint.
     * @param string|null $token  Personal‑access token (or fine‑grained token).
     * @return array              Decoded JSON (or an empty array if the URL responds with HTTP 404).
     * @throws RuntimeException   On network errors, other HTTP errors, or JSON decode errors.
     */
    public function retrieve(string $url, ?string $token = null): array
    {
        $ch       = curl_init($url);
        $headers  = ['User-Agent: Core-Framework'];

        // Ask GitHub’s REST API for the raw file
        if (preg_match('#^https?://api\.github\.com/#', $url)) {
            $headers[] = 'Accept: application/vnd.github.raw';
        }

        // Optional authentication
        if ($token !== null) {
            $headers[] = "Authorization: Bearer {$token}";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $body = curl_exec($ch);

        if ($body === false) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // ── HTTP error handling ───────────────────────────────────────
        if ($status === 404) {
            return [];                    // “not found” → empty result
        }

        if ($status >= 400) {             // any other 4xx/5xx → exception
            throw new RuntimeException("HTTP $status returned for $url");
        }

        // ── Decode JSON ───────────────────────────────────────────────
        $data = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // If we accidentally received GitHub’s wrapper JSON, unwrap it
            if (isset($data['encoding'], $data['content']) && $data['encoding'] === 'base64') {
                $decoded = json_decode(base64_decode($data['content'], true), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException(
                        'JSON decode error (inner content): ' . json_last_error_msg()
                    );
                }
                return $decoded;
            }
            return $data;                 // regular JSON
        }

        throw new RuntimeException('JSON decode error: ' . json_last_error_msg());
    }

    /**
     * Download a file
     *
     * @param string $url
     * @param string $destination
     * @return bool
     */
    public function download(string $url, string $destination, $token = null): bool
    {
        // Check if the URL is valid
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Retrieve the name
        $name = $this->Config->get('installer','name');

        // Check if the destination directory exists
        if(!is_dir(dirname($destination))){
            mkdir(dirname($destination), 0755, true);
        }

        // Check if the destination file exists
        if(file_exists($destination)){
            unlink($destination);
        }

        // Initialize curl
        $cURL = curl_init($url);

        // Set Headers
        $headers = [
            'User-Agent: ' . $name,
            'Accept: application/octet-stream',
        ];
        if (!is_null($token) && !empty($token)) {
            $headers[] = 'Authorization: token ' . $token;
        }

        // Set options for the cURL request
        $cURLOptions = [
            // Provide metadata
            CURLOPT_USERAGENT => $name,
            // Insert Headers
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $headers,
            // Return the transfer as a string
            CURLOPT_RETURNTRANSFER => true,
            // Handle Redirections
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            // Handle Connection Timeout
            CURLOPT_TIMEOUT => 30,
            // Disable SSL Verification
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        // Set the cURL options
        curl_setopt_array($cURL, $cURLOptions);

        // Execute the request
        $stream = curl_exec($cURL);
        $status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
        $error = curl_error($cURL);

        // Close cURL session
        curl_close($cURL);

        // Check if the request was successful
        if ($status !== 200) {
            return false;
        }

        // Create the file using file_put_contents
        $result = file_put_contents($destination, $stream);
        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Unpack (extract) a zip archive to a given location.
     *
     * @param string $source Path to the ZIP file.
     * @param string $destination Directory where files should be extracted.
     * @return bool true on success, false on failure
     */
    public function unpack(string $source, string $destination): bool
    {
        // Check if the archive file exists
        if (!file_exists($source) || !is_file($source)) {
            return false;
        }

        // Attempt to create the destination directory if it doesn't exist
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            return false;
        }

        // Initialize a new ZipArchive instance
        $zip = new ZipArchive();

        // Try opening the ZIP file
        if ($zip->open($source) !== true) {
            return false;
        }

        // Extract the contents to the specified destination
        if (!$zip->extractTo($destination)) {
            $zip->close();
            return false;
        }

        // Close the ZIP
        $zip->close();

        // Done
        return true;
    }

    /**
     * Recursively delete a directory (including its contents).
     *
     * @param string $directory Path to the directory you want to remove
     * @return bool true on success, false on failure
     */
    public function delete(string $directory): bool
    {
        // If it doesn't exist, treat it as an error or success depending on your preference
        if (!file_exists($directory)) {
            // Option 1: Treat as an error
            return false;
        }

        // If it's a file or symlink, just unlink it
        if (!is_dir($directory)) {
            if (!@unlink($directory)) {
                return false;
            }
            return true;
        }

        // Otherwise, recursively remove contents
        $items = scandir($directory);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            // Skip pointers
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            // Recursively call delete on each item
            if (!$this->delete($path)) {
                // If any item fails to be deleted, return false
                return false;
            }
        }

        // Finally, remove the now-empty directory
        if (!@rmdir($directory)) {
            return false;
        }

        return true;
    }

    /**
     * Load the extensions urls from the configuration file
     *
     * @return array
     */
    public function loadExtensions(): array
    {
        // Configure extended listing
        $listing = $this->Config->get('extensions');

        // Set the path to the library folder
        $listPath = $this->Config->root() . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "extensions.cfg";

        // Loop through the listing to retrieve additional details.
        foreach($listing as $type => $extensions){

            // Loop through the extensions
            foreach($extensions as $base => $extension){

                // Set the source in the extension
                $listing[$type][$base]['source'] = $this->Config->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $base;
            }
        }

        // Load the core extensions
        $corePath = $this->Config->root() . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "laswitchtech" . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "extensions.cfg";
        if(file_exists($corePath)){

            // Loop through the core extensions
            foreach(json_decode(file_get_contents($corePath) ?? "[]", true) as $type => $extensions){

                // Loop through the extensions
                foreach($extensions as $base => $extension){

                    // Check if the type is already defined
                    if(!array_key_exists($type, $listing)){
                        $listing[$type] = [];
                    }

                    // Check if the extension is already defined
                    if(!array_key_exists($base, $listing[$type])){

                        // Add the extension to the listing
                        $listing[$type][$base] = $extension;

                        // Set the source in the extension
                        $listing[$type][$base]['source'] = $corePath;
                    }
                }
            }
        }

        // Loop through the existing modules to load additional plugins and themes.
        foreach($listing['modules'] ?? [] as $name => $module){

            // Check if the extension is already installed
            $modulePath = $this->Config->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $name;
            if(is_dir($modulePath) && file_exists($modulePath . DIRECTORY_SEPARATOR . "listing.cfg")){

                // Load the module listing
                foreach(json_decode(file_get_contents($modulePath . DIRECTORY_SEPARATOR . "listing.cfg") ?? "[]",true) as $type => $extensions){

                    // Loop through the extensions
                    foreach($extensions as $base => $extension){

                        // Check if the type is already defined
                        if(!array_key_exists($type, $listing)){
                            $listing[$type] = [];
                        }

                        // Check if the extension is already defined
                        if(!array_key_exists($base, $listing[$type])){

                            // Add the extension to the listing
                            $listing[$type][$base] = $extension;

                            // Set the source in the extension
                            $listing[$type][$base]['source'] = $modulePath . DIRECTORY_SEPARATOR . "listing.cfg";
                        }
                    }
                }
            }
        }

        return $listing;
    }

    /**
     * Load the extensions Meta from the configuration file
     *
     * @param bool $local If true, also load local extensions
     * @return array
     */
    public function loadExtensionsMeta(bool $local = false): array
    {
        // Configure extended listing
        $listing = $this->loadExtensions();

        // Initialize the meta array
        $meta = [
            'modules' => [],
            'plugins' => [],
            'themes'  => [],
        ];

        // Set the path to the library folder
        $libPath = $this->Config->root() . DIRECTORY_SEPARATOR . "lib";

        // Loop through the listing to retrieve additional details.
        foreach($listing as $type => $extensions){

            // Set the path to the type folder
            $typePath = $libPath . DIRECTORY_SEPARATOR . $type;

            // Loop through the extensions
            foreach($extensions as $base => $extension){

                // Load the extension info
                $meta[$type][$base] = $this->retrieve($extension['url'], $extension['token'] ?? null);

                // Set the source
                $meta[$type][$base]['source'] = $extension['source'] ?? null;

                // Set the token
                $meta[$type][$base]['token'] = $extension['token'] ?? null;

                // Set the path to the extension folder
                $extensionPath = $typePath . DIRECTORY_SEPARATOR . $base;

                // Set the path to the info file
                $infoPath = $extensionPath . DIRECTORY_SEPARATOR . "info.cfg";

                // Set the path to the git folder
                $gitPath = $extensionPath . DIRECTORY_SEPARATOR . ".git";

                // Set the path to the HEAD file
                $headPath = $gitPath . DIRECTORY_SEPARATOR . "HEAD";

                // Set the extension path
                $meta[$type][$base]['path'] = $extensionPath;

                // Set the installed status
                $meta[$type][$base]['installed'] = is_dir($extensionPath) && file_exists($infoPath);

                // Set the git status
                $meta[$type][$base]['git'] = is_dir($gitPath) && file_exists($headPath);

                // Set the publish status
                $meta[$type][$base]['published'] = true;

                // Check if the extension is installed
                if($meta[$type][$base]['installed']){

                    // Load the info file
                    $info = json_decode(file_get_contents($infoPath) ?? "[]", true);

                    // Set the current version
                    $meta[$type][$base]['current'] = $info['version'];
                } else {

                    // Set the current version to the version from the repository
                    $meta[$type][$base]['current'] = $meta[$type][$base]['version'];
                }

                // Compare the current version with the latest version and set the latest version
                $meta[$type][$base]['latest'] = !version_compare($meta[$type][$base]['current'], $meta[$type][$base]['version'] ?? $meta[$type][$base]['current'], '<');
            }
        }

        // Check if we also want to load local extensions
        if($local){

            // Scan the directory for extensions
            $types = array_diff(scandir($libPath), ['..', '.', '.DS_Store', 'skeleton', 'init.sh', 'publish.sh', 'tokens.sh']);

            // Loop through the listing to retrieve additional details.
            foreach($types as $type){

                // Set the path to the type folder
                $typePath = $libPath . DIRECTORY_SEPARATOR . $type;

                // Scan the directory for extensions
                $extensions = array_diff(scandir($typePath), ['..', '.', '.DS_Store', 'skeleton', 'init.sh', 'publish.sh', 'tokens.sh']);

                // Loop through the extensions
                foreach($extensions as $base){

                    // Check if the extension is already defined
                    if(!array_key_exists($base, $meta[$type])){

                        // Set the path to the extension folder
                        $extensionPath = $typePath . DIRECTORY_SEPARATOR . $base;

                        // Set the path to the info file
                        $infoPath = $extensionPath . DIRECTORY_SEPARATOR . "info.cfg";

                        // Set the path to the git folder
                        $gitPath = $extensionPath . DIRECTORY_SEPARATOR . ".git";

                        // Set the path to the HEAD file
                        $headPath = $gitPath . DIRECTORY_SEPARATOR . "HEAD";

                        // Check if the extension has an info file
                        if(file_exists($infoPath)){

                            // Load the extension info
                            $meta[$type][$base] = json_decode(file_get_contents($infoPath) ?? "[]", true);

                            // Set the extension path
                            $meta[$type][$base]['path'] = $extensionPath;

                            // Set the installed status
                            $meta[$type][$base]['installed'] = is_dir($extensionPath) && file_exists($infoPath);

                            // Set the git status
                            $meta[$type][$base]['git'] = is_dir($gitPath) && file_exists($headPath);

                            // Set the publish status
                            $meta[$type][$base]['published'] = false;

                            // Set the current version to the version from the repository
                            $meta[$type][$base]['current'] = $meta[$type][$base]['version'];

                            // Compare the current version with the latest version and set the latest version
                            $meta[$type][$base]['latest'] = !version_compare($meta[$type][$base]['current'], $meta[$type][$base]['version'], '<');

                            // Set the source
                            $meta[$type][$base]['source'] = null;
                        }
                    }
                }
            }
        }

        return $meta;
    }

    /**
     * Get the repository information from a URL
     *
     * @param string $url The URL of the repository
     * @return array An associative array containing the repository information
     */
    public function getRepo(string $url): array
    {
        // Initialize array
        $array = [
            'owner' => null,
            'repo'  => null,
            'url'   => null,
            'token' => null,
            'branch' => null,
        ];

        // Check if the repository is from GitHub
        if(preg_match('#^https?://github\.com/#', $url)) {

            // Extract the repository owner and name
            if(preg_match('#^https?://github\.com/([^/]+)/([^/]+)(?:\.git)?$#', $url, $matches)) {
                $array['owner'] = $matches[1];
                $array['repo']  = $matches[2];
                $array['url']   = "https://api.github.com/repos/{$matches[1]}/{$matches[2]}/contents/info.cfg";
            } else {
                throw new RuntimeException("The repository URL {$url} is not a valid GitHub repository.");
            }
        }

        // Check if the repository is from GitLab
        elseif(preg_match('#^https?://gitlab\.com/#', $url)) {

            // Extract the repository owner and name
            if(preg_match('#^https?://gitlab\.com/([^/]+)/([^/]+)(?:\.git)?$#', $url, $matches)) {
                $array['owner'] = $matches[1];
                $array['repo']  = $matches[2];
                $array['url']   = "https://gitlab.com/api/v4/projects/{$matches[1]}%2F{$matches[2]}/repository/files/info.cfg/raw";
            } else {
                throw new RuntimeException("The repository URL {$url} is not a valid GitLab repository.");
            }
        }

        return $array;
    }

    /**
     * Check if an extension is installed
     *
     * @param string $name The name of the extension
     * @param string $type The type of the extension (e.g., 'plugins', 'themes', 'modules')
     * @return bool True if the extension is installed, false otherwise
     */
    public function isInstalled(string $name, string $type = 'plugins'): bool
    {
        // Set the path to the type folder
        $typePath = $this->Config->root() . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $type;

        // Set the path to the extension folder
        $extensionPath = $typePath . DIRECTORY_SEPARATOR . $name;

        // Check if the extension is installed
        return is_dir($extensionPath) && file_exists($extensionPath . DIRECTORY_SEPARATOR . "info.cfg");
    }

    /**
     * Render menu
     *
     * @param array $items
     * @return string
     */
    public function menu(array $items): string
    {
        $html = '<ul class="nav nav-pills flex-column px-2">';
        foreach($items as $route => $item) {
            $html .= $this->item($route, $item, 1);
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Render menu item
     *
     * @param string $id
     * @param array $menu
     * @param int $level
     * @return string
     */
    private function item(string $id, array $menu, int $level): string
    {

        // Import Global Variables
        global $LOCALE;

        $label = $menu['label'];
        $icon = $menu['icon'];
        $link = $menu['link'];
        $items = $menu['items'];

        if ($level == 1) {
            $html = '<li class="nav-item">';
        } else {
            $html = '<li class="nav-item ps-2">';
        }
        if (count($items) > 0) {
            $html .= '<button class="nav-link w-100 text-start" data-route="'.$link.'" data-bs-toggle="collapse" data-bs-target="#menu'. str_replace('/','-',$link) .'-'.$level.'" role="button" aria-expanded="false" aria-controls="menu'. str_replace('/','-',$link) .'-'.$level.'"><i class="bi bi-'. $icon .' me-2"></i><span class="">'. $label .'</span></button>';
            $html .= '<div class="collapse" id="menu'. str_replace('/','-',$link) .'-'.$level.'"><ul class="nav nav-pills flex-column">';
            foreach($items as $route => $item) {
                $html .= $this->item($route, $item, $level + 1);
            }
            $html .= '</ul></div>';
        } else {
            $html .= '<a class="nav-link" href="'.$link.'"><i class="bi bi-'.$icon.' me-2"></i><span class="">'. $LOCALE->get($label) .'</span></a>';
        }
        $html .= '</li>';
        return $html;
    }

    /**
     * Render breadcrumbs
     *
     * @return string
     */
    public function crumbs(): string
    {
        // Import Global Variables
        global $LOCALE, $BUILDER;

        // Initialize the variables
        $html = '';
        foreach($BUILDER->crumbs() as $crumb){
            $html .= '<a class="nav-link" href="' . $crumb['link'] . '">';
            $html .= '<i class="me-1 bi bi-' . $crumb['icon'] . '"></i>';
            $html .= '<span class="brand">' . $LOCALE->get($crumb['label']) . '</span>';
            $html .= '</a>';
        }

        return $html;
    }
}
