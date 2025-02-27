<?php

/**
 * Core Framework - Bootstrap
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Config;
use LaswitchTech\Core\Module;
use Exception;

class Bootstrap {

    // Constant
    const Default = [
        // Utilities
        "UUID" => [
            "class" => "\\LaswitchTech\\Core\\UUID",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        "ENCRYPTION" => [
            "class" => "\\LaswitchTech\\Core\\Encryption",
            "scope" => []
        ],
        // Input/Output
        "REQUEST" => [
            "class" => "\\LaswitchTech\\Core\\Request",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        "OUTPUT" => [
            "class" => "\\LaswitchTech\\Core\\Output",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Logging
        "LOG" => [
            "class" => "\\LaswitchTech\\Core\\Log",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Locale
        "LOCALE" => [
            "class" => "\\LaswitchTech\\Core\\Locales",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Network
        "NET" => [
            "class" => "\\LaswitchTech\\Core\\Net",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Helpers
        "HELPER" => [
            "class" => "\\LaswitchTech\\Core\\Helpers",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Database
        "DATABASE" => [
            "class" => "\\LaswitchTech\\Core\\Database",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        "MODEL" => [
            "class" => "\\LaswitchTech\\Core\\Models",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Authentication
        "AUTH" => [
            "class" => "\\LaswitchTech\\Core\\Auth",
            "scope" => [
                "ROUTER",
                "API"
            ]
        ],
        // Security
        "CSRF" => [
            "class" => "\\LaswitchTech\\Core\\CSRF",
            "scope" => [
                "ROUTER",
                "API"
            ]
        ],
        // Communications
        "SMS" => [
            "class" => "\\LaswitchTech\\Core\\SMS",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        "SMTP" => [
            "class" => "\\LaswitchTech\\Core\\SMTP",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        "IMAP" => [
            "class" => "\\LaswitchTech\\Core\\IMAP",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Licensing
        "SLS" => [
            "class" => "\\LaswitchTech\\Core\\SLS",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Styles and Scripts
        "STYLE" => [
            "class" => "\\LaswitchTech\\Core\\Style",
            "scope" => [
                "ROUTER"
            ]
        ],
        "BUILDER" => [
            "class" => "\\LaswitchTech\\Core\\Builder",
            "scope" => [
                "ROUTER"
            ]
        ],
        // Installation and Update
        "INSTALLER" => [
            "class" => "\\LaswitchTech\\Core\\Installer",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        "UPDATER" => [
            "class" => "\\LaswitchTech\\Core\\Updater",
            "scope" => [
                "ROUTER",
                "API",
                "CLI"
            ]
        ],
        // Routing
        "ROUTER" => [
            "class" => "\\LaswitchTech\\Core\\Router",
            "scope" => [
                "ROUTER"
            ]
        ],
        "API" => [
            "class" => "\\LaswitchTech\\Core\\API",
            "scope" => [
                "API"
            ]
        ],
        "CLI" => [
            "class" => "\\LaswitchTech\\Core\\CLI",
            "scope" => [
                "CLI"
            ]
        ]
    ];

    // Global Properties
    private $Config;

    // Properties
    private $scope;

    /**
     * Constructor.
     */
    public function __construct(string $scope)
    {
        // Start the session
        if (!defined('STDIN') && session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.cookie_secure', 'On');
            session_start();
        }

        // Set the global variable
        global $CONFIG;

        // Initialize Config
        $CONFIG = new Config('bootstrap');
        $this->Config = $CONFIG;

        // Set the scope
        $this->scope = strtoupper($scope);

        // Load the Bootstrap
        $this->load();

        // Start the Bootstrap
        $this->start();
    }

    /**
     * Load the Bootstrap.
     *
     * @return self
     */
    private function load(): self
    {
        // Retrieve the straps
        $straps = $this->Config->get('bootstrap');

        // Loop through the straps
        foreach(self::Default as $strap => $config){

            // Check if an alternate strap exist
            if(isset($straps[$strap])){

                // Loop through the alternate strap
                foreach($config as $key => $value){

                    // Check if the alternate strap has the key
                    if(isset($straps[$strap][$key])){

                        // Set the alternate strap
                        $config[$key] = $straps[$strap][$key];
                    }
                }
            }

            // Check if strap is not the scope
            if(!in_array($this->scope,$config['scope'])) continue;

            // Check if the strap is already initialized
            if(isset($GLOBALS[$strap]) || isset(${$strap})) continue;

            // Initialize the Global Variable
            global ${$strap};

            // Set Class
            $class = $config['class'];

            // Check if the class exists
            if(class_exists($class)){

                // Initialize the class in the global namespace
                ${$strap} = new $class();
            } else {

                // Set default to null
                ${$strap} = new Module();
            }
        }

        return $this;
    }

    /**
     * Start the Bootstrap.
     *
     * @return self
     */
    private function start(): self
    {
        // Initialize the scope
        global ${$this->scope};
        if(!in_array(get_class(${$this->scope}),["Module","LaswitchTech\Core\Module"])){
            if(method_exists(${$this->scope},'start')){
                ${$this->scope}->start();
            }
        }

        return $this;
    }
}
