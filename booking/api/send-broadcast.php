<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Only managers can broadcast
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { throw new Exception('Invalid payload'); }

    $target   = $input['target']   ?? 'all';      // front_desk | housekeeping | manager | all
    $type     = $input['priority'] ?? 'info';     // info | success | warning | error
    $title    = trim($input['title'] ?? 'Announcement');
    $message  = trim($input['message'] ?? '');

    if ($message === '') { throw new Exception('Message is required'); }

    // Determine roles to target
    $roles = [];
    switch ($target) {
        case 'front_desk': $roles = ['front_desk']; break;
        case 'manager': $roles = ['manager']; break;
        default: $roles = ['front_desk','manager'];
    }

    // Fetch users by role
    $in  = str_repeat('?,', count($roles) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role IN ($in) AND is_active = 1");
    $stmt->execute($roles);
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($users)) {
        echo json_encode(['success' => true, 'message' => 'No recipients found']);
        exit();
    }

    // Insert notifications for each user
    $pdo->beginTransaction();

    // Some databases have notifications.title, some don’t. We'll try with title, fallback without.
    $hasTitle = true;
    try {
        $test = $pdo->query("SHOW COLUMNS FROM notifications LIKE 'title'");
        $hasTitle = $test && $test->rowCount() > 0;
    } catch (Exception $e) { $hasTitle = false; }

    if ($hasTitle) {
        $ins = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())");
        foreach ($users as $uid) {
            $ins->execute([$uid, $title, $message, $type]);
        }
    } else {
        // Fallback: prefix title into message
        $ins = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())");
        $full = ($title ? "[$title] " : '') . $message;
        foreach ($users as $uid) {
            $ins->execute([$uid, $full, $type]);
        }
    }

    // Log activity for the broadcaster
    logActivity($_SESSION['user_id'], 'broadcast_sent', "Target: " . implode(',', $roles));

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Broadcast sent to ' . count($users) . ' recipient(s)']);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
    error_log('send-broadcast error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()]);
}
