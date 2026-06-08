<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Log;
use Exception;

class AuditLogger {
    
    protected $Database;
    protected $Log;
    protected $isDebug;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $DATABASE, $LOG, $CONFIG;

        // Initialize Properties
        $this->Database = $DATABASE;
        $this->Log = $LOG;
        $this->isDebug = $CONFIG->get('app', 'debug') ?? false;
    }

    /**
     * Log an audit event to the database
     *
     * @param string $type
     * @param string $message
     * @param array $payload
     * @return bool
     */
    public function log(string $type, string $message, array $payload = []): bool
    {
        // Import Global Variables
        global $AUTH, $REQUEST;

        // Get current user
        $user = $AUTH->isLoaded() ? $AUTH->user() : null;
        $userId = $user ? $user->id : 0;
        
        // Set IP address
        $ip = $REQUEST->getParams('SERVER', 'REMOTE_ADDR') ?? 'UNKNOWN';

        // Create audit log entry
        try {
            $Query = $this->Database->query()
                ->table('admin_audit_log')
                ->insert([
                    'user' => $userId,
                    'type' => $type,
                    'message' => $message,
                    'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                    'ip' => $ip,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);

            $result = $Query->execute();

            // Log to debug log if in debug mode
            if ($this->isDebug) {
                $this->Log->debug("Audit log: {$type} - {$message}", 'audit');
            }

            return $result > 0;
        } catch (Exception $e) {
            // Log error to main logger
            $this->Log->error("Failed to write audit log entry: " . $e->getMessage(), 'audit');
            return false;
        }
    }

    /**
     * Get audit logs with optional filtering
     *
     * @param array $filters
     * @param int $limit
     * @return array
     */
    public function get(array $filters = [], int $limit = 100): array
    {
        // Build query
        $Query = $this->Database->query()
            ->table('admin_audit_log')
            ->select('*')
            ->order('timestamp', 'DESC')
            ->limit($limit);

        // Apply filters if provided
        foreach ($filters as $field => $value) {
            $Query->where($field, $value);
        }

        return $Query->result();
    }

    /**
     * Log authentication event
     *
     * @param string $event
     * @param array $details
     * @return bool
     */
    public function logAuth(string $event, array $details = []): bool
    {
        $message = "Authentication event: {$event}";
        return $this->log('auth', $message, $details);
    }

    /**
     * Log system event
     *
     * @param string $event
     * @param array $details
     * @return bool
     */
    public function logSystem(string $event, array $details = []): bool
    {
        $message = "System event: {$event}";
        return $this->log('system', $message, $details);
    }

    /**
     * Log security event
     *
     * @param string $event
     * @param array $details
     * @return bool
     */
    public function logSecurity(string $event, array $details = []): bool
    {
        $message = "Security event: {$event}";
        return $this->log('security', $message, $details);
    }
}