<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Therapists';
$user_role = $_SESSION['pos_user_role'];
$user_name = $_SESSION['pos_user_name'];
$is_demo_mode = isset($_SESSION['pos_demo_mode']) && $_SESSION['pos_demo_mode'];

// Include POS functions
require_once '../includes/pos-functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../assets/js/pos-sidebar.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        success: '#28a745',
                        danger: '#dc3545',
                        warning: '#ffc107',
                        info: '#17a2b8'
                    }
                }
            }
        }
    </script>
    <style>
        /* Responsive layout fixes */
        .main-content {
            margin-left: 0;
            position: relative;
            z-index: 1;
        }
        
        /* Ensure sidebar is above main content */
        #sidebar {
            z-index: 45 !important;
        }
        
        #sidebar-overlay {
            z-index: 35 !important;
        }
        
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        
        <!-- Include POS-specific header and sidebar -->
        <?php include '../includes/pos-header.php'; ?>
        <?php include '../includes/pos-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content pt-20 px-4 pb-4 lg:px-6 lg:pb-6 flex-1 transition-all duration-300">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Spa Therapists Management</h2>
                    <p class="text-gray-600 mt-1">Manage therapist schedules, performance, and availability</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportTherapists()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openAddTherapistModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Therapist
                    </button>
                </div>
            </div>

            <!-- Therapist Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-therapists">8</h3>
                            <p class="text-sm text-gray-600">Total Therapists</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-user-check text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="active-therapists">7</h3>
                            <p class="text-sm text-gray-600">Active Today</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="scheduled-hours">156</h3>
                            <p class="text-sm text-gray-600">Hours This Week</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-star text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="avg-rating">4.8</h3>
                            <p class="text-sm text-gray-600">Average Rating</p>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Therapists Overview</h3>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add New
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center text-gray-500 py-12">
                            <i class="fas fa-cog text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Spa Therapists Management</h3>
                            <p class="text-gray-600">Manage therapist schedules, performance, and availability.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=60&h=60&fit=crop&crop=face" 
                                             alt="Sarah Johnson" class="w-12 h-12 rounded-full object-cover mr-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Sarah Johnson</h4>
                                            <p class="text-sm text-gray-600">Massage Therapist</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Available</span>
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 text-sm mr-1"></i>
                                            <span class="text-sm font-medium">4.9</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=60&h=60&fit=crop&crop=face" 
                                             alt="Mike Chen" class="w-12 h-12 rounded-full object-cover mr-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Mike Chen</h4>
                                            <p class="text-sm text-gray-600">Deep Tissue Specialist</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Busy</span>
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 text-sm mr-1"></i>
                                            <span class="text-sm font-medium">4.7</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <img src="https://images.unsplash.com/photo-1594824804732-aca5c0a4c1a1?w=60&h=60&fit=crop&crop=face" 
                                             alt="Lisa Rodriguez" class="w-12 h-12 rounded-full object-cover mr-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Lisa Rodriguez</h4>
                                            <p class="text-sm text-gray-600">Facial Expert</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Available</span>
                                        <div class="flex items-center">
                                            <i class="fas fa-star text-yellow-400 text-sm mr-1"></i>
                                            <span class="text-sm font-medium">4.8</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>

    <script>
        // Spa module functionality
        // Sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('sidebar-open')) {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('sidebar-open');
                overlay.classList.remove('hidden');
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.remove('sidebar-open');
            overlay.classList.add('hidden');
        }

        // Submenu toggle functionality
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById('submenu-' + menuId);
            const chevron = document.getElementById('chevron-' + menuId);
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Initialize sidebar on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-expand submenus with active items
            document.querySelectorAll('#sidebar ul[id^="submenu-"]').forEach(submenu => {
                const activeItem = submenu.querySelector('a.text-blue-600, a.active, a.text-primary, a.border-blue-500');
                if (activeItem) {
                    submenu.classList.remove('hidden');
                    const menuId = submenu.id.replace('submenu-', '');
                    const chevron = document.getElementById('chevron-' + menuId);
                    if (chevron) {
                        chevron.style.transform = 'rotate(180deg)';
                    }
                }
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.querySelector('[onclick="toggleSidebar()"]');
                
                if (window.innerWidth < 1024 && 
                    !sidebar.contains(event.target) && 
                    !sidebarToggle.contains(event.target) && 
                    sidebar.classList.contains('sidebar-open')) {
                    closeSidebar();
                }
            });

            // Initialize therapists functionality
            initializeTherapists();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Therapists Management Functions
        function initializeTherapists() {
            initializeFilters();
            loadTherapistStatistics();
        }

        function initializeFilters() {
            const searchInput = document.getElementById('search-therapists');
            const specialtyFilter = document.getElementById('specialty-filter');
            const statusFilter = document.getElementById('status-filter');
            const experienceFilter = document.getElementById('experience-filter');

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (specialtyFilter) specialtyFilter.addEventListener('change', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (experienceFilter) experienceFilter.addEventListener('change', applyFilters);
        }

        function loadTherapistStatistics() {
            // Simulate loading therapist statistics
            const stats = {
                total: Math.floor(Math.random() * 10) + 6,
                active: Math.floor(Math.random() * 8) + 5,
                hours: Math.floor(Math.random() * 50) + 120,
                rating: (Math.random() * 0.5 + 4.5).toFixed(1)
            };

            updateStatisticsDisplay(stats);
        }

        function updateStatisticsDisplay(stats) {
            const totalTherapists = document.getElementById('total-therapists');
            const activeTherapists = document.getElementById('active-therapists');
            const scheduledHours = document.getElementById('scheduled-hours');
            const avgRating = document.getElementById('avg-rating');

            if (totalTherapists) totalTherapists.textContent = stats.total;
            if (activeTherapists) activeTherapists.textContent = stats.active;
            if (scheduledHours) scheduledHours.textContent = stats.hours;
            if (avgRating) avgRating.textContent = stats.rating;
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search-therapists')?.value.toLowerCase() || '';
            const specialtyFilter = document.getElementById('specialty-filter')?.value || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';
            const experienceFilter = document.getElementById('experience-filter')?.value || '';

            console.log('Applying therapist filters:', { searchTerm, specialtyFilter, statusFilter, experienceFilter });
            
            // Filter therapist cards
            const therapistCards = document.querySelectorAll('.therapist-card, .bg-white.border');
            let visibleCount = 0;

            therapistCards.forEach(card => {
                const therapistName = card.querySelector('h4')?.textContent.toLowerCase() || '';
                const therapistSpecialty = card.querySelector('p')?.textContent.toLowerCase() || '';
                const therapistStatus = card.querySelector('.bg-green-100, .bg-yellow-100, .bg-gray-100')?.textContent.toLowerCase() || '';

                const matchesSearch = !searchTerm || 
                    therapistName.includes(searchTerm) || 
                    therapistSpecialty.includes(searchTerm);

                const matchesSpecialty = !specialtyFilter || therapistSpecialty.includes(specialtyFilter);
                const matchesStatus = !statusFilter || therapistStatus.includes(statusFilter);

                if (matchesSearch && matchesSpecialty && matchesStatus) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            console.log(`Showing ${visibleCount} therapists`);
        }

        function setViewMode(mode) {
            const gridView = document.getElementById('grid-view');
            const listView = document.getElementById('list-view');
            const gridBtn = document.getElementById('grid-view-btn');
            const listBtn = document.getElementById('list-view-btn');

            if (mode === 'grid') {
                gridView?.classList.remove('hidden');
                listView?.classList.add('hidden');
                gridBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                gridBtn?.classList.add('bg-purple-600', 'text-white');
                listBtn?.classList.remove('bg-purple-600', 'text-white');
                listBtn?.classList.add('bg-gray-200', 'text-gray-600');
            } else {
                gridView?.classList.add('hidden');
                listView?.classList.remove('hidden');
                listBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                listBtn?.classList.add('bg-purple-600', 'text-white');
                gridBtn?.classList.remove('bg-purple-600', 'text-white');
                gridBtn?.classList.add('bg-gray-200', 'text-gray-600');
            }
        }

        function refreshTherapists() {
            showNotification('Refreshing therapists data...', 'info');
            setTimeout(() => {
                loadTherapistStatistics();
                showNotification('Therapists data refreshed successfully!', 'success');
            }, 1000);
        }

        // Therapist Actions
        function openAddTherapistModal() {
            showNotification('Opening add therapist modal...', 'info');
            console.log('Opening add therapist modal');
        }

        function editTherapist(therapistId) {
            showNotification(`Editing therapist ${therapistId}...`, 'info');
            console.log(`Editing therapist: ${therapistId}`);
        }

        function viewTherapistProfile(therapistId) {
            showNotification(`Viewing therapist profile ${therapistId}...`, 'info');
            console.log(`Viewing therapist profile: ${therapistId}`);
        }

        function viewTherapistSchedule(therapistId) {
            showNotification(`Viewing therapist schedule ${therapistId}...`, 'info');
            console.log(`Viewing therapist schedule: ${therapistId}`);
        }

        function exportTherapists() {
            showNotification('Exporting therapists data...', 'info');
            setTimeout(() => {
                showNotification('Therapists data exported successfully!', 'success');
            }, 1500);
        }

        function updateDateTime() {
            const now = new Date();
            const dateElement = document.getElementById('current-date');
            const timeElement = document.getElementById('current-time');
            
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
            
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full`;
            
            switch(type) {
                case 'success':
                    notification.classList.add('bg-green-500');
                    break;
                case 'error':
                    notification.classList.add('bg-red-500');
                    break;
                case 'warning':
                    notification.classList.add('bg-yellow-500');
                    break;
                default:
                    notification.classList.add('bg-purple-500');
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        function addNew() {
            console.log('Adding new spa entry...');
        }

        function searchRecords() {
            console.log('Searching spa records...');
        }

        function viewAnalytics() {
            console.log('Viewing spa analytics...');
        }
    </script>

    <?php include '../includes/pos-footer.php'; ?>
</body>
</html>