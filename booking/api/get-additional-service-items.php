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

    $reservation_id = is_numeric($raw) ? (int)$raw : 0;
    if ($reservation_id === 0) {
        $lookup = $pdo->prepare('SELECT id FROM reservations WHERE reservation_number = ? LIMIT 1');
        $lookup->execute([$raw]);
        $row = $lookup->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['id'])) { $reservation_id = (int)$row['id']; }
    }
    if ($reservation_id === 0) { throw new Exception('Reservation not found'); }

    $items = [];

    // Prefer normalized table
    try {
        $q = $pdo->prepare('SELECT sc.id, COALESCE(s.name, sc.notes, "Additional Service") AS description, sc.unit_price, COALESCE(sc.quantity,1) AS quantity, sc.total_price
                            FROM service_charges sc
                            LEFT JOIN additional_services s ON sc.service_id = s.id
                            WHERE sc.reservation_id = ?
                            ORDER BY sc.id DESC');
        $q->execute([$reservation_id]);
        $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (empty($items)) {
            $q = $pdo->prepare('SELECT sc.id, COALESCE(s.name, sc.notes, "Additional Service") AS description, sc.unit_price, COALESCE(sc.quantity,1) AS quantity, sc.total_price
                                FROM service_charges sc
                                LEFT JOIN additional_services s ON sc.service_id = s.id
                                WHERE sc.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1)
                                ORDER BY sc.id DESC');
            $q->execute([$reservation_id]);
            $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (PDOException $ignore) {}

    // Fallback legacy
    if (empty($items)) {
        try {
            $q = $pdo->prepare('SELECT rs.id, rs.service_name AS description, rs.amount AS unit_price, COALESCE(rs.quantity,1) as quantity, (COALESCE(rs.amount,0)*COALESCE(rs.quantity,1)) AS total_price
                                FROM reservation_services rs
                                WHERE rs.reservation_id = ?
                                ORDER BY rs.id DESC');
            $q->execute([$reservation_id]);
            $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (empty($items)) {
                $q = $pdo->prepare('SELECT rs.id, rs.service_name AS description, rs.amount AS unit_price, COALESCE(rs.quantity,1) as quantity, (COALESCE(rs.amount,0)*COALESCE(rs.quantity,1)) AS total_price
                                    FROM reservation_services rs
                                    WHERE rs.reservation_number = (SELECT reservation_number FROM reservations WHERE id = ? LIMIT 1)
                                    ORDER BY rs.id DESC');
                $q->execute([$reservation_id]);
                $items = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (PDOException $ignore) {}
    }

    $total = array_sum(array_map(function($r){ return (float)($r['total_price'] ?? 0); }, $items));

    echo json_encode(['success' => true, 'items' => $items, 'total' => $total]);
} catch (Exception $e) {
    error_log('get-additional-service-items error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>



