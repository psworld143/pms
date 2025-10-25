<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Start output buffering to prevent premature output
ob_start();

// Suppress warnings that might cause premature output
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR);

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/booking-paths.php';
require_once dirname(__DIR__, 2) . '/includes/maintenance-helpers.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit(); }
$page_title = 'Maintenance Management';

$limit = 25;
$initialPage = 1;
$initialRequests = getMaintenanceRequests([
    'limit' => $limit,
    'offset' => 0,
]);
$totalRequests = countMaintenanceRequests();
$totalPages = max(1, (int)ceil($totalRequests / $limit));
$summary = getMaintenanceSummary();
$assignees = getMaintenanceAssignees();

$issueTypes = ['plumbing', 'electrical', 'hvac', 'furniture', 'appliance', 'other'];
$priorities = ['low', 'medium', 'high', 'urgent'];
$statusOptions = ['reported', 'assigned', 'in_progress', 'completed', 'verified'];

$asset_version = time();
$bootstrap = [
    'requests' => $initialRequests,
    'pagination' => [
        'page' => $initialPage,
        'limit' => $limit,
        'total' => $totalRequests,
        'total_pages' => $totalPages,
    ],
    'summary' => $summary,
    'filters' => [
        'status' => '',
        'priority' => '',
        'assigned_to' => '',
        'date_from' => null,
        'date_to' => null,
        'search' => '',
    ],
    'assignees' => $assignees,
    'issue_types' => $issueTypes,
    'priorities' => $priorities,
    'statuses' => $statusOptions,
];

$additional_js = '<script>window.maintenanceBootstrap = ' . json_encode($bootstrap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/main.js?v=' . $asset_version) . '"></script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/maintenance-management.js?v=' . $asset_version) . '"></script>';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';

// Flush the output buffer to ensure DOCTYPE is sent first
ob_end_flush();
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Maintenance Management</h2>
                    <p class="text-sm text-gray-500">Track maintenance requests, assign technicians, and monitor service levels.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button id="maintenance-refresh" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button id="maintenance-export" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm flex items-center gap-2">
                        <i class="fas fa-file-export"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Requests</p>
                            <p id="maintenance-summary-active" class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($summary['active'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-green-500/10 text-green-600 flex items-center justify-center">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed Today</p>
                            <p id="maintenance-summary-completed-today" class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($summary['completed_today'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-red-500/10 text-red-600 flex items-center justify-center">
                                <i class="fas fa-exclamation"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Urgent</p>
                            <p id="maintenance-summary-urgent" class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($summary['urgent'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg. Completion (min)</p>
                            <p id="maintenance-summary-average" class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo number_format($summary['average_completion_minutes'] ?? 0, 1); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                        <h3 class="text-lg font-semibold text-gray-800">Maintenance Requests</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 w-full">
                            <select id="maintenance-filter-status" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">All Statuses</option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($statusOptions as $status): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $status; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucwords(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                            <select id="maintenance-filter-priority" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">All Priorities</option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($priorities as $priority): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $priority; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($priority), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                            <select id="maintenance-filter-assignee" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">All Assignees</option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($assignees as $assignee): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo (int)$assignee['id']; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($assignee['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                            <input type="text" id="maintenance-filter-search" placeholder="Search description, room, user" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="maintenance-table-body" class="bg-white divide-y divide-gray-200">
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (empty($initialRequests)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">No maintenance requests found.</td>
                                    </tr>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); else: ?>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($initialRequests as $request): ?>
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
                                            $roomLabel = $request['room_number'] ? 'Room ' . $request['room_number'] : 'Room #' . $request['room_id'];
                                            $priorityLabel = ucfirst($request['priority'] ?? 'medium');
                                            $statusLabel = ucwords(str_replace('_', ' ', $request['status'] ?? 'reported'));
                                            $issueLabel = ucwords(str_replace('_', ' ', $request['issue_type'] ?? 'maintenance'));
                                            $priorityBadge = match ($request['priority'] ?? 'medium') {
                                                'urgent' => 'bg-red-100 text-red-700',
                                                'high' => 'bg-amber-100 text-amber-700',
                                                'low' => 'bg-blue-100 text-blue-700',
                                                default => 'bg-green-100 text-green-700'
                                            };
                                        ?>
                                        <tr data-request-id="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo (int)$request['id']; ?>">
                                            <td class="px-6 py-4 align-top text-sm text-gray-700">
                                                <div class="font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($roomLabel, ENT_QUOTES, 'UTF-8'); ?> &middot; <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($issueLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-xs text-gray-500 mb-1 flex flex-wrap gap-2">
                                                    <span class="px-2 py-0.5 rounded-full text-xs <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $priorityBadge; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="text-gray-500">Reported by: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($request['reported_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="text-gray-400"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(date('M j, Y g:i A', strtotime($request['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <div class="text-sm text-gray-600"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo nl2br(htmlspecialchars($request['description'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
                                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); if (!empty($request['notes'])): ?>
                                                    <div class="mt-2 text-xs text-gray-500">Notes: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo nl2br(htmlspecialchars($request['notes'], ENT_QUOTES, 'UTF-8')); ?></div>
                                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                                            </td>
                                            <td class="px-6 py-4 align-top text-sm text-gray-700">
                                                <select class="maintenance-assignee-select w-full border border-gray-300 rounded-md text-sm px-2 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                                    <option value="">Unassigned</option>
                                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($assignees as $assignee): ?>
                                                        <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo (int)$assignee['id']; ?>" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo (int)$assignee['id'] === (int)($request['assigned_to'] ?? 0) ? 'selected' : ''; ?>><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars($assignee['name'], ENT_QUOTES, 'UTF-8'); ?> (<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucwords(str_replace('_', ' ', $assignee['role'])), ENT_QUOTES, 'UTF-8'); ?>)</option>
                                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 align-top text-sm text-gray-700">
                                                <select class="maintenance-status-select w-full border border-gray-300 rounded-md text-sm px-2 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($statusOptions as $status): ?>
                                                        <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $status; ?>" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $status === ($request['status'] ?? 'reported') ? 'selected' : ''; ?>><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucwords(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 align-top text-right text-sm text-gray-600">
                                                <button class="maintenance-save-btn inline-flex items-center gap-1 px-3 py-2 bg-primary text-white rounded-md text-sm hover:bg-secondary">
                                                    <i class="fas fa-save"></i>
                                                    <span>Save</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <span>Rows per page</span>
                            <select id="maintenance-limit" class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary">
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ([10, 25, 50, 100] as $optionLimit): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $optionLimit; ?>" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $optionLimit === $limit ? 'selected' : ''; ?>><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $optionLimit; ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span>Page <span id="maintenance-page-current"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $initialPage; ?></span> of <span id="maintenance-page-total"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $totalPages; ?></span></span>
                            <div class="flex items-center gap-2">
                                <button id="maintenance-page-prev" class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button id="maintenance-page-next" class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Create New Request</h3>
                    <form id="maintenance-create-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="maintenance-room">Room</label>
                            <select id="maintenance-room" name="room_id" class="w-full border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <option value="">Select room</option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
                                try {
                                    $roomsStmt = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number");
                                    foreach ($roomsStmt->fetchAll() as $room) {
                                        echo '<option value="' . (int)$room['id'] . '">Room ' . htmlspecialchars($room['room_number'], ENT_QUOTES, 'UTF-8') . '</option>'; }
                                } catch (Exception $e) {
                                    echo '<option value="">Unable to load rooms</option>'; }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="maintenance-issue">Issue Type</label>
                            <select id="maintenance-issue" name="issue_type" class="w-full border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($issueTypes as $type): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $type; ?>"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucwords(str_replace('_', ' ', $type)), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="maintenance-priority">Priority</label>
                            <select id="maintenance-priority" name="priority" class="w-full border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); foreach ($priorities as $priority): ?>
                                    <option value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $priority; ?>" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo $priority === 'medium' ? 'selected' : ''; ?>><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); echo htmlspecialchars(ucfirst($priority), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="maintenance-description">Description</label>
                            <textarea id="maintenance-description" name="description" rows="4" class="w-full border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Please describe the maintenance issue" required></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                                <i class="fas fa-plus"></i>
                                <span>Submit Request</span>
                            </button>
                        </div>
                    </form>
                    <p class="text-xs text-gray-400 mt-3">All maintenance activities are tracked and visible to management.</p>
                </div>
            </div>
        </main>

        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1); include '../../includes/footer.php'; ?>
    </body>
</html>

