<?php
// Database connection handler using PDO

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }
    
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchColumn() : null;
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_map(function($k) { return ':' . $k; }, $keys));
        
        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);
        
        return $stmt ? $this->pdo->lastInsertId() : false;
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        // Detect placeholder type based on $whereParams structure
        // Named placeholders: associative array (string keys)
        // Positional placeholders: numeric array (sequential integer keys starting at 0)
        $usesNamedPlaceholders = false;
        if (is_array($whereParams) && !empty($whereParams)) {
            // Check if associative array (has string keys or non-sequential numeric keys)
            $usesNamedPlaceholders = array_keys($whereParams) !== range(0, count($whereParams) - 1);
        }
        
        if ($usesNamedPlaceholders) {
            // Build SET clause with named placeholders
            $setParts = [];
            $namedParams = [];
            foreach ($data as $key => $value) {
                $paramName = ":set_" . $key;
                $setParts[] = "$key = $paramName";
                $namedParams[$paramName] = $value;
            }
            $setClause = implode(', ', $setParts);
            $sql = "UPDATE $table SET $setClause WHERE $where";
            
            // Normalize named parameters to include colons
            $normalizedWhereParams = [];
            foreach ($whereParams as $k => $v) {
                $key = is_string($k) && strpos($k, ':') !== 0 ? ":$k" : $k;
                $normalizedWhereParams[$key] = $v;
            }
            $allParams = array_merge($namedParams, $normalizedWhereParams);
        } else {
            // Build SET clause with positional placeholders
            $setParts = [];
            $params = [];
            foreach ($data as $key => $value) {
                $setParts[] = "$key = ?";
                $params[] = $value;
            }
            $setClause = implode(', ', $setParts);
            $sql = "UPDATE $table SET $setClause WHERE $where";
            $allParams = array_merge($params, is_array($whereParams) ? $whereParams : [$whereParams]);
        }
        
        return $this->query($sql, $allParams) !== false;
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params) !== false;
    }
    
    public function execute($sql, $params = []) {
        return $this->query($sql, $params);
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

// Helper function to get database instance
function getDB() {
    return Database::getInstance();
}
