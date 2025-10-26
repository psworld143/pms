<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../../vps_session_fix.php';

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../booking/includes/functions.php';

// Verify database connection
if (!isset($pdo)) {
    error_log("POS Training Scenarios: PDO not defined!");
    die("Database connection error. Please check configuration.");
}

// Test database connection and get database name
$db_test_count = 0;
$current_database = 'Unknown';
try {
    // Get current database name
    $db_result = $pdo->query("SELECT DATABASE() as db_name")->fetch();
    $current_database = $db_result['db_name'];
    
    // Count POS scenarios
    $test = $pdo->query("SELECT COUNT(*) as cnt FROM training_scenarios WHERE category LIKE 'pos_%'")->fetch();
    $db_test_count = $test['cnt'];
    
    error_log("POS Training: Connected to database: " . $current_database);
    error_log("POS Training: Found " . $db_test_count . " POS scenarios");
} catch (Exception $e) {
    error_log("POS Training: Database test failed - " . $e->getMessage());
    $db_test_count = -1; // Error indicator
}

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['pos_user_id'];
$user_name = $_SESSION['pos_user_name'] ?? 'POS User';
$user_role = $_SESSION['pos_user_role'] ?? 'pos_user';
$is_demo_mode = isset($_SESSION['pos_demo_mode']) && $_SESSION['pos_demo_mode'];

// Get filter parameters
$difficulty_filter = $_GET['difficulty'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Get POS-specific training scenarios from database
$scenarios = [];
$query_error = null;

try {
    // Build WHERE conditions
    $where_parts = ["status = 'active'", "category LIKE 'pos_%'"];
    $params = [];
    
    if (!empty($difficulty_filter)) {
        $where_parts[] = "difficulty = ?";
        $params[] = $difficulty_filter;
    }
    
    if (!empty($category_filter)) {
        $where_parts[] = "category = ?";
        $params[] = $category_filter;
    }
    
    $where_clause = implode(' AND ', $where_parts);
    $query = "SELECT * FROM training_scenarios WHERE $where_clause ORDER BY category, difficulty ASC, title ASC";
    
    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $scenarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Logging for debugging
    error_log("POS Training - Query: " . $query);
    error_log("POS Training - Found: " . count($scenarios) . " scenarios");
    
} catch (PDOException $e) {
    $query_error = $e->getMessage();
    error_log("POS Training Error: " . $query_error);
    $scenarios = [];
}

// Set page title
$page_title = 'Training Scenarios - POS System';
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
    <style>
        /* Main content margin for sidebar */
        .main-content {
            margin-left: 0;
        }
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem;
            }
        }
    </style>
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
                    <i class="fas fa-theater-masks text-primary mr-2"></i>
                    POS Training Scenarios
                </h2>
                <a href="training-dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Debug Info (remove after testing) -->
            <?php if (isset($_GET['debug']) || count($scenarios) === 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg">
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 font-mono">
                        <strong class="block mb-2 font-sans">🔍 Debug Information:</strong>
                        <strong>Current Database:</strong> <?php echo htmlspecialchars($current_database); ?><br>
                        <strong>Scenarios in Result:</strong> <?php echo count($scenarios); ?><br>
                        <strong>DB Test Count:</strong> <?php echo $db_test_count; ?> scenarios with 'pos_%' category<br>
                        <strong>Difficulty Filter:</strong> <?php echo $difficulty_filter ?: 'None'; ?><br>
                        <strong>Category Filter:</strong> <?php echo $category_filter ?: 'None'; ?><br>
                        <strong>Database Connected:</strong> <?php echo isset($pdo) ? '✅ Yes' : '❌ No'; ?><br>
                        <strong>PDO Type:</strong> <?php echo isset($pdo) ? get_class($pdo) : 'N/A'; ?><br>
                        <?php if ($query_error): ?>
                        <strong class="text-red-700">Query Error:</strong> <?php echo htmlspecialchars($query_error); ?><br>
                        <?php endif; ?>
                        <strong>Expected DB:</strong> pms_pms_hotel<br>
                        <strong>Full Query:</strong> SELECT * FROM training_scenarios WHERE status = 'active' AND category LIKE 'pos_%' ORDER BY category, difficulty ASC
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Info Banner -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Complete training scenarios to improve your POS skills and earn certificates! Practice real-world situations in a safe environment.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-signal mr-1"></i>Difficulty
                        </label>
                        <select name="difficulty" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Levels</option>
                            <option value="beginner" <?php echo $difficulty_filter === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo $difficulty_filter === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo $difficulty_filter === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-1"></i>Category
                        </label>
                        <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Categories</option>
                            <option value="pos_restaurant" <?php echo $category_filter === 'pos_restaurant' ? 'selected' : ''; ?>>Restaurant POS</option>
                            <option value="pos_room_service" <?php echo $category_filter === 'pos_room_service' ? 'selected' : ''; ?>>Room Service POS</option>
                            <option value="pos_spa" <?php echo $category_filter === 'pos_spa' ? 'selected' : ''; ?>>Spa & Wellness POS</option>
                            <option value="pos_gift_shop" <?php echo $category_filter === 'pos_gift_shop' ? 'selected' : ''; ?>>Gift Shop POS</option>
                            <option value="pos_events" <?php echo $category_filter === 'pos_events' ? 'selected' : ''; ?>>Events POS</option>
                            <option value="pos_quick_sales" <?php echo $category_filter === 'pos_quick_sales' ? 'selected' : ''; ?>>Quick Sales</option>
                            <option value="pos_customer_service" <?php echo $category_filter === 'pos_customer_service' ? 'selected' : ''; ?>>Customer Service</option>
                            <option value="pos_general" <?php echo $category_filter === 'pos_general' ? 'selected' : ''; ?>>General POS Skills</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-gradient-to-r from-primary to-secondary text-white px-6 py-2 rounded-lg hover:shadow-lg transition-shadow">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                    <?php if ($difficulty_filter || $category_filter): ?>
                    <div>
                        <a href="scenarios.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors inline-block">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Scenarios Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($scenarios)): ?>
                    <?php foreach ($scenarios as $scenario): ?>
                        <?php
                        // Difficulty badge colors
                        $difficulty_colors = [
                            'beginner' => 'bg-green-500',
                            'intermediate' => 'bg-yellow-500',
                            'advanced' => 'bg-red-500'
                        ];
                        $badge_color = $difficulty_colors[$scenario['difficulty'] ?? 'beginner'] ?? 'bg-blue-500';
                        
                        // Category icons
                        $category_icons = [
                            'pos_restaurant' => '🍽️',
                            'pos_room_service' => '🛎️',
                            'pos_spa' => '💆',
                            'pos_gift_shop' => '🎁',
                            'pos_events' => '🎉',
                            'pos_quick_sales' => '⚡',
                            'pos_customer_service' => '🤝',
                            'pos_general' => '💼'
                        ];
                        $icon = $category_icons[$scenario['category'] ?? 'pos_general'] ?? '📚';
                        ?>
                        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-block px-3 py-1 text-xs font-semibold text-white <?php echo $badge_color; ?> rounded-full uppercase">
                                    <?php echo htmlspecialchars($scenario['difficulty'] ?? 'Beginner'); ?>
                                </span>
                                <span class="text-3xl"><?php echo $icon; ?></span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-2 min-h-[3rem]">
                                <?php echo htmlspecialchars($scenario['title'] ?? 'Training Scenario'); ?>
                            </h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($scenario['description'] ?? 'Practice scenario description'); ?>
                            </p>
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-200">
                                <span><i class="fas fa-clock mr-1 text-primary"></i> <?php echo $scenario['duration'] ?? 15; ?> min</span>
                                <span><i class="fas fa-star mr-1 text-yellow-500"></i> <?php echo $scenario['max_score'] ?? 100; ?> pts</span>
                            </div>
                            <a href="scenario-training.php?id=<?php echo $scenario['id']; ?>" 
                               class="block w-full bg-gradient-to-r from-primary to-secondary text-white text-center py-3 rounded-lg hover:shadow-lg transition-all duration-300 font-semibold">
                                <i class="fas fa-play mr-2"></i>Start Training
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white rounded-lg shadow-md p-12 text-center">
                        <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">No Scenarios Found</h3>
                        <p class="text-gray-600 mb-6">
                            <?php if ($difficulty_filter || $category_filter): ?>
                                No scenarios match your filters. Try adjusting your search criteria.
                            <?php else: ?>
                                No training scenarios are currently available.
                            <?php endif; ?>
                        </p>
                        <?php if ($difficulty_filter || $category_filter): ?>
                            <a href="scenarios.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-shadow">
                                <i class="fas fa-redo mr-2"></i>Clear Filters
                            </a>
                        <?php else: ?>
                            <a href="training-dashboard.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-shadow">
                                <i class="fas fa-arrow-left mr-2"></i>Return to Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

