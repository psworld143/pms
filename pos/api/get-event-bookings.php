<?php
/**
 * POS Event Bookings API
 * Fetches event bookings from the database
 * Used for displaying event bookings in the Events module
 */

header('Content-Type: application/json');
session_start();

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
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
    
    // Build the query
    $sql = "
        SELECT 
            o.id,
            o.service_type,
            o.guest_count,
            o.total_amount,
            o.status,
            o.special_requests as notes,
            o.created_at,
            o.updated_at,
            g.id as guest_id,
            g.first_name,
            g.last_name,
            g.email,
            g.phone,
            CONCAT(g.first_name, ' ', g.last_name) as guest_name,
            JSON_UNQUOTE(JSON_EXTRACT(o.items, '$[0].event_name')) as event_name,
            JSON_UNQUOTE(JSON_EXTRACT(o.items, '$[0].event_type')) as event_type,
            JSON_UNQUOTE(JSON_EXTRACT(o.items, '$[0].event_date')) as event_date,
            JSON_UNQUOTE(JSON_EXTRACT(o.items, '$[0].start_time')) as start_time,
            JSON_UNQUOTE(JSON_EXTRACT(o.items, '$[0].end_time')) as end_time,
            JSON_UNQUOTE(JSON_EXTRACT(o.items, '$[0].venue')) as venue
        FROM pos_orders o
        LEFT JOIN guests g ON o.guest_id = g.id
        WHERE o.service_type = 'events'
    ";
    
    $params = [];
    
    // Add status filter
    if ($status) {
        $sql .= " AND o.status = :status";
        $params['status'] = $status;
    }
    
    // Add date range filter
    if ($date_from) {
        $sql .= " AND DATE(o.created_at) >= :date_from";
        $params['date_from'] = $date_from;
    }
    
    if ($date_to) {
        $sql .= " AND DATE(o.created_at) <= :date_to";
        $params['date_to'] = $date_to;
    }
    
    // Order by created date (newest first)
    $sql .= " ORDER BY o.created_at DESC";
    
    // Add limit and offset for pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $count_sql = "
        SELECT COUNT(*) as total
        FROM pos_orders o
        WHERE o.service_type = 'events'
    ";
    
    if ($status) {
        $count_sql .= " AND o.status = :status";
    }
    if ($date_from) {
        $count_sql .= " AND DATE(o.created_at) >= :date_from";
    }
    if ($date_to) {
        $count_sql .= " AND DATE(o.created_at) <= :date_to";
    }
    
    $count_stmt = $pdo->prepare($count_sql);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue(':' . $key, $value);
    }
    $count_stmt->execute();
    $total_count = $count_stmt->fetch()['total'];
    
    // Format the results
    $formatted_bookings = array_map(function($booking) {
        // Parse items JSON if not already extracted
        $items = json_decode($booking['items'] ?? '[]', true);
        if (is_array($items) && !empty($items)) {
            $event_info = $items[0];
        } else {
            $event_info = [];
        }
        
        // Determine status class and label
        $status_map = [
            'confirmed' => ['label' => 'Confirmed', 'class' => 'success'],
            'pending' => ['label' => 'Pending', 'class' => 'warning'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'danger'],
            'completed' => ['label' => 'Completed', 'class' => 'info'],
            'in-progress' => ['label' => 'In Progress', 'class' => 'primary'],
            'setup' => ['label' => 'Setup', 'class' => 'info']
        ];
        
        $status_info = $status_map[$booking['status']] ?? ['label' => ucfirst($booking['status']), 'class' => 'secondary'];
        
        return [
            'id' => (int)$booking['id'],
            'event_name' => $booking['event_name'] ?? $event_info['event_name'] ?? 'Unnamed Event',
            'event_type' => $booking['event_type'] ?? $event_info['event_type'] ?? 'Event',
            'guest_name' => $booking['guest_name'] ?? 'Walk-in Customer',
            'guest_id' => $booking['guest_id'] ? (int)$booking['guest_id'] : null,
            'guest_email' => $booking['email'],
            'guest_phone' => $booking['phone'],
            'guest_count' => (int)$booking['guest_count'],
            'total_amount' => (float)$booking['total_amount'],
            'status' => $booking['status'],
            'status_label' => $status_info['label'],
            'status_class' => $status_info['class'],
            'event_date' => $booking['event_date'] ?? $event_info['event_date'] ?? null,
            'start_time' => $booking['start_time'] ?? $event_info['start_time'] ?? null,
            'end_time' => $booking['end_time'] ?? $event_info['end_time'] ?? null,
            'venue' => $booking['venue'] ?? $event_info['venue'] ?? 'TBD',
            'notes' => $booking['notes'],
            'created_at' => $booking['created_at'],
            'updated_at' => $booking['updated_at']
        ];
    }, $bookings);
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'message' => count($formatted_bookings) . ' booking(s) found',
        'bookings' => $formatted_bookings,
        'pagination' => [
            'total' => (int)$total_count,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total_count
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-event-bookings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while fetching bookings',
        'error' => $e->getMessage(),
        'bookings' => []
    ]);
} catch (Exception $e) {
    error_log("Error in get-event-bookings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching bookings',
        'error' => $e->getMessage(),
        'bookings' => []
    ]);
}
?>

