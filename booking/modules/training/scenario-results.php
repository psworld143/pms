<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
require_once '../../includes/session-config.php';
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit(); }

$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
if (!$attempt_id) { header('Location: progress.php'); exit(); }

try {
    $stmt = $pdo->prepare("SELECT * FROM training_attempts WHERE id = ? AND scenario_type='scenario'");
    $stmt->execute([$attempt_id]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$attempt) { header('Location: progress.php'); exit(); }

    $scenario = getScenarioDetails((int)$attempt['scenario_id']);
    $scenarioInfo = is_array($scenario) && isset($scenario['scenario']) ? $scenario['scenario'] : $scenario;
    $questions = is_array($scenario) && isset($scenario['questions']) ? $scenario['questions'] : [];
    $answers = $attempt['answers'] ? json_decode($attempt['answers'], true) : [];
} catch (Exception $e) { header('Location: progress.php'); exit(); }

$page_title = 'Results - ' . ($scenarioInfo['title'] ?? 'Scenario');
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($scenarioInfo['title'] ?? 'Scenario'); ?></h1>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-purple-600"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($attempt['score'] ?? 0, 1); ?>%</div>
                        <div class="text-sm text-gray-500">Completed: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($attempt['completed_at'] ?? $attempt['created_at']); ?></div>
                    </div>
                </div>
                <p class="text-gray-600 mb-2">Duration: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo (int)($attempt['duration_minutes'] ?? 0); ?> min</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Answer Breakdown</h3>
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (empty($questions)): ?>
                    <p class="text-gray-500">No questions available.</p>
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                    <div class="space-y-4">
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($questions as $i => $q): ?>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); $qnum = ($q['question_order'] ?? ($i+1)); $userAns = $answers[$qnum] ?? null; ?>
                            <div class="p-4 border rounded">
                                <div class="font-medium text-gray-800 mb-1"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $qnum . '. ' . htmlspecialchars($q['question']); ?></div>
                                <div class="text-sm text-gray-600">Your answer: <span class="font-semibold"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($userAns ?? '-'); ?></span></div>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (!empty($q['correct_answer'])): ?>
                                    <div class="text-sm text-gray-600">Correct answer: <span class="font-semibold"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($q['correct_answer']); ?></span></div>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                            </div>
                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                    </div>
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
            </div>
        </main>
    </div>

    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>

