<?php
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

$page_title = 'Guest Communication';

// Load recent feedback (last 20)
$recent_feedback = [];
try {
    $stmt = $pdo->query("SELECT gf.*, g.first_name, g.last_name FROM guest_feedback gf JOIN guests g ON gf.guest_id=g.id ORDER BY gf.created_at DESC LIMIT 20");
    $recent_feedback = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Load feedback error: '.$e->getMessage());
}

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Guest Communication Center</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Broadcast Panel -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Broadcast to Staff</h3>
                    <form id="broadcastForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                                <select name="target" id="target" class="w-full border-gray-300 rounded-md" required>
                                    <option value="front_desk">Front Desk</option>
                                    <option value="housekeeping">Housekeeping</option>
                                    <option value="manager">Managers</option>
                                    <option value="all">All Staff</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select name="priority" id="priority" class="w-full border-gray-300 rounded-md" required>
                                    <option value="info">Info</option>
                                    <option value="success">Success</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" id="title" class="w-full border-gray-300 rounded-md" placeholder="Announcement title" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea id="message" rows="4" class="w-full border-gray-300 rounded-md" placeholder="Write your message..." required></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                                <i class="fas fa-paper-plane mr-2"></i>Send Broadcast
                            </button>
                        </div>
                    </form>
                    <div id="broadcastResult" class="mt-3 text-sm"></div>
                </div>

                <!-- Recent Guest Feedback -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Guest Feedback</h3>
                        <a href="../guests/feedback.php" class="text-primary">Manage Feedback</a>
                    </div>
                    <div class="divide-y">
                        <?php if (empty($recent_feedback)): ?>
                            <div class="text-gray-500 text-center py-8">No feedback found.</div>
                        <?php else: ?>
                            <?php foreach ($recent_feedback as $fb): ?>
                                <div class="py-3 flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($fb['first_name'].' '.$fb['last_name']); ?>
                                            <span class="ml-2 text-xs px-2 py-1 rounded <?php echo $fb['feedback_type']==='complaint'?'bg-red-100 text-red-700':($fb['feedback_type']==='compliment'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-700'); ?>">
                                                <?php echo ucfirst($fb['feedback_type']); ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mb-1"><?php echo date('M j, Y g:i A', strtotime($fb['created_at'])); ?> · <?php echo ucfirst($fb['category']); ?></div>
                                        <div class="text-gray-700"><?php echo nl2br(htmlspecialchars($fb['comments'])); ?></div>
                                    </div>
                                    <div class="pl-3">
                                        <button class="text-sm text-blue-600 hover:underline" onclick="prefillResponse('<?php echo htmlspecialchars(addslashes(substr($fb['comments'],0,120))); ?>')">Respond</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        <?php include '../../includes/footer.php'; ?>

        <script>
        function prefillResponse(text){
            document.getElementById('title').value = 'Guest Feedback Response';
            document.getElementById('message').value = 'Please review and address: ' + text + '...';
            document.getElementById('target').value = 'front_desk';
            window.scrollTo({top:0, behavior:'smooth'});
        }

        document.getElementById('broadcastForm').addEventListener('submit', async (e)=>{
            e.preventDefault();
            const payload = {
                target: document.getElementById('target').value,
                priority: document.getElementById('priority').value,
                title: document.getElementById('title').value,
                message: document.getElementById('message').value
            };
            const res = await fetch('../../api/send-broadcast.php',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            const box = document.getElementById('broadcastResult');
            if(json.success){
                box.className='mt-3 text-sm text-green-600';
                box.textContent = json.message || 'Broadcast sent';
                (document.getElementById('broadcastForm')).reset();
            }else{
                box.className='mt-3 text-sm text-red-600';
                box.textContent = json.message || 'Failed to send';
            }
        });
        </script>
<?php // end file ?>

