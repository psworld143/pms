<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Get Guests API
 * Hotel PMS - Guest Management Module
 */

session_start();
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get pagination parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Get search parameter
    $search = $_GET['search'] ?? '';
    
    // Build the query
    $sql = "
        SELECT 
            g.id,
            g.first_name,
            g.last_name,
            g.email,
            g.phone,
            g.is_vip,
            g.id_number,
            g.created_at,
            COUNT(r.id) as total_stays,
            MAX(r.check_out_date) as last_visit,
            COALESCE(SUM(r.total_amount), 0) as total_spent
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
    ";
    
    $whereConditions = [];
    $params = [];
    
    // Search filter
    if (!empty($search)) {
        $whereConditions[] = "(g.first_name LIKE ? OR g.last_name LIKE ? OR g.email LIKE ? OR g.phone LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Add WHERE clause if conditions exist
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    // Add GROUP BY and ORDER BY
    $sql .= " GROUP BY g.id ORDER BY g.created_at DESC LIMIT $per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countSql = "
        SELECT COUNT(DISTINCT g.id) as total
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
    ";
    
    if (!empty($whereConditions)) {
        $countSql .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $countStmt = $pdo->prepare($countSql);
    $countParams = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
    $countStmt->execute($countParams);
    $totalGuests = $countStmt->fetch()['total'];
    
    // Format the data
    $formattedGuests = array_map(function($guest) {
        return [
            'id' => $guest['id'],
            'first_name' => $guest['first_name'],
            'last_name' => $guest['last_name'],
            'email' => $guest['email'],
            'phone' => $guest['phone'],
            'is_vip' => (bool)$guest['is_vip'],
            'id_number' => $guest['id_number'],
            'total_stays' => (int)$guest['total_stays'],
            'last_visit' => $guest['last_visit'],
            'total_spent' => (float)$guest['total_spent']
        ];
    }, $guests);
    
    echo json_encode([
        'success' => true,
        'guests' => $formattedGuests,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total' => (int)$totalGuests,
            'total_pages' => ceil($totalGuests / $per_page)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>