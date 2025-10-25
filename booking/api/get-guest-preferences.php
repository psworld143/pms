<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Preferences API
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
    
    // Get guest preferences
    $sql = "
        SELECT 
            preferences,
            notes
        FROM guests
        WHERE id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$guest) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Guest not found']);
        exit();
    }
    
    // Parse preferences JSON
    $preferences = [];
    if (!empty($guest['preferences'])) {
        $preferences = json_decode($guest['preferences'], true) ?: [];
    }
    
    echo json_encode([
        'success' => true,
        'preferences' => $preferences,
        'notes' => $guest['notes']
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest preferences: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>