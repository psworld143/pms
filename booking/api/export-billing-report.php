<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Export Billing Report API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and has access (manager or front_desk); allow API key fallback
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        $_SESSION['user_id'] = 1073;
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get filter parameters
    $reportType = $_GET['report_type'] ?? 'revenue';
    $dateRange = (int)($_GET['date_range'] ?? 30);
    $paymentMethod = $_GET['payment_method'] ?? '';
    
    // Calculate date range
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime("-{$dateRange} days"));
    
    // Build where conditions
    $whereConditions = ["b.bill_date BETWEEN ? AND ?"];
    $params = [$startDate, $endDate];
    
    if (!empty($paymentMethod)) {
        $whereConditions[] = "p.payment_method = ?";
        $params[] = $paymentMethod;
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    // Get billing data
    $query = "
        SELECT b.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name,
               r.room_number,
               p.payment_method,
               p.payment_date,
               p.amount as payment_amount
        FROM bills b
        JOIN reservations res ON b.reservation_id = res.id
        JOIN guests g ON res.guest_id = g.id
        JOIN rooms r ON res.room_id = r.id
        LEFT JOIN payments p ON b.id = p.bill_id
        WHERE {$whereClause}
        ORDER BY b.bill_date DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    $filename = "billing_report_{$reportType}_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, [
        'Date',
        'Invoice #',
        'Guest Name',
        'Amount',
        'Payment Method',
        'Status',
        'Room Number'
    ]);
    
    // CSV data
    foreach ($bills as $bill) {
        $paymentMethod = $bill['payment_method'] ?? 'N/A';
        if ($paymentMethod !== 'N/A') {
            $methodLabels = [
                'cash' => 'Cash',
                'credit_card' => 'Credit Card',
                'debit_card' => 'Debit Card',
                'bank_transfer' => 'Bank Transfer',
                'check' => 'Check',
                'digital_wallet' => 'Digital Wallet'
            ];
            $paymentMethod = $methodLabels[$paymentMethod] ?? ucwords(str_replace('_', ' ', $paymentMethod));
        }
        
        fputcsv($output, [
            $bill['bill_date'],
            $bill['bill_number'],
            $bill['guest_name'],
            $bill['total_amount'],
            $paymentMethod,
            ucfirst($bill['status']),
            $bill['room_number']
        ]);
    }
    
    fclose($output);
    exit();
    
} catch (PDOException $e) {
    error_log('Error exporting billing report: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error exporting billing report: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
