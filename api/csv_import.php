<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/job_queue.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$action = $_GET['action'] ?? $_POST['action'] ?? 'upload';

switch ($action) {
    case 'upload':
        if (!isset($_FILES['csv_file'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit;
        }
        
        $file = $_FILES['csv_file'];
        $module = $_POST['module'] ?? 'finance';
        $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 1;
        
        $allowedModules = ['finance', 'crypto', 'assets', 'tasks', 'goals'];
        if (!in_array($module, $allowedModules)) {
            echo json_encode(['success' => false, 'message' => 'Invalid module']);
            exit;
        }
        
        $uploadDir = BASE_PATH . '/uploads/csv/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = 'import_' . $userId . '_' . time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
            exit;
        }
        
        $handle = fopen($targetPath, 'r');
        $rowCount = 0;
        while (fgetcsv($handle) !== false) {
            $rowCount++;
        }
        fclose($handle);
        $rowCount--; 
        
        $queue = new JobQueue();
        $jobId = $queue->enqueue('csv_import_' . $module, $userId, [
            'file_path' => $targetPath,
            'filename' => $file['name'],
            'module' => $module,
            'row_count' => $rowCount,
            'start_row' => 0
        ], $priority);
        
        echo json_encode([
            'success' => true,
            'message' => 'CSV upload successful. Import job queued.',
            'job_id' => $jobId,
            'row_count' => $rowCount
        ]);
        break;
        
    case 'status':
        $jobId = $_GET['job_id'] ?? 0;
        
        $queue = new JobQueue();
        $job = $queue->getJob($jobId);
        
        if (!$job || $job['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        $payload = json_decode($job['payload'], true);
        $result = $job['result'] ? json_decode($job['result'], true) : null;
        
        echo json_encode([
            'success' => true,
            'job' => [
                'id' => $job['id'],
                'status' => $job['status'],
                'progress' => $job['progress'],
                'total' => $job['total'],
                'progress_percent' => $job['total'] > 0 ? round(($job['progress'] / $job['total']) * 100) : 0,
                'started_at' => $job['started_at'],
                'completed_at' => $job['completed_at'],
                'error' => $job['error'],
                'filename' => $payload['filename'] ?? '',
                'result' => $result
            ]
        ]);
        break;
        
    case 'resume':
        $jobId = $_POST['job_id'] ?? 0;
        
        $queue = new JobQueue();
        $job = $queue->getJob($jobId);
        
        if (!$job || $job['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        if ($job['status'] !== JobQueue::STATUS_FAILED) {
            echo json_encode(['success' => false, 'message' => 'Only failed jobs can be resumed']);
            exit;
        }
        
        $db = Database::getInstance();
        $payload = json_decode($job['payload'], true);
        $payload['start_row'] = $job['progress'];
        
        $db->execute(
            "UPDATE jobs SET status = ?, payload = ?, attempts = 0, error = NULL WHERE id = ?",
            [JobQueue::STATUS_PENDING, json_encode($payload), $jobId]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Import will resume from row ' . $job['progress']
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
