<?php
/**
 * Create Inventory Item
 * Hotel PMS Training System - Inventory Module
 */

require_once '../config/database.php';
require_once '../../vps_session_fix.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $minimum_stock = (int)($_POST['minimum_stock'] ?? 0);
    $cost_price = (float)($_POST['cost_price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($category) || empty($unit)) {
        echo json_encode(['success' => false, 'message' => 'Name, category, and unit are required']);
        exit();
    }
    
    $result = createInventoryItem($name, $category, $sku, $unit, $supplier, $quantity, $minimum_stock, $cost_price, $description);
    
    echo json_encode([
        'success' => true,
        'message' => 'Inventory item created successfully',
        'item_id' => $result['item_id']
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'sqlstate' => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("Error creating inventory item: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Check if a column exists on a table
 */
function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch();
}

/**
 * Create inventory item - adapts to legacy or new schema
 */
function createInventoryItem($name, $category, $sku, $unit, $supplier, $quantity, $minimum_stock, $cost_price, $description) {
    global $pdo;
    
    try {
        // Get category ID (create if missing)
        $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ?");
        $stmt->execute([$category]);
        $category_result = $stmt->fetch();
        if (!$category_result) {
            $stmt = $pdo->prepare("INSERT INTO inventory_categories (name, description, active, created_at) VALUES (?, ?, 1, NOW())");
            $stmt->execute([$category, 'Auto-created category']);
            $category_id = (int)$pdo->lastInsertId();
        } else {
            $category_id = (int)$category_result['id'];
        }

        // Generate SKU if none provided
        if (empty($sku)) {
            $sku = 'ITM-' . strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999);
        }

        // Determine actual column names present
        $hasItemName = columnExists($pdo, 'inventory_items', 'item_name');
        $hasName     = columnExists($pdo, 'inventory_items', 'name');
        $hasCurrent  = columnExists($pdo, 'inventory_items', 'current_stock');
        $hasQuantity = columnExists($pdo, 'inventory_items', 'quantity');
        // Note: do NOT attempt to use unit_price; some DBs don't have it
        $hasCostPrice= columnExists($pdo, 'inventory_items', 'cost_price');
        $hasUnitPrice= columnExists($pdo, 'inventory_items', 'unit_price');
        $hasSupplier = columnExists($pdo, 'inventory_items', 'supplier');
        $hasLocation = columnExists($pdo, 'inventory_items', 'location');
        $hasUnitCol  = columnExists($pdo, 'inventory_items', 'unit');
        $hasStatus   = columnExists($pdo, 'inventory_items', 'status');
        $hasUpdatedAt= columnExists($pdo, 'inventory_items', 'updated_at');
        $hasLastUpd  = columnExists($pdo, 'inventory_items', 'last_updated');
        $hasSku      = columnExists($pdo, 'inventory_items', 'sku');

        // Build dynamic insert
        $cols = [];
        $vals = [];
        $params = [];

        // name column (item_name or name)
        if ($hasItemName) { $cols[]='item_name'; $vals[]='?'; $params[]=$name; }
        elseif ($hasName) { $cols[]='name'; $vals[]='?'; $params[]=$name; }

        // optional sku
        if ($hasSku) { $cols[]='sku'; $vals[]='?'; $params[]=$sku; }

        $cols[]='category_id'; $vals[]='?'; $params[]=$category_id;
        // If table has no dedicated SKU column, include a readable SKU in description for visibility
        $descriptionWithSku = $description;
        if (!$hasSku && !empty($sku)) {
            $parts = [];
            $parts[] = trim($description);
            $parts[] = "SKU: $sku";
            if (!empty($unit)) { $parts[] = "Unit: $unit"; }
            if (!empty($supplier)) { $parts[] = "Supplier: $supplier"; }
            $descriptionWithSku = implode(' | ', array_filter($parts));
        }
        $cols[]='description'; $vals[]='?'; $params[]=$descriptionWithSku;

        if ($hasCurrent) { $cols[]='current_stock'; $vals[]='?'; $params[]=$quantity; }
        elseif ($hasQuantity) { $cols[]='quantity'; $vals[]='?'; $params[]=$quantity; }

        $cols[]='minimum_stock'; $vals[]='?'; $params[]=$minimum_stock;

        // Only insert cost_price or unit_price if present
        if ($hasCostPrice) { $cols[]='cost_price'; $vals[]='?'; $params[]=$cost_price; }
        if ($hasUnitPrice) { $cols[]='unit_price'; $vals[]='?'; $params[]=$cost_price; }
        if ($hasSupplier) { $cols[]='supplier'; $vals[]='?'; $params[]=$supplier ?: null; }
        if ($hasLocation) { $cols[]='location'; $vals[]='?'; $params[]='Main Storage'; }
        if ($hasUnitCol) { $cols[]='unit'; $vals[]='?'; $params[]=$unit ?: 'pcs'; }
        if ($hasStatus) { $cols[]='status'; $vals[]='?'; $params[]='active'; }

        // timestamps
        $cols[]='created_at'; $vals[]='NOW()';
        if ($hasUpdatedAt) { $cols[]='updated_at'; $vals[]='NOW()'; }
        if ($hasLastUpd) { $cols[]='last_updated'; $vals[]='NOW()'; }

        $sql = 'INSERT INTO inventory_items (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $item_id = (int)$pdo->lastInsertId();

        // Create initial stock transaction - adapt to existing schema
        if ($quantity > 0) {
            // Validate actor user id exists
            $actorUserId = (int)($_SESSION['user_id'] ?? 0);
            $userExists = false;
            if ($actorUserId > 0) {
                $chk = $pdo->prepare('SELECT id FROM users WHERE id = ?');
                $chk->execute([$actorUserId]);
                $userExists = (bool)$chk->fetchColumn();
            }
            if (!$userExists) {
                // Fallback to first manager or any user
                $fallback = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
                $actorUserId = $fallback ? (int)$fallback : null;
            }
            $hasTxnUnitPrice = columnExists($pdo, 'inventory_transactions', 'unit_price');
            $hasTxnTotalVal  = columnExists($pdo, 'inventory_transactions', 'total_value');
            $hasTxnUserId    = columnExists($pdo, 'inventory_transactions', 'user_id');
            $hasPerformedBy  = columnExists($pdo, 'inventory_transactions', 'performed_by');

            $txnCols = ['item_id', 'transaction_type', 'quantity'];
            $txnVals = ['?', "'in'", '?'];
            $txnParams = [$item_id, $quantity];

            if ($hasTxnUnitPrice) { $txnCols[]='unit_price'; $txnVals[]='?'; $txnParams[]=$cost_price; }
            if ($hasTxnTotalVal)  { $txnCols[]='total_value'; $txnVals[]='?'; $txnParams[]=$quantity * $cost_price; }

            $txnCols[]='reason'; $txnVals[]='?'; $txnParams[]='Initial stock';

            // Prefer user_id if present; otherwise use performed_by
            if ($hasTxnUserId) { $txnCols[]='user_id'; $txnVals[]='?'; $txnParams[]=$_SESSION['user_id']; }
            elseif ($hasPerformedBy) { $txnCols[]='performed_by'; $txnVals[]='?'; $txnParams[]=$_SESSION['user_id']; }

            $txnCols[]='created_at'; $txnVals[]='NOW()';

            // If no valid actor and performed_by is required, skip creating stock txn
            if (in_array('performed_by', $txnCols, true) && $actorUserId === null) {
                // do not throw – proceed without initial transaction
            } else {
                // Replace placeholder for actor if we deferred setting it above
                foreach ($txnCols as $i => $colName) {
                    if (($colName === 'user_id' || $colName === 'performed_by') && ($txnParams[$i- (count($txnCols)-count($txnParams))] ?? null) === null) {
                        // ensure actorUserId is present if column added; defensive, but we already pushed above
                    }
                }

                try {
                    $txnSql = 'INSERT INTO inventory_transactions (' . implode(', ', $txnCols) . ') VALUES (' . implode(', ', $txnVals) . ')';
                    $stmt = $pdo->prepare($txnSql);
                    $stmt->execute($txnParams);
                } catch (PDOException $te) {
                    // Foreign key mismatches (legacy schemas) should not block item creation
                    error_log('inventory_transactions insert skipped: ' . $te->getMessage());
                }
            }
        }

        return ['item_id' => $item_id];
        
    } catch (PDOException $e) {
        // Let the caller handle and return full DB message
        throw $e;
    }
}
?>
