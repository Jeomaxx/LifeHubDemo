<?php
#!/usr/bin/env php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/job_queue.php';

class SystemTest {
    private $passed = 0;
    private $failed = 0;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function run() {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "LIFEHUB SYSTEM TESTS\n";
        echo str_repeat('=', 60) . "\n\n";
        
        $this->testDatabase();
        $this->testAuthentication();
        $this->testRBAC();
        $this->testJobQueue();
        $this->testPagination();
        $this->testFileSystem();
        $this->testPlaceholders();
        
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "RESULTS: {$this->passed} passed, {$this->failed} failed\n";
        echo str_repeat('=', 60) . "\n\n";
        
        exit($this->failed > 0 ? 1 : 0);
    }
    
    private function testDatabase() {
        echo "Testing Database Connection...\n";
        
        try {
            $result = $this->db->fetchOne("SELECT 1 as test");
            $this->assert($result['test'] == 1, "Database query executed");
            
            $tables = $this->db->fetchAll("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $this->assert(count($tables) > 0, "Database tables exist");
            
            $requiredTables = ['users', 'roles', 'permissions', 'jobs', 'finance', 'tasks'];
            foreach ($requiredTables as $table) {
                $exists = $this->db->fetchOne("SELECT to_regclass('public.$table') as exists");
                $this->assert($exists['exists'] !== null, "Table '$table' exists");
            }
        } catch (Exception $e) {
            $this->fail("Database test failed: " . $e->getMessage());
        }
    }
    
    private function testAuthentication() {
        echo "\nTesting Authentication System...\n";
        
        $testEmail = 'test_' . time() . '@example.com';
        $testPassword = 'TestPassword123';
        
        try {
            $auth = new Auth();
            
            $result = $auth->register('Test User', $testEmail, $testPassword);
            $this->assert($result['success'], "User registration works");
            
            $result = $auth->login($testEmail, $testPassword);
            $this->assert($result['success'], "User login works");
            
            $csrfToken = $auth->generateCSRFToken();
            $this->assert(!empty($csrfToken), "CSRF token generation works");
            $this->assert($auth->validateCSRFToken($csrfToken), "CSRF token validation works");
            
            $this->db->execute("DELETE FROM users WHERE email = ?", [$testEmail]);
        } catch (Exception $e) {
            $this->fail("Authentication test failed: " . $e->getMessage());
        }
    }
    
    private function testRBAC() {
        echo "\nTesting RBAC System...\n";
        
        try {
            $rbac = new RBAC();
            
            $roles = $rbac->getAllRoles();
            $this->assert(count($roles) > 0, "Roles exist");
            
            $permissions = $rbac->getAllPermissions();
            $this->assert(count($permissions) > 0, "Permissions exist");
            
            $adminRole = null;
            foreach ($roles as $role) {
                if ($role['name'] === 'admin') {
                    $adminRole = $role;
                    break;
                }
            }
            $this->assert($adminRole !== null, "Admin role exists");
            
            if ($adminRole) {
                $rolePermissions = $rbac->getRolePermissions($adminRole['id']);
                $this->assert(count($rolePermissions) > 0, "Admin role has permissions");
            }
        } catch (Exception $e) {
            $this->fail("RBAC test failed: " . $e->getMessage());
        }
    }
    
    private function testJobQueue() {
        echo "\nTesting Job Queue System...\n";
        
        try {
            $testUserId = $this->db->insert('users', [
                'name' => 'Test Job User',
                'email' => 'testjob_' . time() . '@example.com',
                'password' => password_hash('test', PASSWORD_BCRYPT),
                'settings' => json_encode(['theme' => 'light'])
            ]);
            
            $queue = new JobQueue();
            
            $jobId = $queue->enqueue('test_job', $testUserId, ['test' => 'data']);
            $this->assert($jobId > 0, "Job enqueue works");
            
            $job = $queue->getJob($jobId);
            $this->assert($job !== null, "Job retrieval works");
            $this->assert($job['status'] === JobQueue::STATUS_PENDING, "Job has correct initial status");
            
            $queue->updateJobStatus($jobId, JobQueue::STATUS_COMPLETED);
            $job = $queue->getJob($jobId);
            $this->assert($job['status'] === JobQueue::STATUS_COMPLETED, "Job status update works");
            
            $this->db->execute("DELETE FROM jobs WHERE id = ?", [$jobId]);
            $this->db->execute("DELETE FROM users WHERE id = ?", [$testUserId]);
        } catch (Exception $e) {
            $this->fail("Job queue test failed: " . $e->getMessage());
        }
    }
    
    private function testPagination() {
        echo "\nTesting Pagination System...\n";
        
        try {
            require_once __DIR__ . '/../includes/pagination.php';
            
            $paginator = new Paginator(1, 10);
            $result = $paginator->paginate('users', '', [], 'id DESC');
            
            $this->assert(isset($result['data']), "Pagination returns data");
            $this->assert(isset($result['pagination']), "Pagination returns pagination info");
            $this->assert(isset($result['pagination']['current_page']), "Pagination info has current_page");
        } catch (Exception $e) {
            $this->fail("Pagination test failed: " . $e->getMessage());
        }
    }
    
    private function testFileSystem() {
        echo "\nTesting File System...\n";
        
        $uploadDir = BASE_PATH . '/uploads';
        $this->assert(is_dir($uploadDir), "Upload directory exists");
        $this->assert(is_writable($uploadDir), "Upload directory is writable");
        
        $testFile = $uploadDir . '/test_' . time() . '.txt';
        file_put_contents($testFile, 'test');
        $this->assert(file_exists($testFile), "Can create files in upload directory");
        @unlink($testFile);
    }
    
    private function assert($condition, $message) {
        if ($condition) {
            echo "  ✓ $message\n";
            $this->passed++;
        } else {
            echo "  ✗ $message\n";
            $this->failed++;
        }
    }
    
    private function fail($message) {
        echo "  ✗ $message\n";
        $this->failed++;
    }
    
    private function testPlaceholders() {
        echo "\nTesting Database Update Placeholders (Regression)...\n";
        
        try {
            // Test with positional placeholders (?)
            $testUserId = $this->db->insert('users', [
                'name' => 'Placeholder Test User',
                'email' => 'placeholder_' . time() . '@example.com',
                'password' => password_hash('test', PASSWORD_BCRYPT),
                'settings' => json_encode(['theme' => 'light'])
            ]);
            
            $result = $this->db->update('users', ['name' => 'Updated Positional'], 'id = ?', [$testUserId]);
            $user = $this->db->fetchOne('SELECT name FROM users WHERE id = ?', [$testUserId]);
            $this->assert($user['name'] === 'Updated Positional', "Update with positional placeholders works");
            
            // Test with named placeholders (:param with colon in key)
            $result = $this->db->update('users', ['name' => 'Updated Named Colon'], 'id = :id', [':id' => $testUserId]);
            $user = $this->db->fetchOne('SELECT name FROM users WHERE id = ?', [$testUserId]);
            $this->assert($user['name'] === 'Updated Named Colon', "Update with named placeholders (colon key) works");
            
            // Test with named placeholders (no colon in key, PDO accepts both)
            $result = $this->db->update('users', ['name' => 'Updated Named No Colon'], 'id = :id', ['id' => $testUserId]);
            $user = $this->db->fetchOne('SELECT name FROM users WHERE id = ?', [$testUserId]);
            $this->assert($user['name'] === 'Updated Named No Colon', "Update with named placeholders (no colon key) works");
            
            // Test with PostgreSQL type cast (::) and positional placeholders
            $result = $this->db->update('users', ['name' => 'Cast Test'], "settings::text LIKE ? AND id = ?", ['%light%', $testUserId]);
            $user = $this->db->fetchOne('SELECT name FROM users WHERE id = ?', [$testUserId]);
            $this->assert($user['name'] === 'Cast Test', "Update with :: cast and positional placeholders works");
            
            // Clean up
            $this->db->execute('DELETE FROM users WHERE id = ?', [$testUserId]);
        } catch (Exception $e) {
            $this->fail("Placeholder regression test failed: " . $e->getMessage());
        }
    }
}

$test = new SystemTest();
$test->run();
