<?php

class Database {
    private static $instance = null;
    private $connection;
    
    // Private constructor (singleton)
    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            throw new Exception("Koneksi gagal: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset(DB_CHARSET);
    }
    
    // Get instance (singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    // Get raw connection (kalau butuh mysqli native)
    public function getConnection() {
        return $this->connection;
    }
    
    // SELECT dengan prepared statement
    public function select($sql, $types = '', ...$params) {
        $stmt = $this->connection->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // INSERT dengan prepared statement
    public function insert($sql, $types = '', ...$params) {
        $stmt = $this->connection->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute() ? $stmt->insert_id : false;
    }
    
    // UPDATE/DELETE dengan prepared statement
    public function execute($sql, $types = '', ...$params) {
        $stmt = $this->connection->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute() ? $stmt->affected_rows : false;
    }
    
    // Get single row
    public function getOne($sql, $types = '', ...$params) {
        $results = $this->select($sql, $types, ...$params);
        return $results[0] ?? null;
    }
    
    // Get count (untuk dashboard)
    public function count($table, $where = '1=1') {
        $sql = "SELECT COUNT(*) as total FROM $table WHERE $where";
        $result = $this->getOne($sql);
        return $result['total'] ?? 0;
    }
}