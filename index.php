<?php
// Core-Web — Project Root Entry Point
// This file serves as the entry point for shared hosting environments
// where the document root cannot be configured.
// For advanced setups, point the document root to webroot/ and use webroot/index.php instead.

// Load Composer autoloader
require_once __DIR__ . "/vendor/autoload.php";

// Bootstrap Core-Web
$BOOTSTRAP = new \LaswitchTech\Core\Bootstrap("ROUTER");
