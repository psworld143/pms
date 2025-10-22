<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'completion';

try {
    // Get user information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Get training statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_scenarios,
            AVG(CASE WHEN status = 'completed' THEN score END) as avg_score,
            SUM(CASE WHEN status = 'completed' THEN duration_minutes END) as total_time
        FROM training_attempts 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();
    
    // Generate certificate based on type
    switch ($type) {
        case 'completion':
            generateCompletionCertificate($user, $stats);
            break;
        case 'achievement':
            generateAchievementCertificate($user, $stats);
            break;
        case 'progress':
            generateProgressReport($user, $stats);
            break;
        default:
            throw new Exception('Invalid certificate type');
    }
    
} catch (Exception $e) {
    error_log("Error generating certificate: " . $e->getMessage());
    header('Location: certificates.php?error=' . urlencode($e->getMessage()));
    exit();
}

function generateCompletionCertificate($user, $stats) {
    $certificate_data = [
        'user_name' => $user['name'],
        'completion_date' => date('F j, Y'),
        'completed_scenarios' => $stats['completed_scenarios'],
        'average_score' => round($stats['avg_score'] ?? 0, 1),
        'total_hours' => round(($stats['total_time'] ?? 0) / 60, 1)
    ];
    
    // Create a simple HTML certificate
    $html = generateCertificateHTML($certificate_data, 'Completion Certificate');
    
    // Set headers for download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="completion_certificate_' . date('Y-m-d') . '.html"');
    
    echo $html;
}

function generateAchievementCertificate($user, $stats) {
    $certificate_data = [
        'user_name' => $user['name'],
        'completion_date' => date('F j, Y'),
        'completed_scenarios' => $stats['completed_scenarios'],
        'average_score' => round($stats['avg_score'] ?? 0, 1),
        'total_hours' => round(($stats['total_time'] ?? 0) / 60, 1)
    ];
    
    // Create a simple HTML certificate
    $html = generateCertificateHTML($certificate_data, 'Achievement Certificate');
    
    // Set headers for download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="achievement_certificate_' . date('Y-m-d') . '.html"');
    
    echo $html;
}

function generateProgressReport($user, $stats) {
    $certificate_data = [
        'user_name' => $user['name'],
        'completion_date' => date('F j, Y'),
        'completed_scenarios' => $stats['completed_scenarios'],
        'average_score' => round($stats['avg_score'] ?? 0, 1),
        'total_hours' => round(($stats['total_time'] ?? 0) / 60, 1)
    ];
    
    // Create a simple HTML report
    $html = generateProgressReportHTML($certificate_data);
    
    // Set headers for download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="progress_report_' . date('Y-m-d') . '.html"');
    
    echo $html;
}

function generateCertificateHTML($data, $title) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <title>{$title}</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .certificate { border: 5px solid #333; padding: 50px; max-width: 800px; margin: 0 auto; }
            .title { font-size: 36px; font-weight: bold; margin-bottom: 30px; color: #333; }
            .subtitle { font-size: 24px; margin-bottom: 40px; color: #666; }
            .content { font-size: 18px; line-height: 1.6; margin-bottom: 30px; }
            .stats { display: flex; justify-content: space-around; margin: 30px 0; }
            .stat { text-align: center; }
            .stat-value { font-size: 24px; font-weight: bold; color: #333; }
            .stat-label { font-size: 14px; color: #666; }
            .date { font-size: 16px; color: #666; margin-top: 40px; }
        </style>
    </head>
    <body>
        <div class='certificate'>
            <div class='title'>{$title}</div>
            <div class='subtitle'>Hotel Management Training System</div>
            <div class='content'>
                This certifies that <strong>{$data['user_name']}</strong> has successfully completed 
                the hotel management training program with the following achievements:
            </div>
            <div class='stats'>
                <div class='stat'>
                    <div class='stat-value'>{$data['completed_scenarios']}</div>
                    <div class='stat-label'>Scenarios Completed</div>
                </div>
                <div class='stat'>
                    <div class='stat-value'>{$data['average_score']}%</div>
                    <div class='stat-label'>Average Score</div>
                </div>
                <div class='stat'>
                    <div class='stat-value'>{$data['total_hours']}h</div>
                    <div class='stat-label'>Training Hours</div>
                </div>
            </div>
            <div class='date'>Issued on {$data['completion_date']}</div>
        </div>
    </body>
    </html>";
}

function generateProgressReportHTML($data) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Training Progress Report</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; }
            .header { text-align: center; margin-bottom: 40px; }
            .title { font-size: 32px; font-weight: bold; color: #333; }
            .subtitle { font-size: 18px; color: #666; }
            .content { max-width: 800px; margin: 0 auto; }
            .section { margin-bottom: 30px; }
            .section-title { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 15px; }
            .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
            .stat { text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            .stat-value { font-size: 24px; font-weight: bold; color: #333; }
            .stat-label { font-size: 14px; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='title'>Training Progress Report</div>
            <div class='subtitle'>Hotel Management Training System</div>
        </div>
        <div class='content'>
            <div class='section'>
                <div class='section-title'>Student Information</div>
                <p><strong>Name:</strong> {$data['user_name']}</p>
                <p><strong>Report Date:</strong> {$data['completion_date']}</p>
            </div>
            <div class='section'>
                <div class='section-title'>Training Statistics</div>
                <div class='stats'>
                    <div class='stat'>
                        <div class='stat-value'>{$data['completed_scenarios']}</div>
                        <div class='stat-label'>Scenarios Completed</div>
                    </div>
                    <div class='stat'>
                        <div class='stat-value'>{$data['average_score']}%</div>
                        <div class='stat-label'>Average Score</div>
                    </div>
                    <div class='stat'>
                        <div class='stat-value'>{$data['total_hours']}h</div>
                        <div class='stat-label'>Training Hours</div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>";
}
?>
