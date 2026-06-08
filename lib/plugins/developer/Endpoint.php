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
        // This would contain the actual scaffolding logic
        // For now we'll return just a placeholder with mock files created
        $generated = [
            'type' => $type,
            'name' => $name,
            'timestamp' => date('Y-m-d H:i:s'),
            'files_created' => 1
        ];
        
        // In a real implementation, this would:
        // - Validate the type
        // - Check if files already exist 
        // - Generate appropriate files in the correct locations
        // - Handle directory creation
        // - Return list of created files
        
        return $generated;
    }
}