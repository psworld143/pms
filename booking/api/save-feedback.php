<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "../config/database.php";
require_once '../includes/functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Normalize payload
    $guestId = (int)($input['guest_id'] ?? 0);
    $reservationId = isset($input['reservation_id']) && $input['reservation_id'] !== ''
        ? (int)$input['reservation_id']
        : null;
    $feedbackType = trim((string)($input['feedback_type'] ?? ''));
    $category = trim((string)($input['category'] ?? ''));
    $comments = trim((string)($input['comments'] ?? ''));
    $rating = $input['rating'] !== '' ? $input['rating'] : null;

    // Validate required fields
    if ($guestId <= 0) {
        throw new Exception('Guest ID is required');
    }
    
    if ($feedbackType === '') {
        throw new Exception('Feedback type is required');
    }
    
    if ($category === '') {
        throw new Exception('Category is required');
    }
    
    if ($comments === '') {
        throw new Exception('Comments are required');
    }
    
    // Save feedback
    $result = saveFeedback([
        'guest_id' => $guestId,
        'reservation_id' => $reservationId,
        'feedback_type' => $feedbackType,
        'category' => $category,
        'comments' => $comments,
        'rating' => $rating,
    ]);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error saving feedback: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Save guest feedback
 */
function saveFeedback($data) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();

        // Get guest information
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM guests WHERE id = ?");
        $stmt->execute([$data['guest_id']]);
        $guest = $stmt->fetch();

        if (!$guest) {
            throw new Exception('Guest not found');
        }

        if ($data['reservation_id']) {
            $stmt = $pdo->prepare("SELECT id FROM reservations WHERE id = ? AND guest_id = ?");
            $stmt->execute([$data['reservation_id'], $data['guest_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Reservation does not belong to this guest');
            }
        } else {
            $stmt = $pdo->prepare("SELECT id FROM reservations WHERE guest_id = ? ORDER BY check_in_date DESC, created_at DESC LIMIT 1");
            $stmt->execute([$data['guest_id']]);
            $latestReservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$latestReservation) {
                throw new Exception('Guest has no reservations to attach feedback to. Please create a reservation first.');
            }

            $data['reservation_id'] = (int)$latestReservation['id'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO guest_feedback (
                guest_id, reservation_id, feedback_type, category,
                rating, comments, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['guest_id'],
            $data['reservation_id'],
            $data['feedback_type'],
            $data['category'],
            $data['rating'],
            $data['comments']
        ]);
        
        $feedback_id = $pdo->lastInsertId();
        
        // Create notification for managers if it's a complaint
        if ($data['feedback_type'] === 'complaint') {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, title, message, type, created_at)
                SELECT id, ?, ?, 'warning', NOW()
                FROM users 
                WHERE role = 'manager' AND is_active = 1
            ");
            
            $notification_title = 'New Guest Complaint';
            $notification_message = "Complaint from {$guest['first_name']} {$guest['last_name']}: " . substr($data['comments'], 0, 100) . "...";
            
            $stmt->execute([$notification_title, $notification_message]);
        }
        
        // Log activity
        logActivity($_SESSION['user_id'], 'feedback_added', "Added {$data['feedback_type']} for guest {$guest['first_name']} {$guest['last_name']}");
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'feedback_id' => $feedback_id
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error saving feedback: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>
