<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Certificate ID is required');
    }
    
    $certificate = getCertificateDetails($id);
    
    if (!$certificate) {
        throw new Exception('Certificate not found');
    }
    
    echo json_encode(['success' => true, 'certificate' => $certificate]);
    
} catch (Exception $e) {
    error_log("Error in get-certificate-details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

function getCertificateDetails($certificate_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT tc.*,
                   CASE 
                       WHEN tc.scenario_type = 'training' THEN ts.title
                       WHEN tc.scenario_type = 'customer_service' THEN css.title
                       WHEN tc.scenario_type = 'problem' THEN ps.title
                       ELSE 'Unknown Scenario'
                   END as scenario_title
            FROM training_certificates tc
            LEFT JOIN training_scenarios ts ON tc.scenario_id = ts.id AND tc.scenario_type = 'training'
            LEFT JOIN customer_service_scenarios css ON tc.scenario_id = css.id AND tc.scenario_type = 'customer_service'
            LEFT JOIN problem_scenarios ps ON tc.scenario_id = ps.id AND tc.scenario_type = 'problem'
            WHERE tc.id = ? AND tc.user_id = ?
        ");
        $stmt->execute([$certificate_id, $_SESSION['user_id']]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error getting certificate details: " . $e->getMessage());
        return null;
    }
}
?>

