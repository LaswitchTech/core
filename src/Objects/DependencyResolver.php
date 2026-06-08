<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal classes into the global namespace
use Exception;
use DirectoryIterator;
use stdClass;

class DependencyResolver {
    
    protected $Database;
    protected $pluginsDir;
    protected $plugins = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $DATABASE, $CONFIG;

        // Initialize Properties
        $this->Database = $DATABASE;
        
        // Set plugins directory
        $this->pluginsDir = $CONFIG->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        
        // Load available plugins
        $this->loadPlugins();
    }

    /**
     * Load all available plugins and their dependency information
     */
    protected function loadPlugins(): void
    {
        if (!is_dir($this->pluginsDir)) {
            return;
        }

        $iterator = new DirectoryIterator($this->pluginsDir);
        
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                $pluginName = $fileInfo->getFilename();
                
                // Check if plugin has info.cfg
                $infoFile = $this->pluginsDir . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . 'info.cfg';
                if (file_exists($infoFile)) {
                    $this->plugins[$pluginName] = $this->parsePluginInfo($infoFile);
                }
            }
        }
    }

    /**
     * Parse plugin info.cfg file
     *
     * @param string $infoFile
     * @return array
     */
    protected function parsePluginInfo(string $infoFile): array
    {
        $pluginInfo = [
            'name' => '',
            'version' => '0.0.0',
            'dependencies' => [],
            'requires' => []
        ];

        // Parse INI file
        if ($config = parse_ini_file($infoFile, true)) {
            // Get plugin name
            if (isset($config['plugin']['name'])) {
                $pluginInfo['name'] = $config['plugin']['name'];
            }

            // Get plugin version
            if (isset($config['plugin']['version'])) {
                $pluginInfo['version'] = $config['plugin']['version'];
            }

            // Parse dependencies section
            if (isset($config['dependencies'])) {
                foreach ($config['dependencies'] as $dependency => $versionConstraint) {
                    $pluginInfo['dependencies'][$dependency] = $versionConstraint;
                }
            }

            // Parse requires section for backwards compatibility
            if (isset($config['requires'])) {
                foreach ($config['requires'] as $requirement => $versionConstraint) {
                    $pluginInfo['requires'][$requirement] = $versionConstraint;
                }
            }
        }

        return $pluginInfo;
    }

    /**
     * Validate plugin dependencies
     *
     * @param string $pluginName
     * @return array
     */
    public function validateDependencies(string $pluginName): array
    {
        if (!isset($this->plugins[$pluginName])) {
            return [
                'valid' => false,
                'error' => "Plugin {$pluginName} not found"
            ];
        }

        $plugin = $this->plugins[$pluginName];
        $errors = [];

        // Check dependencies
        foreach ($plugin['dependencies'] as $dependency => $constraint) {
            if (!$this->isDependencySatisfied($dependency, $constraint)) {
                $errors[] = "Missing dependency: {$dependency} ({$constraint})";
            }
        }

        // Check requires (backwards compatibility)
        foreach ($plugin['requires'] as $requirement => $constraint) {
            if (!$this->isDependencySatisfied($requirement, $constraint)) {
                $errors[] = "Missing requirement: {$requirement} ({$constraint})";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if a specific dependency is satisfied
     *
     * @param string $dependency
     * @param string $constraint
     * @return bool
     */
    protected function isDependencySatisfied(string $dependency, string $constraint): bool
    {
        // If the dependency plugin doesn't exist, then it's not satisfied
        if (!isset($this->plugins[$dependency])) {
            return false;
        }

        $dependencyVersion = $this->plugins[$dependency]['version'];
        
        // Parse constraint to check if version is compatible
        return $this->compareVersions($dependencyVersion, $constraint);
    }

    /**
     * Compare plugin version against constraint
     *
     * @param string $version
     * @param string $constraint
     * @return bool
     */
    protected function compareVersions(string $version, string $constraint): bool
    {
        // Remove extra whitespace
        $constraint = trim($constraint);
        
        // Handle different constraint formats
        if (strpos($constraint, '>=') === 0) {
            $requiredVersion = trim(substr($constraint, 2));
            return version_compare($version, $requiredVersion, '>=');
        } elseif (strpos($constraint, '<=') === 0) {
            $requiredVersion = trim(substr($constraint, 2));
            return version_compare($version, $requiredVersion, '<=');
        } elseif (strpos($constraint, '>') === 0) {
            $requiredVersion = trim(substr($constraint, 1));
            return version_compare($version, $requiredVersion, '>');
        } elseif (strpos($constraint, '<') === 0) {
            $requiredVersion = trim(substr($constraint, 1));
            return version_compare($version, $requiredVersion, '<');
        } elseif (strpos($constraint, '==') === 0) {
            $requiredVersion = trim(substr($constraint, 2));
            return version_compare($version, $requiredVersion, '==');
        } else {
            // If no operator, assume exact match
            return version_compare($version, $constraint, '==');
        }
    }

    /**
     * Check if installation would be possible
     *
     * @param string $pluginName
     * @return array
     */
    public function canInstall(string $pluginName): array
    {
        // First check if plugin exists
        if (!isset($this->plugins[$pluginName])) {
            return [
                'allowed' => false,
                'reason' => "Plugin {$pluginName} does not exist"
            ];
        }

        // Validate dependencies
        $dependencyCheck = $this->validateDependencies($pluginName);
        
        if (!$dependencyCheck['valid']) {
            return [
                'allowed' => false,
                'reason' => implode("; ", $dependencyCheck['errors'])
            ];
        }

        return [
            'allowed' => true,
            'reason' => ''
        ];
    }

    /**
     * Check if enabling a plugin is allowed
     *
     * @param string $pluginName
     * @return array
     */
    public function canEnable(string $pluginName): array
    {
        // For now, just check if it can be installed (same logic applies)
        return $this->canInstall($pluginName);
    }

    /**
     * Check if disabling a plugin is allowed
     *
     * @param string $pluginName
     * @return array
     */
    public function canDisable(string $pluginName): array
    {
        // For now, we'll allow it (dependencies are validated at install time)
        return [
            'allowed' => true,
            'reason' => ''
        ];
    }

    /**
     * Check if uninstallation is allowed
     *
     * @param string $pluginName
     * @return array
     */
    public function canUninstall(string $pluginName): array
    {
        // For now, we'll allow it (dependencies are validated at install time)
        return [
            'allowed' => true,
            'reason' => ''
        ];
    }

    /**
     * Get list of all available plugins with their metadata
     *
     * @return array
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get plugin information
     *
     * @param string $pluginName
     * @return array|null
     */
    public function getPlugin(string $pluginName): ?array
    {
        return $this->plugins[$pluginName] ?? null;
    }

    /**
     * Check for conflicts between plugins
     *
     * @param array $pluginList
     * @return array
     */
    public function checkConflicts(array $pluginList): array
    {
        $conflicts = [];
        $dependencies = [];

        // Collect all dependencies from specified plugins
        foreach ($pluginList as $pluginName) {
            if (isset($this->plugins[$pluginName])) {
                $plugin = $this->plugins[$pluginName];
                
                // Add direct dependencies
                foreach ($plugin['dependencies'] as $depName => $depConstraint) {
                    if (!isset($dependencies[$depName])) {
                        $dependencies[$depName] = [];
                    }
                    $dependencies[$depName][] = [
                        'plugin' => $pluginName,
                        'constraint' => $depConstraint
                    ];
                }
            }
        }

        // Check for conflicting dependencies (multiple plugins requiring different versions of same plugin)
        foreach ($dependencies as $depName => $depInfoList) {
            if (count($depInfoList) > 1) {
                // Check if they have the same constraint
                $constraints = array_unique(array_column($depInfoList, 'constraint'));
                if (count($constraints) > 1) {
                    $conflicts[] = [
                        'plugin' => $depName,
                        'conflicting_plugins' => array_column($depInfoList, 'plugin'),
                        'constraints' => $constraints
                    ];
                }
            }
        }

        return $conflicts;
    }
}