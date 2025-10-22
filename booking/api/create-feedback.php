<?php
/**
 * Create Feedback API
 * Hotel PMS - Guest Management Module
 */

// Start session immediately
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

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    
    // Debug logging
    error_log("Raw input: " . $rawInput);
    error_log("Raw input length: " . strlen($rawInput));
    
    // If raw input is empty, it might have been read already, so return an error
    if (empty($rawInput)) {
        error_log("Empty raw input - stream may have been read already");
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'No input data received', 
            'debug' => [
                'raw_input' => $rawInput,
                'raw_input_length' => strlen($rawInput),
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                'request_method' => $_SERVER['REQUEST_METHOD'],
                'php_input_available' => ini_get('enable_post_data_reading')
            ]
        ]);
        exit();
    }
    
    $input = json_decode($rawInput, true);
    
    error_log("Decoded input: " . print_r($input, true));
    
    if (!$input) {
        error_log("JSON decode failed: " . json_last_error_msg());
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid JSON input', 
            'debug' => [
                'raw_input' => $rawInput, 
                'json_error' => json_last_error_msg()
            ]
        ]);
        exit();
    }
    
    // Validate required fields
    $requiredFields = ['guest_id', 'comments'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
            exit();
        }
    }
    
    // Insert feedback into guest_feedback table
    $sql = "
        INSERT INTO guest_feedback (
            guest_id, 
            reservation_id,
            feedback_type, 
            category, 
            rating, 
            comments, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())
    ";
    
    // Ensure required fields have default values
    $feedbackType = !empty($input['feedback_type']) ? $input['feedback_type'] : 'compliment';
    $category = !empty($input['category']) ? $input['category'] : 'other';
    $rating = !empty($input['rating']) ? $input['rating'] : null;
    
    // Handle reservation_id - convert empty string to null
    $reservationId = null;
    if (isset($input['reservation_id']) && $input['reservation_id'] !== '' && $input['reservation_id'] !== null) {
        $reservationId = $input['reservation_id'];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['guest_id'],
        $reservationId,
        $feedbackType,
        $category,
        $rating,
        $input['comments']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error creating feedback: " . $e->getMessage());
    error_log("SQL Error Code: " . $e->getCode());
    error_log("SQL Error Info: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug' => [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_info' => $e->errorInfo
        ]
    ]);
} catch (Exception $e) {
    error_log("General error creating feedback: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating feedback',
        'debug' => [
            'error_message' => $e->getMessage()
        ]
    ]);
}
?>
