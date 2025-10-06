<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Only managers
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Maintenance Management';

// Load open maintenance requests
$requests = [];
try {
    $stmt = $pdo->query("SELECT mr.*, r.room_number FROM maintenance_requests mr LEFT JOIN rooms r ON mr.room_id=r.id ORDER BY mr.created_at DESC LIMIT 50");
    $requests = $stmt->fetchAll();
} catch (Exception $e) { error_log('Load maintenance error: '.$e->getMessage()); }

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Maintenance Management</h2>
                <button id="refreshBtn" class="text-primary"><i class="fas fa-sync-alt"></i></button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Requests</h3>
                    <div id="requestsList" class="divide-y">
                        <?php if (empty($requests)): ?>
                            <div class="text-gray-500 text-center py-8">No requests found.</div>
                        <?php else: ?>
                            <?php foreach ($requests as $rq): ?>
                                <div class="py-3 flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800">Room <?php echo htmlspecialchars($rq['room_number'] ?? $rq['room_id']); ?> · <?php echo ucfirst($rq['issue_type'] ?? $rq['request_type'] ?? 'maintenance'); ?></div>
                                        <div class="text-xs text-gray-500 mb-1">Priority: <?php echo ucfirst($rq['priority'] ?? 'medium'); ?> · Status: <?php echo ucfirst($rq['status']); ?> · <?php echo date('M j, Y g:i A', strtotime($rq['created_at'])); ?></div>
                                        <div class="text-gray-700"><?php echo nl2br(htmlspecialchars($rq['description'])); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Create Request</h3>
                    <form id="createForm" method="post" action="../../api/create-maintenance-request.php" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                            <select name="room_id" class="w-full border-gray-300 rounded-md" required>
                                <?php 
                                try { 
                                    $rs = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number");
                                    foreach ($rs->fetchAll() as $room) {
                                        echo '<option value="'.(int)$room['id'].'">Room '.htmlspecialchars($room['room_number']).'</option>';
                                    }
                                } catch (Exception $e) { echo '<option value="">Error loading rooms</option>'; }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Issue Type</label>
                            <select name="issue_type" class="w-full border-gray-300 rounded-md" required>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="hvac">HVAC</option>
                                <option value="furniture">Furniture</option>
                                <option value="appliances">Appliances</option>
                                <option value="structural">Structural</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" class="w-full border-gray-300 rounded-md" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="4" class="w-full border-gray-300 rounded-md" required></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                                <i class="fas fa-plus mr-2"></i>Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <?php include '../../includes/footer.php'; ?>
<?php // end file ?>

