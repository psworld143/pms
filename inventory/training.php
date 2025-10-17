<?php
/**
 * Inventory Training Scenarios
 * Hotel PMS Training System for Students
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/config/database.php';

// Check if user is logged in and is housekeeping
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['user_role'] ?? '') !== 'housekeeping') {
    header('Location: index.php');
    exit();
}

$inventory_db = new InventoryDatabase();
$user_id = $_SESSION['user_id'];

// Handle training scenario completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_scenario'])) {
    try {
        $scenario_id = $_POST['scenario_id'];
        $score = $_POST['score'];
        $time_taken = $_POST['time_taken'];
        $feedback = $_POST['feedback'] ?? '';
        
        // Check if user already has progress for this scenario
        $stmt = $inventory_db->getConnection()->prepare("
            SELECT id, attempts FROM inventory_training_progress 
            WHERE user_id = ? AND scenario_id = ?
        ");
        $stmt->execute([$user_id, $scenario_id]);
        $existing_progress = $stmt->fetch();
        
        if ($existing_progress) {
            // Update existing progress
            $stmt = $inventory_db->getConnection()->prepare("
                UPDATE inventory_training_progress 
                SET status = 'completed', score = ?, time_taken = ?, attempts = attempts + 1, 
                    completed_at = NOW(), feedback = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$score, $time_taken, $feedback, $existing_progress['id']]);
        } else {
            // Create new progress record
            $stmt = $inventory_db->getConnection()->prepare("
                INSERT INTO inventory_training_progress 
                (user_id, scenario_id, status, score, time_taken, attempts, completed_at, feedback)
                VALUES (?, ?, 'completed', ?, ?, 1, NOW(), ?)
            ");
            $stmt->execute([$user_id, $scenario_id, $score, $time_taken, $feedback]);
        }
        
        $success_message = "Training scenario completed successfully!";
        
    } catch (PDOException $e) {
        $error_message = "Error saving training progress: " . $e->getMessage();
    }
}

// Get training scenarios
$scenarios = [];
try {
    $stmt = $inventory_db->getConnection()->query("
        SELECT * FROM inventory_training_scenarios 
        WHERE active = 1 
        ORDER BY difficulty, title
    ");
    $scenarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting training scenarios: " . $e->getMessage());
}

// Get user's training progress
$user_progress = [];
try {
    $stmt = $inventory_db->getConnection()->prepare("
        SELECT scenario_id, status, score, time_taken, attempts, completed_at
        FROM inventory_training_progress 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $progress_records = $stmt->fetchAll();
    
    foreach ($progress_records as $record) {
        $user_progress[$record['scenario_id']] = $record;
    }
} catch (PDOException $e) {
    error_log("Error getting user progress: " . $e->getMessage());
}

// Get training statistics
$stats = [
    'total_scenarios' => count($scenarios),
    'completed' => 0,
    'in_progress' => 0,
    'total_points' => 0,
    'average_score' => 0
];

foreach ($user_progress as $progress) {
    if ($progress['status'] === 'completed') {
        $stats['completed']++;
        $stats['total_points'] += $progress['score'];
    } elseif ($progress['status'] === 'in_progress') {
        $stats['in_progress']++;
    }
}

if ($stats['completed'] > 0) {
    $stats['average_score'] = $stats['total_points'] / $stats['completed'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Training - Hotel PMS Training</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script></script>
</head>
<body class="bg-gray-50">
    <!-- Include unified inventory header and sidebar -->
    <?php include 'includes/inventory-header.php'; ?>
    <?php include 'includes/sidebar-inventory.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
            <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Training</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Training Progress</span>
                <div class="bg-primary text-white px-3 py-1 rounded-full text-sm">
                    <?php echo $stats['completed']; ?>/<?php echo $stats['total_scenarios']; ?>
                </div>
            </div>
        </div>

        <!-- Training Content -->
        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Training Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-graduation-cap text-primary text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Scenarios</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_scenarios']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completed</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['completed']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">In Progress</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['in_progress']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-star text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Average Score</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['average_score'], 1); ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Scenarios -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Training Scenarios</h3>
                <p class="text-sm text-gray-500 mt-1">Complete these scenarios to improve your inventory management skills</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($scenarios as $scenario): ?>
                        <?php 
                        $progress = $user_progress[$scenario['id']] ?? null;
                        $is_completed = $progress && $progress['status'] === 'completed';
                        $is_in_progress = $progress && $progress['status'] === 'in_progress';
                        ?>
                        <div class="border rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($scenario['title']); ?></h4>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($scenario['scenario_type']); ?></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php
                                    $difficulty_colors = [
                                        'beginner' => 'bg-green-100 text-green-800',
                                        'intermediate' => 'bg-yellow-100 text-yellow-800',
                                        'advanced' => 'bg-red-100 text-red-800'
                                    ];
                                    $color_class = $difficulty_colors[$scenario['difficulty']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                                        <?php echo ucfirst($scenario['difficulty']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($scenario['description']); ?></p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo $scenario['estimated_time']; ?> min
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-star mr-1"></i>
                                    <?php echo $scenario['points']; ?> points
                                </div>
                            </div>
                            
                            <?php if ($is_completed): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                        <span class="text-green-700 font-medium">Completed</span>
                                    </div>
                                    <div class="text-sm text-green-600 mt-1">
                                        Score: <?php echo $progress['score']; ?>% | 
                                        Time: <?php echo $progress['time_taken']; ?> min
                                    </div>
                                </div>
                                <button onclick="viewScenario(<?php echo $scenario['id']; ?>)" 
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-eye mr-2"></i>View Details
                                </button>
                            <?php elseif ($is_in_progress): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                                        <span class="text-yellow-700 font-medium">In Progress</span>
                                    </div>
                                </div>
                                <button onclick="continueScenario(<?php echo $scenario['id']; ?>)" 
                                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-play mr-2"></i>Continue
                                </button>
                            <?php else: ?>
                                <button onclick="startScenario(<?php echo $scenario['id']; ?>)" 
                                        class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-play mr-2"></i>Start Scenario
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Scenario Modal -->
    <div id="scenarioModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900" id="scenarioTitle">Training Scenario</h3>
                </div>
                <div class="p-6" id="scenarioContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function startScenario(scenarioId) {
            // Load scenario details and start training
            fetch(`api/get-scenario.php?id=${scenarioId}`, { credentials: 'include' })
                .then(async (response) => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Non-JSON response from get-scenario.php:', text);
                        throw new Error('Invalid server response');
                    }
                })
                .then(data => {
                    if (data && data.success) {
                        showScenarioModal(data.scenario);
                    } else {
                        alert('Error loading scenario: ' + (data && data.message ? data.message : 'Server error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading scenario');
                });
        }
        
        function continueScenario(scenarioId) {
            startScenario(scenarioId);
        }
        
        function viewScenario(scenarioId) {
            startScenario(scenarioId);
        }
        
        function showScenarioModal(scenario) {
            document.getElementById('scenarioTitle').textContent = scenario.title;
            document.getElementById('scenarioContent').innerHTML = `
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Description</h4>
                    <p class="text-gray-600">${scenario.description}</p>
                </div>
                
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Instructions</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="whitespace-pre-wrap text-sm text-gray-700">${scenario.instructions}</pre>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Expected Outcome</h4>
                    <p class="text-gray-600">${scenario.expected_outcome}</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeScenarioModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                        Close
                    </button>
                    <button onclick="beginScenario(${scenario.id})" 
                            class="px-4 py-2 bg-primary hover:bg-secondary text-white rounded-lg">
                        Begin Training
                    </button>
                </div>
            `;
            document.getElementById('scenarioModal').classList.remove('hidden');
        }
        
        function closeScenarioModal() {
            document.getElementById('scenarioModal').classList.add('hidden');
        }
        
        function beginScenario(scenarioId) {
            // Redirect to scenario execution page
            window.location.href = `scenario-execution.php?id=${scenarioId}`;
        }
        
        // Close modal when clicking outside
        document.getElementById('scenarioModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScenarioModal();
            }
        });
    </script>
</body>
</html>
