<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Search Guests API
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
    $search = $_GET['search'] ?? '';
    
    if (empty($search)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Search term is required']);
        exit();
    }
    
    // Search guests
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
        WHERE (g.first_name LIKE ? OR g.last_name LIKE ? OR g.email LIKE ? OR g.phone LIKE ?)
        GROUP BY g.id
        ORDER BY g.last_name ASC
        LIMIT 50
    ";
    
    $searchTerm = "%{$search}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
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
            'created_at' => $guest['created_at'],
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
    error_log("Error searching guests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
