<?php
/**
 * Safe Database Migration Script
 * This script will work with your actual database configuration
 */

// Start session
session_start();

// Include database configuration
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in first to run the migration.");
}

echo "<h1>Safe Database Migration Script</h1>";
echo "<p>Starting migration...</p>";

try {
    global $pdo;
    
    // Get current database name
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $db_name = $stmt->fetch()['db_name'];
    echo "<p><strong>Working with database:</strong> " . $db_name . "</p>";
    
    // Check if inventory_items table exists and get its structure
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_items'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>Error: inventory_items table does not exist in this database.</p>";
        echo "<p>Please make sure you're connected to the correct database.</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ inventory_items table found</p>";
    
    // Check what columns already exist
    $stmt = $pdo->query("DESCRIBE inventory_items");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Existing columns:</strong> " . implode(', ', $existing_columns) . "</p>";
    
    // Only add columns that don't exist
    $columns_to_add = [];
    
    if (!in_array('sku', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN `sku` VARCHAR(100) DEFAULT NULL AFTER `item_name`";
    }
    
    if (!in_array('unit', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN `unit` VARCHAR(50) DEFAULT 'pcs' AFTER `unit_price`";
    }
    
    if (!in_array('supplier', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN `supplier` VARCHAR(255) DEFAULT NULL AFTER `unit`";
    }
    
    if (!in_array('is_pos_product', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN `is_pos_product` TINYINT(1) DEFAULT 0 AFTER `supplier`";
    }
    
    if (!in_array('status', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN `status` ENUM('active', 'inactive', 'discontinued') DEFAULT 'active' AFTER `is_pos_product`";
    }
    
    if (!empty($columns_to_add)) {
        $sql = "ALTER TABLE `inventory_items` " . implode(', ', $columns_to_add);
        echo "<p>Adding missing columns...</p>";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Columns added successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ All required columns already exist</p>";
    }
    
    // Check inventory_transactions table
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_transactions'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("DESCRIBE inventory_transactions");
        $existing_trans_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $trans_columns_to_add = [];
        
        if (!in_array('unit_price', $existing_trans_columns)) {
            $trans_columns_to_add[] = "ADD COLUMN `unit_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `quantity`";
        }
        
        if (!in_array('total_value', $existing_trans_columns)) {
            $trans_columns_to_add[] = "ADD COLUMN `total_value` DECIMAL(10,2) DEFAULT 0.00 AFTER `unit_price`";
        }
        
        if (!empty($trans_columns_to_add)) {
            $sql = "ALTER TABLE `inventory_transactions` " . implode(', ', $trans_columns_to_add);
            echo "<p>Adding missing columns to inventory_transactions...</p>";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Transaction columns added successfully</p>";
        } else {
            echo "<p style='color: blue;'>ℹ All required transaction columns already exist</p>";
        }
    }
    
    // Create suppliers table if it doesn't exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'suppliers'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Creating suppliers table...</p>";
        $pdo->exec("
            CREATE TABLE `suppliers` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `contact_person` varchar(255) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `phone` varchar(50) DEFAULT NULL,
              `address` text DEFAULT NULL,
              `status` enum('active','inactive') DEFAULT 'active',
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✓ Suppliers table created</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Suppliers table already exists</p>";
    }
    
    // Update inventory items with sample data if needed
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory_items WHERE sku IS NULL OR sku = ''");
    $needs_sku_update = $stmt->fetch()['count'] > 0;
    
    if ($needs_sku_update) {
        echo "<p>Updating inventory items with sample data...</p>";
        $pdo->exec("
            UPDATE `inventory_items` SET 
              `sku` = CONCAT('SKU-', LPAD(id, 4, '0')),
              `unit` = COALESCE(`unit`, 'pcs'),
              `supplier` = COALESCE(`supplier`, 'ABC Supplies Inc.'),
              `is_pos_product` = COALESCE(`is_pos_product`, 0),
              `status` = COALESCE(`status`, 'active')
            WHERE `sku` IS NULL OR `sku` = ''
        ");
        echo "<p style='color: green;'>✓ Inventory items updated</p>";
    }
    
    echo "<h2 style='color: green;'>Migration Completed Successfully!</h2>";
    echo "<p>Your inventory system should now work properly.</p>";
    echo "<p><a href='index.php'>← Go to Inventory Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Migration Failed</h2>";
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection settings.</p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Migration Failed</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
