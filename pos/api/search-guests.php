<?php
/**
 * POS Guest Search API
 * Searches for hotel guests by name or room number
 * Used for linking POS transactions to guest accounts
 */

header('Content-Type: application/json');

// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

require_once '../../includes/database.php';

try {
    // Get search term from query parameter
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Validate search term
    if (empty($search_term)) {
        echo json_encode([
            'success' => false,
            'message' => 'Search term is required',
            'guests' => []
        ]);
        exit();
    }
    
    // Minimum search length
    if (strlen($search_term) < 2) {
        echo json_encode([
            'success' => false,
            'message' => 'Search term must be at least 2 characters',
            'guests' => []
        ]);
        exit();
    }
    
    // Search for guests in the database
    // Look for matches in first name, last name, email, phone, or room number
    // Only return guests who are currently checked in or have upcoming reservations
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            g.id,
            g.first_name,
            g.last_name,
            g.email,
            g.phone,
            r.room_number,
            r.check_in_date,
            r.check_out_date,
            r.status as reservation_status,
            CONCAT(g.first_name, ' ', g.last_name) as full_name
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
        WHERE (
            g.first_name LIKE :search_start OR
            g.first_name LIKE :search_any OR
            g.last_name LIKE :search_start OR
            g.last_name LIKE :search_any OR
            CONCAT(g.first_name, ' ', g.last_name) LIKE :search_any OR
            g.email LIKE :search_any OR
            g.phone LIKE :search_any OR
            r.room_number LIKE :search_start OR
            r.room_number LIKE :search_any
        )
        AND (
            r.status IN ('confirmed', 'checked-in', 'checked_in') 
            OR (r.check_out_date >= CURDATE() AND r.check_in_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
        )
        ORDER BY 
            CASE 
                WHEN r.status = 'checked-in' OR r.status = 'checked_in' THEN 1
                WHEN r.status = 'confirmed' THEN 2
                ELSE 3
            END,
            r.check_in_date DESC,
            g.last_name ASC,
            g.first_name ASC
        LIMIT 20
    ");
    
    $search_start = $search_term . '%';
    $search_any = '%' . $search_term . '%';
    
    $stmt->execute([
        'search_start' => $search_start,
        'search_any' => $search_any
    ]);
    
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the results
    $formatted_guests = array_map(function($guest) {
        $status_label = '';
        if ($guest['reservation_status'] === 'checked-in' || $guest['reservation_status'] === 'checked_in') {
            $status_label = 'Checked In';
        } elseif ($guest['reservation_status'] === 'confirmed') {
            $status_label = 'Upcoming';
        }
        
        return [
            'id' => (int)$guest['id'],
            'name' => $guest['full_name'],
            'first_name' => $guest['first_name'],
            'last_name' => $guest['last_name'],
            'room' => $guest['room_number'] ?? 'N/A',
            'email' => $guest['email'],
            'phone' => $guest['phone'],
            'check_in_date' => $guest['check_in_date'],
            'check_out_date' => $guest['check_out_date'],
            'status' => $status_label,
            'is_checked_in' => ($guest['reservation_status'] === 'checked-in' || $guest['reservation_status'] === 'checked_in')
        ];
    }, $guests);
    
    // Log the search activity
    try {
        $log_stmt = $pdo->prepare("
            INSERT INTO pos_activity_log (user_id, action, description, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $log_stmt->execute([
            $_SESSION['pos_user_id'],
            'guest_search',
            'Searched for guest: ' . $search_term,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        // Silently fail logging - don't affect the main response
        error_log("Error logging guest search: " . $e->getMessage());
    }
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'message' => count($formatted_guests) . ' guest(s) found',
        'guests' => $formatted_guests,
        'search_term' => $search_term,
        'count' => count($formatted_guests)
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in search-guests.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while searching for guests',
        'error' => $e->getMessage(),
        'guests' => []
    ]);
} catch (Exception $e) {
    error_log("Error in search-guests.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching for guests',
        'error' => $e->getMessage(),
        'guests' => []
    ]);
}
?>

