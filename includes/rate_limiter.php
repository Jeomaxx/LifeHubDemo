<?php

class RateLimiter {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function checkLoginAttempts($ipAddress, $email = null, $maxAttempts = 5, $timeWindow = 900) {
        $this->cleanOldAttempts($timeWindow);
        
        $query = "SELECT COUNT(*) FROM login_attempts 
                  WHERE ip_address = ? 
                  AND success = FALSE 
                  AND attempt_time > NOW() - INTERVAL '{$timeWindow} seconds'";
        $params = [$ipAddress];
        
        if ($email) {
            $query .= " OR email = ?";
            $params[] = $email;
        }
        
        $attempts = $this->db->fetchColumn($query, $params);
        
        return $attempts < $maxAttempts;
    }
    
    public function logAttempt($ipAddress, $email, $success = false) {
        $this->db->execute(
            "INSERT INTO login_attempts (ip_address, email, success) VALUES (?, ?, ?)",
            [$ipAddress, $email, $success]
        );
    }
    
    public function clearAttempts($ipAddress, $email) {
        $this->db->execute(
            "DELETE FROM login_attempts WHERE ip_address = ? OR email = ?",
            [$ipAddress, $email]
        );
    }
    
    public function getRemainingAttempts($ipAddress, $email = null, $maxAttempts = 5, $timeWindow = 900) {
        $this->cleanOldAttempts($timeWindow);
        
        $query = "SELECT COUNT(*) FROM login_attempts 
                  WHERE ip_address = ? 
                  AND success = FALSE 
                  AND attempt_time > NOW() - INTERVAL '{$timeWindow} seconds'";
        $params = [$ipAddress];
        
        if ($email) {
            $query .= " OR email = ?";
            $params[] = $email;
        }
        
        $attempts = $this->db->fetchColumn($query, $params);
        $remaining = max(0, $maxAttempts - $attempts);
        
        return [
            'remaining' => $remaining,
            'locked' => $remaining === 0,
            'attempts' => $attempts
        ];
    }
    
    private function cleanOldAttempts($timeWindow) {
        $this->db->execute(
            "DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL '{$timeWindow} seconds'"
        );
    }
    
    public function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function checkLimit($identifier, $action, $maxRequests = 60, $timeWindow = 60) {
        $cacheKey = "rate_limit_{$action}_{$identifier}";
        $now = time();
        $windowStart = $now - $timeWindow;
        
        $query = "SELECT COUNT(*) FROM rate_limit_log 
                  WHERE identifier = ? 
                  AND action = ? 
                  AND request_time > TO_TIMESTAMP(?)";
        
        try {
            $count = $this->db->fetchColumn($query, [$identifier, $action, $windowStart]) ?? 0;
            
            if ($count >= $maxRequests) {
                return false;
            }
            
            $this->db->execute(
                "INSERT INTO rate_limit_log (identifier, action, request_time) VALUES (?, ?, NOW())",
                [$identifier, $action]
            );
            
            $this->cleanOldRateLimitEntries($timeWindow);
            
            return true;
        } catch (Exception $e) {
            error_log("Rate limiter error: " . $e->getMessage());
            return true;
        }
    }
    
    private function cleanOldRateLimitEntries($timeWindow) {
        try {
            $this->db->execute(
                "DELETE FROM rate_limit_log WHERE request_time < NOW() - INTERVAL '{$timeWindow} seconds'"
            );
        } catch (Exception $e) {
            error_log("Rate limit cleanup error: " . $e->getMessage());
        }
    }
}
