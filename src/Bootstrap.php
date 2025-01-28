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
use LaswitchTech\Core\Strap;
use Exception;

class Bootstrap {

    const Default = [
        "REQUEST" => [
            "class" => "\\LaswitchTech\\Core\\Request",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "OUTPUT" => [
            "class" => "\\LaswitchTech\\Core\\Output",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "LOG" => [
            "class" => "\\LaswitchTech\\Core\\Log",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "CSRF" => [
            "class" => "\\LaswitchTech\\Core\\CSRF",
            "scope" => [
                "Router",
                "API"
            ]
        ],
        "NET" => [
            "class" => "\\LaswitchTech\\Core\\Net",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "HELPER" => [
            "class" => "\\LaswitchTech\\Core\\Helpers",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "DATABASE" => [
            "class" => "\\LaswitchTech\\Core\\Database",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "MODEL" => [
            "class" => "\\LaswitchTech\\Core\\Models",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "LOCALE" => [
            "class" => "\\LaswitchTech\\Core\\Locale",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "ENCRYPTION" => [
            "class" => "\\LaswitchTech\\Core\\Encryption",
            "scope" => []
        ],
        "SLS" => [
            "class" => "\\LaswitchTech\\Core\\SLS",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "SMS" => [
            "class" => "\\LaswitchTech\\Core\\SMS",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "SMTP" => [
            "class" => "\\LaswitchTech\\Core\\SMTP",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "IMAP" => [
            "class" => "\\LaswitchTech\\Core\\IMAP",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "INSTALLER" => [
            "class" => "\\LaswitchTech\\Core\\Installer",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "UPDATER" => [
            "class" => "\\LaswitchTech\\Core\\Updater",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "AUTH" => [
            "class" => "\\LaswitchTech\\Core\\Auth",
            "scope" => [
                "Router",
                "API"
            ]
        ],
        "ROUTER" => [
            "class" => "\\LaswitchTech\\Core\\Router",
            "scope" => [
                "Router"
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

    /**
     * Constructor.
     */
    public function __construct(string $scope){

        // Set the global variable
        global $CONFIG;

        // Start the session
        if (!defined('STDIN') && session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.cookie_secure', 'On');
            session_start();
        }

        // Initialize Config
        $CONFIG = new Config('bootstrap');

        // Retrieve the straps
        $straps = $CONFIG->get('bootstrap');

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
            if(!in_array($scope,$config['scope'])) continue;

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
                ${$strap} = new Strap();
            }
        }
    }
}
