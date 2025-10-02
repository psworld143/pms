<?php
/**
 * Enhanced Inventory System Installation Script
 * Hotel PMS Training System - Inventory Module
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Enhanced Inventory System Installation</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
</head>
<body class='bg-gray-100 min-h-screen'>
    <div class='container mx-auto px-4 py-8'>
        <div class='max-w-4xl mx-auto'>
            <div class='bg-white rounded-lg shadow-lg p-8'>
                <div class='text-center mb-8'>
                    <i class='fas fa-boxes text-6xl text-blue-600 mb-4'></i>
                    <h1 class='text-3xl font-bold text-gray-800'>Enhanced Inventory System Installation</h1>
                    <p class='text-gray-600 mt-2'>Installing advanced inventory management features for Hotel PMS Training System</p>
                </div>";

try {
    $inventory_db = new InventoryDatabase();
    $pdo = $inventory_db->getConnection();
    
    echo "<div class='space-y-6'>";
    
    // Step 1: Create enhanced database tables
    echo "<div class='border border-gray-200 rounded-lg p-6'>
            <h2 class='text-xl font-semibold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-database text-blue-600 mr-3'></i>
                Step 1: Creating Enhanced Database Tables
            </h2>";
    
    $schema_file = __DIR__ . '/database/enhanced_inventory_schema.sql';
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $success_count = 0;
        $warning_count = 0;
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    $success_count++;
                    echo "<p class='text-green-600 text-sm mb-1'>✓ " . substr($statement, 0, 60) . "...</p>";
                } catch (PDOException $e) {
                    $warning_count++;
                    echo "<p class='text-yellow-600 text-sm mb-1'>⚠ " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "<div class='mt-4 p-4 bg-green-50 border border-green-200 rounded-lg'>
                <p class='text-green-800 font-medium'>Database tables created successfully!</p>
                <p class='text-green-700 text-sm'>$success_count statements executed, $warning_count warnings</p>
              </div>";
    } else {
        echo "<div class='mt-4 p-4 bg-red-50 border border-red-200 rounded-lg'>
                <p class='text-red-800 font-medium'>Schema file not found: $schema_file</p>
              </div>";
    }
    
    echo "</div>";
    
    // Step 2: Verify table creation
    echo "<div class='border border-gray-200 rounded-lg p-6'>
            <h2 class='text-xl font-semibold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-check-circle text-green-600 mr-3'></i>
                Step 2: Verifying Table Creation
            </h2>";
    
    $enhanced_tables = [
        'hotel_floors', 'hotel_rooms', 'room_inventory_items', 'room_inventory_transactions',
        'housekeeping_carts', 'cart_inventory_items', 'purchase_orders', 'purchase_order_items',
        'reorder_rules', 'barcode_tracking', 'accounting_transactions', 'cost_analysis_reports'
    ];
    
    $all_tables_exist = true;
    foreach ($enhanced_tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p class='text-green-600 text-sm mb-1'>✓ Table '$table' exists</p>";
            } else {
                echo "<p class='text-red-600 text-sm mb-1'>✗ Table '$table' does not exist</p>";
                $all_tables_exist = false;
            }
        } catch (PDOException $e) {
            echo "<p class='text-red-600 text-sm mb-1'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
            $all_tables_exist = false;
        }
    }
    
    echo "</div>";
    
    // Step 3: Test new features
    echo "<div class='border border-gray-200 rounded-lg p-6'>
            <h2 class='text-xl font-semibold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-cogs text-purple-600 mr-3'></i>
                Step 3: Testing New Features
            </h2>";
    
    // Test room inventory functionality
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM hotel_rooms");
        $room_count = $stmt->fetchColumn();
        echo "<p class='text-green-600 text-sm mb-1'>✓ Room inventory system: $room_count rooms available</p>";
    } catch (PDOException $e) {
        echo "<p class='text-red-600 text-sm mb-1'>✗ Room inventory system: " . $e->getMessage() . "</p>";
    }
    
    // Test reorder rules
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM reorder_rules");
        $rule_count = $stmt->fetchColumn();
        echo "<p class='text-green-600 text-sm mb-1'>✓ Automated reordering: $rule_count rules configured</p>";
    } catch (PDOException $e) {
        echo "<p class='text-red-600 text-sm mb-1'>✗ Automated reordering: " . $e->getMessage() . "</p>";
    }
    
    // Test barcode tracking
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM barcode_tracking");
        $barcode_count = $stmt->fetchColumn();
        echo "<p class='text-green-600 text-sm mb-1'>✓ Barcode tracking: $barcode_count barcodes in system</p>";
    } catch (PDOException $e) {
        echo "<p class='text-red-600 text-sm mb-1'>✗ Barcode tracking: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    // Step 4: Installation summary
    echo "<div class='border border-gray-200 rounded-lg p-6'>
            <h2 class='text-xl font-semibold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-clipboard-check text-orange-600 mr-3'></i>
                Step 4: Installation Summary
            </h2>";
    
    if ($all_tables_exist) {
        echo "<div class='bg-green-50 border border-green-200 rounded-lg p-6 mb-6'>
                <div class='flex items-center mb-4'>
                    <i class='fas fa-check-circle text-green-600 text-2xl mr-3'></i>
                    <h3 class='text-lg font-semibold text-green-800'>✅ Installation Successful!</h3>
                </div>
                <p class='text-green-700 mb-4'>The enhanced inventory management system has been successfully installed and is ready for use.</p>
                
                <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
                    <div class='bg-white p-4 rounded-lg border border-green-200'>
                        <h4 class='font-semibold text-green-800 mb-2'>New Features Available:</h4>
                        <ul class='text-sm text-green-700 space-y-1'>
                            <li>• Room Inventory Management</li>
                            <li>• Mobile Interface for Housekeeping</li>
                            <li>• Enhanced Reporting & Analytics</li>
                            <li>• Automated Reordering System</li>
                            <li>• Barcode Scanner Integration</li>
                            <li>• Accounting System Integration</li>
                        </ul>
                    </div>
                    
                    <div class='bg-white p-4 rounded-lg border border-green-200'>
                        <h4 class='font-semibold text-green-800 mb-2'>Quick Access Links:</h4>
                        <ul class='text-sm text-green-700 space-y-1'>
                            <li>• <a href='index.php' class='underline hover:no-underline'>Inventory Dashboard</a></li>
                            <li>• <a href='room-inventory.php' class='underline hover:no-underline'>Room Inventory</a></li>
                            <li>• <a href='mobile.php' class='underline hover:no-underline'>Mobile Interface</a></li>
                            <li>• <a href='enhanced-reports.php' class='underline hover:no-underline'>Enhanced Reports</a></li>
                            <li>• <a href='automated-reordering.php' class='underline hover:no-underline'>Auto Reordering</a></li>
                            <li>• <a href='barcode-scanner.php' class='underline hover:no-underline'>Barcode Scanner</a></li>
                        </ul>
                    </div>
                </div>
              </div>";
    } else {
        echo "<div class='bg-red-50 border border-red-200 rounded-lg p-6'>
                <div class='flex items-center mb-4'>
                    <i class='fas fa-exclamation-triangle text-red-600 text-2xl mr-3'></i>
                    <h3 class='text-lg font-semibold text-red-800'>❌ Installation Failed!</h3>
                </div>
                <p class='text-red-700'>Some tables were not created successfully. Please check the error messages above and try again.</p>
              </div>";
    }
    
    echo "</div>";
    
    // Step 5: System requirements and recommendations
    echo "<div class='border border-gray-200 rounded-lg p-6'>
            <h2 class='text-xl font-semibold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-info-circle text-blue-600 mr-3'></i>
                Step 5: System Requirements & Recommendations
            </h2>
            
            <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
                <div class='bg-blue-50 p-4 rounded-lg border border-blue-200'>
                    <h4 class='font-semibold text-blue-800 mb-2'>For Barcode Scanner:</h4>
                    <ul class='text-sm text-blue-700 space-y-1'>
                        <li>• HTTPS connection required for camera access</li>
                        <li>• Modern browser with camera permissions</li>
                        <li>• QuaggaJS library for barcode detection</li>
                    </ul>
                </div>
                
                <div class='bg-purple-50 p-4 rounded-lg border border-purple-200'>
                    <h4 class='font-semibold text-purple-800 mb-2'>For Mobile Interface:</h4>
                    <ul class='text-sm text-purple-700 space-y-1'>
                        <li>• Responsive design for mobile devices</li>
                        <li>• Touch-friendly interface</li>
                        <li>• Offline capability (future enhancement)</li>
                    </ul>
                </div>
                
                <div class='bg-green-50 p-4 rounded-lg border border-green-200'>
                    <h4 class='font-semibold text-green-800 mb-2'>For Accounting Integration:</h4>
                    <ul class='text-sm text-green-700 space-y-1'>
                        <li>• Chart.js for financial charts</li>
                        <li>• CSV export functionality</li>
                        <li>• Journal entry management</li>
                    </ul>
                </div>
                
                <div class='bg-yellow-50 p-4 rounded-lg border border-yellow-200'>
                    <h4 class='font-semibold text-yellow-800 mb-2'>For Enhanced Reports:</h4>
                    <ul class='text-sm text-yellow-700 space-y-1'>
                        <li>• Chart.js for data visualization</li>
                        <li>• ABC analysis calculations</li>
                        <li>• Turnover rate analysis</li>
                    </ul>
                </div>
            </div>
          </div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-6'>
            <div class='flex items-center mb-4'>
                <i class='fas fa-exclamation-triangle text-red-600 text-2xl mr-3'></i>
                <h3 class='text-lg font-semibold text-red-800'>❌ Installation Error!</h3>
            </div>
            <p class='text-red-700'>Error: " . $e->getMessage() . "</p>
          </div>";
}

echo "<div class='mt-8 text-center text-gray-500 text-sm'>
        <p>Installation completed at: " . date('Y-m-d H:i:s') . "</p>
        <p class='mt-2'>Enhanced Inventory Management System v2.0 - Hotel PMS Training System</p>
      </div>
    </div>
  </div>
</div>
</body>
</html>";
?>
