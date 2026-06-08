<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal class into the global namespace
use Exception;
use LaswitchTech\Core\Log;

class DebugAuditLogger extends Log {
    
    protected $Database;
    protected $isDebug = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Import Global Variables
        global $DATABASE, $CONFIG;

        // Initialize Properties
        $this->Database = $DATABASE;
        $this->isDebug = $CONFIG->get('app', 'debug') ?? false;
        
        // Call parent constructor to initialize logging
        parent::__construct();
    }

    /**
     * Log an audit event
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

        // Only log in debug mode
        if (!$this->isDebug) {
            return true;
        }

        // Get current user
        $user = $AUTH->isLoaded() ? $AUTH->user() : null;
        $userId = $user ? $user->id : 0;
        
        // Set IP address
        $ip = $REQUEST->getParams('SERVER', 'REMOTE_ADDR') ?? 'UNKNOWN';

        // Create audit log entry in database
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

            return $Query->execute() > 0;
        } catch (Exception $e) {
            // Log to main logger
            $this->error("Failed to write debug audit log entry: " . $e->getMessage(), 'audit');
            return false;
        }
    }
}