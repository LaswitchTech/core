<?php

/**
 * Core Framework - CoreEndpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Endpoint;

class CoreEndpoint extends Endpoint {

    /**
     * Constructor
     */
    public function __construct()
    {

        // Call Parent Constructor
        parent::__construct();

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Set Global access
        $this->Public = true;

        // Set Level
        switch($namespace){
            case "/core/info":
                $this->Level = 0;
                break;
        }
    }

    public function infoAction()
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Check if the Note is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Retrieve the name of the application
                $message['data']['name'] = $this->Config->add('application')->get('application', 'name');

                // Retrieve the current version
                $message['data']['version'] = $this->Config->version();

                // Retrieve the owner of the application
                $message['data']['owner'] = $this->Config->add('application')->get('application', 'owner');

                // Retrieve the copyright of the application
                $message['data']['copyright'] = [
                    "from" => intval($this->Config->add('application')->get('application', 'copyright')),
                    "to" => date("Y")
                ];

                // Retrieve the icon
                $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.png';
                if(!file_exists($path)){
                    $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'logo.png';
                }
                if(!file_exists($path)){
                    $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'icon.png';
                }
                if(file_exists($path)){
                    $message['data']['logo'] = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
                }

                // Retrieve the License
                $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'LICENSE';
                if(file_exists($path)){
                    $content = file_get_contents($path);
                    $type = trim(preg_split('/\r\n|\r|\n/', $content)[0]);
                    $message['data']['license'] = [
                        "type" => $type,
                        "content" => $content
                    ];
                }

                // Retrieve the Authors
                $path = $this->Config->root() . DIRECTORY_SEPARATOR . 'AUTHORS';
                if(file_exists($path)){
                    $content = file_get_contents($path);
                    $authors = preg_split('/\r\n|\r|\n/', $content);
                    $array = [];
                    foreach($authors as $author){
                        $author = trim($author);
                        if(empty($author)){
                            continue;
                        }
                        $parts = explode("<", $author);
                        $name = trim($parts[0], '"');
                        $url = trim($parts[1], ">");
                        $array[] = [
                            "name" => $name,
                            "url" => $url
                        ];
                    }
                    $message['data']['authors'] = $array;
                }
            } else {
                $message = ["status" => 400, "message" => "Bad Request", "data" => "Invalid Request Method"];
            }
        }

        return $message;
    }
}
