<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
        if (!($apiKey && $apiKey === 'pms_users_api_2024')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
    }

    $raw = $_GET['reservation_id'] ?? $_GET['reservation_number'] ?? null;
    if (!$raw) { throw new Exception('Reservation ID is required'); }

    // Resolve to numeric id
    $reservation_id = is_numeric($raw) ? (int)$raw : 0;
    if ($reservation_id === 0) {
        $lookup = $pdo->prepare('SELECT id FROM reservations WHERE reservation_number = ? LIMIT 1');
        $lookup->execute([$raw]);
        $row = $lookup->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['id'])) { $reservation_id = (int)$row['id']; }
    }
    if ($reservation_id === 0) { throw new Exception('Reservation not found'); }

    $items = [];

    // Pull from service_charges if available
    try {
        // Try by reservation_id first
        $q = $pdo->prepare('SELECT COALESCE(s.name, sc.notes, "Additional Service") AS description, SUM(sc.total_price) AS total FROM service_charges sc LEFT JOIN additional_services s ON sc.service_id = s.id WHERE sc.reservation_id = ? GROUP BY COALESCE(s.name, sc.notes, "Additional Service") ORDER BY description');
        $q->execute([$reservation_id]);
        $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
        // If empty, try by reservation_number column
        if (empty($items)) {
            $q = $pdo->prepare('SELECT COALESCE(s.name, sc.notes, "Additional Service") AS description, SUM(sc.total_price) AS total FROM service_charges sc LEFT JOIN additional_services s ON sc.service_id = s.id WHERE sc.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1) GROUP BY COALESCE(s.name, sc.notes, "Additional Service") ORDER BY description');
            $q->execute([$reservation_id]);
            $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (PDOException $e) { /* ignore */ }

    // Fallback to reservation_services
    if (empty($items)) {
        try {
            $q = $pdo->prepare('SELECT rs.service_name AS description, SUM(rs.amount * COALESCE(rs.quantity,1)) AS total FROM reservation_services rs WHERE rs.reservation_id = ? GROUP BY rs.service_name ORDER BY rs.service_name');
            $q->execute([$reservation_id]);
            $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (empty($items)) {
                $q = $pdo->prepare('SELECT rs.service_name AS description, SUM(rs.amount * COALESCE(rs.quantity,1)) AS total FROM reservation_services rs WHERE rs.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1) GROUP BY rs.service_name ORDER BY rs.service_name');
                $q->execute([$reservation_id]);
                $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (PDOException $e) { /* ignore */ }
    }

    echo json_encode(['success' => true, 'items' => $items]);
} catch (Exception $e) {
    error_log('get-additional-services error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'items' => []]);
}
?>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
require_once '../includes/session-config.php';
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $refererPath = $referer ? parse_url($referer, PHP_URL_PATH) : '';
    if (!$refererPath || strpos($refererPath, '/booking/') === false) {
        error_log("Additional Services API - Unauthorized access attempt from: " . $referer);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    // Allow access if coming from same module path (temporary fix for session issues)
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $category_filter = $_GET['category'] ?? '';
    
    $services = getAdditionalServices($category_filter);
    
    echo json_encode([
        'success' => true,
        'services' => $services
    ]);
    
} catch (Exception $e) {
    error_log("Error getting additional services: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
