<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal classes into the global namespace
use Exception;
use DateTime;

class MigrationRunner {
    
    protected $Database;
    protected $migrationDir;
    protected $migrationTable = 'migrations';
    protected $appliedMigrations = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $DATABASE, $CONFIG;

        // Initialize Properties
        $this->Database = $DATABASE;
        
        // Set migration directory
        $this->migrationDir = $CONFIG->root() . DIRECTORY_SEPARATOR . 'migrations';
        
        // Create migrations table if it doesn't exist
        $this->createMigrationTable();
    }

    /**
     * Create migrations table if it doesn't exist
     */
    protected function createMigrationTable(): void
    {
        try {
            $query = $this->Database->query()
                ->table($this->migrationTable)
                ->select('*')
                ->limit(1);
                
            // If we can select from the table, it exists
            $query->result();
        } catch (Exception $e) {
            // Table doesn't exist, create it
            $createSql = "
                CREATE TABLE IF NOT EXISTS `{$this->migrationTable}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `version` varchar(255) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `status` enum('pending','applied','failed') NOT NULL DEFAULT 'pending',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `applied_at` datetime DEFAULT NULL,
                    `error_message` text DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `version` (`version`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            
            $this->Database->query()->raw($createSql)->execute();
        }
    }

    /**
     * Get all migration files from the migrations directory
     *
     * @return array
     */
    public function getMigrations(): array
    {
        $migrations = [];
        
        if (!is_dir($this->migrationDir)) {
            return $migrations;
        }

        // Get all PHP files that match the migration pattern (001_name.php)
        $files = glob($this->migrationDir . DIRECTORY_SEPARATOR . "[0-9][0-9][0-9]_*.php");
        
        foreach ($files as $file) {
            $filename = basename($file);
            $version = substr($filename, 0, 3); // First 3 characters are version
            $name = substr($filename, 4, -4); // Remove version prefix and .php suffix
            
            // Replace underscores with spaces for display
            $displayName = str_replace('_', ' ', ucfirst($name));
            
            $migrations[] = [
                'version' => $version,
                'name' => $name,
                'display_name' => $displayName,
                'file' => $file,
                'status' => $this->getMigrationStatus($version)
            ];
        }
        
        // Sort by version
        usort($migrations, function($a, $b) {
            return version_compare($a['version'], $b['version']);
        });
        
        return $migrations;
    }

    /**
     * Get migration status from database
     *
     * @param string $version
     * @return string
     */
    protected function getMigrationStatus(string $version): string
    {
        try {
            $result = $this->Database->query()
                ->table($this->migrationTable)
                ->select('status')
                ->where('version', $version)
                ->limit(1)
                ->result();
                
            return !empty($result) ? $result[0]['status'] : 'pending';
        } catch (Exception $e) {
            return 'pending';
        }
    }

    /**
     * Get applied migrations
     *
     * @return array
     */
    public function getAppliedMigrations(): array
    {
        try {
            $result = $this->Database->query()
                ->table($this->migrationTable)
                ->select('*')
                ->where('status', 'applied')
                ->order('created_at', 'ASC')
                ->result();
                
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Apply a specific migration
     *
     * @param string $version
     * @return bool
     */
    public function applyMigration(string $version): bool
    {
        // Check if migration is already applied
        $status = $this->getMigrationStatus($version);
        if ($status === 'applied') {
            return true;
        }
        
        // Get migration file
        $migrationFile = $this->findMigrationFile($version);
        if (!$migrationFile) {
            throw new Exception("Migration file not found for version {$version}");
        }

        try {
            // Mark migration as started
            $this->updateMigrationStatus($version, 'pending');
            
            // Include and run the migration
            require_once $migrationFile;
            
            // Get migration class name (should match file name pattern)
            $className = 'Migration_' . str_replace(['-', '.'], '_', basename($version));
            $className = preg_replace('/[^a-zA-Z0-9_]/', '', $className); // Sanitize class name
            
            // Create the migration instance and run it
            if (class_exists($className)) {
                $migration = new $className();
                $migration->up($this->Database);
            } else {
                // Fallback to direct function approach
                $functionName = 'migrate_' . str_replace('.', '_', $version);
                if (function_exists($functionName)) {
                    $functionName($this->Database);
                }
            }
            
            // Mark as applied
            $this->updateMigrationStatus($version, 'applied');
            return true;
        } catch (Exception $e) {
            // Mark as failed
            $this->updateMigrationStatus($version, 'failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Rollback a specific migration
     *
     * @param string $version
     * @return bool
     */
    public function rollbackMigration(string $version): bool
    {
        $status = $this->getMigrationStatus($version);
        if ($status !== 'applied') {
            return false;
        }
        
        $migrationFile = $this->findMigrationFile($version);
        if (!$migrationFile) {
            throw new Exception("Migration file not found for version {$version}");
        }

        try {
            // Include and run the rollback
            require_once $migrationFile;
            
            // Get migration class name 
            $className = 'Migration_' . str_replace(['-', '.'], '_', basename($version));
            $className = preg_replace('/[^a-zA-Z0-9_]/', '', $className); // Sanitize class name
            
            // Create the migration instance and rollback
            if (class_exists($className)) {
                $migration = new $className();
                $migration->down($this->Database);
            } else {
                // Fallback to direct function approach for rollback
                $functionName = 'rollback_' . str_replace('.', '_', $version);
                if (function_exists($functionName)) {
                    $functionName($this->Database);
                }
            }
            
            // Mark as rolled back (pending)
            $this->updateMigrationStatus($version, 'pending');
            return true;
        } catch (Exception $e) {
            // Mark as failed
            $this->updateMigrationStatus($version, 'failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find migration file for a specific version
     *
     * @param string $version
     * @return string|null
     */
    protected function findMigrationFile(string $version): ?string
    {
        $pattern = $this->migrationDir . DIRECTORY_SEPARATOR . $version . '_*.php';
        $files = glob($pattern);
        
        return !empty($files) ? $files[0] : null;
    }

    /**
     * Update migration status in database
     *
     * @param string $version
     * @param string $status
     * @param string|null $errorMessage
     * @return bool
     */
    protected function updateMigrationStatus(string $version, string $status, ?string $errorMessage = null): bool
    {
        try {
            // Check if record exists
            $existing = $this->Database->query()
                ->table($this->migrationTable)
                ->select('id')
                ->where('version', $version)
                ->limit(1)
                ->result();
                
            if (!empty($existing)) {
                // Update existing
                $data = [
                    'status' => $status,
                    'error_message' => $errorMessage
                ];
                
                if ($status === 'applied') {
                    $data['applied_at'] = date('Y-m-d H:i:s');
                }
                
                $this->Database->query()
                    ->table($this->migrationTable)
                    ->update($data)
                    ->where('version', $version)
                    ->execute();
            } else {
                // Insert new record
                $this->Database->query()
                    ->table($this->migrationTable)
                    ->insert([
                        'version' => $version,
                        'name' => 'Unknown',
                        'status' => $status,
                        'error_message' => $errorMessage
                    ])
                    ->execute();
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Run all pending migrations
     *
     * @return array
     */
    public function runPending(): array
    {
        $migrations = $this->getMigrations();
        $results = [];
        
        foreach ($migrations as $migration) {
            if ($migration['status'] === 'pending') {
                try {
                    $this->applyMigration($migration['version']);
                    $results[$migration['version']] = ['success' => true];
                } catch (Exception $e) {
                    $results[$migration['version']] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }
        
        return $results;
    }

    /**
     * Get migration status summary
     *
     * @return array
     */
    public function getStatus(): array
    {
        $migrations = $this->getMigrations();
        $summary = [
            'total' => count($migrations),
            'applied' => 0,
            'pending' => 0,
            'failed' => 0
        ];
        
        foreach ($migrations as $migration) {
            switch ($migration['status']) {
                case 'applied':
                    $summary['applied']++;
                    break;
                case 'pending':
                    $summary['pending']++;
                    break;
                case 'failed':
                    $summary['failed']++;
                    break;
            }
        }
        
        return $summary;
    }

    /**
     * Create a new migration file template
     *
     * @param string $name
     * @return string
     */
    public function createMigration(string $name): string
    {
        // Generate next version number
        $migrations = $this->getMigrations();
        $nextVersion = '001';
        
        if (!empty($migrations)) {
            $lastVersion = end($migrations)['version'];
            $nextVersion = str_pad((int)$lastVersion + 1, 3, '0', STR_PAD_LEFT);
        }
        
        // Sanitize name
        $sanitized_name = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $name));
        
        // Create file content
        $content = '<?php

/**
 * Migration: ' . ucfirst(str_replace('_', ' ', $sanitized_name)) . '
 * 
 * @author Your Name
 * @version ' . $nextVersion . '
 */

class Migration_' . $nextVersion . '_' . ucfirst($sanitized_name) . '
{
    public function up($Database)
    {
        // Migration code here
    }
    
    public function down($Database)
    {
        // Rollback code here  
    }
}
';
        
        $filename = $this->migrationDir . DIRECTORY_SEPARATOR . $nextVersion . '_' . $sanitized_name . '.php';
        
        file_put_contents($filename, $content);
        
        return $filename;
    }
}