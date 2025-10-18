<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $recipes = $db->fetchAll("SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
            jsonResponse(['success' => true, 'recipes' => $recipes]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('recipes', [
                'user_id' => $userId,
                'name' => sanitize($data['name']),
                'description' => sanitize($data['description'] ?? ''),
                'category' => $data['category'],
                'cuisine' => sanitize($data['cuisine'] ?? ''),
                'prep_time' => $data['prep_time'] ?? 0,
                'cook_time' => $data['cook_time'] ?? 0,
                'servings' => $data['servings'] ?? 1,
                'ingredients' => sanitize($data['ingredients'] ?? ''),
                'instructions' => sanitize($data['instructions'] ?? ''),
                'image_url' => sanitize($data['image_url'] ?? ''),
                'source_url' => sanitize($data['source_url'] ?? ''),
                'is_favorite' => isset($data['is_favorite']) ? (bool)$data['is_favorite'] : false
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Recipe added successfully']);
            break;
            
        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('recipes', [
                'name' => sanitize($data['name']),
                'description' => sanitize($data['description'] ?? ''),
                'category' => $data['category'],
                'cuisine' => sanitize($data['cuisine'] ?? ''),
                'prep_time' => $data['prep_time'] ?? 0,
                'cook_time' => $data['cook_time'] ?? 0,
                'servings' => $data['servings'] ?? 1,
                'ingredients' => sanitize($data['ingredients'] ?? ''),
                'instructions' => sanitize($data['instructions'] ?? ''),
                'image_url' => sanitize($data['image_url'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND user_id = ?', [$id, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Recipe updated successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->query("DELETE FROM recipes WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Recipe deleted successfully']);
            break;
            
        case 'add_to_meal_plan':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('meal_plans', [
                'user_id' => $userId,
                'recipe_id' => $data['recipe_id'],
                'meal_date' => $data['meal_date'],
                'meal_type' => $data['meal_type'],
                'servings' => $data['servings'] ?? 1,
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Added to meal plan']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Recipes API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
