<?php
require_once 'configs/database.php';

abstract class BaseModel
{
    protected static $_connection = null;

    public function __construct() {
        if (self::$_connection === null) {
            $host = getenv('DB_HOST') ?: 'web-mysql';
            $user = getenv('DB_USER') ?: 'user';
            $pass = getenv('DB_PASSWORD') ?: 'pass';
            $db   = getenv('DB_NAME') ?: 'app_web1';

            self::$_connection = new mysqli($host, $user, $pass, $db);

            if (self::$_connection->connect_errno) {
                die("❌ Failed to connect to MySQL: " . self::$_connection->connect_error);
            }
        }
    }

    protected function query($sql)
    {
        if (!self::$_connection) {
            throw new Exception("Database connection not established.");
        }

        $result = self::$_connection->query($sql);

        if ($result === false) {
            throw new Exception("❌ Query failed: " . self::$_connection->error);
        }

        return $result;
    }

    protected function select($sql)
    {
        $result = $this->query($sql);
        $rows = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    protected function insert($sql)
    {
        return $this->query($sql);
    }

    protected function update($sql)
    {
        return $this->query($sql);
    }

    protected function delete($sql)
    {
        return $this->query($sql);
    }
}
