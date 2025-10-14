<?php
/**
 * Export Enhanced Report
 * Hotel PMS Training System - Inventory Module
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

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
    $report_type = $_GET['report_type'] ?? 'usage_trends';
    $category = $_GET['category'] ?? '';
    $date_range = $_GET['date_range'] ?? 'last_30_days';
    
    $result = exportEnhancedReport($report_type, $category, $date_range);
    
    echo json_encode([
        'success' => true,
        'message' => 'Enhanced report exported successfully',
        'download_url' => $result['download_url']
    ]);
    
} catch (Exception $e) {
    error_log("Error exporting enhanced report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Export enhanced report
 */
function exportEnhancedReport($report_type, $category, $date_range) {
    global $pdo;
    
    try {
        // Calculate date range
        $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        if ($date_range === 'last_90_days') {
            $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
        } elseif ($date_range === 'this_year') {
            $start_date = date('Y-m-d H:i:s', strtotime('first day of January this year'));
        }
        
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
        
        $detailed_items = [];
        foreach ($transactions as $transaction) {
            $item_id = $transaction['item_id'];
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
        
        // Process detailed report
        foreach ($detailed_items as &$item) {
            $days_active = (strtotime(date('Y-m-d')) - $item['first_used_date']) / (60 * 60 * 24);
            $item['avg_daily_usage'] = $days_active > 0 ? $item['total_used'] / $days_active : $item['total_used'];
            $item['last_used_date'] = date('M j, Y', $item['last_used_date']);
            unset($item['first_used_date']);
        }
        $report_data = array_values($detailed_items);
        
        // Create CSV content
        $csv_content = "Item Name,Category,Unit,Total Used,Total Cost,Avg Daily Usage,Last Used Date\n";
        
        foreach ($report_data as $row) {
            $csv_content .= '"' . str_replace('"', '""', $row['item_name']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $row['category_name']) . '",';
            $csv_content .= '"' . str_replace('"', '""', $row['unit']) . '",';
            $csv_content .= $row['total_used'] . ',';
            $csv_content .= $row['total_cost'] . ',';
            $csv_content .= number_format($row['avg_daily_usage'], 2) . ',';
            $csv_content .= '"' . $row['last_used_date'] . '"';
            $csv_content .= "\n";
        }
        
        // Generate filename
        $filename = 'enhanced_report_' . $report_type . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Save to temp directory
        $temp_dir = __DIR__ . '/../../temp/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        $filepath = $temp_dir . $filename;
        file_put_contents($filepath, $csv_content);
        
        return [
            'download_url' => '../../temp/' . $filename
        ];
        
    } catch (PDOException $e) {
        error_log("Error exporting enhanced report: " . $e->getMessage());
        throw $e;
    }
}
?>