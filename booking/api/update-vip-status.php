<?php
/**
 * Update VIP Status API
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit(); }
try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $guestId = $input['guest_id'] ?? null;
    $isVip = $input['is_vip'] ?? false;
    $loyaltyTier = $input['loyalty_tier'] ?? null;

    if (!$guestId) {
        echo json_encode(['success' => false, 'message' => 'Guest ID is required.']);
        exit(); }
    $sql = "
        UPDATE guests SET
            is_vip = ?, loyalty_tier = ?, updated_at = NOW()
        WHERE id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$isVip ? 1 : 0, $loyaltyTier, $guestId]);

    echo json_encode(['success' => true, 'message' => 'VIP status updated successfully!']);

} catch (PDOException $e) {
    error_log("Error updating VIP status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error updating VIP status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error occurred: ' . $e->getMessage()]); }
?>





