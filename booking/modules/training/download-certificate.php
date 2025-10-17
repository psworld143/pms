<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$certificate_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$certificate_id) {
    header('Location: certificates.php?error=Invalid certificate ID');
    exit();
}

try {
    // Get certificate details
    $stmt = $pdo->prepare("
        SELECT 
            tc.*,
            ta.score,
            ta.completed_at,
            CASE 
                WHEN ta.scenario_type = 'scenario' THEN ts.title
                WHEN ta.scenario_type = 'customer_service' THEN css.title
                WHEN ta.scenario_type = 'problem_solving' THEN ps.title
                ELSE 'Unknown Scenario'
            END as scenario_title
        FROM training_certificates tc
        LEFT JOIN training_attempts ta ON tc.attempt_id = ta.id
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.id AND ta.scenario_type = 'scenario'
        LEFT JOIN customer_service_scenarios css ON ta.scenario_id = css.id AND ta.scenario_type = 'customer_service'
        LEFT JOIN problem_scenarios ps ON ta.scenario_id = ps.id AND ta.scenario_type = 'problem_solving'
        WHERE tc.id = ? AND tc.user_id = ?
    ");
    $stmt->execute([$certificate_id, $user_id]);
    $certificate = $stmt->fetch();
    
    if (!$certificate) {
        throw new Exception('Certificate not found');
    }
    
    // Get user information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Generate certificate HTML
    $certificate_data = [
        'user_name' => $user['name'],
        'scenario_title' => $certificate['scenario_title'],
        'score' => $certificate['score'],
        'completion_date' => date('F j, Y', strtotime($certificate['completed_at'])),
        'issued_date' => date('F j, Y', strtotime($certificate['issued_date'])),
        'certificate_type' => ucfirst($certificate['certificate_type'])
    ];
    
    $html = generateCertificateHTML($certificate_data);
    
    // Set headers for download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="certificate_' . $certificate_id . '_' . date('Y-m-d') . '.html"');
    
    echo $html;
    
} catch (Exception $e) {
    error_log("Error downloading certificate: " . $e->getMessage());
    header('Location: certificates.php?error=' . urlencode($e->getMessage()));
    exit();
}

function generateCertificateHTML($data) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Training Certificate</title>
        <style>
            body { 
                font-family: 'Times New Roman', serif; 
                text-align: center; 
                padding: 50px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                margin: 0;
            }
            .certificate { 
                background: white;
                border: 10px solid #333; 
                padding: 60px; 
                max-width: 800px; 
                margin: 0 auto; 
                box-shadow: 0 0 30px rgba(0,0,0,0.3);
                position: relative;
            }
            .certificate::before {
                content: '';
                position: absolute;
                top: 20px;
                left: 20px;
                right: 20px;
                bottom: 20px;
                border: 2px solid #333;
                pointer-events: none;
            }
            .title { 
                font-size: 42px; 
                font-weight: bold; 
                margin-bottom: 20px; 
                color: #333; 
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            .subtitle { 
                font-size: 20px; 
                margin-bottom: 40px; 
                color: #666; 
                font-style: italic;
            }
            .content { 
                font-size: 20px; 
                line-height: 1.8; 
                margin-bottom: 40px; 
                color: #333;
            }
            .scenario-info {
                background: #f8f9fa;
                padding: 20px;
                margin: 30px 0;
                border-radius: 8px;
                border-left: 5px solid #007bff;
            }
            .scenario-title {
                font-size: 18px;
                font-weight: bold;
                color: #333;
                margin-bottom: 10px;
            }
            .score {
                font-size: 24px;
                font-weight: bold;
                color: #28a745;
            }
            .date { 
                font-size: 16px; 
                color: #666; 
                margin-top: 40px; 
                border-top: 2px solid #333;
                padding-top: 20px;
            }
            .signature {
                margin-top: 50px;
                display: flex;
                justify-content: space-between;
            }
            .sig-line {
                border-top: 1px solid #333;
                width: 200px;
                margin-top: 50px;
            }
        </style>
    </head>
    <body>
        <div class='certificate'>
            <div class='title'>Certificate of Completion</div>
            <div class='subtitle'>Hotel Management Training System</div>
            <div class='content'>
                This certifies that<br>
                <strong style='font-size: 24px; color: #333;'>{$data['user_name']}</strong><br>
                has successfully completed the training scenario:
            </div>
            <div class='scenario-info'>
                <div class='scenario-title'>{$data['scenario_title']}</div>
                <div>Score: <span class='score'>{$data['score']}%</span></div>
            </div>
            <div class='content'>
                This certificate is awarded in recognition of successful completion 
                of the hotel management training program.
            </div>
            <div class='date'>
                Completed on: {$data['completion_date']}<br>
                Certificate issued on: {$data['issued_date']}
            </div>
            <div class='signature'>
                <div>
                    <div class='sig-line'></div>
                    <div>Training Coordinator</div>
                </div>
                <div>
                    <div class='sig-line'></div>
                    <div>Hotel Manager</div>
                </div>
            </div>
        </div>
    </body>
    </html>";
}
?>
