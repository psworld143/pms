<?php
require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (($_SESSION['user_role'] ?? '') !== 'housekeeping') { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }

try {
    $db = new InventoryDatabase();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); exit; }
    $stmt = $db->getConnection()->prepare("SELECT id, title, description, instructions, expected_outcome, difficulty, estimated_time, points FROM inventory_training_scenarios WHERE id = ? AND active = 1");
    $stmt->execute([$id]);
    $scenario = $stmt->fetch();
    if (!$scenario) { echo json_encode(['success'=>false,'message'=>'Scenario not found']); exit; }
    echo json_encode(['success'=>true,'scenario'=>$scenario]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error']);
}
?>

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
