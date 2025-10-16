<?php
require_once __DIR__ . '/db.php';

class JobQueue {
    private $db;
    private $handlers = [];
    
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->registerDefaultHandlers();
    }
    
    private function registerDefaultHandlers() {
        $this->registerHandler('csv_import', [$this, 'handleCsvImport']);
        $this->registerHandler('csv_import_finance', [$this, 'handleCsvImportFinance']);
        $this->registerHandler('csv_import_crypto', [$this, 'handleCsvImportCrypto']);
        $this->registerHandler('csv_import_assets', [$this, 'handleCsvImportAssets']);
        $this->registerHandler('csv_import_tasks', [$this, 'handleCsvImportTasks']);
        $this->registerHandler('csv_import_goals', [$this, 'handleCsvImportGoals']);
        $this->registerHandler('send_email', [$this, 'handleSendEmail']);
        $this->registerHandler('backup_data', [$this, 'handleBackupData']);
        $this->registerHandler('generate_report', [$this, 'handleGenerateReport']);
    }
    
    public function registerHandler($type, callable $handler) {
        $this->handlers[$type] = $handler;
    }
    
    public function enqueue($type, $userId, $payload = [], $priority = 0) {
        $jobId = $this->db->insert('jobs', [
            'type' => $type,
            'user_id' => $userId,
            'payload' => json_encode($payload),
            'priority' => $priority,
            'status' => self::STATUS_PENDING
        ]);
        
        return $jobId;
    }
    
    public function getJob($jobId) {
        return $this->db->fetchOne("SELECT * FROM jobs WHERE id = ?", [$jobId]);
    }
    
    public function getUserJobs($userId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT * FROM jobs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
    
    public function getNextJob() {
        return $this->db->fetchOne(
            "SELECT * FROM jobs 
             WHERE status = ? AND attempts < max_attempts
             ORDER BY priority DESC, created_at ASC 
             LIMIT 1",
            [self::STATUS_PENDING]
        );
    }
    
    public function updateJobStatus($jobId, $status, $error = null) {
        $updates = ["status = ?"];
        $params = [$status];
        
        if ($status === self::STATUS_PROCESSING) {
            $updates[] = "started_at = ?";
            $params[] = date('Y-m-d H:i:s');
        } elseif ($status === self::STATUS_COMPLETED) {
            $updates[] = "completed_at = ?";
            $params[] = date('Y-m-d H:i:s');
            $updates[] = "progress = ?";
            $params[] = 100;
        } elseif ($status === self::STATUS_FAILED) {
            $updates[] = "failed_at = ?";
            $params[] = date('Y-m-d H:i:s');
            if ($error !== null) {
                $updates[] = "error = ?";
                $params[] = $error;
            }
        }
        
        $params[] = $jobId;
        $sql = "UPDATE jobs SET " . implode(', ', $updates) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params) !== false;
    }
    
    public function updateProgress($jobId, $progress, $total = null) {
        $data = ['progress' => $progress];
        if ($total !== null) {
            $data['total'] = $total;
        }
        return $this->db->update('jobs', $data, "id = ?", [$jobId]);
    }
    
    public function incrementAttempts($jobId) {
        $this->db->execute(
            "UPDATE jobs SET attempts = attempts + 1 WHERE id = ?",
            [$jobId]
        );
    }
    
    public function logJob($jobId, $level, $message) {
        $this->db->insert('job_logs', [
            'job_id' => $jobId,
            'level' => $level,
            'message' => $message
        ]);
    }
    
    public function getJobLogs($jobId) {
        return $this->db->fetchAll(
            "SELECT * FROM job_logs WHERE job_id = ? ORDER BY created_at ASC",
            [$jobId]
        );
    }
    
    public function processNext() {
        $job = $this->getNextJob();
        
        if (!$job) {
            return false;
        }
        
        return $this->process($job);
    }
    
    public function process($job) {
        $this->updateJobStatus($job['id'], self::STATUS_PROCESSING);
        $this->incrementAttempts($job['id']);
        $this->logJob($job['id'], 'info', "Job processing started (attempt {$job['attempts']})");
        
        try {
            $payload = json_decode($job['payload'], true);
            
            if (!isset($this->handlers[$job['type']])) {
                throw new Exception("No handler registered for job type: {$job['type']}");
            }
            
            $result = call_user_func($this->handlers[$job['type']], $job, $payload);
            
            $this->db->update('jobs', [
                'result' => json_encode($result),
                'status' => self::STATUS_COMPLETED
            ], "id = {$job['id']}");
            
            $this->updateJobStatus($job['id'], self::STATUS_COMPLETED);
            $this->logJob($job['id'], 'success', 'Job completed successfully');
            
            return true;
            
        } catch (Exception $e) {
            $this->updateJobStatus($job['id'], self::STATUS_FAILED, $e->getMessage());
            $this->logJob($job['id'], 'error', 'Job failed: ' . $e->getMessage());
            
            return false;
        }
    }
    
    public function processAll($maxJobs = 10) {
        $processed = 0;
        
        while ($processed < $maxJobs) {
            if (!$this->processNext()) {
                break;
            }
            $processed++;
        }
        
        return $processed;
    }
    
    private function handleCsvImport($job, $payload) {
        $this->logJob($job['id'], 'info', 'Starting CSV import');
        
        $filePath = $payload['file_path'] ?? null;
        $module = $payload['module'] ?? 'finance';
        $startRow = $payload['start_row'] ?? 0;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new Exception('CSV file not found');
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception('Unable to read CSV file');
        }
        
        $header = fgetcsv($handle);
        $imported = 0;
        $errors = [];
        $currentRow = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            if ($currentRow < $startRow) {
                $currentRow++;
                continue;
            }
            
            try {
                $this->updateProgress($job['id'], $currentRow);
                $currentRow++;
                $imported++;
                
            } catch (Exception $e) {
                $errors[] = "Row $currentRow: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        return [
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => $currentRow
        ];
    }
    
    private function handleSendEmail($job, $payload) {
        $this->logJob($job['id'], 'info', 'Sending email');
        
        $to = $payload['to'] ?? '';
        $subject = $payload['subject'] ?? '';
        $message = $payload['message'] ?? '';
        
        require_once __DIR__ . '/functions.php';
        $result = sendEmail($to, $subject, $message);
        
        return ['sent' => $result];
    }
    
    private function handleBackupData($job, $payload) {
        $this->logJob($job['id'], 'info', 'Creating backup');
        
        require_once __DIR__ . '/functions.php';
        $filename = generateBackup($job['user_id']);
        
        return ['filename' => $filename];
    }
    
    private function handleGenerateReport($job, $payload) {
        $this->logJob($job['id'], 'info', 'Generating report');
        
        $reportType = $payload['type'] ?? 'finance';
        
        return ['report_generated' => true, 'type' => $reportType];
    }
    
    private function handleCsvImportFinance($job, $payload) {
        return $this->processCsvImport($job, $payload, function($row, $headerMap) use ($job) {
            $date = isset($headerMap['date']) ? trim($row[$headerMap['date']]) : date('Y-m-d');
            $type = isset($headerMap['type']) ? strtolower(trim($row[$headerMap['type']])) : 'expense';
            $category = isset($headerMap['category']) ? trim($row[$headerMap['category']]) : 'Uncategorized';
            $amount = isset($headerMap['amount']) ? floatval(str_replace(['$', ','], '', $row[$headerMap['amount']])) : 0;
            $description = isset($headerMap['description']) ? trim($row[$headerMap['description']]) : '';
            
            if (!in_array($type, ['income', 'expense'])) {
                $type = 'expense';
            }
            
            if ($amount > 0) {
                $this->db->execute(
                    "INSERT INTO finance (user_id, type, category, amount, date, description) VALUES (?, ?, ?, ?, ?, ?)",
                    [$job['user_id'], $type, $category, $amount, $date, $description]
                );
                return true;
            }
            return false;
        });
    }
    
    private function handleCsvImportCrypto($job, $payload) {
        return $this->processCsvImport($job, $payload, function($row, $headerMap) use ($job) {
            $symbol = strtolower(trim($row[0] ?? ''));
            $name = trim($row[1] ?? '');
            $amount = floatval($row[2] ?? 0);
            $purchasePrice = floatval($row[3] ?? 0);
            $purchaseDate = $row[4] ?? date('Y-m-d');
            $notes = $row[5] ?? '';
            
            if ($amount > 0 && $purchasePrice > 0) {
                $this->db->execute(
                    "INSERT INTO crypto_portfolio (user_id, crypto_id, crypto_symbol, crypto_name, amount, purchase_price, purchase_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$job['user_id'], $symbol, $symbol, $name, $amount, $purchasePrice, $purchaseDate, $notes]
                );
                return true;
            }
            return false;
        });
    }
    
    private function handleCsvImportAssets($job, $payload) {
        return $this->processCsvImport($job, $payload, function($row, $headerMap) use ($job) {
            $name = trim($row[0] ?? '');
            $category = trim($row[1] ?? 'Other');
            $value = floatval($row[2] ?? 0);
            $acquisitionDate = $row[3] ?? date('Y-m-d');
            $description = $row[4] ?? '';
            
            if (!empty($name)) {
                $this->db->execute(
                    "INSERT INTO assets (user_id, name, category, value, acquisition_date, description) VALUES (?, ?, ?, ?, ?, ?)",
                    [$job['user_id'], $name, $category, $value, $acquisitionDate, $description]
                );
                return true;
            }
            return false;
        });
    }
    
    private function handleCsvImportTasks($job, $payload) {
        return $this->processCsvImport($job, $payload, function($row, $headerMap) use ($job) {
            $title = trim($row[0] ?? '');
            $category = trim($row[1] ?? 'General');
            $priority = strtolower(trim($row[2] ?? 'medium'));
            $dueDate = $row[3] ?? null;
            $description = $row[4] ?? '';
            
            if (!empty($title)) {
                $this->db->execute(
                    "INSERT INTO tasks (user_id, title, category, priority, due_date, description) VALUES (?, ?, ?, ?, ?, ?)",
                    [$job['user_id'], $title, $category, $priority, $dueDate, $description]
                );
                return true;
            }
            return false;
        });
    }
    
    private function handleCsvImportGoals($job, $payload) {
        return $this->processCsvImport($job, $payload, function($row, $headerMap) use ($job) {
            $title = trim($row[0] ?? '');
            $category = trim($row[1] ?? 'Personal');
            $targetDate = $row[2] ?? null;
            $description = $row[3] ?? '';
            
            if (!empty($title)) {
                $this->db->execute(
                    "INSERT INTO goals (user_id, title, category, target_date, description) VALUES (?, ?, ?, ?, ?)",
                    [$job['user_id'], $title, $category, $targetDate, $description]
                );
                return true;
            }
            return false;
        });
    }
    
    private function processCsvImport($job, $payload, callable $rowHandler) {
        $this->logJob($job['id'], 'info', 'Starting CSV import');
        
        $filePath = $payload['file_path'] ?? null;
        $startRow = $payload['start_row'] ?? 0;
        $rowCount = $payload['row_count'] ?? 0;
        
        if (!$filePath || !file_exists($filePath)) {
            throw new Exception('CSV file not found');
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception('Unable to read CSV file');
        }
        
        $header = fgetcsv($handle);
        $headerMap = [];
        foreach ($header as $index => $col) {
            $headerMap[strtolower(trim($col))] = $index;
        }
        
        $this->updateProgress($job['id'], 0, $rowCount);
        
        $imported = 0;
        $errors = [];
        $currentRow = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            if ($currentRow < $startRow) {
                $currentRow++;
                continue;
            }
            
            try {
                if ($rowHandler($row, $headerMap)) {
                    $imported++;
                }
                $currentRow++;
                $this->updateProgress($job['id'], $currentRow, $rowCount);
                
            } catch (Exception $e) {
                $errors[] = "Row $currentRow: " . $e->getMessage();
                $this->logJob($job['id'], 'warning', "Row $currentRow failed: " . $e->getMessage());
            }
        }
        
        fclose($handle);
        @unlink($filePath);
        
        $this->logJob($job['id'], 'info', "Import completed. Imported: $imported, Errors: " . count($errors));
        
        return [
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => $currentRow,
            'success_rate' => $currentRow > 0 ? round(($imported / $currentRow) * 100, 2) : 0
        ];
    }
}
