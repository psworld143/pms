<?php
/**
 * Import Items from CSV
 * Hotel PMS Training System - Inventory Module
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No CSV file uploaded']);
        exit();
    }
    
    $csv_file = $_FILES['csv_file']['tmp_name'];
    $result = importItemsFromCSV($csv_file);
    
    echo json_encode([
        'success' => true,
        'message' => 'Items imported successfully',
        'imported_count' => $result['imported_count'],
        'errors' => $result['errors']
    ]);
    
} catch (Exception $e) {
    error_log("Error importing items: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Import items from CSV file
 */
function importItemsFromCSV($csv_file) {
    global $pdo;
    
    try {
        $imported_count = 0;
        $errors = [];
        
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            
            // Expected columns: Name,Category,SKU,Unit,Quantity,Min Stock,Cost Price,Supplier,Description
            $expected_columns = ['Name', 'Category', 'SKU', 'Unit', 'Quantity', 'Min Stock', 'Cost Price', 'Supplier', 'Description'];
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) < 3) continue; // Skip incomplete rows
                
                $name = trim($data[0] ?? '');
                $category = trim($data[1] ?? '');
                $sku = trim($data[2] ?? '');
                $unit = trim($data[3] ?? 'Piece');
                $quantity = (int)($data[4] ?? 0);
                $minimum_stock = (int)($data[5] ?? 0);
                $cost_price = (float)($data[6] ?? 0);
                $supplier = trim($data[7] ?? '');
                $description = trim($data[8] ?? '');
                
                if (empty($name) || empty($category)) {
                    $errors[] = "Row " . ($imported_count + 1) . ": Name and Category are required";
                    continue;
                }
                
                try {
                    // Get or create category
                    $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ? AND active = 1");
                    $stmt->execute([$category]);
                    $category_result = $stmt->fetch();
                    
                    if (!$category_result) {
                        $stmt = $pdo->prepare("
                            INSERT INTO inventory_categories (name, description, active, created_at) 
                            VALUES (?, ?, 1, NOW())
                        ");
                        $stmt->execute([$category, 'Imported category']);
                        $category_id = $pdo->lastInsertId();
                    } else {
                        $category_id = $category_result['id'];
                    }
                    
                    // Generate SKU if not provided
                    if (empty($sku)) {
                        $sku = 'IMP-' . strtoupper(substr($name, 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999);
                    }
                    
                    // Create inventory item
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory_items 
                        (name, sku, description, category_id, quantity, minimum_stock, maximum_stock, unit_price, cost_price, supplier, location, unit, status, created_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $name,
                        $sku,
                        $description,
                        $category_id,
                        $quantity,
                        $minimum_stock,
                        $quantity * 2,
                        $cost_price,
                        $cost_price,
                        $supplier,
                        'Main Storage',
                        $unit,
                        $_SESSION['user_id']
                    ]);
                    
                    $item_id = $pdo->lastInsertId();
                    
                    // Create initial stock transaction
                    if ($quantity > 0) {
                        $stmt = $pdo->prepare("
                            INSERT INTO inventory_transactions 
                            (item_id, transaction_type, quantity, unit_price, total_value, reason, created_by, created_at) 
                            VALUES (?, 'in', ?, ?, ?, 'Imported stock', ?, NOW())
                        ");
                        $stmt->execute([
                            $item_id,
                            $quantity,
                            $cost_price,
                            $quantity * $cost_price,
                            $_SESSION['user_id']
                        ]);
                    }
                    
                    $imported_count++;
                    
                } catch (PDOException $e) {
                    $errors[] = "Row " . ($imported_count + 1) . ": " . $e->getMessage();
                }
            }
            
            fclose($handle);
        }
        
        return [
            'imported_count' => $imported_count,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        error_log("Error importing items from CSV: " . $e->getMessage());
        throw new Exception("Error processing CSV file");
    }
}
?>
