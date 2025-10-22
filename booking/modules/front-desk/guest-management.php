<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'vps_session_fix.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'functions.php';
// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$vip_filter = $_GET['vip'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get guest statistics
$guest_stats = getGuestStatistics();

// Get guests data directly
try {
    $guests_sql = "
        SELECT 
            g.id,
            g.first_name,
            g.last_name,
            g.email,
            g.phone,
            g.is_vip,
            g.id_number,
            g.created_at,
            COUNT(r.id) as total_stays,
            MAX(r.check_out_date) as last_visit,
            COALESCE(SUM(r.total_amount), 0) as total_spent
        FROM guests g
        LEFT JOIN reservations r ON g.id = r.guest_id
        GROUP BY g.id
        ORDER BY g.created_at DESC
        LIMIT 20
    ";
    
    $guests_stmt = $pdo->prepare($guests_sql);
    $guests_stmt->execute();
    $guests_data = $guests_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error loading guests: " . $e->getMessage());
    $guests_data = [];
}

// Set page title
$page_title = 'Guest Management';

// Handle success/error messages
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === '1') {
        $success_message = 'Guest updated successfully!';
    } elseif ($_GET['success'] === 'deleted') {
        $success_message = 'Guest deleted successfully!';
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'active_reservations') {
        $error_message = 'Cannot delete guest with active reservations.';
    } elseif ($_GET['error'] === 'delete_failed') {
        $error_message = 'Failed to delete guest. Please try again.';
    }
}

// Include unified navigation (automatically selects based on user role)
include dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header-unified.php';
include dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'sidebar-unified.php';
?>
        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
        
        <!-- Success/Error Messages -->
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); if ($success_message): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($success_message); ?>
            </div>
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); endif; ?>
        
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); if ($error_message): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($error_message); ?>
            </div>
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); endif; ?>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Guests</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo number_format($guest_stats['total_guests'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-crown text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">VIP Guests</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo number_format($guest_stats['vip_guests'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Guests</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo number_format($guest_stats['active_guests'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Feedback</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo number_format($guest_stats['pending_feedback'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-lg">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="search-input" placeholder="Search guests by name, email, or phone..." 
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($search); ?>"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <select id="vip-filter" onchange="window.location.href='?vip='+this.value" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                        <option value="">All VIP Status</option>
                        <option value="1" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $vip_filter === '1' ? 'selected' : ''; ?>>VIP Only</option>
                        <option value="0" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $vip_filter === '0' ? 'selected' : ''; ?>>Non-VIP Only</option>
                    </select>
                    
                    <select id="status-filter" onchange="window.location.href='?status='+this.value" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                        <option value="">All Status</option>
                        <option value="active" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $status_filter === 'active' ? 'selected' : ''; ?>>Currently Staying</option>
                        <option value="recent" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $status_filter === 'recent' ? 'selected' : ''; ?>>Recent Guests</option>
                        <option value="frequent" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $status_filter === 'frequent' ? 'selected' : ''; ?>>Frequent Guests</option>
                    </select>
                    
                    <button onclick="addNewGuest()" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Guest
                    </button>
                </div>
            </div>
        </div>

        <!-- Guests Table -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Guest Directory</h3>
            </div>
            
            <div id="guests-table-container">
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); if (empty($guests_data)): ?>
                    <div class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4"></i>
                        <p>No guests found</p>
                    </div>
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIP Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stays</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); foreach ($guests_data as $guest): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['id_number'] ?? 'N/A'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['email']); ?></div>
                                        <div class="text-sm text-gray-500"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['phone']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); if ($guest['is_vip']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-crown mr-1"></i>
                                                VIP
                                            </span>
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Regular
                                            </span>
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $guest['total_stays']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₱<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo number_format($guest['total_spent'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewGuestDetails(<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $guest['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editGuestForm(<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $guest['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteGuestConfirm(<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $guest['id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination-container" class="mt-6">
            <!-- Pagination will be loaded here -->
        </div>
    </div>

    <!-- Add/Edit Guest Modal -->
    <div id="guest-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Add New Guest</h3>
                <button onclick="closeGuestModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="guest-form" class="space-y-6">
                <input type="hidden" id="guest_id" name="guest_id">
                
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" id="first_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                            <input type="tel" name="phone" id="phone" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                            <input type="text" name="nationality" id="nationality" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" id="address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                </div>

                <!-- VIP and Preferences -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">VIP Status & Preferences</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Type</label>
                            <select name="id_type" id="id_type" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Select ID Type</option>
                                <option value="passport">Passport</option>
                                <option value="driver_license">Driver's License</option>
                                <option value="national_id">National ID</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Number *</label>
                            <input type="text" name="id_number" id="id_number" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_vip" id="is_vip" class="mr-2">
                            <label for="is_vip" class="text-sm font-medium text-gray-700">VIP Guest</label>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guest Preferences</label>
                        <textarea name="preferences" id="preferences" rows="3" 
                                  placeholder="Room preferences, dietary restrictions, special requests..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                </div>

                <!-- Service Notes -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Service Notes</h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Personalized Service Notes</label>
                        <textarea name="service_notes" id="service_notes" rows="4" 
                                  placeholder="Add personalized service notes, special instructions, or important information about this guest..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeGuestModal()" 
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Guest
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Guest Details Modal -->
    <div id="guest-details-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Guest Details</h3>
                <button onclick="closeGuestDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="guest-details-content">
                <!-- Guest details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="feedback-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Add Feedback/Complaint</h3>
                <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="feedback-form" class="space-y-6">
                <input type="hidden" id="feedback_guest_id" name="guest_id">
                <input type="hidden" id="feedback_reservation_id" name="reservation_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Feedback Type *</label>
                        <select name="feedback_type" id="feedback_type" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select Type</option>
                            <option value="compliment">Compliment</option>
                            <option value="complaint">Complaint</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" id="category" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select Category</option>
                            <option value="service">Service</option>
                            <option value="cleanliness">Cleanliness</option>
                            <option value="facilities">Facilities</option>
                            <option value="staff">Staff</option>
                            <option value="food">Food</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <div class="flex space-x-2">
                        <input type="radio" name="rating" value="1" id="rating-1" class="mr-1">
                        <label for="rating-1" class="text-sm text-gray-700">1</label>
                        <input type="radio" name="rating" value="2" id="rating-2" class="mr-1">
                        <label for="rating-2" class="text-sm text-gray-700">2</label>
                        <input type="radio" name="rating" value="3" id="rating-3" class="mr-1">
                        <label for="rating-3" class="text-sm text-gray-700">3</label>
                        <input type="radio" name="rating" value="4" id="rating-4" class="mr-1">
                        <label for="rating-4" class="text-sm text-gray-700">4</label>
                        <input type="radio" name="rating" value="5" id="rating-5" class="mr-1">
                        <label for="rating-5" class="text-sm text-gray-700">5</label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comments *</label>
                    <textarea name="comments" id="comments" rows="4" required 
                              placeholder="Please provide detailed feedback..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeFeedbackModal()" 
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                        <i class="fas fa-save mr-2"></i>Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>

        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
    <!-- <script src="../../assets/js/guest-management.js"></script> -->
    <script>
        // Guest action functions that work without API calls
        function viewGuestDetails(guestId) {
            // Get guest data from the table
            const row = document.querySelector(`button[onclick="viewGuestDetails(${guestId})"]`).closest('tr');
            const cells = row.querySelectorAll('td');
            
            // Try multiple ways to get the guest name
            let guestName = '';
            
            // Method 1: Try the specific selector
            const guestNameElement = cells[0].querySelector('.text-sm.font-medium.text-gray-900');
            if (guestNameElement) {
                guestName = guestNameElement.textContent;
            } else {
                // Method 2: Try just the font-medium class
                const altElement = cells[0].querySelector('.font-medium');
                if (altElement) {
                    guestName = altElement.textContent;
                } else {
                    // Method 3: Get all text from the first cell and filter
                    const allText = cells[0].textContent;
                    const lines = allText.split('\n').map(line => line.trim()).filter(line => line && !line.startsWith('ID:'));
                    if (lines.length > 0) {
                        guestName = lines[0];
                    }
                }
            }
            
            const guestEmail = cells[1].querySelector('.text-sm.text-gray-900').textContent;
            const guestPhone = cells[1].querySelector('.text-sm.text-gray-500') ? cells[1].querySelector('.text-sm.text-gray-500').textContent : '';
            const vipStatus = cells[2].textContent.trim();
            const stays = cells[3].textContent.trim();
            const totalSpent = cells[4].textContent.trim();
            
            // Parse the name
            const nameParts = guestName.trim().split(' ');
            const firstName = nameParts[0] || '';
            const lastName = nameParts.slice(1).join(' ') || '';
            
            // Show modal with guest details
            showGuestDetailsModal({
                first_name: firstName,
                last_name: lastName,
                email: guestEmail,
                phone: guestPhone,
                is_vip: vipStatus.includes('VIP'),
                total_stays: stays,
                total_spent: parseFloat(totalSpent.replace('₱', '').replace(',', ''))
            });
        }
        
        function editGuestForm(guestId) {
            // Redirect to edit page or show edit modal
            window.location.href = `edit-guest.php?id=${guestId}`;
        }
        
        function deleteGuestConfirm(guestId) {
            if (confirm('Are you sure you want to delete this guest? This action cannot be undone.')) {
                // Redirect to delete page
                window.location.href = `delete-guest.php?id=${guestId}`;
            }
        }
        
        // Show guest details modal
        function showGuestDetailsModal(guest) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Guest Details</h3>
                            <button onclick="closeGuestDetailsModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <p class="mt-1 text-sm text-gray-900">${guest.first_name || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <p class="mt-1 text-sm text-gray-900">${guest.last_name || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">${guest.email || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <p class="mt-1 text-sm text-gray-900">${guest.phone || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">VIP Status</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    ${guest.is_vip ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-crown mr-1"></i>VIP</span>' : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Regular</span>'}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Stays</label>
                                <p class="mt-1 text-sm text-gray-900">${guest.total_stays || 0}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Spent</label>
                                <p class="mt-1 text-sm text-gray-900">₱${(guest.total_spent || 0).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button onclick="closeGuestDetailsModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        // Close guest details modal
        function closeGuestDetailsModal() {
            const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
            if (modal) {
                modal.remove();
            }
        }
    </script>
    
    <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); include '../../includes/footer.php'; ?>
