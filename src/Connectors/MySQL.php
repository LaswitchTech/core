<?php

// Declaring namespace
namespace LaswitchTech\Core\Connectors;

// Import additionnal class into the global namespace
use LaswitchTech\Core\Abstracts\Connector;
use Exception;
use mysqli;

class MySQL extends Connector {

    /**
     * @var mysqli
     */
    private $mysqli;

    /**
     * @var array
     */
    private static $describeCache = [];

    /**
     * Establish the MySQL connection
     *
     * @throws Exception
     */
    public function connect()
    {
        // In a real-world scenario, retrieve these from $this->Config
        // e.g. $dbConfig = $this->Config->get('database');
        // For demonstration, we’ll just hardcode or assume values are set in $dbConfig.
        $dbConfig = $this->Config->get('database') ?? [];

        $host = $dbConfig['host'] ?? 'localhost';
        $user = $dbConfig['username'] ?? 'root';
        $pass = $dbConfig['password'] ?? '';
        $name = $dbConfig['database'] ?? 'demo';

        $this->mysqli = new mysqli($host, $user, $pass, $name);

        if ($this->mysqli->connect_error) {
            throw new Exception('MySQL Connection Error: ' . $this->mysqli->connect_error);
        }
    }

    /**
     * Close the connection
     */
    public function close()
    {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }

    /**
     * Check if connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return ($this->mysqli && !$this->mysqli->connect_errno);
    }

    /**
     * Execute a query
     *
     * @param string $sql
     * @return mixed
     * @throws Exception
     */
    public function query(string $sql)
    {
        $result = $this->mysqli->query($sql);

        if ($result === false) {
            throw new Exception('MySQL Query Error: ' . $this->mysqli->error);
        }
        return $result;
    }

    /**
     * Describe a table
     *
     * @param string $table
     * @return mixed
     */
    public function describe(string $table)
    {
        if (!isset(self::$describeCache[$table])) {
            $result = $this->mysqli->query("DESCRIBE `$table`");
            self::$describeCache[$table] = $result->fetch_all(MYSQLI_ASSOC);
        }
        return self::$describeCache[$table];
    }

    /**
     * Return the number of affected rows in last operation
     *
     * @return int
     */
    public function affectedRows(): int
    {
        return (int) $this->mysqli->affected_rows;
    }

    /**
     * Return the ID of the last inserted row
     *
     * @return int
     */
    public function lastId(): int
    {
        return (int) $this->mysqli->insert_id;
    }

    /**
     * Prepare a query
     *
     * @param string $sql
     */
    public function prepare(string $sql, array $params = [])
    {
        // 1) Prepare the statement
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            throw new Exception('MySQL Prepare Error: ' . $this->mysqli->error);
        }

        // 2) Build the type string.
        if (!empty($params)) {
            $types = '';
            foreach($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';              // Integer
                } elseif (is_float($param)) {
                    $types .= 'd';              // Double
                } elseif (is_string($param)) {
                    $types .= 's';              // String
                } else {
                    $types .= 'b';              // Blob and Unknown
                }
            }

            // 3) Bind the parameters
            $stmt->bind_param($types, ...$params);
        }

        // 4) Execute
        if (!$stmt->execute()) {
            throw new Exception('MySQL Execute Error: ' . $stmt->error);
        }

        return $stmt;
    }
}
