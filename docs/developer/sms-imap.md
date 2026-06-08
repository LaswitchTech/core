# SMS and IMAP Services

This document describes the SMS and IMAP service implementations in the Core-Web framework. These services provide integration points for sending SMS messages and parsing emails, which are essential for authentication features like 2FA via SMS and email verification.

## Overview

The SMS and IMAP services are designed to be extensible through a plugin-based architecture, allowing different providers (Twilio, Vonage, etc.) to be easily integrated into the system.

## SMS Service

### Implementation

The `SMS` class (`src/Objects/SMS.php`) provides a unified interface for sending SMS messages:

```php
use LaswitchTech\Core\Objects\SMS;

$sms = new SMS();

// Send an SMS message
$result = $sms->send('+1234567890', 'Hello World!');

// Set specific provider
$sms->setProvider('twilio');
$result = $sms->send('+1234567890', 'Hello World!');
```

### Providers

The SMS service supports multiple providers through a plugin-based architecture:

1. **Twilio Provider**: Default provider for enterprise-grade SMS services
2. **Vonage Provider**: Alternative SMS gateway provider
3. **Generic Provider**: Basic provider that works with simple APIs

### Configuration

SMS configuration is handled through the application settings. Example configuration in `config/application.cfg`:

```ini
[sms]
provider = "twilio"

[providers]
[twilio]  
api_key = "your-twilio-api-key"
api_secret = "your-twilio-api-secret"
account_sid = "your-account-sid"

[vonage]
api_key = "your-vonage-api-key"
api_secret = "your-vonage-api-secret"
```

### Extensibility

Providers are loaded dynamically and can be extended through plugins. To create a custom provider:

1. Create a new plugin with `info.cfg` declaring it as a provider
2. Implement the required API methods in your plugin's PHP files
3. The system will automatically load these providers

## IMAP Service

### Implementation

The `IMAP` class (`src/Objects/IMAP.php`) provides email server integration for parsing incoming messages:

```php
use LaswitchTech\Core\Objects\IMAP;

$imap = new IMAP();

// Connect to IMAP server
$result = $imap->connect();

// Parse verification emails  
$emails = $imap->parseVerificationEmails('user@example.com');
```

### Configuration

IMAP configuration through `config/application.cfg`:

```ini
[imap]
provider = "default"

[providers]
[default]
host = "imap.example.com"
port = 993
username = "your-email@example.com"
password = "your-password"
encryption = "ssl"
```

### Features

1. **Connection Management**: Secure connection to IMAP servers
2. **Email Parsing**: Extract verification codes and other information from emails
3. **Provider Support**: Plugin-based architecture for different email service providers
4. **Error Handling**: Comprehensive error logging and handling

## Integration with Authentication System

The SMS and IMAP services are integrated into the authentication system to support:

### 2FA via SMS
- Generate OTP codes
- Send verification codes via SMS
- Verify codes against user's account

### Email Verification 
- Parse incoming verification emails from IMAP
- Extract and validate verification tokens
- Support for email-to-user mapping

## Extensibility through Plugins

Both services are designed to be fully extensible:

1. **Plugin Structure**: Providers can be implemented as separate plugins
2. **Loading Mechanism**: Automatic provider loading based on configuration
3. **Interface Standardization**: Consistent API across all provider implementations

### Example Plugin Structure for SMS Provider

```
lib/plugins/telico/
├── info.cfg
├── Helper.php  # Telico-specific helper functions
├── SMSProvider.php  # Telico-Specific SMS provider class
└── routes.cfg
```

The framework will automatically detect and load the Telico provider when configured in the application settings.

## Testing with Mock Providers

For testing purposes, mock providers are available that simulate SMS/IMAP behavior without making actual calls to external services. This allows for comprehensive testing while maintaining system compatibility.