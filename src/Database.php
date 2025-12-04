<?php
/**
 * Database Helper Class
 * Simple PDO-based SQLite connection wrapper for ByaHero
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $dbPath;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct($dbPath = null)
    {
        if ($dbPath === null) {
            // Default path relative to project root
            $this->dbPath = __DIR__ . '/../data/buses.sqlite';
        } else {
            $this->dbPath = $dbPath;
        }

        try {
            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get singleton instance of Database
     * Note: The dbPath parameter is only used on first call when creating the instance.
     * Subsequent calls with different paths will return the existing instance.
     */
    public static function getInstance($dbPath = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($dbPath);
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Execute a query with optional parameters
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }

    /**
     * Fetch all rows from query
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single row from query
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Execute update/insert/delete query and return affected rows
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
