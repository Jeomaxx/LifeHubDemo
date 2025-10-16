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
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

$queue = new JobQueue();

switch ($action) {
    case 'list':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $jobs = $queue->getUserJobs($userId, $limit);
        
        foreach ($jobs as &$job) {
            $job['payload'] = json_decode($job['payload'], true);
            $job['result'] = $job['result'] ? json_decode($job['result'], true) : null;
            $job['progress_percent'] = $job['total'] > 0 ? round(($job['progress'] / $job['total']) * 100) : 0;
        }
        
        echo json_encode(['success' => true, 'jobs' => $jobs]);
        break;
        
    case 'get':
        $jobId = $_GET['id'] ?? 0;
        $job = $queue->getJob($jobId);
        
        if (!$job || $job['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        $job['payload'] = json_decode($job['payload'], true);
        $job['result'] = $job['result'] ? json_decode($job['result'], true) : null;
        $job['progress_percent'] = $job['total'] > 0 ? round(($job['progress'] / $job['total']) * 100) : 0;
        $job['logs'] = $queue->getJobLogs($jobId);
        
        echo json_encode(['success' => true, 'job' => $job]);
        break;
        
    case 'create':
        $type = $_POST['type'] ?? '';
        $payload = isset($_POST['payload']) ? json_decode($_POST['payload'], true) : [];
        $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
        
        if (empty($type)) {
            echo json_encode(['success' => false, 'message' => 'Job type is required']);
            exit;
        }
        
        $jobId = $queue->enqueue($type, $userId, $payload, $priority);
        
        echo json_encode([
            'success' => true,
            'message' => 'Job created successfully',
            'job_id' => $jobId
        ]);
        break;
        
    case 'cancel':
        $jobId = $_POST['id'] ?? 0;
        $job = $queue->getJob($jobId);
        
        if (!$job || $job['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        if ($job['status'] === JobQueue::STATUS_PROCESSING) {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel a job that is currently processing']);
            exit;
        }
        
        $queue->updateJobStatus($jobId, JobQueue::STATUS_CANCELLED);
        
        echo json_encode(['success' => true, 'message' => 'Job cancelled successfully']);
        break;
        
    case 'retry':
        $jobId = $_POST['id'] ?? 0;
        $job = $queue->getJob($jobId);
        
        if (!$job || $job['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        if ($job['status'] !== JobQueue::STATUS_FAILED) {
            echo json_encode(['success' => false, 'message' => 'Only failed jobs can be retried']);
            exit;
        }
        
        $db = Database::getInstance();
        $db->execute(
            "UPDATE jobs SET status = ?, attempts = 0, error = NULL, failed_at = NULL WHERE id = ?",
            [JobQueue::STATUS_PENDING, $jobId]
        );
        
        echo json_encode(['success' => true, 'message' => 'Job will be retried']);
        break;
        
    case 'logs':
        $jobId = $_GET['id'] ?? 0;
        $job = $queue->getJob($jobId);
        
        if (!$job || $job['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
            exit;
        }
        
        $logs = $queue->getJobLogs($jobId);
        
        echo json_encode(['success' => true, 'logs' => $logs]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
