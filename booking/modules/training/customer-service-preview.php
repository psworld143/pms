<?php
require_once '../../includes/session-config.php';
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit(); }

$scenario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$scenario_id) { header('Location: customer-service.php'); exit(); }

try {
    $item = getCustomerServiceDetails($scenario_id);
    if (!$item) { header('Location: customer-service.php'); exit(); }
} catch (Exception $e) { header('Location: customer-service.php'); exit(); }

$page_title = 'Preview - ' . ($item['title'] ?? 'Customer Service');
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($item['title'] ?? 'Case'); ?></h1>
                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($item['prompt'] ?? ''); ?></p>
                    </div>
                    <div class="text-right">
                        <a href="customer-service-start.php?id=<?php echo (int)$scenario_id; ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><i class="fas fa-play mr-2"></i>Start</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Type:</strong> <?php echo htmlspecialchars(ucfirst($item['type'] ?? '')); ?></div>
                    <div><strong>Difficulty:</strong> <?php echo htmlspecialchars(ucfirst($item['difficulty'] ?? '')); ?></div>
                    <div><strong>Estimated Time:</strong> <?php echo (int)($item['estimated_time'] ?? 0); ?> min</div>
                    <div><strong>Points:</strong> <?php echo (int)($item['points'] ?? 0); ?></div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>

