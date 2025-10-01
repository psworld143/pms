<?php
session_start();
require_once '../../config/database.php';;
require_once '../../includes/functions.php';
// Check if user is logged in and has manager access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ../../login.php');
    exit();
}

// Get management statistics
$stats = getManagementStats();

// Set page title
$page_title = 'Management Dashboard';

// Include unified navigation (automatically selects based on user role)
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-800">Management Dashboard</h2>
                <div class="text-right">
                    <div id="current-date" class="text-sm text-gray-600"></div>
                    <div id="current-time" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-percentage text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['occupancy_rate']; ?>%</h3>
                            <p class="text-gray-600">Occupancy Rate</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-dollar-sign text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-800">₱<?php echo number_format($stats['today_revenue'], 2); ?></h3>
                            <p class="text-gray-600">Today's Revenue</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['pending_checkins']; ?></h3>
                            <p class="text-gray-600">Pending Check-ins</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-sign-out-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['today_checkouts']; ?></h3>
                            <p class="text-gray-600">Today's Check-outs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Revenue Overview</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Today's Revenue:</span>
                            <span class="font-semibold text-green-600">₱<?php echo number_format($stats['today_revenue'], 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">This Month's Revenue:</span>
                            <span class="font-semibold text-blue-600">₱<?php echo number_format($stats['month_revenue'], 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Average Daily Revenue:</span>
                            <span class="font-semibold text-purple-600">₱<?php echo number_format($stats['month_revenue'] / max(1, date('j')), 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Room Status</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Rooms:</span>
                            <span class="font-semibold"><?php echo $stats['total_rooms']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Occupied Rooms:</span>
                            <span class="font-semibold text-green-600"><?php echo $stats['occupied_rooms']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Available Rooms:</span>
                            <span class="font-semibold text-blue-600"><?php echo $stats['total_rooms'] - $stats['occupied_rooms']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 shadow-md mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="../front-desk/manage-reservations.php" class="flex items-center p-4 bg-blue-50 border-2 border-blue-200 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition-all duration-300">
                        <i class="fas fa-calendar-alt text-blue-600 text-xl mr-3"></i>
                        <span class="font-medium text-blue-800">Manage Reservations</span>
                    </a>
                    <a href="../housekeeping/index.php" class="flex items-center p-4 bg-green-50 border-2 border-green-200 rounded-lg hover:bg-green-100 hover:border-green-300 transition-all duration-300">
                        <i class="fas fa-broom text-green-600 text-xl mr-3"></i>
                        <span class="font-medium text-green-800">Housekeeping</span>
                    </a>
                    <a href="../management/reports-dashboard.php" class="flex items-center p-4 bg-purple-50 border-2 border-purple-200 rounded-lg hover:bg-purple-100 hover:border-purple-300 transition-all duration-300">
                        <i class="fas fa-chart-bar text-purple-600 text-xl mr-3"></i>
                        <span class="font-medium text-purple-800">View Reports</span>
                    </a>
                    <a href="../management/staff.php" class="flex items-center p-4 bg-orange-50 border-2 border-orange-200 rounded-lg hover:bg-orange-100 hover:border-orange-300 transition-all duration-300">
                        <i class="fas fa-users text-orange-600 text-xl mr-3"></i>
                        <span class="font-medium text-orange-800">Staff Management</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Recent Activity</h3>
                    <a href="../management/audit-log.php" class="text-primary hover:text-primary-dark">View All</a>
                </div>
                <div id="recent-activity" class="overflow-x-auto">
                    <!-- Recent activity will be loaded here -->
                </div>
            </div>
        </main>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/manager-dashboard.js"></script>
    
    <?php include '../../includes/footer.php'; ?>