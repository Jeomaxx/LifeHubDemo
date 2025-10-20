<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $stats = [
                    'active_projects' => $db->fetchColumn("SELECT COUNT(*) FROM freelance_projects WHERE user_id = ? AND status = 'in_progress'", [$userId]) ?: 0,
                    'active_clients' => $db->fetchColumn("SELECT COUNT(*) FROM freelance_clients WHERE user_id = ? AND status = 'active'", [$userId]) ?: 0,
                    'pending_invoices' => $db->fetchColumn("SELECT COUNT(*) FROM freelance_invoices WHERE user_id = ? AND status != 'paid'", [$userId]) ?: 0,
                    'total_earned' => $db->fetchColumn("SELECT COALESCE(SUM(amount_paid), 0) FROM freelance_invoices WHERE user_id = ? AND EXTRACT(MONTH FROM invoice_date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 0
                ];
                echo json_encode(['success' => true, 'data' => $stats]);
            } elseif ($type === 'clients') {
                $clients = $db->fetchAll("SELECT * FROM freelance_clients WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $clients]);
            } elseif ($type === 'projects') {
                $status = $_GET['status'] ?? '';
                if ($status) {
                    $projects = $db->fetchAll("SELECT p.*, c.client_name FROM freelance_projects p LEFT JOIN freelance_clients c ON p.client_id = c.id WHERE p.user_id = ? AND p.status = ? ORDER BY p.created_at DESC", [$userId, $status]);
                } else {
                    $projects = $db->fetchAll("SELECT p.*, c.client_name FROM freelance_projects p LEFT JOIN freelance_clients c ON p.client_id = c.id WHERE p.user_id = ? ORDER BY p.created_at DESC", [$userId]);
                }
                echo json_encode(['success' => true, 'data' => $projects]);
            } elseif ($type === 'invoices') {
                $status = $_GET['status'] ?? '';
                if ($status) {
                    $invoices = $db->fetchAll("SELECT i.*, c.client_name FROM freelance_invoices i LEFT JOIN freelance_clients c ON i.client_id = c.id WHERE i.user_id = ? AND i.status = ? ORDER BY i.invoice_date DESC", [$userId, $status]);
                } else {
                    $invoices = $db->fetchAll("SELECT i.*, c.client_name FROM freelance_invoices i LEFT JOIN freelance_clients c ON i.client_id = c.id WHERE i.user_id = ? ORDER BY i.invoice_date DESC", [$userId]);
                }
                echo json_encode(['success' => true, 'data' => $invoices]);
            } elseif ($type === 'time-entries') {
                $entries = $db->fetchAll("SELECT t.*, p.project_name FROM freelance_time_entries t LEFT JOIN freelance_projects p ON t.project_id = p.id WHERE t.user_id = ? ORDER BY t.entry_date DESC LIMIT 100", [$userId]);
                echo json_encode(['success' => true, 'data' => $entries]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'client') {
                $id = $db->insert('freelance_clients', [
                    'user_id' => $userId,
                    'client_name' => $data['client_name'],
                    'company_name' => $data['company_name'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Client added successfully']);
            } elseif ($type === 'project') {
                $clientId = $data['client_id'] ?? null;
                
                if ($clientId) {
                    $clientExists = $db->fetchColumn("SELECT COUNT(*) FROM freelance_clients WHERE id = ? AND user_id = ?", [$clientId, $userId]);
                    if (!$clientExists) {
                        throw new Exception('Client not found or access denied');
                    }
                }
                
                $id = $db->insert('freelance_projects', [
                    'user_id' => $userId,
                    'client_id' => $clientId,
                    'project_name' => $data['project_name'],
                    'description' => $data['description'] ?? null,
                    'status' => $data['status'] ?? 'in_progress',
                    'start_date' => $data['start_date'] ?? null,
                    'deadline' => $data['deadline'] ?? null,
                    'budget' => $data['budget'] ?? null,
                    'hourly_rate' => $data['hourly_rate'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Project added successfully']);
            } elseif ($type === 'invoice') {
                $clientId = $data['client_id'] ?? null;
                $projectId = $data['project_id'] ?? null;
                
                if ($clientId) {
                    $clientExists = $db->fetchColumn("SELECT COUNT(*) FROM freelance_clients WHERE id = ? AND user_id = ?", [$clientId, $userId]);
                    if (!$clientExists) {
                        throw new Exception('Client not found or access denied');
                    }
                }
                
                if ($projectId) {
                    $projectExists = $db->fetchColumn("SELECT COUNT(*) FROM freelance_projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);
                    if (!$projectExists) {
                        throw new Exception('Project not found or access denied');
                    }
                }
                
                $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $id = $db->insert('freelance_invoices', [
                    'user_id' => $userId,
                    'client_id' => $clientId,
                    'project_id' => $projectId,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $data['invoice_date'],
                    'due_date' => $data['due_date'] ?? null,
                    'subtotal' => $data['subtotal'],
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'total_amount' => $data['total_amount'],
                    'status' => $data['status'] ?? 'draft',
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'invoice_number' => $invoiceNumber, 'message' => 'Invoice created successfully']);
            } elseif ($type === 'time-entry') {
                $projectId = $data['project_id'];
                
                $projectExists = $db->fetchColumn("SELECT COUNT(*) FROM freelance_projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);
                if (!$projectExists) {
                    throw new Exception('Project not found or access denied');
                }
                
                $id = $db->insert('freelance_time_entries', [
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'description' => $data['description'] ?? null,
                    'hours' => $data['hours'],
                    'entry_date' => $data['entry_date'],
                    'billable' => $data['billable'] ?? true
                ]);
                
                $db->execute("UPDATE freelance_projects SET actual_hours = COALESCE(actual_hours, 0) + ? WHERE id = ? AND user_id = ?", [$data['hours'], $projectId, $userId]);
                
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Time entry logged successfully']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            if ($type === 'invoice-status') {
                $affected = $db->execute("UPDATE freelance_invoices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?", [$data['status'], $id, $userId]);
                
                if ($affected === 0) {
                    throw new Exception('Invoice not found or access denied');
                }
                
                if ($data['status'] === 'paid' && isset($data['amount_paid'])) {
                    $db->execute("UPDATE freelance_invoices SET amount_paid = ? WHERE id = ? AND user_id = ?", [$data['amount_paid'], $id, $userId]);
                }
                echo json_encode(['success' => true, 'message' => 'Invoice updated successfully']);
            } elseif ($type === 'project-status') {
                $affected = $db->execute("UPDATE freelance_projects SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?", [$data['status'], $id, $userId]);
                
                if ($affected === 0) {
                    throw new Exception('Project not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID is required');
            }
            
            if ($type === 'client') {
                $affected = $db->execute("DELETE FROM freelance_clients WHERE id = ? AND user_id = ?", [$id, $userId]);
                if ($affected === 0) {
                    throw new Exception('Client not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Client deleted successfully']);
            } elseif ($type === 'project') {
                $affected = $db->execute("DELETE FROM freelance_projects WHERE id = ? AND user_id = ?", [$id, $userId]);
                if ($affected === 0) {
                    throw new Exception('Project not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
            } elseif ($type === 'invoice') {
                $affected = $db->execute("DELETE FROM freelance_invoices WHERE id = ? AND user_id = ?", [$id, $userId]);
                if ($affected === 0) {
                    throw new Exception('Invoice not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully']);
            } elseif ($type === 'time-entry') {
                $entry = $db->fetchOne("SELECT * FROM freelance_time_entries WHERE id = ? AND user_id = ?", [$id, $userId]);
                if (!$entry) {
                    throw new Exception('Time entry not found or access denied');
                }
                $db->execute("UPDATE freelance_projects SET actual_hours = COALESCE(actual_hours, 0) - ? WHERE id = ? AND user_id = ?", [$entry['hours'], $entry['project_id'], $userId]);
                $db->execute("DELETE FROM freelance_time_entries WHERE id = ? AND user_id = ?", [$id, $userId]);
                echo json_encode(['success' => true, 'message' => 'Time entry deleted successfully']);
            }
            break;

        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
