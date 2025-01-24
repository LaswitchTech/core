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
use LaswitchTech\coreConfigurator\Configurator;
use Exception;

class Bootstrap {

    const Default = [
        "CONFIGURATOR" => [
            "class" => "\\LaswitchTech\\coreConfigurator\\Configurator",
            "scope" => [
                "Router",
                "API",
                "CLI"
            ]
        ],
        "LOGGER" => [
            "class" => "\\LaswitchTech\\coreLogger\\Logger",
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
        "DATABASE" => [
            "class" => "\\LaswitchTech\\coreDatabase\\Database",
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
        "INPUT" => [
            "class" => "\\LaswitchTech\\Core\\Input",
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
            "class" => "\\LaswitchTech\\coreCLI\\CLI",
            "scope" => [
                "CLI"
            ]
        ]
    ];

	// core Modules
	private $Configurator;

    /**
     * Constructor.
     */
    public function __construct($scope){

        // Initialize Configurator
        $this->Configurator = new Configurator(['bootstrap']);

        // Retrieve the straps
        $straps = $this->Configurator->get('bootstrap');

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

            // Set default to null
            ${$strap} = null;

            // Set Class
            $class = $config['class'];

            // Check if the class exists
            if(class_exists($class)){

                // Initialize the class in the global namespace
                ${$strap} = new $class();
            }
        }
    }
}
