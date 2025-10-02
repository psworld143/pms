<?php
/**
 * Export Enhanced Report
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
    $report_type = $_POST['report_type'] ?? '';
    $date_range = $_POST['date_range'] ?? '';
    $category = $_POST['category'] ?? '';
    
    $data = getEnhancedReportData($report_type, $date_range, $category);
    $csvContent = generateEnhancedCSVContent($data, $report_type);
    $filename = 'enhanced_report_' . $report_type . '_' . date('Y-m-d_H-i-s') . '.csv';
    
    // Save CSV file
    $filepath = '../exports/' . $filename;
    if (!file_exists('../exports/')) {
        mkdir('../exports/', 0755, true);
    }
    
    file_put_contents($filepath, $csvContent);
    
    echo json_encode([
        'success' => true,
        'download_url' => 'exports/' . $filename,
        'filename' => $filename
    ]);
    
} catch (Exception $e) {
    error_log("Error exporting enhanced report: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get enhanced report data based on type
 */
function getEnhancedReportData($report_type, $date_range, $category) {
    global $pdo;
    
    try {
        switch ($report_type) {
            case 'abc-analysis':
                return getABCAnalysisData();
            case 'cost-analysis':
                return getCostAnalysisData($category);
            case 'turnover-analysis':
                return getTurnoverAnalysisData($category);
            case 'supplier-performance':
                return getSupplierPerformanceData();
            case 'room-utilization':
                return getRoomUtilizationData();
            default:
                return [];
        }
    } catch (PDOException $e) {
        error_log("Error getting enhanced report data: " . $e->getMessage());
        return [];
    }
}

/**
 * Get ABC Analysis data
 */
function getABCAnalysisData() {
    global $pdo;
    
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
    
    return $stmt->fetchAll();
}

/**
 * Get Cost Analysis data
 */
function getCostAnalysisData($category) {
    global $pdo;
    
    $where_clause = "WHERE c.active = 1";
    $params = [];
    
    if (!empty($category)) {
        $where_clause .= " AND c.id = ?";
        $params[] = $category;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            c.name as category_name,
            COUNT(i.id) as item_count,
            SUM(i.quantity * i.cost_price) as total_cost,
            AVG(i.cost_price) as avg_cost
        FROM inventory_categories c
        LEFT JOIN inventory_items i ON c.id = i.category_id AND i.status = 'active'
        $where_clause
        GROUP BY c.id, c.name
        ORDER BY total_cost DESC
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get Turnover Analysis data
 */
function getTurnoverAnalysisData($category) {
    global $pdo;
    
    $where_clause = "WHERE i.status = 'active'";
    $params = [];
    
    if (!empty($category)) {
        $where_clause .= " AND i.category_id = ?";
        $params[] = $category;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            i.name,
            c.name as category_name,
            i.quantity,
            COUNT(t.id) as transaction_count,
            CASE 
                WHEN i.quantity > 0 THEN COUNT(t.id) / i.quantity * 12
                ELSE 0 
            END as turnover_rate
        FROM inventory_items i
        LEFT JOIN inventory_categories c ON i.category_id = c.id
        LEFT JOIN inventory_transactions t ON i.id = t.item_id 
            AND t.transaction_type = 'out' 
            AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        $where_clause
        GROUP BY i.id, i.name, c.name, i.quantity
        ORDER BY turnover_rate DESC
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get Supplier Performance data
 */
function getSupplierPerformanceData() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            s.name as supplier_name,
            s.contact_person,
            s.email,
            s.phone,
            s.rating,
            COUNT(DISTINCT i.id) as items_supplied,
            AVG(r.lead_time_days) as avg_lead_time
        FROM inventory_suppliers s
        LEFT JOIN inventory_items i ON s.id = i.supplier_id
        LEFT JOIN reorder_rules r ON i.id = r.item_id
        WHERE s.active = 1
        GROUP BY s.id, s.name, s.contact_person, s.email, s.phone, s.rating
        ORDER BY s.rating DESC
    ");
    
    return $stmt->fetchAll();
}

/**
 * Get Room Utilization data
 */
function getRoomUtilizationData() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            r.room_number,
            f.floor_name,
            COUNT(ri.id) as total_items,
            SUM(ri.quantity_current) as used_items,
            AVG(ri.quantity_current / ri.par_level * 100) as utilization_percentage,
            MAX(ri.last_restocked) as last_restocked
        FROM hotel_rooms r
        LEFT JOIN hotel_floors f ON r.floor_id = f.id
        LEFT JOIN room_inventory_items ri ON r.id = ri.room_id
        WHERE r.active = 1
        GROUP BY r.id, r.room_number, f.floor_name
        ORDER BY f.floor_number, r.room_number
    ");
    
    return $stmt->fetchAll();
}

/**
 * Generate CSV content based on report type
 */
function generateEnhancedCSVContent($data, $report_type) {
    $csv = "";
    
    switch ($report_type) {
        case 'abc-analysis':
            $csv = "Item Name,Category,Annual Usage Value,Percentage of Total,ABC Classification\n";
            foreach ($data as $item) {
                $classification = $item['percentage_of_total'] >= 80 ? 'A' : 
                                 ($item['percentage_of_total'] >= 15 ? 'B' : 'C');
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s\n",
                    '"' . str_replace('"', '""', $item['name']) . '"',
                    '"' . str_replace('"', '""', $item['category_name']) . '"',
                    $item['annual_usage_value'],
                    $item['percentage_of_total'],
                    $classification
                );
            }
            break;
            
        case 'cost-analysis':
            $csv = "Category,Item Count,Total Cost,Average Cost\n";
            foreach ($data as $item) {
                $csv .= sprintf(
                    "%s,%s,%s,%s\n",
                    '"' . str_replace('"', '""', $item['category_name']) . '"',
                    $item['item_count'],
                    $item['total_cost'],
                    $item['avg_cost']
                );
            }
            break;
            
        case 'turnover-analysis':
            $csv = "Item Name,Category,Quantity,Transaction Count,Turnover Rate\n";
            foreach ($data as $item) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s\n",
                    '"' . str_replace('"', '""', $item['name']) . '"',
                    '"' . str_replace('"', '""', $item['category_name']) . '"',
                    $item['quantity'],
                    $item['transaction_count'],
                    round($item['turnover_rate'], 2)
                );
            }
            break;
            
        case 'supplier-performance':
            $csv = "Supplier Name,Contact Person,Email,Phone,Rating,Items Supplied,Avg Lead Time\n";
            foreach ($data as $item) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s\n",
                    '"' . str_replace('"', '""', $item['supplier_name']) . '"',
                    '"' . str_replace('"', '""', $item['contact_person']) . '"',
                    $item['email'],
                    $item['phone'],
                    $item['rating'],
                    $item['items_supplied'],
                    $item['avg_lead_time']
                );
            }
            break;
            
        case 'room-utilization':
            $csv = "Room Number,Floor,Total Items,Used Items,Utilization %,Last Restocked\n";
            foreach ($data as $item) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s,%s\n",
                    $item['room_number'],
                    '"' . str_replace('"', '""', $item['floor_name']) . '"',
                    $item['total_items'],
                    $item['used_items'],
                    round($item['utilization_percentage'], 2),
                    $item['last_restocked']
                );
            }
            break;
    }
    
    return $csv;
}
?>
