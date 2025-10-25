<?php
/**
 * Process Scanned Items
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
@error_reporting(E_ERROR | E_PARSE);
@ini_set('display_errors', 0);

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $items = $_POST['items'] ?? [];
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No items provided']);
        exit();
    }
    
            // Log the incoming data for debugging
            error_log("Processing scanned items: " . json_encode($items));
            
            // Debug each item's values
            foreach ($items as $index => $item) {
                error_log("Item $index - Barcode: " . ($item['barcode'] ?? 'none') . 
                         ", Name: " . ($item['name'] ?? 'none') . 
                         ", Current Stock: " . ($item['current_stock'] ?? 'none') . 
                         ", Min Level: " . ($item['min_level'] ?? 'none') . 
                         ", Unit Cost: " . ($item['unit_cost'] ?? 'none') . 
                         ", Quantity: " . ($item['quantity'] ?? 'none'));
            }
    
    $result = processScannedItems($items);
    
    echo json_encode([
        'success' => true,
        'message' => 'Items processed successfully',
        'processed_count' => $result['processed_count']
    ]);
    
} catch (Exception $e) {
    error_log("Error processing scanned items: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Database error processing scanned items: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

/**
 * Process scanned items
 */
function processScannedItems($items) {
    global $pdo;
    
    try {
        // Ensure inventory_transactions table exists first (outside transaction)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                item_id INT,
                transaction_type VARCHAR(50),
                quantity INT,
                unit_price DECIMAL(10,2),
                total_value DECIMAL(10,2),
                reason TEXT,
                user_id INT,
                performed_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Ensure inventory_items table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                item_name VARCHAR(255),
                name VARCHAR(255),
                sku VARCHAR(100),
                barcode VARCHAR(100),
                category VARCHAR(100),
                category_name VARCHAR(100),
                unit VARCHAR(50),
                description TEXT,
                current_stock INT DEFAULT 0,
                quantity INT DEFAULT 0,
                unit_price DECIMAL(10,2) DEFAULT 0,
                price DECIMAL(10,2) DEFAULT 0,
                cost_price DECIMAL(10,2) DEFAULT 0,
                minimum_stock INT DEFAULT 0,
                min_level INT DEFAULT 0,
                supplier VARCHAR(255),
                status VARCHAR(50) DEFAULT 'active',
                category_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Try to ensure inventory_categories table exists (schema-adaptive)
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS inventory_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    category_name VARCHAR(255),
                    name VARCHAR(255),
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Insert default category if none exists (schema-adaptive)
            try {
                // Check which name column exists
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_categories LIKE 'name'");
                if ($stmt->fetch()) {
                    $pdo->exec("INSERT IGNORE INTO inventory_categories (id, name, description) VALUES (1, 'General', 'General inventory items')");
                } else {
                    $pdo->exec("INSERT IGNORE INTO inventory_categories (id, category_name, description) VALUES (1, 'General', 'General inventory items')");
                }
            } catch (PDOException $e) {
                // If insert fails, just continue - category might not be needed
            }
        } catch (PDOException $e) {
            // If we can't create categories table, just continue without it
            error_log("Could not create categories table: " . $e->getMessage());
        }
        
        $pdo->beginTransaction();
        $processed_count = 0;
        
        foreach ($items as $item) {
            $barcode = $item['barcode'] ?? '';
            $name = $item['name'] ?? '';
            $category = $item['category'] ?? '';
            $unit = $item['unit'] ?? '';
            $description = $item['description'] ?? '';
            $current_stock = (int)($item['current_stock'] ?? 0);
            $min_level = (int)($item['min_level'] ?? 0);
            $unit_cost = (float)($item['unit_cost'] ?? 0);
            $supplier = $item['supplier'] ?? '';
            $quantity = (int)($item['quantity'] ?? 0);
            $status = $item['status'] ?? 'manual';
            
            // Debug logging
            error_log("Processing item: " . json_encode($item));
            error_log("Parsed values - Name: $name, Category: $category, Unit: $unit, Unit Cost: $unit_cost, Min Level: $min_level, Current Stock: $current_stock");
            
            if (empty($name)) {
                error_log("Skipping item - empty name");
                continue;
            }
            
            // Try to find existing item by barcode or name
            $item_id = null;
            $existing_current_stock = 0;
            $existing_unit_price = 0;
            $existing_min_level = 0;
            
            // Detect available columns first
            $stock_col = 'current_stock';
            $price_col = 'unit_price';
            $name_col = 'item_name';
            $sku_col = 'sku';
            $min_col = 'minimum_stock';
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'quantity'");
                if ($stmt->fetch()) $stock_col = 'quantity';
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'price'");
                if ($stmt->fetch()) $price_col = 'price';
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'name'");
                if ($stmt->fetch()) $name_col = 'name';
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'barcode'");
                if ($stmt->fetch()) $sku_col = 'barcode';
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'min_level'");
                if ($stmt->fetch()) $min_col = 'min_level';
            } catch (PDOException $e) {}
            
            // First try to find by barcode/SKU
            if (!empty($barcode)) {
                $stmt = $pdo->prepare("SELECT id, $stock_col as stock, $price_col as price, $min_col as min_level FROM inventory_items WHERE $sku_col = ? LIMIT 1");
                $stmt->execute([$barcode]);
                $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($item_data) {
                    $item_id = $item_data['id'];
                    $existing_current_stock = (int)$item_data['stock'];
                    $existing_unit_price = (float)$item_data['price'];
                    $existing_min_level = (int)$item_data['min_level'];
                }
            }
            
            // If not found by barcode, try by name
            if (!$item_id && !empty($name)) {
                $stmt = $pdo->prepare("SELECT id, $stock_col as stock, $price_col as price, $min_col as min_level FROM inventory_items WHERE $name_col LIKE ? LIMIT 1");
                $stmt->execute(["%$name%"]);
                $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($item_data) {
                    $item_id = $item_data['id'];
                    $existing_current_stock = (int)$item_data['stock'];
                    $existing_unit_price = (float)$item_data['price'];
                    $existing_min_level = (int)$item_data['min_level'];
                }
            }
            
            // If still not found, create a new item
            if (!$item_id) {
                // Try to get a category ID, but don't fail if we can't
                $default_category_id = null;
                try {
                    $stmt = $pdo->query("SELECT id FROM inventory_categories LIMIT 1");
                    $category = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($category) {
                        $default_category_id = $category['id'];
                    }
                } catch (PDOException $e) {
                    // No categories available, continue without category
                    $default_category_id = null;
                }
                
                // Determine the correct column names
                $name_col = 'item_name';
                $stock_col = 'current_stock';
                $price_col = 'unit_price';
                $status_col = 'status';
                $created_col = 'created_at';
                
                // Check for alternative column names
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'name'");
                    if ($stmt->fetch()) $name_col = 'name';
                } catch (PDOException $e) {}
                
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'quantity'");
                    if ($stmt->fetch()) $stock_col = 'quantity';
                } catch (PDOException $e) {}
                
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'price'");
                    if ($stmt->fetch()) $price_col = 'price';
                } catch (PDOException $e) {}
                
                // Build insert query dynamically
                $insert_cols = [$name_col, 'sku', $stock_col, $price_col];
                $insert_vals = ['?', '?', '?', '?'];
                $insert_params = [$name, $barcode, $current_stock, $unit_cost];
                
                // Handle category - need to find or create category_id
                $category_id = null;
                if (!empty($category)) {
                    try {
                        // First try to find existing category
                        $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ? OR category_name = ? LIMIT 1");
                        $stmt->execute([$category, $category]);
                        $existing_category = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existing_category) {
                            $category_id = $existing_category['id'];
                        } else {
                            // Create new category
                            $stmt = $pdo->prepare("INSERT INTO inventory_categories (name, category_name, description) VALUES (?, ?, ?)");
                            $stmt->execute([$category, $category, "Category for $category"]);
                            $category_id = $pdo->lastInsertId();
                        }
                    } catch (PDOException $e) {
                        error_log("Error handling category: " . $e->getMessage());
                    }
                }
                
                // Add category_id if we have one
                if ($category_id) {
                    try {
                        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'category_id'");
                        if ($stmt->fetch()) {
                            $insert_cols[] = 'category_id';
                            $insert_vals[] = '?';
                            $insert_params[] = $category_id;
                        }
                    } catch (PDOException $e) {}
                }
                
                // Add unit if column exists
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'unit'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = 'unit';
                        $insert_vals[] = '?';
                        $insert_params[] = $unit;
                    }
                } catch (PDOException $e) {}
                
                // Add description if column exists
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'description'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = 'description';
                        $insert_vals[] = '?';
                        $insert_params[] = $description;
                    }
                } catch (PDOException $e) {}
                
                // Add minimum stock if column exists
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'minimum_stock'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = 'minimum_stock';
                        $insert_vals[] = '?';
                        $insert_params[] = $min_level;
                    }
                } catch (PDOException $e) {}
                
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'min_level'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = 'min_level';
                        $insert_vals[] = '?';
                        $insert_params[] = $min_level;
                    }
                } catch (PDOException $e) {}
                
                // Add supplier if column exists
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'supplier'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = 'supplier';
                        $insert_vals[] = '?';
                        $insert_params[] = $supplier;
                    }
                } catch (PDOException $e) {}
                
                // Add optional columns
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'status'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = $status_col;
                        $insert_vals[] = '?';
                        $insert_params[] = 'active';
                    }
                } catch (PDOException $e) {}
                
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'category_id'");
                    if ($stmt->fetch() && $default_category_id) {
                        $insert_cols[] = 'category_id';
                        $insert_vals[] = '?';
                        $insert_params[] = $default_category_id;
                    }
                } catch (PDOException $e) {}
                
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'created_at'");
                    if ($stmt->fetch()) {
                        $insert_cols[] = $created_col;
                        $insert_vals[] = 'NOW()';
                    }
                } catch (PDOException $e) {}
                
                try {
                    $insert_sql = "INSERT INTO inventory_items (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $insert_vals) . ")";
                    error_log("Insert SQL: $insert_sql");
                    error_log("Insert params: " . json_encode($insert_params));
                    
                    $stmt = $pdo->prepare($insert_sql);
                    $stmt->execute($insert_params);
                    $item_id = $pdo->lastInsertId();
                    $current_stock = $current_stock; // Use the actual current_stock from form
                    $unit_price = $unit_cost; // Use the actual unit_cost from form
                    
                    error_log("New item created with ID: $item_id, Current Stock: $current_stock, Unit Price: $unit_price");
                } catch (PDOException $e) {
                    // If foreign key constraint fails, try without category_id
                    if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                        // Remove category_id from the insert
                        $insert_cols_no_fk = array_filter($insert_cols, function($col) {
                            return $col !== 'category_id';
                        });
                        $insert_vals_no_fk = array_slice($insert_vals, 0, count($insert_cols_no_fk));
                        $insert_params_no_fk = array_slice($insert_params, 0, count($insert_cols_no_fk));
                        
                        $insert_sql = "INSERT INTO inventory_items (" . implode(', ', $insert_cols_no_fk) . ") VALUES (" . implode(', ', $insert_vals_no_fk) . ")";
                        error_log("Fallback Insert SQL: $insert_sql");
                        error_log("Fallback Insert params: " . json_encode($insert_params_no_fk));
                        
                        $stmt = $pdo->prepare($insert_sql);
                        $stmt->execute($insert_params_no_fk);
                        $item_id = $pdo->lastInsertId();
                        $current_stock = $current_stock; // Use the actual current_stock from form
                        $unit_price = $unit_cost; // Use the actual unit_cost from form
                        
                        error_log("Fallback item created with ID: $item_id, Current Stock: $current_stock, Unit Price: $unit_price");
                    } else {
                        // Re-throw if it's not a foreign key constraint error
                        throw $e;
                    }
                }
            }
            
            // Update inventory with the values from the barcode scanner form
            $update_cols = [];
            $update_params = [];
            
            // Debug the values being used for update
            error_log("Updating item $item_id - Current Stock: $current_stock, Unit Cost: $unit_cost, Min Level: $min_level");
            
            // Update current stock (use form value if provided, otherwise adjust existing)
            if ($item_id) {
                // For existing items, use the form values to update the database
                $update_cols[] = "$stock_col = ?";
                $update_params[] = $current_stock;
                
                // Update unit price (always update with form value, even if 0)
                $update_cols[] = "$price_col = ?";
                $update_params[] = $unit_cost;
                
                // Update min level (always update with form value, even if 0)
                $update_cols[] = "$min_col = ?";
                $update_params[] = $min_level;
                
                // Update category if provided
                if (!empty($category)) {
                    try {
                        // Find or create category
                        $stmt = $pdo->prepare("SELECT id FROM inventory_categories WHERE name = ? OR category_name = ? LIMIT 1");
                        $stmt->execute([$category, $category]);
                        $existing_category = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existing_category) {
                            $category_id = $existing_category['id'];
                        } else {
                            // Create new category
                            $stmt = $pdo->prepare("INSERT INTO inventory_categories (name, category_name, description) VALUES (?, ?, ?)");
                            $stmt->execute([$category, $category, "Category for $category"]);
                            $category_id = $pdo->lastInsertId();
                        }
                        
                        // Update category_id
                        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'category_id'");
                        if ($stmt->fetch()) {
                            $update_cols[] = "category_id = ?";
                            $update_params[] = $category_id;
                        }
                    } catch (PDOException $e) {
                        error_log("Error updating category: " . $e->getMessage());
                    }
                }
                
                // Update unit if provided
                if (!empty($unit)) {
                    try {
                        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'unit'");
                        if ($stmt->fetch()) {
                            $update_cols[] = "unit = ?";
                            $update_params[] = $unit;
                        }
                    } catch (PDOException $e) {}
                }
                
                // Update description if provided
                if (!empty($description)) {
                    try {
                        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'description'");
                        if ($stmt->fetch()) {
                            $update_cols[] = "description = ?";
                            $update_params[] = $description;
                        }
                    } catch (PDOException $e) {}
                }
                
                // Update supplier if provided
                if (!empty($supplier)) {
                    try {
                        $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'supplier'");
                        if ($stmt->fetch()) {
                            $update_cols[] = "supplier = ?";
                            $update_params[] = $supplier;
                        }
                    } catch (PDOException $e) {}
                }
                
                // Check if last_updated column exists
                try {
                    $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items LIKE 'last_updated'");
                    if ($stmt->fetch()) {
                        $update_cols[] = 'last_updated = NOW()';
                    }
                } catch (PDOException $e) {}
                
                $update_sql = "UPDATE inventory_items SET " . implode(', ', $update_cols) . " WHERE id = ?";
                $update_params[] = $item_id;
                
                // Debug the SQL and parameters
                error_log("Update SQL: $update_sql");
                error_log("Update params: " . json_encode($update_params));
                
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute($update_params);
                
                // Debug the result
                error_log("Update executed successfully for item $item_id");
                
                // Verify the update
                $verify_stmt = $pdo->prepare("SELECT * FROM inventory_items WHERE id = ?");
                $verify_stmt->execute([$item_id]);
                $updated_item = $verify_stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Updated item verification: " . json_encode($updated_item));
            }
            
            // For transaction logging, use the quantity for the transaction
            $new_stock = max(0, $current_stock - $quantity);
            $transaction_type = 'out';
            $reason = "Barcode scan: $barcode - $name (Status: $status)";
            
            // Log transaction
            $quantity_change = $quantity;
            $total_value = $quantity_change * $unit_cost;
            
            // Build insert query dynamically based on available columns
            $insert_cols = ['item_id', 'transaction_type', 'quantity', 'reason'];
            $insert_vals = [':item_id', ':transaction_type', ':quantity', ':reason'];
            $params = [
                ':item_id' => $item_id,
                ':transaction_type' => $transaction_type,
                ':quantity' => $quantity_change,
                ':reason' => $reason
            ];
            
            // Add optional columns if they exist
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_transactions LIKE 'unit_price'");
                if ($stmt->fetch()) {
                    $insert_cols[] = 'unit_price';
                    $insert_vals[] = ':unit_price';
                    $params[':unit_price'] = $unit_cost;
                }
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_transactions LIKE 'total_value'");
                if ($stmt->fetch()) {
                    $insert_cols[] = 'total_value';
                    $insert_vals[] = ':total_value';
                    $params[':total_value'] = $total_value;
                }
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_transactions LIKE 'user_id'");
                if ($stmt->fetch()) {
                    $insert_cols[] = 'user_id';
                    $insert_vals[] = ':user_id';
                    $params[':user_id'] = $_SESSION['user_id'];
                }
            } catch (PDOException $e) {}
            
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM inventory_transactions LIKE 'performed_by'");
                if ($stmt->fetch()) {
                    $insert_cols[] = 'performed_by';
                    $insert_vals[] = ':performed_by';
                    $params[':performed_by'] = $_SESSION['user_id'];
                }
            } catch (PDOException $e) {}
            
            $insert_sql = "INSERT INTO inventory_transactions (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $insert_vals) . ")";
            $stmt = $pdo->prepare($insert_sql);
            $stmt->execute($params);
            
            $processed_count++;
        }
        
        $pdo->commit();
        
        return ['processed_count' => $processed_count];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
?>