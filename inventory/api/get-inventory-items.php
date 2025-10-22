<?php
/**
 * Get Inventory Items
 * Hotel PMS Training System - Inventory Module
 */

// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Direct database connection without debug output
try {
    // Use the same database configuration as the main system
    require_once __DIR__ . '/../../includes/database.php';
    
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection not established');
    }
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Clean any output before sending JSON
ob_clean();
header('Content-Type: application/json');

try {
    // Get filter parameters
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'active';
    
    $inventory_items = getInventoryItemsWithFilters($category, $search, $status);
    
    echo json_encode([
        'success' => true,
        'inventory_items' => $inventory_items
    ]);
    
} catch (Exception $e) {
    error_log("Error getting inventory items: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get inventory items with filters
 */
function getInventoryItemsWithFilters($category = '', $search = '', $status = 'active') {
    global $pdo;
    
    try {
        $where_conditions = ["1=1"];
        $params = [];
        
        // Category filter
        if (!empty($category)) {
            $where_conditions[] = "c.name = ?";
            $params[] = $category;
        }
        
        // Search filter
        if (!empty($search)) {
            $where_conditions[] = "(i.item_name LIKE ? OR i.description LIKE ?)";
            $search_term = "%$search%";
            $params = array_merge($params, [$search_term, $search_term]);
        }
        
        $where_clause = implode(" AND ", $where_conditions);

        // Check what columns exist in inventory_items table
        $columns = [];
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
            $column_data = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $columns = array_flip($column_data);
        } catch (Exception $e) {
            error_log("Error getting column information: " . $e->getMessage());
        }

        // Build dynamic SELECT based on available columns
        $select_fields = [
            'i.id',
            'i.item_name as name',
            'i.description',
            'i.current_stock as quantity',
            'i.minimum_stock',
            'i.unit_price',
            'i.created_at',
            'i.last_updated as updated_at',
            'c.name as category_name',
            "'#10B981' as category_color",
            "'fas fa-box' as category_icon"
        ];

        // Add optional fields if they exist
        if (isset($columns['sku'])) {
            $select_fields[] = 'i.sku';
        } else {
            $select_fields[] = "'' as sku";
        }

        if (isset($columns['unit'])) {
            $select_fields[] = 'i.unit';
        } else {
            $select_fields[] = "'pcs' as unit";
        }

        if (isset($columns['supplier'])) {
            $select_fields[] = 'i.supplier';
        } else {
            $select_fields[] = "'' as supplier";
        }

        if (isset($columns['location'])) {
            $select_fields[] = 'i.location';
        } else {
            $select_fields[] = "'Main Storage' as location";
        }

        if (isset($columns['barcode'])) {
            $select_fields[] = 'i.barcode';
        } else {
            $select_fields[] = "'' as barcode";
        }

        if (isset($columns['image'])) {
            $select_fields[] = 'i.image';
        } else {
            $select_fields[] = "'' as image";
        }

        if (isset($columns['status'])) {
            $select_fields[] = 'i.status';
        } else {
            $select_fields[] = "'active' as status";
        }

        // Always prioritize unit_price over cost_price for display
        if (isset($columns['unit_price'])) {
            $select_fields[] = 'i.unit_price as cost_price';
        } elseif (isset($columns['cost_price'])) {
            $select_fields[] = 'i.cost_price';
        } else {
            $select_fields[] = '0.00 as cost_price';
        }

        if (isset($columns['maximum_stock'])) {
            $select_fields[] = 'i.maximum_stock';
        } else {
            $select_fields[] = '(i.current_stock * 2) as maximum_stock';
        }

        // Add stock status calculation
        $select_fields[] = "CASE 
            WHEN i.current_stock = 0 THEN 'out_of_stock'
            WHEN i.current_stock <= i.minimum_stock THEN 'low_stock'
            ELSE 'in_stock'
        END as stock_status";

        $sql = "
            SELECT " . implode(', ', $select_fields) . "
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE {$where_clause}
            ORDER BY c.name, i.item_name ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If SKU column doesn't exist, try to extract SKU from description
        if (!isset($columns['sku']) && is_array($rows)) {
            foreach ($rows as &$row) {
                if (empty($row['sku']) && !empty($row['description'])) {
                    if (preg_match('/SKU\s*:\s*([^|\n]+)/i', $row['description'], $m)) {
                        $row['sku'] = trim($m[1]);
                    }
                }
            }
        }

        return $rows;
        
    } catch (PDOException $e) {
        error_log("Error getting inventory items with filters: " . $e->getMessage());
        return [];
    }
}
?>