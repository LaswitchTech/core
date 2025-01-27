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
        "LOG" => [
            "class" => "\\LaswitchTech\\Core\\Log",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "REQUEST" => [
            "class" => "\\LaswitchTech\\Core\\Request",
            "scope" => [
                "Router",
                "API"
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
        "NET" => [
            "class" => "\\LaswitchTech\\coreNet\\Net",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "HELPERS" => [
            "class" => "\\LaswitchTech\\Core\\Helpers",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "DATABASE" => [
            "class" => "\\LaswitchTech\\coreDatabase\\Database",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "MODELS" => [
            "class" => "\\LaswitchTech\\Core\\Models",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "LOCALE" => [
            "class" => "\\LaswitchTech\\coreLocale\\Locale",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "ENCRYPTION" => [
            "class" => "\\LaswitchTech\\coreEncryption\\Encryption",
            "scope" => []
        ],
        "CSRF" => [
            "class" => "\\LaswitchTech\\coreCSRF\\CSRF",
            "scope" => [
                "Router",
                "API"
            ]
        ],
        "SLS" => [
            "class" => "\\LaswitchTech\\coreSLS\\SLS",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "SMS" => [
            "class" => "\\LaswitchTech\\coreSMS\\SMS",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "SMTP" => [
            "class" => "\\LaswitchTech\\coreSMTP\\SMTP",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "IMAP" => [
            "class" => "\\LaswitchTech\\coreIMAP\\IMAP",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "INSTALLER" => [
            "class" => "\\LaswitchTech\\coreInstaller\\Installer",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "AUTH" => [
            "class" => "\\LaswitchTech\\coreAuth\\Auth",
            "scope" => [
                "Router",
                "API"
            ]
        ],
        "ROUTER" => [
            "class" => "\\LaswitchTech\\coreRouter\\Router",
            "scope" => [
                "Router"
            ]
        ],
        "API" => [
            "class" => "\\LaswitchTech\\coreAPI\\API",
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
    public function __construct($scope){

        // Set the global variable
        global $CONFIG;

        // Initialize Config
        $CONFIG = new Config(['bootstrap']);

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
