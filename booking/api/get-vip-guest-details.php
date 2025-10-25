<?php
/**
 * Get VIP Guest Details API
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
$guestId = $_GET['id'] ?? null;

if (!$guestId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
    exit(); }
try {
    $sql = "
        SELECT 
            g.*,
            COALESCE(r.status, 'not_staying') AS stay_status,
            rm.room_number,
            rm.room_type
        FROM guests g
        LEFT JOIN reservations r ON r.guest_id = g.id AND r.status IN ('checked_in')
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE g.id = ? AND g.is_vip = 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($guest) {
        // Format data
        $guest['is_vip'] = (bool)$guest['is_vip'];
        
        echo json_encode(['success' => true, 'guest' => $guest]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'VIP guest not found']); }
} catch (PDOException $e) {
    error_log("Error getting VIP guest details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error getting VIP guest details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error occurred: ' . $e->getMessage()]); }
?>






