<?php
// VPS Session Fix - Robust session configuration
require_once __DIR__ . '/../vps_session_fix.php';

require_once '../includes/database.php';
require_once 'includes/progress-tracker.php';
require_once 'includes/dynamic-training-manager.php';

// Check if user is logged in (allow all user roles to access tutorials)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize progress tracker and dynamic training manager
$progress_tracker = new TutorialProgressTracker($pdo);
$dynamic_training = new DynamicTrainingManager($pdo);
$user_id = $_SESSION['user_id'];

// Get student progress and statistics
$student_stats = $progress_tracker->getStudentStats($user_id);

// Get all available training modules dynamically
$available_modules = $dynamic_training->getAllModules();
$all_progress = $progress_tracker->getAllProgress($user_id);

// Set page title
$page_title = 'Interactive Tutorials';
$user_name = $_SESSION['user_name'] ?? 'Student';
$user_role = $_SESSION['user_role'] ?? 'student';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel PMS Training System</title>
    <link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Custom styles for tutorial system */
        .tutorial-highlight {
            position: relative;
            z-index: 1000;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5);
            border-radius: 8px;
        }
        
        .tutorial-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            display: none;
        }
        
        .tutorial-step {
            position: fixed;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            max-width: 400px;
            padding: 24px;
        }
        
        .progress-bar {
            transition: width 0.3s ease;
        }
        
        .module-card {
            transition: all 0.3s ease;
        }
        
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Sidebar responsive styles are handled by the sidebar component */
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50">
    
    <!-- Top Navigation Bar -->
    <nav class="fixed top-0 left-0 right-0 bg-white shadow-md z-30 lg:ml-64">
        <div class="flex items-center justify-between px-4 py-3">
            <!-- Mobile Menu Button -->
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-bars text-gray-600"></i>
            </button>
            
            <!-- Page Title -->
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-graduation-cap text-white text-sm"></i>
                </div>
                <h1 class="text-lg font-semibold text-gray-800"><?php echo $page_title; ?></h1>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div id="current-date" class="text-sm text-gray-600"></div>
                    <div id="current-time" class="text-sm text-gray-600"></div>
                            </div>
                <div class="relative">
                    <button onclick="toggleUserDropdown()" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700"><?php echo $user_name; ?></span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden">
                        <div class="py-2">
                            <a href="../booking/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="../booking/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-tachometer-alt mr-2"></i>PMS Dashboard
                            </a>
                            <hr class="my-2">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                            </div>
                        </div>
    </nav>

    <!-- Include Tutorial Sidebar -->
    <?php include 'includes/tutorial-sidebar.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 mt-16 p-4 lg:p-6 transition-all duration-300">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Interactive Tutorials</h2>
                    <p class="text-gray-600">Learn hotel operations through guided, hands-on practice</p>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>New Tutorial
                    </button>
                    <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                        </div>
                    </div>
                </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo $student_stats['total_modules'] ?? 0; ?></h3>
                        <p class="text-gray-600">Total Modules</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo $student_stats['completed_modules'] ?? 0; ?></h3>
                        <p class="text-gray-600">Completed</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo $student_stats['in_progress_modules'] ?? 0; ?></h3>
                        <p class="text-gray-600">In Progress</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-percentage text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($student_stats['completion_rate'] ?? 0, 1); ?>%</h3>
                        <p class="text-gray-600">Completion Rate</p>
                    </div>
                </div>
            </div>
        </div>

            <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Module Type</label>
                        <select class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option>All Modules</option>
                            <option>POS System</option>
                            <option>Inventory</option>
                            <option>Booking</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                        <select class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option>All Levels</option>
                            <option>Beginner</option>
                            <option>Intermediate</option>
                            <option>Advanced</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-refresh mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Tutorial Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- E2E Test Module -->
            <div class="module-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-cash-register text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">E2E Test Module</h3>
                            <p class="text-sm text-gray-500">POS • beginner</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">NOT STARTED</span>
    </div>

                    <p class="text-gray-600 mb-4">Test module for end-to-end testing</p>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Progress</span>
                            <span>0.0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                        <span><i class="fas fa-clock mr-1"></i>30 min</span>
                        <span><i class="fas fa-star mr-1"></i>0.0</span>
                    </div>
                    
                    <a href="training.php?module=<?php echo urlencode('E2E Test Module'); ?>&type=pos" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors inline-block text-center">
                        Start Tutorial
                    </a>
                </div>
            </div>

            <!-- POS System Basics -->
            <div class="module-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-cash-register text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">POS System Basics</h3>
                            <p class="text-sm text-gray-500">POS • beginner</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-600 rounded-full">IN PROGRESS</span>
                    </div>
                    
                    <p class="text-gray-600 mb-4">Learn fundamental point of sale operations including order processing, payment handling, and receipt generation</p>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Progress</span>
                            <span>25.0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: 25%"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                        <span><i class="fas fa-clock mr-1"></i>30 min</span>
                        <span><i class="fas fa-star mr-1"></i>85.5</span>
                    </div>
                    
                    <a href="training.php?module=<?php echo urlencode('POS System Basics'); ?>&type=pos" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors inline-block text-center">
                        Continue Tutorial
                    </a>
                                </div>
                            </div>

            <!-- Enterprise POS Operations -->
            <div class="module-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-cash-register text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">Enterprise POS Operations</h3>
                            <p class="text-sm text-gray-500">POS • advanced</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">NOT STARTED</span>
                        </div>
                        
                    <p class="text-gray-600 mb-4">Multi-location management, advanced reporting, and system integration</p>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progress</span>
                            <span>0.0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: 0%"></div>
                        </div>
                            </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                        <span><i class="fas fa-clock mr-1"></i>45 min</span>
                        <span><i class="fas fa-star mr-1"></i>0.0</span>
                        </div>
                        
                    <a href="training.php?module=<?php echo urlencode('Enterprise POS Operations'); ?>&type=pos" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors inline-block text-center">
                        Start Tutorial
                    </a>
                </div>
                            </div>

            <!-- Inventory Management Fundamentals -->
            <div class="module-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-boxes text-blue-600 text-xl"></i>
                            </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">Inventory Management Fundamentals</h3>
                            <p class="text-sm text-gray-500">INVENTORY • beginner</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">NOT STARTED</span>
                    </div>
                    
                    <p class="text-gray-600 mb-4">Master stock control, supplier relations, and automated reordering systems</p>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Progress</span>
                            <span>0.0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                        <span><i class="fas fa-clock mr-1"></i>40 min</span>
                        <span><i class="fas fa-star mr-1"></i>0.0</span>
                    </div>
                    
                    <a href="training.php?module=<?php echo urlencode('Inventory Management Fundamentals'); ?>&type=inventory" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors inline-block text-center">
                        Start Tutorial
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                // Open sidebar
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
            } else {
                // Close sidebar
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
            }
        }

        // User dropdown toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('user-dropdown');
            const button = e.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleUserDropdown') === -1) {
                dropdown.classList.add('hidden');
            }
        });

        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update date/time every minute
        updateDateTime();
        setInterval(updateDateTime, 60000);

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            // On desktop, ensure sidebar is visible and overlay is hidden
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.add('hidden');
            }
        });

        // Initialize sidebar state based on screen size
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth >= 1024) {
                // Desktop: show sidebar
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.add('hidden');
            } else {
                // Mobile: hide sidebar
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
            }
        });

        // Close sidebar when clicking overlay
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebar-overlay');
                    
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    overlay.classList.add('hidden');
                });
            }
        });

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>