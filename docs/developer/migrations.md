# Migration System

This document describes the migration system used in the Core-Web framework. Migrations provide versioned database schema updates that can be applied and rolled back as needed.

## Overview

The migration system allows for versioned database schema changes using PHP files that define up and down methods to apply or rollback changes.

## Directory Structure

Migrations are stored in the `migrations/` directory with the following naming pattern:

```
001_create_users.php
002_add_email_to_users.php
003_create_orders_table.php
```

Each migration file follows the numeric prefix (3 digits) and underscore convention.

## Migration File Format

Migration files should contain a PHP class that extends the migration functionality:

```php
<?php

/**
 * Migration: Create Users Table
 * 
 * @author Your Name
 * @version 001
 */

class Migration_001_create_users
{
    public function up($Database)
    {
        // Code to create or modify database tables
        $Database->query()
            ->table('users')
            ->create([
                'id' => 'int(11) NOT NULL AUTO_INCREMENT',
                'username' => 'varchar(255) NOT NULL',
                'email' => 'varchar(255) NOT NULL',
                'created_at' => 'datetime DEFAULT CURRENT_TIMESTAMP',
                'PRIMARY KEY (id)'
            ]);
    }
    
    public function down($Database)
    {
        // Code to rollback database changes
        $Database->query()
            ->table('users')
            ->drop();
    }
}
```

## Using MigrationRunner

MigrationRunner is used for running and managing migrations:

```php
use LaswitchTech\Core\Objects\MigrationRunner;

$migrationRunner = new MigrationRunner();

// Apply all pending migrations
$migrationRunner->runPending();

// Apply a specific migration
$migrationRunner->applyMigration('001');

// Rollback a specific migration  
$migrationRunner->rollbackMigration('001');

// Get migration status
$status = $migrationRunner->getStatus();
```

## Migration Tracking

The system tracks applied migrations in the `migrations` table with columns:
- `id`: Unique identifier
- `version`: Migration version number 
- `name`: Migration name
- `status`: Current status (`pending`, `applied`, `failed`)
- `created_at`: When migration was created
- `applied_at`: When migration was applied
- `error_message`: Error message if failed

## Integration with Plugin Lifecycle

Migrations are automatically run when plugins are installed and can be rolled back when plugins are uninstalled. This ensures database schema changes match plugin lifecycle operations.

## Testing Migrations

When testing migrations, ensure:
1. Each up() method properly creates the necessary database structures
2. Each down() method correctly reverses the changes  
3. Migration files are well-named and organized
4. Error handling is in place for failed migrations
5. Test rollback scenarios to ensure data integrity

## Example Migration

Here's a complete example of a migration file:

```php
<?php

/**
 * Migration: Add Email Column to Users
 * 
 * @author Your Name
 * @version 002
 */

class Migration_002_add_email_to_users
{
    public function up($Database)
    {
        // Add email column to users table
        $Database->query()
            ->table('users')
            ->addColumn([
                'email' => 'varchar(255) NOT NULL DEFAULT ""'
            ]);
            
        // Update existing records with default values if needed
        $Database->query()
            ->table('users')
            ->update(['email' => ''])
            ->where('email', '', '=');  
    }
    
    public function down($Database)
    {
        // Remove email column from users table
        $Database->query()
            ->table('users')
            ->dropColumn('email');
    }
}
```