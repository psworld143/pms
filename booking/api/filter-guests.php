<?php
/**
 * Filter Guests API
 * Hotel PMS - Guest Management Module
 */

require_once dirname(__DIR__, 2) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get filter parameters
    $vip = $_GET['vip'] ?? '';
    $status = $_GET['status'] ?? '';
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
    
    // VIP filter
    if ($vip !== '') {
        $whereConditions[] = "g.is_vip = ?";
        $params[] = $vip;
    }
    
    // Status filter
    if ($status === 'active') {
        $whereConditions[] = "r.status = 'checked_in'";
    } elseif ($status === 'recent') {
        $whereConditions[] = "r.check_out_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    } elseif ($status === 'frequent') {
        $whereConditions[] = "COUNT(r.id) >= 3";
    }
    
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
    $sql .= " GROUP BY g.id ORDER BY g.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
        'guests' => $formattedGuests
    ]);
    
} catch (PDOException $e) {
    error_log("Error filtering guests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
