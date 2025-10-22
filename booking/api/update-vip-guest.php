<?php
/**
 * Update VIP Guest API
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
    $guestId = $_POST['guest_id'] ?? null;
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $idType = $_POST['id_type'] ?? '';
    $idNumber = $_POST['id_number'] ?? '';
    $loyaltyTier = $_POST['loyalty_tier'] ?? null;
    $isVip = isset($_POST['is_vip']) ? 1 : 0;
    $preferences = $_POST['preferences'] ?? null;
    $serviceNotes = $_POST['service_notes'] ?? null;

    if (!$guestId || empty($firstName) || empty($lastName) || empty($idType) || empty($idNumber)) {
        echo json_encode(['success' => false, 'message' => 'Required fields (Guest ID, First Name, Last Name, ID Type, ID Number) are missing.']);
        exit(); }
    $sql = "
        UPDATE guests SET
            first_name = ?, last_name = ?, email = ?, phone = ?, 
            id_type = ?, id_number = ?, loyalty_tier = ?, is_vip = ?, 
            preferences = ?, service_notes = ?, updated_at = NOW()
        WHERE id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $firstName, $lastName, $email, $phone, $idType, $idNumber, $loyaltyTier, 
        $isVip, $preferences, $serviceNotes, $guestId
    ]);

    echo json_encode(['success' => true, 'message' => 'VIP guest updated successfully!']);

} catch (PDOException $e) {
    error_log("Error updating VIP guest: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error updating VIP guest: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error occurred: ' . $e->getMessage()]); }
?>
