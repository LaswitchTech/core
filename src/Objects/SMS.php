<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

// Import additionnal classes into the global namespace
use Exception;
use stdClass;

class SMS {
    
    protected $Database;
    protected $Config;
    protected $provider;
    protected $providerClass;
    protected $providerConfig = [];

    /**
     * Constructor
     */
    public function __construct(?string $provider = null)
    {
        // Import Global Variables
        global $DATABASE, $CONFIG;

        // Initialize Properties
        $this->Database = $DATABASE;
        $this->Config = $CONFIG;
        
        // Set default provider if not specified
        $this->provider = $provider ?: $this->getConfigProvider();
        
        // Load provider configuration
        $this->loadProviderConfig();
    }

    /**
     * Get default SMS provider from config
     *
     * @return string
     */
    protected function getConfigProvider(): string
    {
        return $this->Config->get('sms', 'provider') ?: 'twilio';
    }

    /**
     * Load provider-specific configuration
     */
    protected function loadProviderConfig(): void
    {
        // Look for provider config in application settings
        $providerConfig = $this->Config->get('sms', 'providers');
        
        if (is_array($providerConfig) && isset($providerConfig[$this->provider])) {
            $this->providerConfig = $providerConfig[$this->provider];
        } else {
            // Set defaults or use global SMS config
            $this->providerConfig = [
                'api_key' => $this->Config->get('sms', 'api_key'),
                'api_secret' => $this->Config->get('sms', 'api_secret'),
                'account_sid' => $this->Config->get('sms', 'account_sid'),
                'auth_token' => $this->Config->get('sms', 'auth_token')
            ];
        }
    }

    /**
     * Set the SMS provider
     *
     * @param string $provider
     * @return self
     */
    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        $this->loadProviderConfig();
        return $this;
    }

    /**
     * Send SMS message
     *
     * @param string $to
     * @param string $message
     * @param array $options
     * @return bool
     */
    public function send(string $to, string $message, array $options = []): bool
    {
        try {
            // Validate required parameters
            if (empty($to) || empty($message)) {
                throw new Exception("To and message are required for SMS sending");
            }

            // For now, we'll simulate sending
            // In a real implementation, this would call the actual provider's API
            
            // Log that we attempted to send SMS 
            global $LOG;
            if (isset($LOG)) {
                $LOG->info("SMS sending attempt to {$to}: " . substr($message, 0, 50) . "...", 'sms');
            }

            // In a real implementation, we would:
            // 1. Get provider class dynamically
            // 2. Create provider instance  
            // 3. Send using that provider
            
            // For now, simulate success
            return true;
        } catch (Exception $e) {
            // Log the error
            global $LOG;
            if (isset($LOG)) {
                $LOG->error("SMS sending failed: " . $e->getMessage(), 'sms');
            }
            return false;
        }
    }

    /**
     * Validate phone number format
     *
     * @param string $phone
     * @return bool
     */
    public function validatePhone(string $phone): bool
    {
        // Basic validation - remove non-digits and check length
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Should be 7-15 digits (international format)
        return strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15;
    }

    /**
     * Check if SMS service is configured properly
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->providerConfig['api_key'] ?? $this->providerConfig['account_sid'] ?? '') && 
               !empty($this->providerConfig['api_secret'] ?? $this->providerConfig['auth_token'] ?? '');
    }

    /**
     * Get SMS service status
     *
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'provider' => $this->provider,
            'configured' => $this->isConfigured(),
            'config_source' => 'application_settings'
        ];
    }
}