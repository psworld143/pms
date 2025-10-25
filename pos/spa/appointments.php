<?php
session_start();

// Check if user is logged in to POS
if (!isset($_SESSION['pos_user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Appointments';
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
                    <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Spa Appointments Management</h2>
                    <p class="text-gray-600 mt-1">Schedule, manage, and track spa appointments and bookings</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div id="current-date" class="text-sm text-gray-600"></div>
                        <div id="current-time" class="text-sm text-gray-600"></div>
                    </div>
                    <button onclick="exportAppointments()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="openNewAppointmentModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>New Appointment
                    </button>
                </div>
                </div>

            <!-- Appointment Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="total-appointments">18</h3>
                            <p class="text-sm text-gray-600">Today's Appointments</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-user-check text-green-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="completed-appointments">12</h3>
                            <p class="text-sm text-gray-600">Completed Today</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="pending-appointments">4</h3>
                            <p class="text-sm text-gray-600">Pending Today</p>
                        </div>
                        </div>
                    </div>
                    
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-900" id="cancelled-appointments">2</h3>
                            <p class="text-sm text-gray-600">Cancelled Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search-appointments" class="block text-sm font-medium text-gray-700 mb-2">Search Appointments</label>
                        <div class="relative">
                            <input type="text" id="search-appointments" placeholder="Search by client name, service, or therapist..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="lg:w-48">
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                        <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <label for="therapist-filter" class="block text-sm font-medium text-gray-700 mb-2">Therapist</label>
                        <select id="therapist-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">All Therapists</option>
                            <option value="sarah">Sarah</option>
                            <option value="mike">Mike</option>
                            <option value="lisa">Lisa</option>
                            <option value="anna">Anna</option>
                        </select>
                    </div>
                    <div class="lg:w-48">
                        <label for="date-filter" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="date-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="today" selected>Today</option>
                            <option value="tomorrow">Tomorrow</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h3 class="text-lg font-semibold text-gray-800">Appointment Views</h3>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-600">View:</label>
                            <button onclick="setViewMode('calendar')" id="calendar-view-btn" class="view-mode-btn bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-calendar-alt mr-2"></i>Calendar
                            </button>
                            <button onclick="setViewMode('list')" id="list-view-btn" class="view-mode-btn bg-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                                <i class="fas fa-list mr-2"></i>List
                            </button>
                        </div>
                        <button onclick="refreshAppointments()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                        </div>
                    </div>
                </div>

            <!-- Calendar View -->
            <div id="calendar-view" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Appointment Calendar</h3>
                    <div class="flex items-center space-x-2">
                        <button onclick="previousWeek()" class="p-2 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="current-week" class="text-sm font-medium text-gray-700">January 15-21, 2024</span>
                        <button onclick="nextWeek()" class="p-2 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Weekly Calendar Grid -->
                <div class="grid grid-cols-8 gap-1">
                    <!-- Time column header -->
                    <div class="p-2 text-sm font-medium text-gray-500">Time</div>
                    
                    <!-- Day headers -->
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Mon 15</div>
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Tue 16</div>
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Wed 17</div>
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Thu 18</div>
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Fri 19</div>
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Sat 20</div>
                    <div class="p-2 text-sm font-medium text-center text-gray-500">Sun 21</div>
                    
                    <!-- Time slots -->
                    <div class="col-span-8 grid grid-cols-8 gap-1">
                        <!-- 9:00 AM -->
                        <div class="p-2 text-sm text-gray-600 border-t">9:00 AM</div>
                        <div class="p-2 border border-gray-200 bg-green-50 cursor-pointer hover:bg-green-100" onclick="openTimeSlot('mon-9am')">
                            <div class="text-xs font-medium text-green-800">Sarah - Massage</div>
                            <div class="text-xs text-green-600">John Doe</div>
                        </div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200 bg-blue-50 cursor-pointer hover:bg-blue-100" onclick="openTimeSlot('wed-9am')">
                            <div class="text-xs font-medium text-blue-800">Lisa - Facial</div>
                            <div class="text-xs text-blue-600">Jane Smith</div>
                        </div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        
                        <!-- 10:00 AM -->
                        <div class="p-2 text-sm text-gray-600 border-t">10:00 AM</div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200 bg-yellow-50 cursor-pointer hover:bg-yellow-100" onclick="openTimeSlot('tue-10am')">
                            <div class="text-xs font-medium text-yellow-800">Mike - Deep Tissue</div>
                            <div class="text-xs text-yellow-600">Bob Wilson</div>
                        </div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200 bg-purple-50 cursor-pointer hover:bg-purple-100" onclick="openTimeSlot('thu-10am')">
                            <div class="text-xs font-medium text-purple-800">Anna - Hot Stone</div>
                            <div class="text-xs text-purple-600">Mary Brown</div>
                        </div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        
                        <!-- 11:00 AM -->
                        <div class="p-2 text-sm text-gray-600 border-t">11:00 AM</div>
                        <div class="p-2 border border-gray-200 bg-red-50 cursor-pointer hover:bg-red-100" onclick="openTimeSlot('mon-11am')">
                            <div class="text-xs font-medium text-red-800">Sarah - Massage</div>
                            <div class="text-xs text-red-600">CANCELLED</div>
                        </div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        
                        <!-- 12:00 PM -->
                        <div class="p-2 text-sm text-gray-600 border-t">12:00 PM</div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200 bg-green-50 cursor-pointer hover:bg-green-100" onclick="openTimeSlot('wed-12pm')">
                            <div class="text-xs font-medium text-green-800">Lisa - Facial</div>
                            <div class="text-xs text-green-600">Alice Green</div>
                        </div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                        <div class="p-2 border border-gray-200"></div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div id="list-view" class="hidden">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Appointment List</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Therapist</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">JD</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">John Doe</div>
                                                <div class="text-sm text-gray-500">john@email.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Swedish Massage</div>
                                        <div class="text-sm text-gray-500">$120</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Sarah
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Mon, Jan 15<br>9:00 AM
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        60 min
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Confirmed
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editAppointment('1')" class="text-purple-600 hover:text-purple-900 mr-3">Edit</button>
                                        <button onclick="cancelAppointment('1')" class="text-red-600 hover:text-red-900 mr-3">Cancel</button>
                                        <button onclick="completeAppointment('1')" class="text-green-600 hover:text-green-900">Complete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

            // Initialize appointments functionality
            initializeAppointments();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        // Appointments Management Functions
        function initializeAppointments() {
            initializeFilters();
            initializeViewControls();
            loadAppointmentStatistics();
        }

        function initializeFilters() {
            const searchInput = document.getElementById('search-appointments');
            const statusFilter = document.getElementById('status-filter');
            const therapistFilter = document.getElementById('therapist-filter');
            const dateFilter = document.getElementById('date-filter');

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (therapistFilter) therapistFilter.addEventListener('change', applyFilters);
            if (dateFilter) dateFilter.addEventListener('change', applyFilters);
        }

        function initializeViewControls() {
            setViewMode('calendar'); // Default to calendar view
        }

        function loadAppointmentStatistics() {
            // Simulate loading appointment statistics
            const stats = {
                total: Math.floor(Math.random() * 25) + 15,
                completed: Math.floor(Math.random() * 20) + 10,
                pending: Math.floor(Math.random() * 10) + 2,
                cancelled: Math.floor(Math.random() * 5) + 1
            };

            updateStatisticsDisplay(stats);
        }

        function updateStatisticsDisplay(stats) {
            const totalAppointments = document.getElementById('total-appointments');
            const completedAppointments = document.getElementById('completed-appointments');
            const pendingAppointments = document.getElementById('pending-appointments');
            const cancelledAppointments = document.getElementById('cancelled-appointments');

            if (totalAppointments) totalAppointments.textContent = stats.total;
            if (completedAppointments) completedAppointments.textContent = stats.completed;
            if (pendingAppointments) pendingAppointments.textContent = stats.pending;
            if (cancelledAppointments) cancelledAppointments.textContent = stats.cancelled;
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search-appointments')?.value.toLowerCase() || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';
            const therapistFilter = document.getElementById('therapist-filter')?.value || '';
            const dateFilter = document.getElementById('date-filter')?.value || '';

            console.log('Applying filters:', { searchTerm, statusFilter, therapistFilter, dateFilter });
            
            // Filter calendar appointments
            const calendarSlots = document.querySelectorAll('#calendar-view .cursor-pointer');
            let visibleCount = 0;

            calendarSlots.forEach(slot => {
                const therapistName = slot.querySelector('.text-xs.font-medium')?.textContent.toLowerCase() || '';
                const clientName = slot.querySelector('.text-xs:not(.font-medium)')?.textContent.toLowerCase() || '';
                const slotStatus = slot.classList.contains('bg-red-50') ? 'cancelled' : 
                                 slot.classList.contains('bg-yellow-50') ? 'in_progress' : 'confirmed';

                const matchesSearch = !searchTerm || 
                    therapistName.includes(searchTerm) || 
                    clientName.includes(searchTerm);

                const matchesStatus = !statusFilter || slotStatus.includes(statusFilter);
                const matchesTherapist = !therapistFilter || therapistName.includes(therapistFilter);

                if (matchesSearch && matchesStatus && matchesTherapist) {
                    slot.style.display = '';
                    visibleCount++;
                } else {
                    slot.style.display = 'none';
                }
            });

            console.log(`Showing ${visibleCount} appointments`);
        }

        function setViewMode(mode) {
            const calendarView = document.getElementById('calendar-view');
            const listView = document.getElementById('list-view');
            const calendarBtn = document.getElementById('calendar-view-btn');
            const listBtn = document.getElementById('list-view-btn');

            if (mode === 'calendar') {
                calendarView?.classList.remove('hidden');
                listView?.classList.add('hidden');
                calendarBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                calendarBtn?.classList.add('bg-purple-600', 'text-white');
                listBtn?.classList.remove('bg-purple-600', 'text-white');
                listBtn?.classList.add('bg-gray-200', 'text-gray-600');
            } else {
                calendarView?.classList.add('hidden');
                listView?.classList.remove('hidden');
                listBtn?.classList.remove('bg-gray-200', 'text-gray-600');
                listBtn?.classList.add('bg-purple-600', 'text-white');
                calendarBtn?.classList.remove('bg-purple-600', 'text-white');
                calendarBtn?.classList.add('bg-gray-200', 'text-gray-600');
            }
        }

        function refreshAppointments() {
            showNotification('Refreshing appointments...', 'info');
            setTimeout(() => {
                loadAppointmentStatistics();
                showNotification('Appointments refreshed successfully!', 'success');
            }, 1000);
        }

        // Calendar Navigation
        function previousWeek() {
            showNotification('Loading previous week...', 'info');
            // Simulate loading previous week
            setTimeout(() => {
                document.getElementById('current-week').textContent = 'January 8-14, 2024';
                showNotification('Previous week loaded', 'success');
            }, 500);
        }

        function nextWeek() {
            showNotification('Loading next week...', 'info');
            // Simulate loading next week
            setTimeout(() => {
                document.getElementById('current-week').textContent = 'January 22-28, 2024';
                showNotification('Next week loaded', 'success');
            }, 500);
        }

        // Appointment Actions
        function openTimeSlot(slotId) {
            showNotification(`Opening time slot: ${slotId}`, 'info');
            console.log(`Opening time slot: ${slotId}`);
        }

        function openNewAppointmentModal() {
            showNotification('Opening new appointment modal...', 'info');
            console.log('Opening new appointment modal');
        }

        function editAppointment(appointmentId) {
            showNotification(`Editing appointment ${appointmentId}...`, 'info');
            console.log(`Editing appointment: ${appointmentId}`);
        }

        function cancelAppointment(appointmentId) {
            if (confirm(`Are you sure you want to cancel appointment ${appointmentId}?`)) {
                showNotification(`Appointment ${appointmentId} cancelled successfully`, 'success');
                loadAppointmentStatistics(); // Update stats
            }
        }

        function confirmAppointment(appointmentId) {
            showNotification(`Appointment ${appointmentId} confirmed successfully`, 'success');
            loadAppointmentStatistics(); // Update stats
        }

        function completeAppointment(appointmentId) {
            if (confirm(`Mark appointment ${appointmentId} as completed?`)) {
                showNotification(`Appointment ${appointmentId} completed successfully`, 'success');
                loadAppointmentStatistics(); // Update stats
            }
        }

        function exportAppointments() {
            showNotification('Exporting appointments data...', 'info');
            setTimeout(() => {
                showNotification('Appointments exported successfully!', 'success');
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