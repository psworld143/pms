<?php
/**
 * Approve Supply Request
 * Allows managers to approve or reject supply requests from housekeeping
 */

require_once __DIR__ . '/../../vps_session_fix.php';
require_once __DIR__ . '/../../includes/database.php';

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Manager role required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate required fields
if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$valid_actions = ['approve', 'reject'];
$action = trim($_POST['action']);
if (!in_array($action, $valid_actions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action. Must be: ' . implode(', ', $valid_actions)]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $notes = trim($_POST['notes'] ?? '');
    
    // Get request details
    $stmt = $pdo->prepare("
        SELECT sr.*, ii.item_name, u.username as requested_by_name
        FROM supply_requests sr
        LEFT JOIN inventory_items ii ON sr.item_id = ii.id
        LEFT JOIN users u ON sr.requested_by = u.id
        WHERE sr.id = ? AND sr.status = 'pending'
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();
    
    if (!$request) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Supply request not found or already processed']);
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update request status
    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("
        UPDATE supply_requests 
        SET status = ?, 
            approved_by = ?, 
            approved_at = NOW(),
            notes = CONCAT(IFNULL(notes, ''), IF(notes IS NOT NULL AND notes != '', '\n', ''), ?)
        WHERE id = ?
    ");
    $stmt->execute([$new_status, $user_id, $notes, $request_id]);
    
    // If approved, create a restock transaction
    if ($action === 'approve') {
        // Find the room inventory item
        $stmt = $pdo->prepare("
            SELECT ri.id, ri.room_id, ri.quantity_current
            FROM room_inventory ri
            JOIN rooms r ON ri.room_id = r.id
            WHERE ri.item_id = ? AND r.room_number = ?
        ");
        $stmt->execute([$request['item_id'], $request['room_number']]);
        $room_item = $stmt->fetch();
        
        if ($room_item) {
            // Update existing room inventory
            $new_quantity = $room_item['quantity_current'] + $request['quantity_requested'];
            $stmt = $pdo->prepare("
                UPDATE room_inventory 
                SET quantity_current = ?, last_updated = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$new_quantity, $room_item['id']]);
            
            // Record transaction using your existing table structure
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO room_inventory_transactions (
                        room_id,
                        item_id,
                        transaction_type,
                        quantity_change,
                        quantity_before,
                        quantity_after,
                        reason,
                        notes,
                        user_id,
                        created_at
                    ) VALUES (?, ?, 'restock', ?, ?, ?, 'restock', ?, ?, NOW())
                ");
                
                $transaction_notes = "Restocked via approved supply request #{$request_id}";
                $stmt->execute([
                    $room_item['room_id'],
                    $request['item_id'],
                    $request['quantity_requested'],
                    $room_item['quantity_current'],
                    $new_quantity,
                    $transaction_notes,
                    $user_id
                ]);
            } catch (Throwable $e) {
                // Ignore if transaction table doesn't exist or has issues
                error_log("Transaction logging failed: " . $e->getMessage());
            }
        } else {
            // Room inventory item doesn't exist, create it
            // First, get the room_id from room_number
            $stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $stmt->execute([$request['room_number']]);
            $room = $stmt->fetch();
            
            if ($room) {
                $room_id = $room['id'];
                $quantity_requested = $request['quantity_requested'];
                
                // Create new room inventory item
                $stmt = $pdo->prepare("
                    INSERT INTO room_inventory (room_id, item_id, quantity_allocated, quantity_current, par_level, last_updated) 
                    VALUES (?, ?, 0, ?, 0, NOW())
                ");
                $stmt->execute([$room_id, $request['item_id'], $quantity_requested]);
                
                // Record transaction
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO room_inventory_transactions (
                            room_id,
                            item_id,
                            transaction_type,
                            quantity_change,
                            quantity_before,
                            quantity_after,
                            reason,
                            notes,
                            user_id,
                            created_at
                        ) VALUES (?, ?, 'restock', ?, 0, ?, 'restock', ?, ?, NOW())
                    ");
                    
                    $transaction_notes = "Created new room inventory via approved supply request #{$request_id}";
                    $stmt->execute([
                        $room_id,
                        $request['item_id'],
                        $quantity_requested,
                        $quantity_requested,
                        $transaction_notes,
                        $user_id
                    ]);
                } catch (Throwable $e) {
                    // Ignore if transaction table doesn't exist or has issues
                    error_log("Transaction logging failed: " . $e->getMessage());
                }
            }
        }
    }
    
    // Log the activity
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $action_text = $action === 'approve' ? 'supply_request_approved' : 'supply_request_rejected';
        $details = "Supply request #{$request_id} for '{$request['item_name']}' {$action}d";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt->execute([$user_id, $action_text, $details, $ip_address, $user_agent]);
    } catch (Throwable $e) {
        // Ignore if activity_logs table doesn't exist or has issues
        error_log("Activity logging failed: " . $e->getMessage());
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Supply request {$action}d successfully",
        'request_id' => $request_id,
        'new_status' => $new_status
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Database error in approve-supply-request.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
