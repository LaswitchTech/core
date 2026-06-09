<?php

/**
 * Endpoint Template
 * 
 * A scaffold for a new endpoint in the framework.
 */

namespace [PLUGIN_NAMESPACE];

use \LaswitchTech\Core\Abstracts\Endpoint;

class [CLASS_NAME] extends Endpoint {

    public function __construct() {
        parent::__construct();
        
        // Set access level
        $this->Level = 1;
    }

    /**
     * Default action for the endpoint
     */
    public function indexAction() {
        return [
            'status' => 200,
            'message' => 'OK',
            'data' => []
        ];
    }
}