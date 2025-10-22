<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Housekeeping Dashboard
 * Hotel PMS Training System for Students
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has access (manager or front_desk only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    header('Location: ../../login.php');
    exit();
}

// Get housekeeping statistics
try {
    // Room status overview
    $stmt = $pdo->query("
        SELECT 
            housekeeping_status,
            COUNT(*) as count
        FROM rooms 
        WHERE housekeeping_status IS NOT NULL
        GROUP BY housekeeping_status
    ");
    $roomStatusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Task statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM housekeeping_tasks");
    $totalTasks = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as completed FROM housekeeping_tasks WHERE status = 'completed'");
    $completedTasks = $stmt->fetch()['completed'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM housekeeping_tasks WHERE status = 'pending'");
    $pendingTasks = $stmt->fetch()['pending'];
    
    // Maintenance statistics
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM maintenance_requests WHERE status IN ('pending', 'in_progress')");
    $activeMaintenance = $stmt->fetch()['active'];
    
    // Recent tasks
    $stmt = $pdo->query("
        SELECT 
            ht.*,
            r.room_number,
            u.name as staff_name
        FROM housekeeping_tasks ht
        LEFT JOIN rooms r ON ht.room_id = r.id
        LEFT JOIN users u ON ht.assigned_to = u.id
        ORDER BY ht.created_at DESC
        LIMIT 5
    ");
    $recentTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Error loading housekeeping dashboard data: ' . $e->getMessage());
    $roomStatusData = [];
    $totalTasks = $completedTasks = $pendingTasks = $activeMaintenance = 0;
    $recentTasks = [];
}

// Set page title
$page_title = 'Housekeeping Dashboard';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Housekeeping Dashboard</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshDashboard()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Room Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); 
                $statusConfig = [
                    'clean' => ['label' => 'Clean', 'color' => 'green', 'icon' => 'fas fa-check-circle'],
                    'dirty' => ['label' => 'Dirty', 'color' => 'red', 'icon' => 'fas fa-times-circle'],
                    'maintenance' => ['label' => 'Maintenance', 'color' => 'yellow', 'icon' => 'fas fa-tools'],
                    'cleaning' => ['label' => 'Cleaning', 'color' => 'blue', 'icon' => 'fas fa-broom']
                ];
                
                foreach ($statusConfig as $status => $config):
                    $count = 0;
                    foreach ($roomStatusData as $data) {
                        if ($data['housekeeping_status'] === $status) {
                            $count = $data['count'];
                            break;
                        }
                    }
                ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $config['color']; ?>-500 rounded-md flex items-center justify-center">
                                <i class="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $config['icon']; ?> text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $config['label']; ?></p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $count; ?></p>
                        </div>
                    </div>
                </div>
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-tasks text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $totalTasks; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed Tasks</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $completedTasks; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Tasks</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $pendingTasks; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Maintenance</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $activeMaintenance; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Tasks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (!empty($recentTasks)): ?>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($recentTasks as $task): 
                                    $statusClass = '';
                                    $statusText = '';
                                    switch($task['status']) {
                                        case 'completed':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            $statusText = 'Completed';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            $statusText = 'In Progress';
                                            break;
                                        case 'pending':
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                            $statusText = 'Pending';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucwords(str_replace('_', ' ', $task['task_type']))); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($task['room_number'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($task['staff_name'] ?? 'Unassigned'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $statusClass; ?>">
                                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo date('M j, Y', strtotime($task['created_at'])); ?></td>
                                </tr>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-tasks text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No recent tasks</p>
                                            <p class="text-sm">Tasks will appear here as they are created</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Scripts -->
        <script src="../../assets/js/housekeeping.js"></script>
        <script src="../../assets/js/main.js"></script>
    </body>
</html>
