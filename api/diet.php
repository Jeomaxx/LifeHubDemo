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
$type = $_GET['type'] ?? 'plans'; // plans or meals

try {
    switch ($method) {
        case 'GET':
            if ($type === 'plans') {
                $plans = $db->fetchAll(
                    "SELECT * FROM diet_plans WHERE user_id = ? ORDER BY is_active DESC, created_at DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'plans' => $plans]);
            } elseif ($type === 'meals') {
                $planId = $_GET['plan_id'] ?? null;
                $date = $_GET['date'] ?? date('Y-m-d');
                
                if ($planId) {
                    $meals = $db->fetchAll(
                        "SELECT * FROM diet_meals WHERE user_id = ? AND plan_id = ? AND meal_date = ? ORDER BY created_at",
                        [$userId, $planId, $date]
                    );
                } else {
                    $meals = $db->fetchAll(
                        "SELECT * FROM diet_meals WHERE user_id = ? AND meal_date = ? ORDER BY created_at",
                        [$userId, $date]
                    );
                }
                echo json_encode(['success' => true, 'meals' => $meals]);
            } elseif ($action === 'daily_summary') {
                $date = $_GET['date'] ?? date('Y-m-d');
                $summary = $db->fetchOne(
                    "SELECT 
                        COALESCE(SUM(calories), 0) as total_calories,
                        COALESCE(SUM(protein), 0) as total_protein,
                        COALESCE(SUM(carbs), 0) as total_carbs,
                        COALESCE(SUM(fat), 0) as total_fat,
                        COUNT(*) as meal_count
                    FROM diet_meals WHERE user_id = ? AND meal_date = ?",
                    [$userId, $date]
                );
                echo json_encode(['success' => true, 'summary' => $summary]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'plans' && $action === 'create') {
                $id = $db->insert('diet_plans', [
                    'user_id' => $userId,
                    'plan_name' => $data['plan_name'],
                    'daily_calories_goal' => $data['daily_calories_goal'] ?? null,
                    'protein_goal' => $data['protein_goal'] ?? null,
                    'carbs_goal' => $data['carbs_goal'] ?? null,
                    'fat_goal' => $data['fat_goal'] ?? null,
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                    'is_active' => $data['is_active'] ?? true
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Diet plan created successfully']);
            } elseif ($type === 'meals' && $action === 'create') {
                $id = $db->insert('diet_meals', [
                    'user_id' => $userId,
                    'plan_id' => $data['plan_id'] ?? null,
                    'meal_date' => $data['meal_date'],
                    'meal_type' => $data['meal_type'] ?? null,
                    'meal_name' => $data['meal_name'],
                    'calories' => $data['calories'] ?? null,
                    'protein' => $data['protein'] ?? null,
                    'carbs' => $data['carbs'] ?? null,
                    'fat' => $data['fat'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Meal logged successfully']);
            } elseif ($action === 'activate_plan' && isset($data['id'])) {
                // Deactivate all other plans
                $db->execute("UPDATE diet_plans SET is_active = FALSE WHERE user_id = ?", [$userId]);
                // Activate selected plan
                $db->execute("UPDATE diet_plans SET is_active = TRUE WHERE id = ? AND user_id = ?", [$data['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Plan activated successfully']);
            }
            break;

        case 'DELETE':
            if ($type === 'plans' && isset($_GET['id'])) {
                $db->execute("DELETE FROM diet_plans WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Diet plan deleted successfully']);
            } elseif ($type === 'meals' && isset($_GET['id'])) {
                $db->execute("DELETE FROM diet_meals WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Meal deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
