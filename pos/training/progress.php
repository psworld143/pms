<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

require_once __DIR__ . '/../../includes/database.php';

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

// Query training data DIRECTLY (bypass broken function)
try {
    // Get statistics - POS ONLY
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_attempts,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_attempts,
            AVG(CASE WHEN status = 'completed' THEN score END) as avg_score,
            SUM(CASE WHEN status = 'completed' THEN duration_minutes ELSE 0 END) as total_minutes
        FROM training_attempts 
        WHERE user_id = ? AND system = 'pos'
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent activity - POS ONLY
    $stmt = $pdo->prepare("
        SELECT 
            ta.*,
            ts.title as scenario_title,
            ts.category,
            ts.difficulty
        FROM training_attempts ta
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.scenario_id
        WHERE ta.user_id = ? AND ta.system = 'pos'
        ORDER BY ta.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Progress page error: " . $e->getMessage());
    $stats = ['total_attempts' => 0, 'completed_attempts' => 0, 'avg_score' => 0, 'total_minutes' => 0];
    $recent_activity = [];
}

// Set page title
$page_title = 'Training Progress - POS System';
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
                    <i class="fas fa-chart-line text-primary mr-2"></i>
                    My Training Progress
                </h2>
                <a href="training-dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Progress Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">Total Attempts</h3>
                    <p class="text-3xl font-bold text-primary"><?php echo $stats['total_attempts']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">Completed</h3>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['completed_attempts']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">Average Score</h3>
                    <p class="text-3xl font-bold text-blue-600"><?php echo round($stats['avg_score'] ?? 0); ?>%</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">Training Hours</h3>
                    <p class="text-3xl font-bold text-purple-600"><?php echo number_format(($stats['total_minutes'] ?? 0) / 60, 1); ?></p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Recent Training Activity
                </h3>
                <?php if (!empty($recent_activity)): ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="border-l-4 <?php echo $activity['status'] === 'completed' ? 'border-green-500 bg-green-50' : 'border-yellow-500 bg-yellow-50'; ?> pl-4 py-3 rounded">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($activity['scenario_title'] ?? 'Training Scenario'); ?>
                                        </h4>
                                        <div class="flex items-center gap-4 text-sm text-gray-600 mt-1">
                                            <span>
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo date('M d, Y \a\t H:i', strtotime($activity['created_at'])); ?>
                                            </span>
                                            <span class="<?php echo $activity['status'] === 'completed' ? 'text-green-600' : 'text-yellow-600'; ?> font-semibold">
                                                <i class="fas <?php echo $activity['status'] === 'completed' ? 'fa-check-circle' : 'fa-clock'; ?> mr-1"></i>
                                                <?php echo ucfirst($activity['status']); ?>
                                            </span>
                                            <span class="font-semibold <?php 
                                                $score = $activity['score'];
                                                if ($score >= 90) echo 'text-green-600';
                                                elseif ($score >= 80) echo 'text-blue-600';
                                                elseif ($score >= 70) echo 'text-yellow-600';
                                                else echo 'text-red-600';
                                            ?>">
                                                <i class="fas fa-star mr-1"></i>
                                                Score: <?php echo round($score); ?>%
                                            </span>
                                            <?php if (!empty($activity['category'])): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <?php echo ucwords(str_replace('_', ' ', str_replace('pos_', '', $activity['category']))); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($activity['status'] === 'completed'): ?>
                                        <a href="scenario-results.php?attempt_id=<?php echo $activity['id']; ?>" 
                                           class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                            <i class="fas fa-eye mr-1"></i>
                                            View Results
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">In Progress</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-600 text-lg">No training activity yet.</p>
                        <p class="text-gray-500 mb-6">Start a scenario to begin tracking your progress!</p>
                        <a href="scenarios.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-shadow">
                            <i class="fas fa-play mr-2"></i>Browse Training Scenarios
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
