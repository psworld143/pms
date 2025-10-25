<?php
/**
 * Get VIP Guests API
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();
require_once dirname(__DIR__, 2) . '/includes/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit(); }
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection not established']);
    exit(); }
try {
    $stmt = $pdo->query("SELECT 
            g.id,
            g.first_name,
            g.last_name,
            g.email,
            g.phone,
            g.loyalty_tier,
            g.preferences,
            g.service_notes,
            g.is_vip,
            COALESCE(r.status, 'not_staying') AS stay_status,
            rm.room_number,
            rm.room_type,
            g.created_at,
            g.updated_at
        FROM guests g
        LEFT JOIN reservations r ON r.guest_id = g.id AND r.status IN ('checked_in')
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE g.is_vip = 1
        ORDER BY g.first_name, g.last_name");
    
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    foreach ($guests as &$guest) {
        $guest['is_vip'] = (bool)$guest['is_vip'];
        $guest['special_requests'] = $guest['service_notes']; // Map service notes to special requests for display
    }
    
    echo json_encode([
        'success' => true,
        'guests' => $guests
    ]);

} catch (PDOException $e) {
    error_log("Error getting VIP guests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error getting VIP guests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error occurred: ' . $e->getMessage()]); }
?>






