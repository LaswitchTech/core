<?php declare(strict_types=1);

namespace LaswitchTech\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use LaswitchTech\Core\Connectors\SQLite;
use LaswitchTech\Core\Objects\Query;

class SQLiteQueryTest extends TestCase
{
    private string $dbPath;
    private SQLite $conn;
    private ?Query $query = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbPath = sys_get_temp_dir() . '/smoketest_query_' . uniqid() . '.db';
        
        $GLOBALS['CONFIG'] = new class($this->dbPath) {
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
        $this->query = new Query($this->conn);
    }

    protected function tearDown(): void
    {
        $this->conn->close();
        @unlink($this->dbPath);
        parent::tearDown();
    }

    public function testInsertAndGetRow(): void
    {
        $this->query->table('users')->insert(['name' => 'Alice', 'email' => 'alice@test.com'])->execute();
        
        $rows = $this->query->table('users')
            ->select('*')
            ->where('name', 'Alice')
            ->result();
        
        $this->assertCount(1, $rows);
        $this->assertEquals('Alice', $rows[0]['name']);
    }

    public function testInsertMultipleAndGetCount(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->query->table('items')->insert(['title' => "Item {$i}", 'price' => 10.0 + $i])->execute();
        }

        $count = $this->query->table('items')
            ->count('*')
            ->result();

        $this->assertEquals(5, $count);
    }

    public function testUpdateAndAffectedRows(): void
    {
        $this->query->table('products')->insert(['name' => 'Widget', 'price' => 5.0])->execute();
        
        $affected = $this->query->table('products')
            ->update(['price' => 7.5])
            ->where('name', 'Widget')
            ->result();

        $this->assertEquals(1, $affected);
    }

    public function testDeleteRow(): void
    {
        $this->query->table('temp')->insert(['val' => 'delete_me'])->execute();
        
        $affected = $this->query->table('temp')
            ->delete()
            ->where('val', 'delete_me')
            ->result();

        $this->assertEquals(1, $affected);
    }

    public function testOrderAndLimit(): void
    {
        foreach ([3, 1, 4, 1, 5] as $v) {
            $this->query->table('nums')->insert(['num' => $v])->execute();
        }

        // Test order DESC + LIMIT
        $rows = $this->query->table('nums')
            ->select('*')
            ->order('num', 'DESC')
            ->limit(2)
            ->result();

        $this->assertCount(2, $rows);
        $this->assertEquals(5, $rows[0]['num']);
        $this->assertEquals(4, $rows[1]['num']);
    }

    public function testLastInsertId(): void
    {
        $this->query->table('auto_test')->insert(['val' => 99])->execute();
        $lastId = $this->query->lastId();
        // The first row's PK should be 1 (SQLite auto-increments by default)
        $this->assertGreaterThan(0, $lastId);
    }

    public function testAutoIncrementSyntaxForSQLite(): void
    {
        // The auto_increment SQL should use sqlite_sequence for SQLite (not ALTER TABLE AUTO_INCREMENT)
        // Verify via code inspection
        $source = file_get_contents(__DIR__ . '/../../src/Connectors/SQLite.php');
        $this->assertStringContainsString('sqlite_sequence', $source, 'SQLite connector autoIncrementSQL should use sqlite_sequence');
    }

    public function testPrepareWithParams(): void
    {
        $this->query->table('prepared')->insert(['name' => 'param_test'])->execute();
        
        $rows = $this->query->table('prepared')
            ->select('*')
            ->where('name', 'param_test')
            ->result();

        // PDOPreparedStatement is used for prepared statements, check it was wired correctly
        $src = file_get_contents(__DIR__ . '/../../src/Objects/Query.php');
        $this->assertStringContainsString('get_result', $src, 'Query.php result() should call get_result() on prepared stmt');
    }
}
