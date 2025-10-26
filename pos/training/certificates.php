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

// Get user certificates - POS ONLY
$certificates = [];
try {
    $stmt = $pdo->prepare("
        SELECT tc.*, 
               DATE_FORMAT(tc.earned_at, '%M %d, %Y') AS formatted_date,
               ta.scenario_id,
               ta.score,
               ts.title AS scenario_title
        FROM training_certificates tc
        LEFT JOIN training_attempts ta ON tc.user_id = ta.user_id
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.scenario_id
        WHERE tc.user_id = ? AND ta.system = 'pos'
        ORDER BY tc.earned_at DESC
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching certificates: " . $e->getMessage());
}

// Set page title
$page_title = 'Training Certificates - POS System';
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
                    <i class="fas fa-certificate text-primary mr-2"></i>
                    My Training Certificates
                </h2>
                <a href="training-dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Certificates Grid -->
            <?php if (!empty($certificates)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($certificates as $cert): ?>
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg border-2 border-yellow-300 shadow-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <i class="fas fa-certificate text-yellow-500 text-4xl"></i>
                                <span class="px-3 py-1 text-xs font-semibold bg-yellow-500 text-white rounded-full">
                                    CERTIFIED
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($cert['certificate_type'] ?? 'Excellence Certificate'); ?>
                            </h3>
                            <p class="text-gray-700 mb-1">
                                <strong><?php echo htmlspecialchars($user_name); ?></strong>
                            </p>
                            <p class="text-sm text-gray-600 mb-4">
                                <?php echo htmlspecialchars($cert['scenario_title'] ?? 'Training Scenario'); ?>
                            </p>
                            <div class="border-t border-yellow-300 pt-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        Score: <?php echo round($cert['score'] ?? 0); ?>%
                                    </span>
                                    <span class="text-gray-600">
                                        <i class="fas fa-calendar text-yellow-500 mr-1"></i>
                                        <?php echo $cert['formatted_date']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-certificate text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No Certificates Yet</h3>
                    <p class="text-gray-600 mb-6">Complete training scenarios to earn certificates!</p>
                    <a href="scenarios.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-shadow">
                        <i class="fas fa-play mr-2"></i>Start Training
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

