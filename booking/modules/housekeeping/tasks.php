<?php
/**
 * Housekeeping Task Management
 * Hotel PMS Training System for Students
 */

require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has access (manager or front_desk only)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'front_desk'])) {
    header('Location: ../../login.php');
    exit();
}

// Get task statistics
try {
    // Total tasks
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM housekeeping_tasks");
    $totalTasks = $stmt->fetch()['total'];
    
    // Completed tasks
    $stmt = $pdo->query("SELECT COUNT(*) as completed FROM housekeeping_tasks WHERE status = 'completed'");
    $completedTasks = $stmt->fetch()['completed'];
    
    // In progress tasks
    $stmt = $pdo->query("SELECT COUNT(*) as in_progress FROM housekeeping_tasks WHERE status = 'in_progress'");
    $inProgressTasks = $stmt->fetch()['in_progress'];
    
    // Overdue tasks
    $stmt = $pdo->query("SELECT COUNT(*) as overdue FROM housekeeping_tasks WHERE status != 'completed' AND scheduled_time < NOW()");
    $overdueTasks = $stmt->fetch()['overdue'];
    
    // Get all tasks with room and staff information
    $stmt = $pdo->query("
        SELECT 
            ht.*,
            r.room_number,
            u.name as staff_name
        FROM housekeeping_tasks ht
        LEFT JOIN rooms r ON ht.room_id = r.id
        LEFT JOIN users u ON ht.assigned_to = u.id
        ORDER BY ht.created_at DESC
    ");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Error loading task data: ' . $e->getMessage());
    $totalTasks = $completedTasks = $inProgressTasks = $overdueTasks = 0;
    $tasks = [];
}

// Set page title
$page_title = 'Task Management';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Task Management</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="showNewTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>New Task
                    </button>
                </div>
            </div>

            <!-- Task Statistics -->
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
                            <p class="text-2xl font-semibold text-gray-900" data-stat="total"><?php echo $totalTasks; ?></p>
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
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-2xl font-semibold text-gray-900" data-stat="completed"><?php echo $completedTasks; ?></p>
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
                            <p class="text-sm font-medium text-gray-500">In Progress</p>
                            <p class="text-2xl font-semibold text-gray-900" data-stat="in_progress"><?php echo $inProgressTasks; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Overdue</p>
                            <p class="text-2xl font-semibold text-gray-900" data-stat="overdue"><?php echo $overdueTasks; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Tasks Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Tasks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tasksList" class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($tasks)): ?>
                                <?php foreach ($tasks as $task): 
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
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $task['task_type']))); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($task['notes'] ?? ''); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($task['room_number'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($task['staff_name'] ?? 'Unassigned'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M j, Y H:i', strtotime($task['scheduled_time'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewTask(<?php echo $task['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'in_progress')" class="text-yellow-600 hover:text-yellow-900 mr-3">Start</button>
                                        <button onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'completed')" class="text-green-600 hover:text-green-900">Complete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-tasks text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No tasks found</p>
                                            <p class="text-sm">Create a new task to get started</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- New Task Modal -->
        <div id="new-task-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Create New Task</h3>
                        <button onclick="closeNewTaskModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="new-task-form" class="space-y-4" action="../../api/create-housekeeping-task.php" method="POST">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Number *</label>
                            <select name="room_id" id="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Room</option>
                                <?php
                                try {
                                    $stmt = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number ASC");
                                    $rooms = $stmt->fetchAll();
                                    foreach ($rooms as $room) {
                                        echo '<option value="' . $room['id'] . '">' . htmlspecialchars($room['room_number']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log('Error loading rooms: ' . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Task Type *</label>
                            <select name="task_type" id="task_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Task Type</option>
                                <option value="daily_cleaning">Daily Cleaning</option>
                                <option value="turn_down">Turn Down Service</option>
                                <option value="deep_cleaning">Deep Cleaning</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inspection">Inspection</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                            <select name="assigned_to" id="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Leave Unassigned</option>
                                <?php
                                try {
                                    // Get all users with housekeeping or maintenance roles
                                    $stmt = $pdo->query("SELECT id, name FROM users WHERE role IN ('housekeeping', 'maintenance') AND is_active = 1 ORDER BY name ASC");
                                    $staff = $stmt->fetchAll();
                                    foreach ($staff as $member) {
                                        echo '<option value="' . $member['id'] . '">' . htmlspecialchars($member['name']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log('Error loading staff: ' . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Date *</label>
                            <input type="date" name="scheduled_date" id="scheduled_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled Time *</label>
                            <input type="time" name="scheduled_time" id="scheduled_time" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Additional notes about the task..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <!-- Hidden field for combined scheduled_time -->
                        <input type="hidden" name="scheduled_time" id="scheduled_time_combined" value="">
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closeNewTaskModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="../../assets/js/main.js?v=<?php echo time(); ?>"></script>
        <script src="../../assets/js/housekeeping-tasks.js?v=<?php echo time(); ?>"></script>
    </body>
</html>
