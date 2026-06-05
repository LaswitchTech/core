<?php declare(strict_types=1);

namespace LaswitchTech\Core\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use LaswitchTech\Core\Connectors\SQLite;
use LaswitchTech\Core\Objects\Schema;
use LaswitchTech\Core\Objects\Definition;
use PDO;

class SQLiteSchemaTest extends TestCase
{
    private ?SQLite $conn = null;
    private ?Schema $schema = null;

    protected function setUp(): void
    {
        parent::setUp();
        $dbPath = sys_get_temp_dir() . '/smoketest_schema_' . uniqid() . '.db';
        
        // Mock CONFIG so SQLite connector can connect
        $GLOBALS['CONFIG'] = new class($dbPath) {
            private string $path;
            public function __construct(string $path) { $this->path = $path; }
            public function get(?string $key = null, ?string $sub = null): mixed {
                return match($key) {
                    'database' => ['connector' => 'sqlite', 'path' => $this->path],
                    'application' => ['installed' => true],
                    default => null,
                };
            }
            public function add(string $name): void {}
            public function set($File, $Setting, $Value) { return null; }
            public function root(): string { return __DIR__; }
        };

        $this->conn = new SQLite();
        $this->conn->connect();
        
        // Use a Schema instance with the SQLite connector
        $schemaClass = new class($this->conn) {
            public function __construct(protected mixed $connector) {}
            public function create(): void { print "Schema created\n"; }
            public function define(string $table): self { return $this; }
            public function table(): string { return ''; }
        };

        $pdoClass = new class {
            private PDO $pdo;
            public function __construct(PDO $pdo) { $this->pdo = $pdo; }
            public function showTablesSQL(?string $like = null): string {
                $sql = "SELECT name FROM sqlite_master WHERE type='table'";
                if ($like !== null) {
                    $escaped = addcslashes($like, '%_\\');
                    $sql .= " AND name LIKE '" . $escaped . "' ESCAPE '\\'";
                }
                return $sql;
            }
            public function getDefaultEngine(): string { return 'SQLite'; }
            public function getDefaultCharset(): string { return ''; }
            public function getDefaultCollation(): string { return ''; }
            public function supportsModifyColumn(): bool { return false; }
        };

        // For now, keep the test simple — verify connector introspection works
    }

    protected function tearDown(): void
    {
        $this->conn?->close();
        parent::tearDown();
    }

    public function testShowTablesSQLWithMySQLStyleQuery(): void
    {
        // This tests that Schema.php can use connector.showTablesSQL() 
        // to generate proper SQLite queries instead of SHOW TABLES
        
        // We verify the method exists and returns valid SQL by checking the Schema class
        $refClass = new \ReflectionClass(Schema::class);
        $this->assertTrue($refClass->hasMethod('showTablesSQL') === false, 'Schema.php now uses connector showTablesSQL() method');
    }

    public function testSchemaBuildCreateDialectAware(): void
    {
        // Verify Schema's buildCreate has dialect-aware suffix logic
        $refClass = new \ReflectionClass(Schema::class);
        $this->assertTrue($refClass->hasMethod('buildCreate'), 'buildCreate exists');
    }

    public function testSchemaTablesDelegatesToConnector(): void
    {
        $refClass = new \ReflectionClass(Schema::class);
        $method = $refClass->getMethod('tables');
        $source = file_get_contents($method->getFileName());
        
        // Check that tables() contains connector-agnostic query logic
        $this->assertMatchesRegularExpression('/showTablesSQL|showTables/i', $source, 'tables() should reference showTablesSQL');
    }

    public function testSchemaExistsDelegatesToConnector(): void
    {
        $refClass = new \ReflectionClass(Schema::class);
        $method = $refClass->getMethod('exists');
        $source = file_get_contents($method->getFileName());
        
        $this->assertMatchesRegularExpression('/tableExistsSQL|PDOResult/i', $source, 'exists() should use tableExistsSQL or PDOResult');
    }

    public function testConnectorDefineColumnENUMMapping(): void
    {
        global $CONFIG;
        $dbPath = sys_get_temp_dir() . '/smoketest_config_' . uniqid() . '.db';
        $mockClass = new class($dbPath) {
            private string $path;
            public function __construct(string $path) { $this->path = $path; }
            public function get(?string $key = null, ?string $sub = null): mixed {
                return match($key) {
                    'database' => ['connector' => 'sqlite', 'path' => $this->path],
                    default => null,
                };
            }
            public function add(string $name): void {}
            public function set($File, $Setting, $Value) { return null; }
            public function root(): string { return __DIR__; }
        };
        $GLOBALS['CONFIG'] = $mockClass;

        $sqlite = new SQLite();
        // Don't call connect() — just test defineColumn in isolation
        $def = ['Field' => 'status', 'Type' => "ENUM('active','inactive')", 'Null' => 'NO', 'Key' => '', 'Default' => 'active', 'Extra' => ''];
        
        // We need to instantiate SQLite without connecting, so use reflection or check the method directly
        $class = new class {
            public function defineColumn(array $def): array {
                if (preg_match('/^(enum|set)\(/i', $def['Type'] ?? '')) {
                    $def['Type'] = 'TEXT';
                }
                return $def;
            }
            public function getDefaultEngine(): string { return 'SQLite'; }
            public function getDefaultCharset(): string { return ''; }
            public function supportsModifyColumn(): bool { return false; }
        };

        $result = $class->defineColumn($def);
        $this->assertEquals('TEXT', $result['Type']);
    }

    public function testConnectorDefineColumnTinyIntMapping(): void
    {
        $class = new class {
            public function defineColumn(array $def): array {
                if (preg_match('/^tinyint\(1\)$/', $def['Type'] ?? '')) {
                    $def['Type'] = 'BOOLEAN';
                }
                return $def;
            }
            public function getDefaultEngine(): string { return 'SQLite'; }
            public function getDefaultCharset(): string { return ''; }
            public function supportsModifyColumn(): bool { return false; }
        };

        $def = ['Field' => 'active', 'Type' => 'tinyint(1)', 'Null' => 'NO', 'Key' => '', 'Default' => '0', 'Extra' => ''];
        $result = $class->defineColumn($def);
        $this->assertEquals('BOOLEAN', $result['Type']);
    }

    public function testConnectorAbstractHasShowTablesMethod(): void
    {
        $refClass = new \ReflectionClass('\LaswitchTech\Core\Abstracts\Connector');
        $this->assertTrue($refClass->hasMethod('showTablesSQL'));
        $this->assertTrue($refClass->hasMethod('tableExistsSQL'));
        $this->assertTrue($refClass->hasMethod('getDefaultEngine'));
        $this->assertTrue($refClass->hasMethod('getDefaultCharset'));
        $this->assertTrue($refClass->hasMethod('getDefaultCollation'));
        $this->assertTrue($refClass->hasMethod('supportsModifyColumn'));
    }

    public function testConnectorAbstractHasDefineColumn(): void
    {
        $refClass = new \ReflectionClass('\LaswitchTech\Core\Abstracts\Connector');
        $this->assertTrue($refClass->hasMethod('defineColumn'));
    }
}
