<?php declare(strict_types=1);

namespace LaswitchTech\Core\Tests\Unit;

use LaswitchTech\Core\Connectors\PDOPreparedStatement;
use LaswitchTech\Core\Connectors\PDOResult;
use LaswitchTech\Core\Connectors\SQLite;
use PHPUnit\Framework\TestCase;

class SQLiteConnectorTest extends TestCase
{
    private string $dbPath;
    private SQLite $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbPath = sys_get_temp_dir() . '/smoketest_sqlite_' . uniqid() . '.db';
        $GLOBALS['CONFIG'] = new class {
            public function get(?string $key = null, ?string $sub = null): mixed {
                return [
                    'connector' => 'sqlite',
                    'path' => realpath('.') . '/' . str_replace(realpath('.'), '', (function() use(){ echo __DIR__; })()),
                ][$key] ?? match([$key,$sub]) {
                    ['application','installed'] => true,
                    default => null,
                };
            }
        };
        $this->conn = new SQLite();
    }

    protected function tearDown(): void
    {
        $this->conn->close();
        @unlink($this->dbPath);
        parent::tearDown();
    }

    public function testConnect(): void
    {
        $this->conn->connect();
        $this->assertTrue($this->conn->isConnected());
    }

    public function testDescribeNewTable(): void
    {
        $this->conn->connect();
        $this->conn->query('CREATE TABLE test_desc (id INTEGER PRIMARY KEY, name TEXT NOT NULL DEFAULT "x", val REAL)');
        $cols = $this->conn->describe('test_desc');
        $this->assertCount(3, $cols);
        $names = array_column($cols, 'Field');
        $this->assertContains('id', $names);
        $this->assertContains('name', $names);
        $this->assertEquals('PRI', $cols[0]['Key']);
    }

    public function testDescribeNonexistentTable(): void
    {
        $this->conn->connect();
        $this->assertEmpty($this->conn->describe('no_such_table'));
    }

    public function testQuerySelectReturnsResultLikeObject(): void
    {
        $this->conn->connect();
        $this->conn->query('CREATE TABLE t (id INTEGER PRIMARY KEY, val TEXT)');
        $this->conn->query("INSERT INTO t VALUES (1,'a'),(2,'b')");
        $result = $this->conn->query("SELECT * FROM t ORDER BY id");
        $row1 = $result->fetch_assoc();
        $this->assertNotNull($row1);
        $this->assertEquals('a', $row1['val']);
        $row2 = $result->fetch_assoc();
        $this->assertNotNull($row2);
        $this->assertEquals('b', $row2['val']);
        $this->assertNull($result->fetch_assoc());
    }

    public function testAffectedRows(): void
    {
        $this->conn->connect();
        $this->conn->query('CREATE TABLE ar (id INTEGER PRIMARY KEY)');
        $this->conn->query("INSERT INTO ar VALUES (1),(2),(3)");
        $this->assertEquals(3, $this->conn->affectedRows());
    }

    public function testPrepareBindParam(): void
    {
        $this->conn->connect();
        $this->conn->query('CREATE TABLE pb (id INTEGER PRIMARY KEY, name TEXT, price REAL)');
        $stmt = $this->conn->prepare('INSERT INTO pb (name, price) VALUES (?, ?)', ['hello', 9.99]);
        $this->assertInstanceOf(PDOPreparedStatement::class, $stmt);
        $stmt->execute();
        $count = $this->conn->query("SELECT COUNT(*) as c FROM pb")->fetch_assoc()['c'];
        $this->assertEquals(1, $count);
    }

    public function testLastId(): void
    {
        $this->conn->connect();
        $this->conn->query('CREATE TABLE li (id INTEGER PRIMARY KEY, val TEXT)');
        $stmt = $this->conn->prepare('INSERT INTO li (val) VALUES (?)', ['x']);
        $stmt->execute();
        $this->assertEquals(1, $this->conn->lastId());
    }

    public function testClose(): void
    {
        $this->conn->connect();
        $this->assertTrue($this->conn->isConnected());
        $this->conn->close();
        $this->assertFalse($this->conn->isConnected());
    }
}
