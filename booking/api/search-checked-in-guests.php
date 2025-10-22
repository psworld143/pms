<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Search Checked-in Guests API
 * Handles searching and filtering checked-in guests
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit(); }
try {
    // Get search parameters
    $reservation_number = $_GET['reservation_number'] ?? '';
    $guest_name = $_GET['guest_name'] ?? '';
    $room_number = $_GET['room_number'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Build the query
    $where_conditions = ["r.status = 'checked_in'"];
    $params = [];
    
    if (!empty($reservation_number)) {
        $where_conditions[] = "r.reservation_number LIKE ?";
        $params[] = "%{$reservation_number}%"; }
    if (!empty($guest_name)) {
        $where_conditions[] = "(g.first_name LIKE ? OR g.last_name LIKE ? OR CONCAT(g.first_name, ' ', g.last_name) LIKE ?)";
        $params[] = "%{$guest_name}%";
        $params[] = "%{$guest_name}%";
        $params[] = "%{$guest_name}%"; }
    if (!empty($room_number)) {
        $where_conditions[] = "rm.room_number LIKE ?";
        $params[] = "%{$room_number}%"; }
    // Add status filter
    switch ($status) {
        case 'due_today':
            $where_conditions[] = "DATE(r.check_out_date) = CURDATE()";
            break;
        case 'overdue':
            $where_conditions[] = "DATE(r.check_out_date) < CURDATE()";
            break;
        case 'vip':
            $where_conditions[] = "g.is_vip = 1";
            break; }
    $where_clause = implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT 
            r.*,
            CONCAT(g.first_name, ' ', g.last_name) as guest_name,
            g.email,
            g.phone,
            g.is_vip,
            rm.room_number,
            rm.room_type,
            rm.rate,
            DATEDIFF(r.check_out_date, CURDATE()) as days_remaining,
            CASE 
                WHEN DATEDIFF(r.check_out_date, CURDATE()) < 0 THEN 'overdue'
                WHEN DATEDIFF(r.check_out_date, CURDATE()) = 0 THEN 'due_today'
                WHEN g.is_vip = 1 THEN 'vip'
                ELSE 'normal'
            END as checkout_status
        FROM reservations r
        LEFT JOIN guests g ON r.guest_id = g.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE {$where_clause}
        ORDER BY r.check_out_date ASC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'guests' => $guests
    ]);
    
} catch (Exception $e) {
    error_log("Error searching checked-in guests: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error searching checked-in guests'
    ]); }
?>






