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
// use lessc;
use Exception;

class Style {

    // Constants

    // Global Properties
    private $Config;

    // Properties
    private $CSS = '';
    private $Compiler;

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

        // Initialize the Less Compiler
        // $this->Compiler = new lessc;
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
                    return $this->sanitize($content);
                }
            }
        }

        return '';
    }

    private function load()
    {
        // Set Path
        $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'css';

        // Check if the path exists
        if(is_dir($path)) {

            // Loop through the files
            foreach(array_diff(scandir($path), ['..', '.']) as $file) {

                // Add the content to the CSS
                $this->CSS .= $this->extract($path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    /**
     * Compile the CSS
     */
    public function compile()
    {
        try {
            $this->load();
            return $less->compile(this->CSS);
        } catch (exception $e) {
            echo "fatal error: " . $e->getMessage();
        }
    }
}
