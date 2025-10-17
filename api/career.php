<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
requireLogin();
$user = $auth->getCurrentUser();
$userId = $user['id'];
$db = Database::getInstance();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? 'jobs';
            
            if ($type === 'jobs') {
                $status = $_GET['status'] ?? null;
                $query = "SELECT * FROM job_applications WHERE user_id = ?";
                $params = [$userId];
                
                if ($status) {
                    $query .= " AND status = ?";
                    $params[] = $status;
                }
                
                $query .= " ORDER BY application_date DESC";
                $jobs = $db->fetchAll($query, $params);
                
                echo json_encode(['success' => true, 'data' => $jobs]);
                
            } elseif ($type === 'interviews') {
                $jobId = $_GET['job_id'] ?? null;
                $query = "SELECT i.*, j.company_name, j.position 
                         FROM interviews i 
                         LEFT JOIN job_applications j ON i.job_application_id = j.id 
                         WHERE i.user_id = ?";
                $params = [$userId];
                
                if ($jobId) {
                    $query .= " AND i.job_application_id = ?";
                    $params[] = $jobId;
                }
                
                $query .= " ORDER BY i.interview_date DESC";
                $interviews = $db->fetchAll($query, $params);
                
                echo json_encode(['success' => true, 'data' => $interviews]);
                
            } elseif ($type === 'certifications') {
                $certifications = $db->fetchAll(
                    "SELECT * FROM career_certifications WHERE user_id = ? ORDER BY issue_date DESC",
                    [$userId]
                );
                
                echo json_encode(['success' => true, 'data' => $certifications]);
                
            } elseif ($type === 'resumes') {
                $resumes = $db->fetchAll(
                    "SELECT * FROM resume_versions WHERE user_id = ? ORDER BY created_at DESC",
                    [$userId]
                );
                
                echo json_encode(['success' => true, 'data' => $resumes]);
                
            } elseif ($type === 'stats') {
                $stats = [
                    'total_applications' => $db->fetchOne("SELECT COUNT(*) as count FROM job_applications WHERE user_id = ?", [$userId])['count'] ?? 0,
                    'interviews_scheduled' => $db->fetchOne("SELECT COUNT(*) as count FROM interviews WHERE user_id = ? AND outcome IS NULL", [$userId])['count'] ?? 0,
                    'offers_received' => $db->fetchOne("SELECT COUNT(*) as count FROM job_applications WHERE user_id = ? AND status = 'offer'", [$userId])['count'] ?? 0,
                    'active_applications' => $db->fetchOne("SELECT COUNT(*) as count FROM job_applications WHERE user_id = ? AND status IN ('applied', 'interview', 'assessment')", [$userId])['count'] ?? 0,
                    'applications_by_status' => $db->fetchAll(
                        "SELECT status, COUNT(*) as count FROM job_applications WHERE user_id = ? GROUP BY status",
                        [$userId]
                    ),
                    'recent_activity' => $db->fetchAll(
                        "SELECT * FROM job_applications WHERE user_id = ? ORDER BY application_date DESC LIMIT 5",
                        [$userId]
                    )
                ];
                
                echo json_encode(['success' => true, 'data' => $stats]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $type = $data['type'] ?? 'job';
            
            if ($type === 'job') {
                $jobId = $db->insert('job_applications', [
                    'user_id' => $userId,
                    'company_name' => sanitize($data['company_name']),
                    'position' => sanitize($data['position']),
                    'job_url' => sanitize($data['job_url'] ?? ''),
                    'status' => sanitize($data['status'] ?? 'applied'),
                    'application_date' => sanitize($data['application_date']),
                    'follow_up_date' => sanitize($data['follow_up_date'] ?? null),
                    'salary_range' => sanitize($data['salary_range'] ?? ''),
                    'location' => sanitize($data['location'] ?? ''),
                    'job_type' => sanitize($data['job_type'] ?? ''),
                    'notes' => sanitize($data['notes'] ?? '')
                ]);
                
                echo json_encode(['success' => true, 'id' => $jobId, 'message' => 'Job application added successfully']);
                
            } elseif ($type === 'interview') {
                $interviewId = $db->insert('interviews', [
                    'job_application_id' => (int)$data['job_application_id'],
                    'user_id' => $userId,
                    'interview_type' => sanitize($data['interview_type'] ?? ''),
                    'interview_date' => sanitize($data['interview_date']),
                    'interviewer_name' => sanitize($data['interviewer_name'] ?? ''),
                    'interview_notes' => sanitize($data['interview_notes'] ?? ''),
                    'outcome' => sanitize($data['outcome'] ?? null),
                    'feedback' => sanitize($data['feedback'] ?? '')
                ]);
                
                echo json_encode(['success' => true, 'id' => $interviewId, 'message' => 'Interview added successfully']);
                
            } elseif ($type === 'certification') {
                $certId = $db->insert('career_certifications', [
                    'user_id' => $userId,
                    'name' => sanitize($data['name']),
                    'issuing_organization' => sanitize($data['issuing_organization'] ?? ''),
                    'issue_date' => sanitize($data['issue_date'] ?? null),
                    'expiry_date' => sanitize($data['expiry_date'] ?? null),
                    'credential_id' => sanitize($data['credential_id'] ?? ''),
                    'credential_url' => sanitize($data['credential_url'] ?? ''),
                    'skills_gained' => sanitize($data['skills_gained'] ?? '')
                ]);
                
                echo json_encode(['success' => true, 'id' => $certId, 'message' => 'Certification added successfully']);
                
            } elseif ($type === 'resume') {
                $resumeId = $db->insert('resume_versions', [
                    'user_id' => $userId,
                    'version_name' => sanitize($data['version_name']),
                    'file_path' => sanitize($data['file_path'] ?? ''),
                    'content' => sanitize($data['content'] ?? ''),
                    'ai_feedback' => sanitize($data['ai_feedback'] ?? '')
                ]);
                
                echo json_encode(['success' => true, 'id' => $resumeId, 'message' => 'Resume version saved successfully']);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $type = $data['type'] ?? 'job';
            $id = (int)$data['id'];
            
            if ($type === 'job') {
                $db->update('job_applications', $id, [
                    'company_name' => sanitize($data['company_name']),
                    'position' => sanitize($data['position']),
                    'job_url' => sanitize($data['job_url'] ?? ''),
                    'status' => sanitize($data['status']),
                    'application_date' => sanitize($data['application_date']),
                    'follow_up_date' => sanitize($data['follow_up_date'] ?? null),
                    'salary_range' => sanitize($data['salary_range'] ?? ''),
                    'location' => sanitize($data['location'] ?? ''),
                    'job_type' => sanitize($data['job_type'] ?? ''),
                    'notes' => sanitize($data['notes'] ?? ''),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Job application updated successfully']);
                
            } elseif ($type === 'interview') {
                $db->update('interviews', $id, [
                    'interview_type' => sanitize($data['interview_type'] ?? ''),
                    'interview_date' => sanitize($data['interview_date']),
                    'interviewer_name' => sanitize($data['interviewer_name'] ?? ''),
                    'interview_notes' => sanitize($data['interview_notes'] ?? ''),
                    'outcome' => sanitize($data['outcome'] ?? null),
                    'feedback' => sanitize($data['feedback'] ?? '')
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Interview updated successfully']);
                
            } elseif ($type === 'certification') {
                $db->update('career_certifications', $id, [
                    'name' => sanitize($data['name']),
                    'issuing_organization' => sanitize($data['issuing_organization'] ?? ''),
                    'issue_date' => sanitize($data['issue_date'] ?? null),
                    'expiry_date' => sanitize($data['expiry_date'] ?? null),
                    'credential_id' => sanitize($data['credential_id'] ?? ''),
                    'credential_url' => sanitize($data['credential_url'] ?? ''),
                    'skills_gained' => sanitize($data['skills_gained'] ?? '')
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Certification updated successfully']);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'job';
            
            if ($type === 'job') {
                $db->delete('job_applications', $id);
                echo json_encode(['success' => true, 'message' => 'Job application deleted successfully']);
            } elseif ($type === 'interview') {
                $db->delete('interviews', $id);
                echo json_encode(['success' => true, 'message' => 'Interview deleted successfully']);
            } elseif ($type === 'certification') {
                $db->delete('career_certifications', $id);
                echo json_encode(['success' => true, 'message' => 'Certification deleted successfully']);
            } elseif ($type === 'resume') {
                $db->delete('resume_versions', $id);
                echo json_encode(['success' => true, 'message' => 'Resume version deleted successfully']);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
