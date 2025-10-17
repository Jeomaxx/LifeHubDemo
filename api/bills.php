<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/rate_limiter.php';

header('Content-Type: application/json');

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting
$rateLimiter = new RateLimiter();
if (!$rateLimiter->checkLimit($_SERVER['REMOTE_ADDR'], 'bills_api', 100, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.']);
    exit;
}

// Require authentication
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

// CSRF protection for non-GET requests
if ($method !== 'GET' && !verifyCsrfToken($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Route handling
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($action, $userId, $db);
            break;
        case 'POST':
            handlePost($action, $userId, $db);
            break;
        case 'PUT':
            handlePut($userId, $db);
            break;
        case 'DELETE':
            handleDelete($userId, $db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($action, $userId, $db) {
    switch ($action) {
        case 'list':
            getBillsList($userId, $db);
            break;
        case 'detail':
            getBillDetail($userId, $db);
            break;
        case 'overdue':
            getOverdueBills($userId, $db);
            break;
        case 'upcoming':
            $days = (int)($_GET['days'] ?? 7);
            if ($days < 1) $days = 1;
            if ($days > 365) $days = 365;
            $bills = getUpcomingBills($userId, $days);
            echo json_encode(['success' => true, 'bills' => $bills]);
            break;
        case 'payment-history':
            getPaymentHistory($userId, $db);
            break;
        case 'stats':
            getBillStats($userId, $db);
            break;
        case 'by-vendor':
            getBillsByVendor($userId, $db);
            break;
        default:
            getBillsList($userId, $db);
    }
}

function handlePost($action, $userId, $db) {
    switch ($action) {
        case 'create':
            createBill($userId, $db);
            break;
        case 'mark-paid':
            markBillPaid($userId, $db);
            break;
        case 'bulk-mark-paid':
            bulkMarkPaid($userId, $db);
            break;
        case 'send-reminder':
            sendBillReminder($userId, $db);
            break;
        case 'generate-next':
            generateNextRecurring($userId, $db);
            break;
        default:
            createBill($userId, $db);
    }
}

function handlePut($userId, $db) {
    $input = json_decode(file_get_contents('php://input'), true);
    updateBill($userId, $db, $input);
}

function handleDelete($userId, $db) {
    $billId = $_GET['id'] ?? null;
    if (!$billId) {
        http_response_code(400);
        echo json_encode(['error' => 'Bill ID is required']);
        return;
    }
    
    deleteBill($userId, $db, $billId);
}

function getBillsList($userId, $db) {
    $filters = [
        'status' => $_GET['status'] ?? null,
        'category' => $_GET['category'] ?? null,
        'vendor' => $_GET['vendor'] ?? null,
        'from_date' => $_GET['from_date'] ?? null,
        'to_date' => $_GET['to_date'] ?? null,
        'recurring' => $_GET['recurring'] ?? null
    ];
    
    $query = "SELECT * FROM bills WHERE user_id = ?";
    $params = [$userId];
    
    if ($filters['status']) {
        $query .= " AND payment_status = ?";
        $params[] = $filters['status'];
    }
    
    if ($filters['category']) {
        $query .= " AND category = ?";
        $params[] = $filters['category'];
    }
    
    if ($filters['vendor']) {
        $query .= " AND vendor = ?";
        $params[] = $filters['vendor'];
    }
    
    if ($filters['from_date']) {
        $query .= " AND due_date >= ?";
        $params[] = $filters['from_date'];
    }
    
    if ($filters['to_date']) {
        $query .= " AND due_date <= ?";
        $params[] = $filters['to_date'];
    }
    
    if ($filters['recurring'] !== null) {
        $query .= " AND recurring = ?";
        $params[] = $filters['recurring'] === 'true' ? true : false;
    }
    
    $query .= " ORDER BY due_date ASC";
    
    $bills = $db->fetchAll($query, $params);
    
    // Add payment history for each bill
    foreach ($bills as &$bill) {
        $bill['payments'] = $db->fetchAll(
            "SELECT * FROM bill_payments WHERE bill_id = ? ORDER BY payment_date DESC",
            [$bill['id']]
        );
    }
    
    echo json_encode(['success' => true, 'bills' => $bills]);
}

function getBillDetail($userId, $db) {
    $billId = $_GET['id'] ?? null;
    if (!$billId) {
        http_response_code(400);
        echo json_encode(['error' => 'Bill ID is required']);
        return;
    }
    
    $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
    
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Bill not found']);
        return;
    }
    
    // Get payment history
    $bill['payments'] = $db->fetchAll(
        "SELECT * FROM bill_payments WHERE bill_id = ? ORDER BY payment_date DESC",
        [$billId]
    );
    
    // Get budget info if linked
    if ($bill['budget_id']) {
        $bill['budget'] = $db->fetchOne(
            "SELECT * FROM budgets WHERE id = ? AND user_id = ?",
            [$bill['budget_id'], $userId]
        );
    }
    
    echo json_encode(['success' => true, 'bill' => $bill]);
}

function getOverdueBills($userId, $db) {
    $bills = $db->fetchAll(
        "SELECT * FROM bills 
         WHERE user_id = ? 
         AND payment_status != 'paid' 
         AND due_date < CURRENT_DATE
         ORDER BY due_date ASC",
        [$userId]
    );
    
    echo json_encode(['success' => true, 'bills' => $bills]);
}


function getPaymentHistory($userId, $db) {
    $billId = $_GET['bill_id'] ?? null;
    
    $query = "SELECT bp.*, b.name as bill_name, b.vendor 
              FROM bill_payments bp 
              JOIN bills b ON bp.bill_id = b.id 
              WHERE bp.user_id = ?";
    $params = [$userId];
    
    if ($billId) {
        $query .= " AND bp.bill_id = ?";
        $params[] = $billId;
    }
    
    $query .= " ORDER BY bp.payment_date DESC";
    
    $payments = $db->fetchAll($query, $params);
    echo json_encode(['success' => true, 'payments' => $payments]);
}

function getBillStats($userId, $db) {
    $stats = [
        'total_bills' => $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ?", [$userId]),
        'pending_bills' => $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND payment_status = 'pending'", [$userId]),
        'overdue_bills' => $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND payment_status != 'paid' AND due_date < CURRENT_DATE", [$userId]),
        'total_amount_due' => $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE user_id = ? AND payment_status != 'paid'", [$userId]),
        'total_paid_this_month' => $db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM bill_payments WHERE user_id = ? AND EXTRACT(MONTH FROM payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)",
            [$userId]
        ),
        'recurring_bills' => $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND recurring = true", [$userId])
    ];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function getBillsByVendor($userId, $db) {
    $bills = $db->fetchAll(
        "SELECT vendor, COUNT(*) as count, SUM(amount) as total_amount 
         FROM bills 
         WHERE user_id = ? AND vendor IS NOT NULL 
         GROUP BY vendor 
         ORDER BY count DESC",
        [$userId]
    );
    
    echo json_encode(['success' => true, 'vendors' => $bills]);
}

function createBill($userId, $db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['name', 'amount', 'due_date'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Sanitize and validate inputs
    $data = [
        'user_id' => $userId,
        'name' => sanitize($input['name']),
        'amount' => floatval($input['amount']),
        'due_date' => $input['due_date'],
        'payment_status' => $input['payment_status'] ?? 'pending',
        'recurring' => isset($input['recurring']) ? (bool)$input['recurring'] : false,
        'frequency' => $input['frequency'] ?? null,
        'category' => sanitize($input['category'] ?? ''),
        'vendor' => sanitize($input['vendor'] ?? ''),
        'reminder_days_before' => intval($input['reminder_days_before'] ?? 3),
        'notes' => sanitize($input['notes'] ?? ''),
        'auto_pay' => isset($input['auto_pay']) ? (bool)$input['auto_pay'] : false,
        'budget_id' => !empty($input['budget_id']) ? intval($input['budget_id']) : null,
        'payment_method' => $input['payment_method'] ?? null
    ];
    
    // Calculate next due date for recurring bills
    if ($data['recurring'] && $data['frequency']) {
        $data['next_due_date'] = calculateNextDueDate($data['due_date'], $data['frequency']);
    }
    
    $billId = $db->insert('bills', $data);
    
    if ($billId) {
        $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ?", [$billId]);
        echo json_encode(['success' => true, 'message' => 'Bill created successfully', 'bill' => $bill]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create bill']);
    }
}

function updateBill($userId, $db, $input) {
    $billId = $_GET['id'] ?? $input['id'] ?? null;
    
    if (!$billId) {
        http_response_code(400);
        echo json_encode(['error' => 'Bill ID is required']);
        return;
    }
    
    // Verify ownership
    $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Bill not found']);
        return;
    }
    
    // Build update data
    $updateData = [];
    $allowedFields = ['name', 'amount', 'due_date', 'payment_status', 'recurring', 'frequency', 'category', 'vendor', 'reminder_days_before', 'notes', 'auto_pay', 'budget_id', 'payment_method'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if (in_array($field, ['name', 'category', 'vendor', 'notes'])) {
                $updateData[$field] = sanitize($input[$field]);
            } elseif ($field === 'amount') {
                $updateData[$field] = floatval($input[$field]);
            } elseif (in_array($field, ['recurring', 'auto_pay'])) {
                $updateData[$field] = (bool)$input[$field];
            } elseif (in_array($field, ['reminder_days_before', 'budget_id'])) {
                $updateData[$field] = $input[$field] ? intval($input[$field]) : null;
            } else {
                $updateData[$field] = $input[$field];
            }
        }
    }
    
    // Update next due date if recurring changed
    if (isset($updateData['recurring']) && $updateData['recurring'] && isset($updateData['frequency'])) {
        $dueDate = $updateData['due_date'] ?? $bill['due_date'];
        $updateData['next_due_date'] = calculateNextDueDate($dueDate, $updateData['frequency']);
    }
    
    $updateData['updated_at'] = date('Y-m-d H:i:s');
    
    $success = $db->update('bills', $updateData, ['id' => $billId, 'user_id' => $userId]);
    
    if ($success) {
        $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ?", [$billId]);
        echo json_encode(['success' => true, 'message' => 'Bill updated successfully', 'bill' => $bill]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update bill']);
    }
}

function deleteBill($userId, $db, $billId) {
    // Verify ownership
    $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Bill not found']);
        return;
    }
    
    $success = $db->delete('bills', ['id' => $billId, 'user_id' => $userId]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Bill deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete bill']);
    }
}

function markBillPaid($userId, $db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $billId = $input['bill_id'] ?? null;
    
    if (!$billId) {
        http_response_code(400);
        echo json_encode(['error' => 'Bill ID is required']);
        return;
    }
    
    // Verify ownership
    $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Bill not found']);
        return;
    }
    
    // Record payment
    $paymentData = [
        'bill_id' => $billId,
        'user_id' => $userId,
        'amount' => $input['amount'] ?? $bill['amount'],
        'payment_date' => $input['payment_date'] ?? date('Y-m-d'),
        'payment_method' => $input['payment_method'] ?? $bill['payment_method'],
        'transaction_id' => $input['transaction_id'] ?? null,
        'notes' => sanitize($input['notes'] ?? '')
    ];
    
    $paymentId = $db->insert('bill_payments', $paymentData);
    
    // Update bill status
    $updateData = [
        'payment_status' => 'paid',
        'last_paid_date' => $paymentData['payment_date']
    ];
    
    // If recurring, generate next occurrence
    if ($bill['recurring'] && $bill['frequency']) {
        $nextDueDate = calculateNextDueDate($bill['due_date'], $bill['frequency']);
        
        $newBillData = [
            'user_id' => $userId,
            'name' => $bill['name'],
            'amount' => $bill['amount'],
            'due_date' => $nextDueDate,
            'payment_status' => 'pending',
            'recurring' => true,
            'frequency' => $bill['frequency'],
            'category' => $bill['category'],
            'vendor' => $bill['vendor'],
            'reminder_days_before' => $bill['reminder_days_before'],
            'notes' => $bill['notes'],
            'auto_pay' => $bill['auto_pay'],
            'budget_id' => $bill['budget_id'],
            'payment_method' => $bill['payment_method'],
            'next_due_date' => calculateNextDueDate($nextDueDate, $bill['frequency'])
        ];
        
        $newBillId = $db->insert('bills', $newBillData);
        $updateData['next_due_date'] = $nextDueDate;
    }
    
    $db->update('bills', $updateData, ['id' => $billId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Bill marked as paid',
        'payment_id' => $paymentId,
        'next_bill_id' => $newBillId ?? null
    ]);
}

function bulkMarkPaid($userId, $db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $billIds = $input['bill_ids'] ?? [];
    
    if (empty($billIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'No bills specified']);
        return;
    }
    
    $results = [];
    foreach ($billIds as $billId) {
        $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
        if ($bill) {
            // Record payment
            $paymentData = [
                'bill_id' => $billId,
                'user_id' => $userId,
                'amount' => $bill['amount'],
                'payment_date' => date('Y-m-d'),
                'payment_method' => $bill['payment_method']
            ];
            
            $db->insert('bill_payments', $paymentData);
            $db->update('bills', ['payment_status' => 'paid', 'last_paid_date' => date('Y-m-d')], ['id' => $billId]);
            
            $results[] = ['bill_id' => $billId, 'success' => true];
        } else {
            $results[] = ['bill_id' => $billId, 'success' => false, 'error' => 'Not found'];
        }
    }
    
    echo json_encode(['success' => true, 'results' => $results]);
}

function sendBillReminder($userId, $db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $billId = $input['bill_id'] ?? null;
    
    if (!$billId) {
        http_response_code(400);
        echo json_encode(['error' => 'Bill ID is required']);
        return;
    }
    
    $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Bill not found']);
        return;
    }
    
    require_once '../includes/notifications.php';
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    
    $message = "Bill Reminder: {$bill['name']} of " . formatCurrency($bill['amount']) . " is due on " . formatDate($bill['due_date']);
    
    $sent = [];
    if ($user['email']) {
        sendEmailNotification($user['email'], 'Bill Reminder', $message);
        $sent[] = 'email';
    }
    
    if ($user['telegram_chat_id']) {
        sendTelegramNotification($user['telegram_chat_id'], $message);
        $sent[] = 'telegram';
    }
    
    echo json_encode(['success' => true, 'message' => 'Reminder sent', 'channels' => $sent]);
}

function generateNextRecurring($userId, $db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $billId = $input['bill_id'] ?? null;
    
    if (!$billId) {
        http_response_code(400);
        echo json_encode(['error' => 'Bill ID is required']);
        return;
    }
    
    $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ? AND recurring = true", [$billId, $userId]);
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Recurring bill not found']);
        return;
    }
    
    $nextDueDate = calculateNextDueDate($bill['due_date'], $bill['frequency']);
    
    $newBillData = [
        'user_id' => $userId,
        'name' => $bill['name'],
        'amount' => $bill['amount'],
        'due_date' => $nextDueDate,
        'payment_status' => 'pending',
        'recurring' => true,
        'frequency' => $bill['frequency'],
        'category' => $bill['category'],
        'vendor' => $bill['vendor'],
        'reminder_days_before' => $bill['reminder_days_before'],
        'notes' => $bill['notes'],
        'auto_pay' => $bill['auto_pay'],
        'budget_id' => $bill['budget_id'],
        'payment_method' => $bill['payment_method'],
        'next_due_date' => calculateNextDueDate($nextDueDate, $bill['frequency'])
    ];
    
    $newBillId = $db->insert('bills', $newBillData);
    $newBill = $db->fetchOne("SELECT * FROM bills WHERE id = ?", [$newBillId]);
    
    echo json_encode(['success' => true, 'message' => 'Next occurrence generated', 'bill' => $newBill]);
}

function calculateNextDueDate($currentDate, $frequency) {
    $date = new DateTime($currentDate);
    
    switch ($frequency) {
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'biweekly':
            $date->modify('+2 weeks');
            break;
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'quarterly':
            $date->modify('+3 months');
            break;
        case 'yearly':
            $date->modify('+1 year');
            break;
        default:
            $date->modify('+1 month');
    }
    
    return $date->format('Y-m-d');
}
