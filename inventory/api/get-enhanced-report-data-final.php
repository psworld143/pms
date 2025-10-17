<?php
/**
 * Get Enhanced Report Data (Final Clean Version)
 * Hotel PMS Training System - Inventory Module
 */

// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'pms_pms_hotel';
    $username = 'pms_pms_hotel';
    $password = '020894HotelPMS';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Clean any output before sending JSON
ob_clean();
header('Content-Type: application/json');

try {
    $report_type = $_GET['report_type'] ?? 'usage_trends';
    $category = $_GET['category'] ?? '';
    $date_range = $_GET['date_range'] ?? 'last_30_days';
    
    // Calculate date range
    $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
    if ($date_range === 'last_90_days') {
        $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
    } elseif ($date_range === 'this_year') {
        $start_date = date('Y-m-d H:i:s', strtotime('first day of January this year'));
    }
    
    $response_data = [
        'usage_trends' => ['labels' => [], 'values' => []],
        'cost_analysis' => ['labels' => [], 'values' => []],
        'detailed_report' => []
    ];
    
    // Get transaction data
    $base_sql = "
        SELECT 
            it.item_id,
            ii.item_name,
            ii.unit,
            ic.name as category_name,
            it.quantity,
            it.unit_price,
            it.created_at
        FROM inventory_transactions it
        JOIN inventory_items ii ON it.item_id = ii.id
        LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
        WHERE it.created_at >= ? AND it.transaction_type = 'out'
    ";
    $params = [$start_date];
    
    if (!empty($category)) {
        $base_sql .= " AND ic.name = ?";
        $params[] = $category;
    }
    
    $stmt = $pdo->prepare($base_sql . " ORDER BY it.created_at ASC");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $usage_data = [];
    $cost_data = [];
    $detailed_items = [];
    
    foreach ($transactions as $transaction) {
        $date = date('Y-m-d', strtotime($transaction['created_at']));
        $month = date('Y-m', strtotime($transaction['created_at']));
        $item_id = $transaction['item_id'];
        
        // Usage Trends (monthly)
        $usage_data[$month] = ($usage_data[$month] ?? 0) + $transaction['quantity'];
        
        // Cost Analysis (monthly)
        $cost_data[$month] = ($cost_data[$month] ?? 0) + ($transaction['quantity'] * $transaction['unit_price']);
        
        // Detailed Report
        if (!isset($detailed_items[$item_id])) {
            $detailed_items[$item_id] = [
                'item_name' => $transaction['item_name'],
                'category_name' => $transaction['category_name'],
                'unit' => $transaction['unit'],
                'total_used' => 0,
                'total_cost' => 0,
                'first_used_date' => strtotime($transaction['created_at']),
                'last_used_date' => strtotime($transaction['created_at'])
            ];
        }
        $detailed_items[$item_id]['total_used'] += $transaction['quantity'];
        $detailed_items[$item_id]['total_cost'] += ($transaction['quantity'] * $transaction['unit_price']);
        $detailed_items[$item_id]['last_used_date'] = max($detailed_items[$item_id]['last_used_date'], strtotime($transaction['created_at']));
        $detailed_items[$item_id]['first_used_date'] = min($detailed_items[$item_id]['first_used_date'], strtotime($transaction['created_at']));
    }
    
    // Process usage trends
    ksort($usage_data);
    $response_data['usage_trends']['labels'] = array_keys($usage_data);
    $response_data['usage_trends']['values'] = array_values($usage_data);
    
    // Process cost analysis
    ksort($cost_data);
    $response_data['cost_analysis']['labels'] = array_keys($cost_data);
    $response_data['cost_analysis']['values'] = array_values($cost_data);
    
    // Process detailed report
    foreach ($detailed_items as &$item) {
        $days_active = (strtotime(date('Y-m-d')) - $item['first_used_date']) / (60 * 60 * 24);
        $item['avg_daily_usage'] = $days_active > 0 ? $item['total_used'] / $days_active : $item['total_used'];
        $item['last_used_date'] = date('M j, Y', $item['last_used_date']);
        unset($item['first_used_date']);
    }
    $response_data['detailed_report'] = array_values($detailed_items);
    
    // Add demo data for missing fields
    $response_data['expensive_items'] = [
        ['name' => 'Premium Towels', 'cost' => 25.50, 'category' => 'Amenities'],
        ['name' => 'Organic Soap', 'cost' => 18.75, 'category' => 'Amenities'],
        ['name' => 'Luxury Bedding', 'cost' => 45.00, 'category' => 'Amenities'],
        ['name' => 'High-End Coffee', 'cost' => 32.25, 'category' => 'Food & Beverage'],
        ['name' => 'Professional Cleaning Kit', 'cost' => 28.90, 'category' => 'Cleaning Supplies']
    ];
    
    $response_data['supplier_performance'] = [
        ['name' => 'Luxury Supplies Co.', 'total_orders' => 45, 'on_time_delivery' => 95, 'quality_rating' => 4.8, 'total_value' => 12500, 'performance_score' => 92],
        ['name' => 'Hotel Essentials Ltd.', 'total_orders' => 32, 'on_time_delivery' => 88, 'quality_rating' => 4.2, 'total_value' => 8900, 'performance_score' => 85],
        ['name' => 'Premium Amenities Inc.', 'total_orders' => 28, 'on_time_delivery' => 92, 'quality_rating' => 4.6, 'total_value' => 11200, 'performance_score' => 89]
    ];
    
    $response_data['analytics'] = [
        ['name' => 'Bath Towels', 'usage_rate' => 2.5, 'cost_per_unit' => 8.50, 'monthly_usage' => 75, 'monthly_cost' => 637.50, 'trend' => 5.2],
        ['name' => 'Coffee Pods', 'usage_rate' => 1.8, 'cost_per_unit' => 0.75, 'monthly_usage' => 54, 'monthly_cost' => 40.50, 'trend' => -2.1],
        ['name' => 'Cleaning Spray', 'usage_rate' => 0.9, 'cost_per_unit' => 3.25, 'monthly_usage' => 27, 'monthly_cost' => 87.75, 'trend' => 1.5],
        ['name' => 'Toilet Paper', 'usage_rate' => 3.2, 'cost_per_unit' => 1.20, 'monthly_usage' => 96, 'monthly_cost' => 115.20, 'trend' => 0.8]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $response_data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}
?>
