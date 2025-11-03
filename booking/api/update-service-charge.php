<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
        if (!($apiKey && $apiKey === 'pms_users_api_2024')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($input['id'] ?? 0);
    $unit_price = isset($input['unit_price']) ? (float)$input['unit_price'] : null;
    $quantity = isset($input['quantity']) ? max(1,(int)$input['quantity']) : null;
    $description = isset($input['description']) ? trim($input['description']) : null;

    if ($id <= 0) { throw new Exception('id is required'); }

    $updated = false;
    // Try normalized table first
    try {
        $sel = $pdo->prepare('SELECT * FROM service_charges WHERE id = ? LIMIT 1');
        $sel->execute([$id]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $newQty = $quantity ?? (int)($row['quantity'] ?? 1);
            $newPrice = $unit_price ?? (float)($row['unit_price'] ?? 0);
            $newTotal = round($newQty * $newPrice, 2);
            $upd = $pdo->prepare('UPDATE service_charges SET unit_price = ?, quantity = ?, total_price = ?, notes = COALESCE(?, notes) WHERE id = ?');
            $upd->execute([$newPrice, $newQty, $newTotal, $description, $id]);
            $updated = true;
        }
    } catch (PDOException $ignore) {}

    if (!$updated) {
        // Legacy table
        try {
            $sel = $pdo->prepare('SELECT * FROM reservation_services WHERE id = ? LIMIT 1');
            $sel->execute([$id]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $newQty = $quantity ?? (int)($row['quantity'] ?? 1);
                $newPrice = $unit_price ?? (float)($row['amount'] ?? 0);
                $upd = $pdo->prepare('UPDATE reservation_services SET amount = ?, quantity = ?, service_name = COALESCE(?, service_name) WHERE id = ?');
                $upd->execute([$newPrice, $newQty, $description, $id]);
                $updated = true;
            }
        } catch (PDOException $ignore) {}
    }

    if (!$updated) { throw new Exception('Charge not found'); }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('update-service-charge error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>



