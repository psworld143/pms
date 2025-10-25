<?php
session_start();
// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);


/**
 * Get Guest Feedback API
 * Hotel PMS - Guest Management Module
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in - handle both session and API key authentication
if (!isset($_SESSION['user_id'])) {
    // Check for API key in headers (for AJAX requests)
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
    
    if ($apiKey && $apiKey === 'pms_feedback_api_2024') {
        // Valid API key, create session
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Please log in']);
        exit();
    }
}

try {
    // Handle both individual guest feedback and general feedback listing
    $guestId = $_GET['guest_id'] ?? null;
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $sort = $_GET['sort'] ?? 'newest';
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    // Build the base query
    $baseSql = "
        SELECT 
            f.id,
            f.rating,
            f.comments,
            f.created_at,
            f.feedback_type,
            f.category,
            f.guest_id,
            r.id as reservation_id,
            r.check_in_date,
            r.check_out_date,
            g.first_name,
            g.last_name,
            g.email,
            rm.room_number
        FROM guest_feedback f
        LEFT JOIN reservations r ON f.reservation_id = r.id
        LEFT JOIN guests g ON f.guest_id = g.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
    ";
    
    // Add WHERE clause if guest_id is provided
    $whereClause = "";
    $params = [];
    if ($guestId) {
        $whereClause = " WHERE f.guest_id = ?";
        $params[] = $guestId;
    }
    
    // Add ORDER BY clause based on sort parameter
    $orderBy = " ORDER BY f.created_at DESC";
    switch ($sort) {
        case 'oldest':
            $orderBy = " ORDER BY f.created_at ASC";
            break;
        case 'rating_high':
            $orderBy = " ORDER BY f.rating DESC, f.created_at DESC";
            break;
        case 'rating_low':
            $orderBy = " ORDER BY f.rating ASC, f.created_at DESC";
            break;
        case 'newest':
        default:
            $orderBy = " ORDER BY f.created_at DESC";
            break;
    }
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM guest_feedback f" . $whereClause;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get feedback with pagination
    $sql = $baseSql . $whereClause . $orderBy . " LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedFeedback = array_map(function($item) {
        return [
            'id' => $item['id'],
            'rating' => (int)$item['rating'],
            'comments' => $item['comments'],
            'created_at' => $item['created_at'],
            'feedback_type' => $item['feedback_type'],
            'category' => $item['category'],
            'guest_id' => $item['guest_id'],
            'guest_name' => trim($item['first_name'] . ' ' . $item['last_name']),
            'email' => $item['email'],
            'reservation_id' => $item['reservation_id'],
            'check_in_date' => $item['check_in_date'],
            'check_out_date' => $item['check_out_date'],
            'room_number' => $item['room_number']
        ];
    }, $feedback);
    
    // Calculate pagination info
    $totalPages = max(1, (int)ceil($totalCount / $limit));
    
    echo json_encode([
        'success' => true,
        'data' => $formattedFeedback,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$totalCount,
            'total_pages' => $totalPages
        ],
        'sort' => $sort
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest feedback: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>