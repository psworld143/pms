<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../config/database.php';

// Auth: allow any logged-in user or API key
if (!isset($_SESSION['user_id'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    if (!($apiKey && $apiKey === 'pms_users_api_2024')) {
        http_response_code(401);
        echo 'Unauthorized';
        exit();
    }
}

try {
    $method = $_GET['method'] ?? '';
    $date = $_GET['date'] ?? '';

    $where = ["1=1"]; $params = [];
    if ($method !== '') { $where[] = "p.payment_method = ?"; $params[] = $method; }
    if ($date !== '') { $where[] = "DATE(p.payment_date) = ?"; $params[] = $date; }
    $whereClause = implode(' AND ', $where);

    $sql = "
        SELECT p.payment_number, p.amount, p.payment_method, p.payment_date,
               COALESCE(CONCAT(g.first_name,' ',g.last_name),'Unknown Guest') AS guest_name,
               COALESCE(r.room_number,'N/A') AS room_number,
               b.bill_number
        FROM payments p
        LEFT JOIN bills b ON b.id = p.bill_id OR (p.bill_id IS NULL AND b.reservation_id = p.reservation_id)
        LEFT JOIN reservations res ON res.id = COALESCE(b.reservation_id, p.reservation_id)
        LEFT JOIN guests g ON res.guest_id = g.id
        LEFT JOIN rooms r ON res.room_id = r.id
        WHERE {$whereClause}
        ORDER BY p.payment_date DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payments_'.date('Ymd_His').'.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Payment #','Guest','Room','Invoice #','Amount','Method','Payment Date']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['payment_number'] ?? '',
            $r['guest_name'] ?? '',
            $r['room_number'] ?? '',
            $r['bill_number'] ?? '',
            number_format((float)($r['amount'] ?? 0), 2, '.', ''),
            $r['payment_method'] ?? '',
            $r['payment_date'] ?? ''
        ]);
    }
    fclose($out);
    exit();
} catch (Throwable $e) {
    error_log('Export payments error: '.$e->getMessage());
    http_response_code(500);
    echo 'Error exporting payments';
}
?>



