<?php
/**
 * Complete Inventory System Installation Script
 * Hotel PMS Training System - Inventory Module
 * 
 * This script installs the complete inventory system including:
 * - Basic inventory tables with sample data
 * - Enhanced tables for room inventory, mobile interface, reporting, etc.
 */

// Include database configuration
require_once 'config/database.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Start output buffering
ob_start();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Complete Inventory System Installation</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
</head>
<body class='bg-gray-100 min-h-screen'>
    <div class='container mx-auto px-4 py-8'>
        <div class='max-w-4xl mx-auto'>
            <div class='bg-white rounded-lg shadow-lg p-8'>
                <div class='text-center mb-8'>
                    <i class='fas fa-database text-4xl text-blue-600 mb-4'></i>
                    <h1 class='text-3xl font-bold text-gray-800'>Complete Inventory System Installation</h1>
                    <p class='text-gray-600 mt-2'>Installing complete inventory management system for hotel operations</p>
                </div>";

try {
    // Test database connection
    echo "<div class='mb-6'>
            <h2 class='text-xl font-semibold text-gray-800 mb-4'>
                <i class='fas fa-plug text-green-600 mr-2'></i>Database Connection
            </h2>";
    
    $pdo = getDatabaseConnection();
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>
            <i class='fas fa-check-circle mr-2'></i>Database connection successful
          </div>";
    
    // Step 1: Install basic inventory schema
    echo "<h2 class='text-xl font-semibold text-gray-800 mb-4'>
            <i class='fas fa-database text-blue-600 mr-2'></i>Step 1: Basic Inventory Schema
          </h2>";
    
    $basic_schema = file_get_contents(__DIR__ . '/database/inventory_schema.sql');
    if ($basic_schema === false) {
        throw new Exception("Could not read basic inventory schema file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $basic_schema)));
    
    $basic_success = 0;
    $basic_total = count($statements);
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $basic_success++;
            } catch (PDOException $e) {
                // Ignore table already exists errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-2'>
                            <i class='fas fa-exclamation-triangle mr-2'></i>Warning: " . htmlspecialchars($e->getMessage()) . "
                          </div>";
                } else {
                    $basic_success++;
                }
            }
        }
    }
    
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>
            <i class='fas fa-check-circle mr-2'></i>Basic inventory schema installed: {$basic_success}/{$basic_total} statements executed
          </div>";
    
    // Step 2: Install enhanced inventory schema
    echo "<h2 class='text-xl font-semibold text-gray-800 mb-4'>
            <i class='fas fa-cogs text-purple-600 mr-2'></i>Step 2: Enhanced Inventory Schema
          </h2>";
    
    $enhanced_schema = file_get_contents(__DIR__ . '/database/enhanced_inventory_schema.sql');
    if ($enhanced_schema === false) {
        throw new Exception("Could not read enhanced inventory schema file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $enhanced_schema)));
    
    $enhanced_success = 0;
    $enhanced_total = count($statements);
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $enhanced_success++;
            } catch (PDOException $e) {
                // Ignore table already exists errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-2'>
                            <i class='fas fa-exclamation-triangle mr-2'></i>Warning: " . htmlspecialchars($e->getMessage()) . "
                          </div>";
                } else {
                    $enhanced_success++;
                }
            }
        }
    }
    
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>
            <i class='fas fa-check-circle mr-2'></i>Enhanced inventory schema installed: {$enhanced_success}/{$enhanced_total} statements executed
          </div>";
    
    // Step 3: Verify installation
    echo "<h2 class='text-xl font-semibold text-gray-800 mb-4'>
            <i class='fas fa-search text-indigo-600 mr-2'></i>Step 3: Verification
          </h2>";
    
    $tables_to_check = [
        'inventory_categories',
        'inventory_items', 
        'inventory_transactions',
        'hotel_floors',
        'hotel_rooms',
        'room_inventory_items',
        'reorder_rules',
        'purchase_orders',
        'barcode_tracking',
        'accounting_transactions'
    ];
    
    $verified_tables = [];
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $verified_tables[] = $table;
            }
        } catch (PDOException $e) {
            // Table doesn't exist
        }
    }
    
    echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4'>
            <i class='fas fa-info-circle mr-2'></i>Verified tables: " . count($verified_tables) . "/" . count($tables_to_check) . "
          </div>";
    
    // Step 4: Check sample data
    echo "<h2 class='text-xl font-semibold text-gray-800 mb-4'>
            <i class='fas fa-chart-bar text-orange-600 mr-2'></i>Step 4: Sample Data Check
          </h2>";
    
    $data_checks = [
        'inventory_categories' => 'SELECT COUNT(*) as count FROM inventory_categories',
        'inventory_items' => 'SELECT COUNT(*) as count FROM inventory_items',
        'hotel_floors' => 'SELECT COUNT(*) as count FROM hotel_floors',
        'hotel_rooms' => 'SELECT COUNT(*) as count FROM hotel_rooms',
        'housekeeping_carts' => 'SELECT COUNT(*) as count FROM housekeeping_carts'
    ];
    
    foreach ($data_checks as $table => $query) {
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            echo "<div class='bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded mb-2'>
                    <i class='fas fa-table mr-2'></i>{$table}: {$count} records
                  </div>";
        } catch (PDOException $e) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-2'>
                    <i class='fas fa-exclamation-circle mr-2'></i>{$table}: Error checking data
                  </div>";
        }
    }
    
    // Success message
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6'>
            <i class='fas fa-check-circle mr-2'></i>Complete Inventory System installation completed successfully!
          </div>";
    
    // Quick access links
    echo "<h2 class='text-xl font-semibold text-gray-800 mb-4'>
            <i class='fas fa-link text-teal-600 mr-2'></i>Quick Access Links
          </h2>
          <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>
            <a href='items.php' class='bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-boxes mr-2'></i>Inventory Items
            </a>
            <a href='room-inventory.php' class='bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-bed mr-2'></i>Room Inventory
            </a>
            <a href='mobile.php' class='bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-mobile-alt mr-2'></i>Mobile Interface
            </a>
            <a href='enhanced-reports.php' class='bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-chart-line mr-2'></i>Enhanced Reports
            </a>
            <a href='automated-reordering.php' class='bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-shopping-cart mr-2'></i>Automated Reordering
            </a>
            <a href='barcode-scanner.php' class='bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-barcode mr-2'></i>Barcode Scanner
            </a>
            <a href='accounting-integration.php' class='bg-teal-500 hover:bg-teal-600 text-white px-4 py-3 rounded-lg text-center transition-colors'>
                <i class='fas fa-calculator mr-2'></i>Accounting Integration
            </a>
          </div>";
    
    // Installation summary
    echo "<div class='mt-8 bg-gray-50 border border-gray-200 rounded-lg p-6'>
            <h3 class='text-lg font-semibold text-gray-800 mb-4'>
                <i class='fas fa-clipboard-list mr-2'></i>Installation Summary
            </h3>
            <div class='grid grid-cols-1 md:grid-cols-2 gap-4 text-sm'>
                <div>
                    <h4 class='font-semibold text-gray-700 mb-2'>Basic Inventory System:</h4>
                    <ul class='list-disc list-inside text-gray-600 space-y-1'>
                        <li>Inventory categories and items</li>
                        <li>Transaction tracking</li>
                        <li>Supplier management</li>
                        <li>Training scenarios</li>
                    </ul>
                </div>
                <div>
                    <h4 class='font-semibold text-gray-700 mb-2'>Enhanced Features:</h4>
                    <ul class='list-disc list-inside text-gray-600 space-y-1'>
                        <li>Room inventory management</li>
                        <li>Mobile interface for housekeeping</li>
                        <li>Enhanced reporting and analytics</li>
                        <li>Automated reordering system</li>
                        <li>Barcode scanning support</li>
                        <li>Accounting integration</li>
                    </ul>
                </div>
            </div>
          </div>";

} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>
            <i class='fas fa-exclamation-circle mr-2'></i>Installation Error: " . htmlspecialchars($e->getMessage()) . "
          </div>";
    
    echo "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4'>
            <i class='fas fa-lightbulb mr-2'></i>Please check:
            <ul class='list-disc list-inside mt-2'>
                <li>Database connection settings</li>
                <li>Database user permissions</li>
                <li>Schema file accessibility</li>
                <li>MySQL server status</li>
            </ul>
          </div>";
}

echo "            </div>
        </div>
    </div>
</body>
</html>";

// Flush output
ob_end_flush();
?>
