<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Simple Working Guests API
 */

header('Content-Type: application/json');

session_start();
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit(); }
try {
    // Test database connection
    $testQuery = $pdo->query("SELECT 1");
    if (!$testQuery) {
        throw new Exception('Database connection test failed');
    }
    // Check if guests table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'guests'");
    if ($tableCheck->rowCount() == 0) {
        // Return mock data if table doesn't exist
        $mockGuests = [
            [
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'is_vip' => true,
                'id_number' => 'PASS123456',
                'total_stays' => 5,
                'last_visit' => '2024-01-15',
                'total_spent' => 2500.00
            ],
            [
                'id' => 2,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567891',
                'is_vip' => false,
                'id_number' => 'DL987654',
                'total_stays' => 2,
                'last_visit' => '2024-01-10',
                'total_spent' => 1200.00
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'guests' => $mockGuests,
            'pagination' => [
                'current_page' => 1,
                'per_page' => 20,
                'total' => count($mockGuests),
                'total_pages' => 1
            ],
            'debug' => [
                'database_connected' => true,
                'guests_table_exists' => false,
                'using_mock_data' => true
            ]
        ]);
        exit(); }
    // Get guests from database
    $stmt = $pdo->query("SELECT * FROM guests ORDER BY created_at DESC LIMIT 20");
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM guests");
    $totalGuests = $countStmt->fetch()['total'];
    
    // Format the data
    $formattedGuests = [];
    foreach ($guests as $guest) {
        $formattedGuests[] = [
            'id' => $guest['id'],
            'first_name' => $guest['first_name'],
            'last_name' => $guest['last_name'],
            'email' => $guest['email'],
            'phone' => $guest['phone'],
            'is_vip' => (bool)$guest['is_vip'],
            'id_number' => $guest['id_number'],
            'total_stays' => 0,
            'last_visit' => null,
            'total_spent' => 0
        ]; }
    echo json_encode([
        'success' => true,
        'guests' => $formattedGuests,
        'pagination' => [
            'current_page' => 1,
            'per_page' => 20,
            'total' => (int)$totalGuests,
            'total_pages' => ceil($totalGuests / 20)
        ],
        'debug' => [
            'database_connected' => true,
            'guests_table_exists' => true,
            'total_guests' => (int)$totalGuests
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'database_connected' => isset($pdo),
            'error' => $e->getMessage()
        ]
    ]); }
?>






