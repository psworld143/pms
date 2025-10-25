<?php
/**
 * Create VIP Service Request API
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
    $serviceType = $_POST['service_type'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $requestDetails = $_POST['request_details'] ?? '';
    $specialInstructions = $_POST['special_instructions'] ?? null;

    if (!$guestId || empty($serviceType) || empty($priority) || empty($requestDetails)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
        exit(); }
    // Create vip_services table if it doesn't exist
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'vip_services'");
    if ($tableCheck->rowCount() == 0) {
        $createTableSql = "
            CREATE TABLE vip_services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guest_id INT NOT NULL,
                service_type VARCHAR(50) NOT NULL,
                priority VARCHAR(20) NOT NULL,
                request_details TEXT NOT NULL,
                special_instructions TEXT NULL,
                status VARCHAR(20) DEFAULT 'pending',
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($createTableSql); }
    $sql = "
        INSERT INTO vip_services (
            guest_id, service_type, priority, request_details, special_instructions, 
            created_by, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $guestId, $serviceType, $priority, $requestDetails, $specialInstructions, 
        $_SESSION['user_id']
    ]);

    $serviceId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'VIP service request submitted successfully!', 'service_id' => $serviceId]);

} catch (PDOException $e) {
    error_log("Error creating VIP service: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error creating VIP service: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error occurred: ' . $e->getMessage()]); }
?>






