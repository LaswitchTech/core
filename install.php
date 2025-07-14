<?php
// Usage: php install.php <repository> [<token>]
if ($argc < 2) {
    die("Usage: php install.php <repository> [<token>]\n");
}

// Get command line arguments
$repository = $argv[1];
$token = isset($argv[2]) ? $argv[2] : null;

// Check if required extensions are loaded
if (!extension_loaded('curl')) {
    die("cURL extension is not loaded.");
}
if (!class_exists('ZipArchive')) {
    die("ZipArchive class is not available.");
}

// Check if required commands are available
function checkCommand($command): bool
{
    exec("which $command", $output, $returnVar);
    return $returnVar === 0;
}
$commands = ['php', 'wget', 'git'];
foreach ($commands as $cmd) {
    if (!checkCommand($cmd)) {
        die($cmd . " is not installed or not in the PATH.");
    }
}

// Create a temporary directory for installation
$tmp = __DIR__ . '/tmp';
if (!is_dir($tmp)) {
    mkdir($tmp, 0755, true);
}

// Step 1: Get the latest release from GitHub API
function getReleases(): array
{
    // Import global variables
    global $repository, $token;

    // Set the API endpoint
    $endpoint = "https://api.github.com/repos/$repository/releases";

    // Initialize curl
    $cURL = curl_init($endpoint);

    // Set Headers
    $headers = [
        'User-Agent: ' . $repository,
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
$releases = getReleases();
if(empty($releases)){
    die("Could not retrieve the releases.");
}
$latest = $releases[array_key_first($releases)];
$assets = $latest['assets'];
$version = $latest['tag_name'];

// Step 2: Retrieve the assets
foreach($assets as $asset){
    if($asset['name'] == $version.".zip"){
        $archive = $asset['url'];
    }
    if($asset['name'] == $version.".sha256"){
        $checksum = $asset['url'];
    }
}
if(!isset($archive) || !isset($checksum)){
    die("Could not find the archive and/or checksum.");
}

// Step 3: Download the release ZIP file
function download(string $url, string $destination): bool
{
    // Import global variables
    global $repository, $token;

    // Check if the URL is valid
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

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
        'User-Agent: ' . $repository,
        'Accept: application/octet-stream',
    ];
    if (!is_null($token) && !empty($token)) {
        $headers[] = 'Authorization: token ' . $token;
    }

    // Set options for the cURL request
    $cURLOptions = [
        // Provide metadata
        CURLOPT_USERAGENT => $repository,
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
download($archive, $tmp . DIRECTORY_SEPARATOR . $version . '.zip');
download($checksum, $tmp . DIRECTORY_SEPARATOR . $version . '.sha256');
if(!file_exists($tmp . DIRECTORY_SEPARATOR . $version . '.zip')){
    die("Could not download the file(s).");
}

// Step 4: Validate the checksum
function getChecksum(string $path): string
{
    // Check if the file exists
    if (!file_exists($path)) {
        return '';
    }

    // Get the checksum from the file
    $checksum = file_get_contents($path);
    if ($checksum === false) {
        return '';
    }

    // Return the checksum
    return trim(explode(" ", $checksum)[0]);
}
function validate(string $path, string $checksum): bool
{
    // Check if the file exists
    if (!file_exists($path)) {
        return false;
    }

    // Calculate the checksum of the file
    $fileChecksum = hash_file('sha256', $path);

    // Compare the checksums
    return hash_equals($fileChecksum, $checksum);
}
if(!validate($tmp . DIRECTORY_SEPARATOR . $version . '.zip', getChecksum($tmp . DIRECTORY_SEPARATOR . $version . '.sha256'))){
    die("Checksum validation failed.");
}

// Step 5: Extract the ZIP file
function extractArchive(string $source, string $destination): bool
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
if(!extractArchive($tmp . DIRECTORY_SEPARATOR . $version . '.zip', __DIR__)){
    die("Could not extract the archive.");
}

// Step 6: Setup the environment
function setupEnvironment(): bool
{
    // Set the path to the Composer executable
    $Path = __DIR__ . DIRECTORY_SEPARATOR . '.composer';

    // Set the user home directory
    putenv('HOME=' . $Path);
    putenv('COMPOSER_HOME=' . $Path);

    // Create the home directory if it doesn't exist
    if (!is_dir($Path)) {
        mkdir($Path, 0755, true);
    }

    // Check if the auth.json file exists
    $authFile = $Path . DIRECTORY_SEPARATOR . 'auth.json';
    if(!is_file($authFile)){

        // Create the auth.json file with default content
        $config = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'installer.cfg') ?? '[]', true);
        $defaultContent = json_encode($config['composer']['auth'] ?? [], JSON_PRETTY_PRINT);
        file_put_contents($authFile, $defaultContent);
    }

    return is_file($authFile);
}
if(!setupEnvironment()){
    die("Could not setup the environment.");
}

// Step 7: Install Composer
function installComposer(string $destination): bool
{
    try {

        // Download the latest Composer installer
        $installerPath = $destination . DIRECTORY_SEPARATOR . 'composer-setup.php';
        file_put_contents($installerPath, file_get_contents('https://getcomposer.org/installer'));

        // Verify the installer's signature (optional but recommended)
        $signature = file_get_contents('https://composer.github.io/installer.sig');
        if (!hash_equals(hash_file('sha384', $installerPath), trim($signature))) {
            unlink($installerPath);
            return false;
        }

        // Run the Composer installer
        chdir($destination);
        $command = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php ' . escapeshellarg(basename($installerPath)) . ' --install-dir=' . escapeshellarg($destination) . ' --filename=composer.phar';
        exec($command, $output, $exitCode);
        chdir(__DIR__);

        // Create a symlink to the Composer executable
        $composerPath = $destination . DIRECTORY_SEPARATOR . 'composer.phar';
        $symlinkPath = __DIR__ . DIRECTORY_SEPARATOR . 'composer';
        if (file_exists($symlinkPath)) {
            unlink($symlinkPath);
        }
        symlink($composerPath, $symlinkPath);

        return $exitCode === 0;
    } catch (\Exception $e) {
        return false;
    }
}
if(!installComposer(__DIR__ . DIRECTORY_SEPARATOR . '.composer')){
    die("Could not install Composer.");
}

// Step 8: Install Dependencies
function installDependencies(string $path): bool
{
    try {
        chdir(__DIR__);
        // Install dependencies using Composer
        $command = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php ' . escapeshellarg(basename($path)) . ' install --no-dev --no-interaction --prefer-dist';
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    } catch (\Exception $e) {
        return false;
    }
}
if(!installDependencies(__DIR__ . DIRECTORY_SEPARATOR . 'composer')){
    die("Could not install dependencies.");
}

// Step 9: Cleanup
function cleanup(string $directory): bool
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
        if (!cleanup($path)) {
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
if(!cleanup(__DIR__ . DIRECTORY_SEPARATOR . '.composer' . DIRECTORY_SEPARATOR . 'composer-setup.php')){
    die("Could not delete the composer installer.");
}
if(!cleanup($tmp)){
    die("Could not delete the temporary directory.");
}

// Step 10: Execute the initialization script
function executeCMD(string $cmd): bool
{
    // Run the command
    exec($cmd, $output, $exitCode);

    // Show output when something goes wrong
    if ($exitCode !== 0) {
        echo implode("\n", $output) . "\n";
    }
    return $exitCode === 0;
}
if(!executeCMD(PHP_BINDIR . DIRECTORY_SEPARATOR . 'php cli core init')){
    die("Could not execute the initialization script.");
}

// Step 11: Install the required extensions
function installExtensions(): bool
{
    // Load the extensions to install from the config file
    $configPath = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'requirement.cfg';

    // Check if the config file exists
    if (!file_exists($configPath)) {
        return false;
    }

    // Load the config file
    $config = json_decode(file_get_contents($configPath), true);

    // Check if the config is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    // Loop through the extensions (modules) and install them
    foreach ($config['modules'] as $extension) {

        // Execute the command to install the extension
        if(!executeCMD(PHP_BINDIR . DIRECTORY_SEPARATOR . 'php cli core extension install modules ' . escapeshellarg($extension))){
            return false;
        }
    }

    // Loop through the extensions (plugins) and install them
    foreach ($config['plugins'] as $extension) {

        // Execute the command to install the extension
        if(!executeCMD(PHP_BINDIR . DIRECTORY_SEPARATOR . 'php cli core extension install plugins ' . escapeshellarg($extension))){
            return false;
        }
    }

    // Loop through the extensions (themes) and install them
    foreach ($config['themes'] as $extension) {

        // Execute the command to install the extension
        if(!executeCMD(PHP_BINDIR . DIRECTORY_SEPARATOR . 'php cli core extension install themes ' . escapeshellarg($extension))){
            return false;
        }
    }

    // Return true if all extensions were installed successfully
    return true;
}

echo "Installation completed successfully.\n";
