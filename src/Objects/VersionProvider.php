<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal classes into the global namespace
use Exception;

class VersionProvider {
    
    protected $Config;
    protected $kernelVersion;
    protected $appVersion;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $CONFIG;

        // Initialize Properties
        $this->Config = $CONFIG;
        
        // Get kernel version from VERSION file
        $this->kernelVersion = $this->Config->version();
        
        // Get application version from config (if available)
        $this->appVersion = $this->Config->add('application')->get('application', 'version') ?? $this->kernelVersion;
    }

    /**
     * Get kernel version
     *
     * @return string
     */
    public function getKernelVersion(): string
    {
        return $this->kernelVersion;
    }

    /**
     * Get application version
     *
     * @return string
     */
    public function getAppVersion(): string
    {
        return $this->appVersion;
    }

    /**
     * Check if a version is valid semver
     *
     * @param string $version
     * @return bool
     */
    public function isValidVersion(string $version): bool
    {
        // Basic version validation pattern (semantic versioning)
        return (bool) preg_match('/^(\d+\.\d+\.\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/', $version);
    }

    /**
     * Compare two versions
     *
     * @param string $version1
     * @param string $version2
     * @param string $operator
     * @return bool
     */
    public function compare(string $version1, string $version2, string $operator = '=='): bool
    {
        // Handle semver comparison operators
        switch ($operator) {
            case '>':
                return version_compare($version1, $version2, '>');
            case '>=':
                return version_compare($version1, $version2, '>=');
            case '<':
                return version_compare($version1, $version2, '<');
            case '<=':
                return version_compare($version1, $version2, '<=');
            case '==':
            case '=':
            case 'eq':
                return version_compare($version1, $version2, '==');
            case '!=':
            case 'ne':
                return version_compare($version1, $version2, '!=');
            case '~':
                // Tilde operator for patch versioning (e.g., ~1.2.3 = >=1.2.3 <1.3.0)
                $parts1 = explode('.', $version1);
                $parts2 = explode('.', $version2);
                
                if (count($parts1) < 3 || count($parts2) < 3) {
                    return false;
                }
                
                // Check if major and minor versions match
                return ($parts1[0] === $parts2[0]) && ($parts1[1] === $parts2[1]);
            case '^':
                // Caret operator for major versioning (e.g., ^1.2.3 = >=1.2.3 <2.0.0)
                $parts1 = explode('.', $version1);
                $parts2 = explode('.', $version2);
                
                if (count($parts1) < 3 || count($parts2) < 3) {
                    return false;
                }
                
                // Check if major version matches
                return $parts1[0] === $parts2[0];
            default:
                return version_compare($version1, $version2, '==');
        }
    }

    /**
     * Validate that a given extension version is compatible with current kernel version
     *
     * @param string $extensionVersion
     * @param string $compatibilityRule
     * @return bool
     */
    public function isCompatible(string $extensionVersion, string $compatibilityRule): bool
    {
        // Extract the operator and version from rule
        if (preg_match('/^([<>=~^!]+)(.*)/', $compatibilityRule, $matches)) {
            $operator = $matches[1];
            $requiredVersion = trim($matches[2]);
            
            return $this->compare($extensionVersion, $requiredVersion, $operator);
        }
        
        // If no operator found, assume exact match
        return $this->compare($extensionVersion, $compatibilityRule, '==');
    }

    /**
     * Get version information for admin overview
     *
     * @return array
     */
    public function getOverview(): array
    {
        return [
            'kernel_version' => $this->getKernelVersion(),
            'app_version' => $this->getAppVersion(),
            'update_status' => $this->getUpdateStatus()
        ];
    }

    /**
     * Get update status for kernel version
     *
     * @return string
     */
    public function getUpdateStatus(): string
    {
        // In a real implementation this would check GitHub or other sources
        // For now, we'll consider it up-to-date if we have the latest version from local files
        
        // This is a simplified check - in reality we'd need to:
        // 1. Query GitHub API for latest release
        // 2. Compare with current version
        // 3. Return "up-to-date", "available-update", or "security-update"
        
        return 'up-to-date'; // Placeholder
    }
}