<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../vps_session_fix.php';

require_once __DIR__ . '/config/database.php';

// Check if user is logged in and has housekeeping role
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user role is housekeeping
if ($_SESSION['user_role'] !== 'housekeeping') {
    header('Location: login.php?error=access_denied');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Housekeeping Staff';
$user_role = $_SESSION['user_role'];

// Initialize inventory database
$inventory_db = new InventoryDatabase();

// Get housekeeping-specific statistics
$stats = [
    'completed_tasks' => 0,
    'room_inventory_items' => 0
];

// Check if inventory tables exist
$tables_exist = $inventory_db->checkInventoryTables();

if ($tables_exist) {
    try {
        // Get completed tasks count (from room inventory updates)
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM room_inventory WHERE last_updated >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $stats['completed_tasks'] = $stmt->fetch()['count'];
        
        // Get room inventory items count
        $stmt = $inventory_db->getConnection()->query("SELECT COUNT(*) as count FROM inventory_items WHERE category = 'Room Supplies' OR category = 'Housekeeping'");
        $stats['room_inventory_items'] = $stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        error_log("Error getting housekeeping stats: " . $e->getMessage());
    }
}

// Requests module removed - no recent requests data needed

// Get low stock room supplies
$low_stock_items = [];
if ($tables_exist) {
    try {
        $stmt = $inventory_db->getConnection()->query("
            SELECT name, current_stock, minimum_stock, unit_price 
            FROM inventory_items 
            WHERE (category = 'Room Supplies' OR category = 'Housekeeping') 
            AND current_stock <= minimum_stock 
            ORDER BY (current_stock / minimum_stock) ASC 
            LIMIT 5
        ");
        $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting low stock items: " . $e->getMessage());
    }
}

// Demo data for when tables don't exist
if (empty($recent_requests)) {
    $recent_requests = [
        ['item_name' => 'Bath Towels', 'quantity_requested' => 20, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')],
        ['item_name' => 'Shampoo Bottles', 'quantity_requested' => 15, 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
        ['item_name' => 'Coffee Cups', 'quantity_requested' => 10, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))]
    ];
}

if (empty($low_stock_items)) {
    $low_stock_items = [
        ['name' => 'Bath Towels', 'current_stock' => 5, 'minimum_stock' => 10, 'unit_price' => 15.00],
        ['name' => 'Shampoo Bottles', 'current_stock' => 3, 'minimum_stock' => 8, 'unit_price' => 8.50],
        ['name' => 'Coffee Cups', 'current_stock' => 7, 'minimum_stock' => 15, 'unit_price' => 5.00]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housekeeping Dashboard - Hotel PMS Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script></script>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Include Header -->
        <?php include 'includes/inventory-header.php'; ?>
        
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar-inventory.php'; ?>
        
        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Housekeeping Dashboard Header -->
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                            🧹 Housekeeping Dashboard
                        </h1>
                        <p class="text-gray-600 mt-1 sm:mt-2">
                            Daily supply management and room inventory updates
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-user mr-1"></i>
                            <?php echo htmlspecialchars($user_name); ?>
                        </span>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-broom mr-1"></i>
                            Housekeeping Staff
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards: denser layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 sm:gap-6 mb-6 sm:mb-8">

                <!-- Completed Tasks -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $stats['completed_tasks']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Room Items -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bed text-blue-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Room Items</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $stats['room_inventory_items']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- My Rooms (live from API if available) -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-door-open text-indigo-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">My Rooms</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="stat-my-rooms">—</p>
                        </div>
                    </div>
                </div>

                <!-- Items Used Today -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-box-open text-purple-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Items Used Today</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="stat-items-used">—</p>
                        </div>
                    </div>
                </div>

                <!-- Missing/Low Items -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Rooms Needing Restock</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="stat-missing-items">—</p>
                        </div>
                    </div>
                </div>

                <!-- Tasks Due Today (static counter placeholder) -->
                <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-gray-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-tasks text-gray-600 text-xl sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-sm font-medium text-gray-500">Tasks Today</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="stat-tasks-today">6</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow mb-6 sm:mb-8">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">🧹 Quick Actions</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <a href="transactions.php?action=record" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-clipboard-check text-2xl mb-2"></i>
                            <p class="font-medium">Record Usage</p>
                            <p class="text-xs opacity-90">Log item usage</p>
                        </a>
                        <a href="room-inventory.php" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-bed text-2xl mb-2"></i>
                            <p class="font-medium">Room Inventory</p>
                            <p class="text-xs opacity-90">Update room items</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid: add more panels -->
            <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6 sm:gap-8">

                <!-- Low Stock Alert -->
                <div class="bg-white rounded-lg shadow xl:col-span-1">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Low Stock Alert</h3>
                            <a href="items.php?filter=low_stock" class="text-sm text-red-600 hover:text-red-700">View All</a>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="space-y-3">
                            <?php if (empty($low_stock_items)): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                                    <p>All room supplies are well stocked!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($low_stock_items as $item): ?>
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </p>
                                            <div class="flex items-center mt-1">
                                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                                                    <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo min(100, ($item['current_stock'] / $item['minimum_stock']) * 100); ?>%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500">
                                                    <?php echo $item['current_stock']; ?>/<?php echo $item['minimum_stock']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Low Stock
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- My Rooms Overview -->
                <div class="bg-white rounded-lg shadow 2xl:col-span-2">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">My Rooms Overview</h3>
                            <a href="enhanced-room-inventory.php" class="text-sm text-blue-600 hover:text-blue-700">Open Inventory</a>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div id="my-rooms-list" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="text-gray-500 text-sm">Loading rooms…</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics -->
            <div class="mt-6 grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Items Used - Last 7 Days</h3>
                        <span class="text-xs text-gray-500" id="chart-usage-range">7 days</span>
                    </div>
                    <div class="p-4 sm:p-6">
                        <canvas id="usageChart" height="120"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Low Stock by Category</h3>
                        <a href="items.php?filter=low_stock" class="text-sm text-red-600 hover:text-red-700">Manage</a>
                    </div>
                    <div class="p-4 sm:p-6">
                        <canvas id="lowStockChart" height="120"></canvas>
                        <div class="text-xs text-gray-500 mt-2">Tip: click a category to open Items filtered to that category.</div>
                    </div>
                </div>
            </div>

            <!-- Daily Tasks Checklist -->
            <div class="mt-6 sm:mt-8">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">📋 Daily Tasks Checklist</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="tasks-list">
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input id="task-check-supplies" type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label for="task-check-supplies" class="ml-3 text-sm text-gray-700">Check room supplies</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input id="task-update-usage" type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label for="task-update-usage" class="ml-3 text-sm text-gray-700">Update inventory usage</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input id="task-replenish" type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label for="task-replenish" class="ml-3 text-sm text-gray-700">Replenish low stock rooms</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input id="task-confirm" type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label for="task-confirm" class="ml-3 text-sm text-gray-700">Confirm received supplies</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input id="task-room-status" type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label for="task-room-status" class="ml-3 text-sm text-gray-700">Update room status</label>
                            </div>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <input id="task-maintenance" type="checkbox" class="h-4 w-4 text-purple-600 rounded border-gray-300">
                                <label for="task-maintenance" class="ml-3 text-sm text-gray-700">Report maintenance issues</label>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button id="reset-tasks" class="text-xs px-3 py-1.5 border rounded-md text-gray-600 hover:bg-gray-50">Reset checklist</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript for interactive features -->
    <script>
        // Handle URL parameters for quick actions
        function handleUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            
            if (action === 'create') {
                // Open create request modal or redirect
                // Requests module removed
            } else if (action === 'record') {
                // Open record usage modal or redirect
                window.location.href = 'transactions.php?action=record';
            }
        }
        
        // Fetch live stats and rooms
        async function fetchHousekeepingStats() {
            try {
                const res = await fetch('api/get-housekeeping-stats.php', { credentials: 'include' });
                const data = await res.json();
                if (data && data.success) {
                    const s = data.data || data.statistics || {};
                    document.getElementById('stat-my-rooms').textContent = s.total_rooms ?? '0';
                    document.getElementById('stat-items-used').textContent = s.total_items ?? '0';
                    document.getElementById('stat-missing-items').textContent = s.missing_items ?? '0';
                }
            } catch (e) { /* ignore */ }
        }

        async function fetchMyRooms() {
            try {
                // Load first floor as approximation; or list top rooms by stock issues
                const res = await fetch('api/get-hotel-floors.php', { credentials: 'include' });
                const j = await res.json();
                const floors = (j && j.floors) || [];
                const first = floors[0] && (floors[0].floor_number || floors[0].id || floors[0]);
                if (!first) { document.getElementById('my-rooms-list').innerHTML = '<div class="text-gray-500 text-sm">No floors found.</div>'; return; }
                const r = await fetch('api/get-rooms-for-floor.php?floor=' + encodeURIComponent(first), { credentials: 'include' });
                const rr = await r.json();
                const rooms = (rr && rr.rooms) || [];
                const container = document.getElementById('my-rooms-list');
                container.innerHTML = '';
                rooms.slice(0, 6).forEach(room => {
                    const status = room.stock_status || 'unknown';
                    const badge = status === 'fully_stocked' ? 'bg-green-100 text-green-800' : (status === 'needs_restocking' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    const div = document.createElement('div');
                    div.className = 'p-3 border rounded-lg flex items-center justify-between';
                    div.innerHTML = '<div><div class="font-medium text-gray-800">Room ' + (room.room_number || room.id) + '</div><div class="text-xs text-gray-500 capitalize">' + (room.room_type || 'standard') + '</div></div>' +
                                    '<span class="px-2 py-1 rounded-full text-xs font-semibold ' + badge + '">' + (status.replace('_',' ') ) + '</span>';
                    container.appendChild(div);
                });
            } catch (e) { /* ignore */ }
        }

        // Charts
        let usageChart, lowStockChart;
        async function renderUsageChart(){
            try {
                const r = await fetch('api/get-usage-reports.php', { credentials: 'include' });
                const j = await r.json();
                const reports = (j && j.reports) || [];
                // group by day (last 7 days)
                const map = {};
                const today = new Date();
                for(let i=6;i>=0;i--){ const d = new Date(today); d.setDate(today.getDate()-i); const k = d.toISOString().substring(0,10); map[k]=0; }
                reports.forEach(rep=>{ const k = (rep.date_used || rep.created_at || '').substring(0,10); if (map[k] !== undefined) { map[k] += parseFloat(rep.quantity||0); } });
                const labels = Object.keys(map);
                const data = Object.values(map);
                const ctx = document.getElementById('usageChart').getContext('2d');
                if (usageChart) usageChart.destroy();
                usageChart = new Chart(ctx, { type:'bar', data:{ labels, datasets:[{ label:'Qty Used', data, backgroundColor:'#6366F1' }] }, options:{ responsive:true, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 }}}}});
            } catch(e) { /* ignore */ }
        }

        async function renderLowStockChart(){
            try {
                // Reuse low_stock_items list rendered server-side fallback; fetch items for accuracy
                const res = await fetch('api/get-inventory-items.php', { credentials:'include' });
                const j = await res.json();
                const items = (j && (j.items || j.inventory_items)) || [];
                // Normalize stock fields
                const low = items.filter(it=>{
                    const curr = (it.current_stock!==undefined) ? Number(it.current_stock) : (it.quantity!==undefined ? Number(it.quantity) : 0);
                    const min = (it.minimum_stock!==undefined) ? Number(it.minimum_stock) : (it.minimum!==undefined ? Number(it.minimum) : 0);
                    return curr <= min;
                });
                const byCat = {};
                low.forEach(it=>{ const c = it.category_name || it.category || 'Uncategorized'; byCat[c]=(byCat[c]||0)+1; });
                const labels = Object.keys(byCat).slice(0,8);
                const data = labels.map(k=>byCat[k]);
                const ctx = document.getElementById('lowStockChart').getContext('2d');
                if (lowStockChart) lowStockChart.destroy();
                if (labels.length === 0) {
                    // Draw empty state
                    ctx.font = '14px Arial';
                    ctx.fillStyle = '#6B7280';
                    ctx.fillText('No low stock items found.', 10, 20);
                    return;
                }
                lowStockChart = new Chart(ctx, { type:'doughnut', data:{ labels, datasets:[{ data, backgroundColor:['#F87171','#FB923C','#FBBF24','#34D399','#60A5FA','#A78BFA','#F472B6','#F59E0B'] }] }, options:{ responsive:true, plugins:{ legend:{ position:'bottom' }}, onClick:(evt, els)=>{ if (!els || !els.length) return; const idx = els[0].index; const cat = labels[idx]; if (cat){ const url = new URL(window.location.origin + window.location.pathname.replace('housekeeping-dashboard.php','items.php')); url.searchParams.set('filter','low_stock'); url.searchParams.set('category',cat); window.location.href = url.toString(); } } }});
            } catch(e) { /* ignore */ }
        }

        // Call on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleUrlParameters();
            fetchHousekeepingStats();
            fetchMyRooms();
            renderUsageChart();
            renderLowStockChart();
            
            // Checklist persistence using localStorage
            const TASK_KEY = 'hk_tasks_v1';
            const state = JSON.parse(localStorage.getItem(TASK_KEY) || '{}');
            Object.keys(state).forEach(id=>{ const el = document.getElementById(id); if (el){ el.checked = !!state[id]; if (el.checked){ el.parentElement.classList.add('bg-green-50','border-green-200'); el.parentElement.classList.remove('bg-gray-50'); } } });
            document.getElementById('tasks-list').addEventListener('change', function(e){ const t = e.target; if (t && t.type==='checkbox'){ state[t.id] = t.checked; localStorage.setItem(TASK_KEY, JSON.stringify(state)); if (t.checked){ t.parentElement.classList.add('bg-green-50','border-green-200'); t.parentElement.classList.remove('bg-gray-50'); } else { t.parentElement.classList.remove('bg-green-50','border-green-200'); t.parentElement.classList.add('bg-gray-50'); } }});
            document.getElementById('reset-tasks').addEventListener('click', function(){ localStorage.removeItem(TASK_KEY); document.querySelectorAll('#tasks-list input[type="checkbox"]').forEach(cb=>{ cb.checked=false; cb.dispatchEvent(new Event('change')); }); });
        });
    </script>
</body>
</html>


