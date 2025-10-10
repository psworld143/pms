<?php
require_once '../../includes/session-config.php';
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit(); }

$scenario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$scenario_id) { header('Location: scenarios.php'); exit(); }

try {
    $details = getScenarioDetails($scenario_id);
    if (!$details) { header('Location: scenarios.php'); exit(); }
    $scenario = is_array($details) && isset($details['scenario']) ? $details['scenario'] : $details;
    $questions = is_array($details) && isset($details['questions']) ? $details['questions'] : [];
} catch (Exception $e) { header('Location: scenarios.php'); exit(); }

$page_title = 'Preview - ' . ($scenario['title'] ?? 'Scenario');
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($scenario['title'] ?? 'Scenario'); ?></h1>
                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($scenario['description'] ?? ''); ?></p>
                    </div>
                    <div class="text-right">
                        <a href="scenario-start.php?id=<?php echo (int)$scenario_id; ?>" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"><i class="fas fa-play mr-2"></i>Start</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Category:</strong> <?php echo htmlspecialchars(ucfirst($scenario['category'] ?? '')); ?></div>
                    <div><strong>Difficulty:</strong> <?php echo htmlspecialchars(ucfirst($scenario['difficulty'] ?? '')); ?></div>
                    <div><strong>Estimated Time:</strong> <?php echo (int)($scenario['estimated_time'] ?? 0); ?> min</div>
                    <div><strong>Points:</strong> <?php echo (int)($scenario['points'] ?? 0); ?></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sample Questions</h3>
                <?php if (empty($questions)): ?>
                    <p class="text-gray-500">No questions available for preview.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($questions, 0, 3) as $i => $q): ?>
                            <div>
                                <div class="font-medium text-gray-800 mb-1"><?php echo ($i+1) . '. ' . htmlspecialchars($q['question']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>

