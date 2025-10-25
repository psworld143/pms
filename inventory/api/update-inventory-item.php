<?php
/**
 * Update Inventory Item
 */

require_once '../config/database.php';
require_once '../../vps_session_fix.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

try {
    $input = $_POST;
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Invalid item id');
    }

    $fields = [
        'name' => $input['name'] ?? null,
        'description' => $input['description'] ?? null,
        'quantity' => isset($input['quantity']) ? (int)$input['quantity'] : null,
        'minimum_stock' => isset($input['minimum_stock']) ? (int)$input['minimum_stock'] : null,
        'unit_price' => isset($input['cost_price']) ? (float)$input['cost_price'] : null,
        'unit' => $input['unit'] ?? null,
        'sku' => $input['sku'] ?? null
    ];

    $sets = [];
    $params = [];
    // Helper: check column exists
    $hasSkuCol = false;
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `inventory_items` LIKE 'sku'");
        $stmt->execute();
        $hasSkuCol = (bool)$stmt->fetch();
    } catch (Exception $e) {}

    foreach ($fields as $col => $val) {
        if ($val !== null) {
            switch ($col) {
                case 'name': $sets[] = 'item_name = ?'; $params[] = $val; break;
                case 'description': $sets[] = 'description = ?'; $params[] = $val; break;
                case 'quantity': $sets[] = 'current_stock = ?'; $params[] = $val; break;
                case 'minimum_stock': $sets[] = 'minimum_stock = ?'; $params[] = $val; break;
                case 'unit_price': $sets[] = 'unit_price = ?'; $params[] = $val; break;
                case 'unit': /* schema may not have unit; skip safely */ break;
                case 'sku':
                    if ($hasSkuCol) {
                        $sets[] = 'sku = ?';
                        $params[] = $val;
                    } else {
                        // Embed or replace SKU in description field
                        $currentDesc = '';
                        $s = $pdo->prepare('SELECT description FROM inventory_items WHERE id = ?');
                        $s->execute([$id]);
                        $currentDesc = (string)$s->fetchColumn();
                        // Remove existing SKU tag if present
                        $newDesc = preg_replace('/\s*\|?\s*SKU\s*:\s*[^|\n]*/i', '', $currentDesc);
                        $newDesc = trim($newDesc);
                        if ($newDesc !== '') {
                            $newDesc .= ' | ';
                        }
                        $newDesc .= 'SKU: ' . $val;
                        $sets[] = 'description = ?';
                        $params[] = $newDesc;
                    }
                    break;
            }
        }
    }

    if (empty($sets)) {
        echo json_encode(['success' => true, 'message' => 'Nothing to update']);
        exit();
    }

    $params[] = $id;
    $sql = 'UPDATE inventory_items SET ' . implode(', ', $sets) . ', last_updated = NOW() WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


