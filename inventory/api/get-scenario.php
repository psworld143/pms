<?php
/**
 * Get Training Scenario Details API
 * Hotel PMS Training System for Students
 */

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$inventory_db = new InventoryDatabase();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Scenario ID required']);
    exit();
}

$scenario_id = $_GET['id'];

try {
    $stmt = $inventory_db->getConnection()->prepare("
        SELECT * FROM inventory_training_scenarios 
        WHERE id = ? AND active = 1
    ");
    $stmt->execute([$scenario_id]);
    $scenario = $stmt->fetch();
    
    if (!$scenario) {
        echo json_encode(['success' => false, 'message' => 'Scenario not found']);
        exit();
    }
    
    echo json_encode(['success' => true, 'scenario' => $scenario]);
    
} catch (PDOException $e) {
    error_log("Error getting scenario: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
