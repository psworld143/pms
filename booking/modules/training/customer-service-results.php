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
    $stmt = $pdo->prepare("SELECT * FROM training_attempts WHERE id = ? AND scenario_type='customer_service'");
    $stmt->execute([$attempt_id]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$attempt) { header('Location: progress.php'); exit(); }
    $item = getCustomerServiceDetails((int)$attempt['scenario_id']);
    $answers = $attempt['answers'] ? json_decode($attempt['answers'], true) : [];
} catch (Exception $e) { header('Location: progress.php'); exit(); }

$page_title = 'Results - ' . ($item['title'] ?? 'Customer Service');
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($item['title'] ?? 'Case'); ?></h1>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-blue-600"><?php
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
                <p class="text-gray-600">Type: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($item['type'] ?? '')); ?> • Difficulty: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($item['difficulty'] ?? '')); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Response</h3>
                <div class="text-gray-700 whitespace-pre-line"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($answers['response'] ?? 'No response recorded'); ?></div>
            </div>
        </main>
    </div>

    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>

