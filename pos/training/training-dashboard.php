<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../booking/includes/functions.php';

// Include session bridge for POS users
require_once __DIR__ . '/../../booking/modules/training/training-session-bridge.php';

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user information (from session bridge)
$user_id = $_SESSION['user_id']; // Use bridged user_id, not pos_user_id
$user_name = $_SESSION['user_name'] ?? $_SESSION['pos_user_name'] ?? 'POS User';
$user_role = $_SESSION['pos_user_role'] ?? 'pos_user';
$is_demo_mode = isset($_SESSION['pos_demo_mode']) && $_SESSION['pos_demo_mode'];

// Get training statistics - USER SPECIFIC & POS ONLY
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_scenarios,
            COALESCE(AVG(CASE WHEN status = 'completed' THEN score END), 0) as average_score,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN duration_minutes END), 0) / 60 as training_hours
        FROM training_attempts
        WHERE user_id = ? AND system = 'pos'
    ");
    $stmt->execute([$user_id]);
    $training_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get certificates earned - POS ONLY
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM training_certificates tc
        JOIN training_attempts ta ON tc.user_id = ta.user_id
        WHERE tc.user_id = ? AND tc.status = 'earned' AND ta.system = 'pos'
    ");
    $stmt->execute([$user_id]);
    $training_stats['certificates_earned'] = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("Error getting training stats: " . $e->getMessage());
    $training_stats = [
        'completed_scenarios' => 0,
        'average_score' => 0,
        'training_hours' => 0,
        'certificates_earned' => 0
    ];
}

// Current streak (consecutive days with a completed attempt ending today) - POS ONLY
$current_streak = 0;
try {
    $stmt = $pdo->prepare("SELECT DISTINCT DATE(created_at) AS d FROM training_attempts WHERE user_id = ? AND status = 'completed' AND system = 'pos' ORDER BY d DESC");
    $stmt->execute([$user_id]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $cursor = new DateTime('today');
    foreach ($dates as $d) {
        if ($d === $cursor->format('Y-m-d')) { $current_streak++; $cursor->modify('-1 day'); } else { break; }
    }
} catch (Exception $e) { $current_streak = 0; }

// Achievement points - POS ONLY
$achievement_points = 0;
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(score),0) FROM training_attempts WHERE user_id = ? AND status = 'completed' AND system = 'pos'");
    $stmt->execute([$user_id]);
    $achievement_points = (int)round($stmt->fetchColumn());
} catch (Exception $e) { $achievement_points = 0; }

// This week's performance - POS ONLY
$week_completed = 0; $week_avg = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(AVG(score),0) AS avg_score FROM training_attempts WHERE user_id=? AND status='completed' AND system='pos' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $week_completed = (int)$row['cnt'];
    $week_avg = round((float)$row['avg_score']);
} catch (Exception $e) {}

// Set page title
$page_title = 'Training Dashboard - POS System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pos-styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden" onclick="closeSidebar()"></div>
        
        <!-- Include POS-specific header and sidebar -->
        <?php include '../includes/pos-header.php'; ?>
        <?php include '../includes/pos-sidebar.php'; ?>
        
        <!-- Load sidebar JS after header to ensure proper function order -->
        <script src="../assets/js/pos-sidebar.js"></script>

        <!-- Main Content -->
        <main class="main-content pt-20 px-4 pb-4 lg:px-6 lg:pb-6 flex-1 transition-all duration-300">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i>
                    POS Training Dashboard
                </h2>
            </div>

            <!-- Training Progress Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Completed Scenarios -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-700">Completed Scenarios</p>
                            <p class="text-3xl font-bold text-blue-900"><?php echo number_format($training_stats['completed_scenarios'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Average Score -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-700">Average Score</p>
                            <p class="text-3xl font-bold text-green-900"><?php echo round($training_stats['average_score'] ?? 0); ?>%</p>
                        </div>
                    </div>
                </div>

                <!-- Training Hours -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-700">Training Hours</p>
                            <p class="text-3xl font-bold text-purple-900"><?php echo number_format($training_stats['training_hours'] ?? 0, 1); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Certificates Earned -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg border border-yellow-200 p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-certificate text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-700">Certificates Earned</p>
                            <p class="text-3xl font-bold text-yellow-900"><?php echo number_format($training_stats['certificates_earned'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-fire text-orange-500 mr-2"></i>Current Streak
                    </h3>
                    <p class="text-4xl font-bold text-orange-600"><?php echo $current_streak; ?> days</p>
                    <p class="text-sm text-gray-600 mt-2">Keep it up!</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>Achievement Points
                    </h3>
                    <p class="text-4xl font-bold text-yellow-600"><?php echo number_format($achievement_points); ?></p>
                    <p class="text-sm text-gray-600 mt-2">Total earned</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-calendar-week text-blue-500 mr-2"></i>This Week
                    </h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $week_completed; ?> completed</p>
                    <p class="text-sm text-gray-600 mt-2">Average: <?php echo $week_avg; ?>%</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="scenarios.php" class="bg-gradient-to-r from-primary to-secondary text-white rounded-lg p-4 text-center hover:shadow-lg transition-shadow">
                        <i class="fas fa-play text-2xl mb-2"></i>
                        <p class="font-semibold">Start Training</p>
                    </a>
                    <a href="progress.php" class="bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-lg p-4 text-center hover:shadow-lg transition-shadow">
                        <i class="fas fa-chart-line text-2xl mb-2"></i>
                        <p class="font-semibold">View Progress</p>
                    </a>
                    <a href="certificates.php" class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg p-4 text-center hover:shadow-lg transition-shadow">
                        <i class="fas fa-certificate text-2xl mb-2"></i>
                        <p class="font-semibold">My Certificates</p>
                    </a>
                    <a href="../index.php" class="bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-lg p-4 text-center hover:shadow-lg transition-shadow">
                        <i class="fas fa-arrow-left text-2xl mb-2"></i>
                        <p class="font-semibold">Back to POS</p>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

