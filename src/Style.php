<?php

/**
 * Core Framework - Style
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Less_Parser;
use Less_Exception_Parser;
use Exception;

class Style {

    // Constants

    // Global Properties
    private $Config;

    // Properties
    private $CSS = '';
    private $compress = true;
    private $vars = [];
    private $importDirs = [];

    /**
     * Constructor
     */
    public function __construct()
    {

        // Global Variables
        global $CONFIG;

        // Set Global Properties
        $this->Config = $CONFIG;

        // Configure Globals
        $this->Config->add('css')->add('style');
    }

    /**
     * Get the CSS
     *
     * @param string $string
     * @return string
     */
    private function sanitize(string $string): string
    {
        // 1) Remove /* … */  (non‑greedy, dot matches new‑lines with the s‑modifier)
        $string = preg_replace('#/\*.*?\*/#s', '', $string);

        // 2) Remove // … to end‑of‑line (m‑modifier = multiline, so ^ and $ work per line)
        $string  = preg_replace('#//.*$#m', '', $string);

        // 3) Remove every whitespace character (space, tab, CR, LF, FF, etc.)
        // \s in PCRE covers all of those.
        $string   = preg_replace('/\s+/', '', $string);

        // Return the sanitized string (or '' if everything was removed)
        return $string ?? '';
    }

    /**
     * Extract the CSS from a file
     *
     * @param string $path
     * @return string
     */
    private function extract(string $path): string
    {
        // Check if the file is a CSS file
        if(pathinfo($path, PATHINFO_EXTENSION) === 'less') {

            // Check if the file is readable
            if(is_readable($path)) {

                // Get the file content
                $content = file_get_contents($path);

                // Check if the content is not empty
                if(!empty($content)) {

                    // Add the content to the CSS
                    return $content . PHP_EOL;
                }
            }
        }

        return '';
    }

    /**
     * Read the styles from a directory
     *
     * @param string $path
     * @return void
     */
    private function read(string $path): void
    {
        // Check if the path exists
        if(is_dir($path)){

            // Check if the path contains a styles.cfg file
            if(is_file($path . DIRECTORY_SEPARATOR . 'styles.cfg')){

                // Get the file content
                $content = file_get_contents($path . DIRECTORY_SEPARATOR . 'styles.cfg');

                // Check if the content is not empty
                if(!empty($content)) {

                    // Parse the JSON content
                    $config = json_decode($content, true);

                    // Loop through the stylesheets to load
                    foreach($config['stylesheets'] ?? [] as $stylesheet => $scope){

                        // Check if the stylesheet is a CSS file
                        $this->CSS .= $this->extract($path . DIRECTORY_SEPARATOR . $stylesheet);
                    }
                }
            }
        }
    }

    /**
     * Load the CSS files
     *
     * @return void
     */
    private function load(): void
    {
        // Read the dist/css directory
        $this->read($this->Config->root() . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'css');

        // Set Path
        $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';

        // Check if the path exists
        if(is_dir($path)){

            // Loop through the files
            foreach(array_diff(scandir($path), ['..', '.']) as $file) {

                // Read the plugin styles
                $this->read($path . DIRECTORY_SEPARATOR . $file);
            }
        }

        // Retrieve the current theme
        $theme = $this->Config->get('application','theme') ?? ($this->Config->get('style','theme') ?? $this->Config->get('installer','theme'));

        // Set Path
        $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme;

        // Check if the path exists
        if(is_dir($path)){

            // Read the theme styles
            $this->read($path);
        }
    }

    /**
     * Compile the CSS
     *
     * @return string
     */
    public function compile(): string
    {
        $this->load();

        $parser = new Less_Parser([
            'compress' => $this->compress,
        ]);

        if ($this->importDirs) {
            $parser->SetImportDirs($this->importDirs);
        }

        try {
            $parser->parse($this->CSS);

            if ($this->vars) {
                $parser->ModifyVars($this->vars);
            }

            return $parser->getCss();
        } catch (Less_Exception_Parser $e) {
            // Wrap to keep external interface clean
            throw new Exception('LESS compile error: ' . $e->getMessage(), 0, $e);
        }
    }
}
