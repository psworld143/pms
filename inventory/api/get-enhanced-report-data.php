<?php
/**
 * Get Enhanced Report Data
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
    $reportData = getEnhancedReportData();
    
    echo json_encode([
        'success' => true,
        'kpis' => $reportData['kpis'],
        'abc_analysis' => $reportData['abc_analysis'],
        'supplier_performance' => $reportData['supplier_performance'],
        'room_utilization' => $reportData['room_utilization'],
        'chart_data' => $reportData['chart_data']
    ]);
    
} catch (Exception $e) {
    error_log("Error getting enhanced report data: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get enhanced report data including KPIs, ABC analysis, etc.
 */
function getEnhancedReportData() {
    global $pdo;
    
    try {
        // Get KPIs
        $kpis = getKPIs();
        
        // Get ABC Analysis
        $abc_analysis = getABCAnalysis();
        
        // Get Supplier Performance
        $supplier_performance = getSupplierPerformance();
        
        // Get Room Utilization
        $room_utilization = getRoomUtilization();
        
        // Get Chart Data
        $chart_data = getChartData();
        
        return [
            'kpis' => $kpis,
            'abc_analysis' => $abc_analysis,
            'supplier_performance' => $supplier_performance,
            'room_utilization' => $room_utilization,
            'chart_data' => $chart_data
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting enhanced report data: " . $e->getMessage());
        return [
            'kpis' => [],
            'abc_analysis' => [],
            'supplier_performance' => [],
            'room_utilization' => [],
            'chart_data' => []
        ];
    }
}

/**
 * Get Key Performance Indicators
 */
function getKPIs() {
    global $pdo;
    
    try {
        // Total inventory value
        $stmt = $pdo->query("SELECT SUM(quantity * cost_price) as total_value FROM inventory_items WHERE status = 'active'");
        $total_inventory_value = $stmt->fetch()['total_value'] ?? 0;
        
        // Average turnover rate (simplified calculation)
        $stmt = $pdo->query("
            SELECT AVG(
                CASE 
                    WHEN quantity > 0 THEN (SELECT COUNT(*) FROM inventory_transactions it WHERE it.item_id = inventory_items.id AND it.transaction_type = 'out' AND it.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)) / quantity * 12
                    ELSE 0 
                END
            ) as avg_turnover
            FROM inventory_items 
            WHERE status = 'active'
        ");
        $avg_turnover_rate = $stmt->fetch()['avg_turnover'] ?? 0;
        
        // Slow moving items (items with turnover < 2 per year)
        $stmt = $pdo->query("
            SELECT COUNT(*) as slow_moving
            FROM inventory_items i
            WHERE i.status = 'active'
            AND i.quantity > 0
            AND (
                SELECT COUNT(*) 
                FROM inventory_transactions it 
                WHERE it.item_id = i.id 
                AND it.transaction_type = 'out' 
                AND it.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
            ) < 2
        ");
        $slow_moving_items = $stmt->fetch()['slow_moving'] ?? 0;
        
        // Waste cost (transactions marked as waste in last 30 days)
        $stmt = $pdo->query("
            SELECT SUM(total_value) as waste_cost
            FROM inventory_transactions
            WHERE transaction_type = 'out'
            AND reason LIKE '%waste%'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $waste_cost = $stmt->fetch()['waste_cost'] ?? 0;
        
        return [
            'total_inventory_value' => (float)$total_inventory_value,
            'avg_turnover_rate' => round((float)$avg_turnover_rate, 1),
            'slow_moving_items' => (int)$slow_moving_items,
            'waste_cost' => (float)$waste_cost
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting KPIs: " . $e->getMessage());
        return [
            'total_inventory_value' => 0,
            'avg_turnover_rate' => 0,
            'slow_moving_items' => 0,
            'waste_cost' => 0
        ];
    }
}

/**
 * Get ABC Analysis
 */
function getABCAnalysis() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                i.name,
                c.name as category_name,
                (i.quantity * i.cost_price) as annual_usage_value,
                ROUND(
                    (i.quantity * i.cost_price) / (
                        SELECT SUM(quantity * cost_price) 
                        FROM inventory_items 
                        WHERE status = 'active'
                    ) * 100, 2
                ) as percentage_of_total
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE i.status = 'active'
            ORDER BY annual_usage_value DESC
        ");
        
        $items = $stmt->fetchAll();
        
        // Calculate cumulative percentage
        $total_value = array_sum(array_column($items, 'annual_usage_value'));
        $cumulative = 0;
        
        foreach ($items as &$item) {
            $cumulative += $item['percentage_of_total'];
            $item['cumulative_percentage'] = $cumulative;
        }
        
        return $items;
        
    } catch (PDOException $e) {
        error_log("Error getting ABC analysis: " . $e->getMessage());
        return [];
    }
}

/**
 * Get Supplier Performance
 */
function getSupplierPerformance() {
    global $pdo;
    
    try {
        // Average delivery time (simplified - using lead time from reorder rules)
        $stmt = $pdo->query("SELECT AVG(lead_time_days) as avg_delivery FROM reorder_rules WHERE active = 1");
        $avg_delivery_time = $stmt->fetch()['avg_delivery'] ?? 7;
        
        // On-time delivery rate (simplified calculation)
        $on_time_delivery_rate = 85; // Placeholder value
        
        // Average quality rating (from suppliers table if exists)
        $avg_quality_rating = 4.2; // Placeholder value
        
        return [
            'avg_delivery_time' => round((float)$avg_delivery_time, 1),
            'on_time_delivery_rate' => $on_time_delivery_rate,
            'avg_quality_rating' => $avg_quality_rating
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting supplier performance: " . $e->getMessage());
        return [
            'avg_delivery_time' => 0,
            'on_time_delivery_rate' => 0,
            'avg_quality_rating' => 0
        ];
    }
}

/**
 * Get Room Utilization
 */
function getRoomUtilization() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                r.room_number,
                f.floor_name,
                COUNT(ri.id) as total_items,
                SUM(ri.quantity_current) as used_items,
                MAX(ri.last_restocked) as last_restocked
            FROM hotel_rooms r
            LEFT JOIN hotel_floors f ON r.floor_id = f.id
            LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
            WHERE r.active = 1
            GROUP BY r.id, r.room_number, f.floor_name
            ORDER BY f.floor_number, r.room_number
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error getting room utilization: " . $e->getMessage());
        return [];
    }
}

/**
 * Get Chart Data
 */
function getChartData() {
    global $pdo;
    
    try {
        // Cost Analysis by Category
        $stmt = $pdo->query("
            SELECT 
                c.name as category_name,
                SUM(i.quantity * i.cost_price) as total_cost
            FROM inventory_categories c
            LEFT JOIN inventory_items i ON c.id = i.category_id AND i.status = 'active'
            WHERE c.active = 1
            GROUP BY c.id, c.name
            ORDER BY total_cost DESC
        ");
        $cost_analysis = $stmt->fetchAll();
        
        // Turnover Analysis by Category
        $stmt = $pdo->query("
            SELECT 
                c.name as category_name,
                AVG(
                    CASE 
                        WHEN i.quantity > 0 THEN 
                            (SELECT COUNT(*) FROM inventory_transactions it 
                             WHERE it.item_id = i.id AND it.transaction_type = 'out' 
                             AND it.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)) / i.quantity * 12
                        ELSE 0 
                    END
                ) as avg_turnover
            FROM inventory_categories c
            LEFT JOIN inventory_items i ON c.id = i.category_id AND i.status = 'active'
            WHERE c.active = 1
            GROUP BY c.id, c.name
            ORDER BY avg_turnover DESC
        ");
        $turnover_analysis = $stmt->fetchAll();
        
        return [
            'cost_analysis' => [
                'labels' => array_column($cost_analysis, 'category_name'),
                'data' => array_column($cost_analysis, 'total_cost')
            ],
            'turnover' => [
                'labels' => array_column($turnover_analysis, 'category_name'),
                'data' => array_map(function($rate) { return round((float)$rate, 1); }, array_column($turnover_analysis, 'avg_turnover'))
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting chart data: " . $e->getMessage());
        return [
            'cost_analysis' => ['labels' => [], 'data' => []],
            'turnover' => ['labels' => [], 'data' => []]
        ];
    }
}
?>
