<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Get Guest Documents API
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
    
    // Get guest documents
    $sql = "
        SELECT 
            id,
            document_type,
            document_name,
            file_path,
            file_size,
            created_at,
            status
        FROM guest_documents
        WHERE guest_id = ?
        ORDER BY created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guestId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedDocuments = array_map(function($item) {
        return [
            'id' => $item['id'],
            'document_type' => $item['document_type'],
            'document_name' => $item['document_name'],
            'file_path' => $item['file_path'],
            'file_size' => (int)$item['file_size'],
            'created_at' => $item['created_at'],
            'status' => $item['status']
        ];
    }, $documents);
    
    echo json_encode([
        'success' => true,
        'documents' => $formattedDocuments
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting guest documents: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
