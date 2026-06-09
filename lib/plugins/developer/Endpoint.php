<?php

/**
 * Core Framework - DeveloperEndpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Endpoint;

class DeveloperEndpoint extends Endpoint {

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
        $this->Public = false;

        // Set Level
        switch($namespace){
            case "/developer":
                $this->Level = 1;
                break;
        }
    }

    /**
     * Index action for developer page
     */
    public function indexAction()
    {
        // Import Global Variables
        global $AUTH, $VIEW, $REQUEST;

        // Check if user is authenticated and has proper permissions
        if (!$AUTH->isAuthenticated() || !$AUTH->isAuthorized('Developer', 1)) {
            header('Location: ?login');
            exit;
        }

        // Prepare data for template
        $data = [
            'title' => 'Developer Tools',
            'development_mode' => $this->Config->get('application', 'development') ?? false,
            'maintenance_mode' => $this->Config->get('application', 'maintenance') ?? false,
            'installer_mode' => $this->Config->get('application', 'installed') ?? true,
            'logger_level' => $this->Config->get('log', 'level') ?? 0,
            'plugins' => $this->getPluginsList(),
            'scaffold_templates' => $this->getScaffoldTemplates()
        ];

        // Render the developer tools page
        $VIEW->render('admin/developer/index', $data);
    }

    /**
     * Get list of available plugins
     */
    protected function getPluginsList(): array
    {
        $plugins = [];
        $pluginsDir = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        
        if (is_dir($pluginsDir)) {
            $iterator = new DirectoryIterator($pluginsDir);
            
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                    $pluginName = $fileInfo->getFilename();
                    
                    // Check if plugin has info.cfg
                    $infoFile = $pluginsDir . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . 'info.cfg';
                    if (file_exists($infoFile)) {
                        $config = parse_ini_file($infoFile, true);
                        $plugins[] = [
                            'name' => isset($config['plugin']['name']) ? $config['plugin']['name'] : $pluginName,
                            'version' => isset($config['plugin']['version']) ? $config['plugin']['version'] : '0.0.0',
                            'description' => isset($config['plugin']['description']) ? $config['plugin']['description'] : '',
                            'enabled' => true // Assuming all loaded plugins are enabled
                        ];
                    }
                }
            }
        }
        
        return $plugins;
    }

    /**
     * Get available scaffold templates
     */
    protected function getScaffoldTemplates(): array
    {
        $templates = [];
        $templateDir = $this->Config->root() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'scaffolds';
        
        if (is_dir($templateDir)) {
            // Return a list of possible templates
            $templates = ['plugin', 'endpoint', 'model', 'controller', 'layout'];
        }
        
        return $templates;
    }

    /**
     * Scaffold generator endpoint
     */
    public function scaffoldAction()
    {
        // Import Global Variables
        global $AUTH, $REQUEST;

        // Check if user is authenticated and has proper permissions
        if (!$AUTH->isAuthenticated() || !$AUTH->isAuthorized('Developer', 1)) {
            return ["status" => 403, "message" => "Access Denied"];
        }

        // Get request parameters
        $type = $REQUEST->getParams('REQUEST', 'type');
        $name = $REQUEST->getParams('REQUEST', 'name');
        
        if (empty($type) || empty($name)) {
            return ["status" => 400, "message" => "Missing required parameters: type and name"];
        }

        // Generate scaffold
        try {
            $result = $this->generateScaffold($type, $name);
            
            return [
                "status" => 200,
                "message" => "Scaffold generated successfully",
                "data" => $result
            ];
        } catch (Exception $e) {
            return [
                "status" => 500,
                "message" => "Error generating scaffold: " . $e->getMessage()
            ];
        }
    }

    /**
     * Generate scaffold for a given type and name
     */
    protected function generateScaffold(string $type, string $name): array
    {
        // Validate the type
        $validTypes = ['plugin', 'endpoint', 'model', 'controller', 'layout'];
        if (!in_array($type, $validTypes)) {
            throw new Exception('Invalid scaffold type');
        }
        
        // Basic safety checks for name
        if (empty($name) || !preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new Exception('Invalid name for scaffold generation');
        }
        
        $generated = [
            'type' => $type,
            'name' => $name,
            'timestamp' => date('Y-m-d H:i:s'),
            'files_created' => 0
        ];
        
        $created_files = [];
        $base_path = $this->Config->root();
        
        // Create the scaffolding based on type
        switch($type) {
            case 'plugin':
                $plugin_dir = $base_path . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $name;
                
                if (!is_dir($plugin_dir)) {
                    mkdir($plugin_dir, 0755, true);
                }
                
                // Create info.cfg file
                $info_cfg_file = $plugin_dir . DIRECTORY_SEPARATOR . 'info.cfg';
                if (!file_exists($info_cfg_file)) {
                    $info_content = [
                        'name' => ucfirst($name),
                        'type' => 'plugins',
                        'base' => $name,
                        'author' => 'Developer',
                        'email' => '',
                        'date' => date('Y-m-d'),
                        'version' => '1.0.0',
                        'tags' => '',
                        'description' => 'A new plugin for the framework',
                        'repository' => '',
                        'download' => '',
                        'tracker' => '',
                        'support' => '',
                        'picture' => '',
                        'dependencies' => [],
                        'avoid' => []
                    ];
                    
                    file_put_contents($info_cfg_file, json_encode($info_content, JSON_PRETTY_PRINT));
                    $created_files[] = $info_cfg_file;
                }
                
                // Create Endpoint.php file
                $endpoint_file = $plugin_dir . DIRECTORY_SEPARATOR . 'Endpoint.php';
                if (!file_exists($endpoint_file)) {
                    $endpoint_content = "<?php\n\n/**\n * {$name} Plugin - Endpoint\n */\n\nuse \LaswitchTech\Core\Abstracts\Endpoint;\n\nclass {$name}Endpoint extends Endpoint {\n\n    public function __construct() {\n        parent::__construct();\n        // Set level as needed\n        $this->Level = 1;\n    }\n\n    /**\n     * Default action\n     */\n    public function indexAction() {\n        return ['status' => 200, 'message' => 'OK', 'data' => []];\n    }\n}";
                    file_put_contents($endpoint_file, $endpoint_content);
                    $created_files[] = $endpoint_file;
                }
                
                // Create routes.cfg file
                $routes_cfg_file = $plugin_dir . DIRECTORY_SEPARATOR . 'routes.cfg';
                if (!file_exists($routes_cfg_file)) {
                    $route_content = "{\n    \"/{$name}\": {\n        \"template\": null,\n        \"view\": null,\n        \"public\": true,\n        \"action\": null,\n        \"location\": [\"developer\"],\n        \"level\": 0,\n        \"parent\": null,\n        \"label\": \"{$name}\",\n        \"icon\": \"gear\",\n        \"color\": null\n    }\n}";
                    file_put_contents($routes_cfg_file, $route_content);
                    $created_files[] = $routes_cfg_file;
                }
                
                // Create View directory
                $view_dir = $plugin_dir . DIRECTORY_SEPARATOR . 'View';
                if (!is_dir($view_dir)) {
                    mkdir($view_dir, 0755, true);
                }
                
                $generated['files_created'] = count($created_files);
                break;
                
            case 'endpoint':
                // Implementation for endpoint scaffolding
                $generated['files_created'] = 0; // Not implemented yet
                break;
                
            case 'model':
                // Implementation for model scaffolding
                $generated['files_created'] = 0; // Not implemented yet
                break;
                
            case 'controller':
                // Implementation for controller scaffolding
                $generated['files_created'] = 0; // Not implemented yet
                break;
                
            case 'layout':
                // Implementation for layout scaffolding
                $generated['files_created'] = 0; // Not implemented yet
                break;
        }
        
        return array_merge($generated, [
            'created_files' => $created_files
        ]);
    }
}