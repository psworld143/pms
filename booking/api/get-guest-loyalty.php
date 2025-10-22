<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Loyalty Data API
 * Hotel PMS - Guest Management Module
 */

session_start();
require_once dirname(__DIR__, 2) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $guestId = $_GET['guest_id'] ?? null;
    
    if (!$guestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Guest ID is required']);
        exit();
    }
    
    // Get guest loyalty data
    $sql = "
        SELECT 
            g.id,
            g.first_name,
            g.last_name,
            g.is_vip,
            g.loyalty_points,
            COUNT(r.id) as total_stays,
            COALESCE(SUM(r.total_amount), 0) as total_spent,
            MAX(r.check_out_date) as last_visit,
            MIN(r.check_in_date) as first_visit
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
        WHERE g.id = ?
        GROUP BY g.id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$guest) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guest not found']);
        exit();
    }
    
    // Calculate loyalty tier
    $totalSpent = (float)$guest['total_spent'];
    $tier = 'Bronze';
    if ($totalSpent >= 10000) {
        $tier = 'Platinum';
    } elseif ($totalSpent >= 5000) {
        $tier = 'Gold';
    } elseif ($totalSpent >= 2000) {
        $tier = 'Silver';
    }
    
    // Calculate points needed for next tier
    $nextTierPoints = 0;
    if ($tier === 'Bronze') {
        $nextTierPoints = 2000 - $totalSpent;
    } elseif ($tier === 'Silver') {
        $nextTierPoints = 5000 - $totalSpent;
    } elseif ($tier === 'Gold') {
        $nextTierPoints = 10000 - $totalSpent;
    }
    
    echo json_encode([
        'success' => true,
        'loyalty' => [
            'points' => (int)$guest['loyalty_points'],
            'tier' => $tier,
            'total_stays' => (int)$guest['total_stays'],
            'total_spent' => $totalSpent,
            'last_visit' => $guest['last_visit'],
            'first_visit' => $guest['first_visit'],
            'next_tier_points' => max(0, $nextTierPoints),
            'is_vip' => (bool)$guest['is_vip']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest loyalty data: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
