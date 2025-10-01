<?php
/**
 * Training Scenario Execution
 * Hotel PMS Training System for Students
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$inventory_db = new InventoryDatabase();
$user_id = $_SESSION['user_id'];
$scenario_id = $_GET['id'] ?? '';

if (!$scenario_id) {
    header('Location: training.php');
    exit();
}

// Get scenario details
$scenario = null;
try {
    $stmt = $inventory_db->getConnection()->prepare("
        SELECT * FROM inventory_training_scenarios 
        WHERE id = ? AND active = 1
    ");
    $stmt->execute([$scenario_id]);
    $scenario = $stmt->fetch();
    
    if (!$scenario) {
        header('Location: training.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error getting scenario: " . $e->getMessage());
    header('Location: training.php');
    exit();
}

// Handle scenario completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_scenario'])) {
    try {
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
        
        header('Location: training.php?completed=1');
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Error saving training progress: " . $e->getMessage();
    }
}

// Get current inventory items for the scenario
$inventory_items = $inventory_db->getInventoryItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($scenario['title']); ?> - Training</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="training.php" class="text-primary hover:text-secondary mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <i class="fas fa-graduation-cap text-primary text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($scenario['title']); ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-primary text-white px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="timer">00:00</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Scenario Information -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Scenario Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Difficulty</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            <?php echo $scenario['difficulty'] === 'beginner' ? 'bg-green-100 text-green-800' : 
                                ($scenario['difficulty'] === 'intermediate' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo ucfirst($scenario['difficulty']); ?>
                        </span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Estimated Time</h4>
                        <p class="text-sm text-gray-900"><?php echo $scenario['estimated_time']; ?> minutes</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Points</h4>
                        <p class="text-sm text-gray-900"><?php echo $scenario['points']; ?> points</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                    <p class="text-gray-900"><?php echo htmlspecialchars($scenario['description']); ?></p>
                </div>
                
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Instructions</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="whitespace-pre-wrap text-sm text-gray-700"><?php echo htmlspecialchars($scenario['instructions']); ?></pre>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Expected Outcome</h4>
                    <p class="text-gray-900"><?php echo htmlspecialchars($scenario['expected_outcome']); ?></p>
                </div>
            </div>
        </div>

        <!-- Training Interface -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Current Inventory -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Current Inventory</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Min</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($inventory_items as $item): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $item['quantity']; ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo $item['minimum_stock']; ?></td>
                                        <td class="px-4 py-2">
                                            <?php if ($item['quantity'] <= $item['minimum_stock']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Low Stock
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    In Stock
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Training Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Training Actions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Quick Actions</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="items.php" class="bg-primary hover:bg-secondary text-white px-3 py-2 rounded text-sm text-center">
                                    <i class="fas fa-box mr-1"></i>Manage Items
                                </a>
                                <a href="transactions.php" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm text-center">
                                    <i class="fas fa-exchange-alt mr-1"></i>Transactions
                                </a>
                                <a href="requests.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded text-sm text-center">
                                    <i class="fas fa-clipboard-list mr-1"></i>Requests
                                </a>
                                <a href="reports.php" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded text-sm text-center">
                                    <i class="fas fa-chart-bar mr-1"></i>Reports
                                </a>
                            </div>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Scenario Progress</h4>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="step1" class="mr-2">
                                    <label for="step1" class="text-sm text-gray-700">Review current inventory levels</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="step2" class="mr-2">
                                    <label for="step2" class="text-sm text-gray-700">Identify low stock items</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="step3" class="mr-2">
                                    <label for="step3" class="text-sm text-gray-700">Create reorder requests</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="step4" class="mr-2">
                                    <label for="step4" class="text-sm text-gray-700">Update inventory records</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Scenario -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Complete Scenario</h3>
            </div>
            <div class="p-6">
                <form method="POST" id="completeForm">
                    <input type="hidden" name="complete_scenario" value="1">
                    <input type="hidden" name="time_taken" id="timeTaken" value="0">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Score (0-100)</label>
                            <input type="number" name="score" min="0" max="100" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Time Taken (minutes)</label>
                            <input type="number" name="time_taken" id="timeTakenInput" min="1" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Feedback (Optional)</label>
                            <textarea name="feedback" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Share your experience with this scenario..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button type="submit" 
                                class="bg-primary hover:bg-secondary text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-check mr-2"></i>Complete Scenario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Timer functionality
        let startTime = Date.now();
        let timerInterval;
        
        function updateTimer() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        // Start timer
        timerInterval = setInterval(updateTimer, 1000);
        
        // Update time taken when form is submitted
        document.getElementById('completeForm').addEventListener('submit', function() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            document.getElementById('timeTaken').value = minutes;
            document.getElementById('timeTakenInput').value = minutes;
        });
        
        // Auto-save progress
        function saveProgress() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const progress = Array.from(checkboxes).map(cb => cb.checked);
            localStorage.setItem('scenario_progress_<?php echo $scenario_id; ?>', JSON.stringify(progress));
        }
        
        // Load saved progress
        function loadProgress() {
            const saved = localStorage.getItem('scenario_progress_<?php echo $scenario_id; ?>');
            if (saved) {
                const progress = JSON.parse(saved);
                const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach((cb, index) => {
                    cb.checked = progress[index] || false;
                });
            }
        }
        
        // Load progress on page load
        loadProgress();
        
        // Save progress when checkboxes change
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', saveProgress);
        });
    </script>
</body>
</html>
