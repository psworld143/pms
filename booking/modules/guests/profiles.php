<?php
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

$page_title = 'Guest Profiles';
$stats = getGuestStatistics();
$asset_version = time();
$additional_js = '<script src="' . booking_url('assets/js/main.js?v=' . $asset_version) . '"></script>' . "\n";
$additional_js .= '<script src="' . booking_url('assets/js/guest-management.js?v=' . $asset_version) . '"></script>';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
        <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Guest Profiles</h2>
        <div class="flex items-center space-x-4">
            <button onclick="addNewGuest()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-user-plus mr-2"></i>Add Guest
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-md bg-blue-500 text-white flex items-center justify-center">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Guests</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_guests']); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-md bg-amber-500 text-white flex items-center justify-center">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">VIP Guests</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['vip_guests']); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-md bg-emerald-500 text-white flex items-center justify-center">
                        <i class="fas fa-bed"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Stays</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['active_guests']); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-md bg-indigo-500 text-white flex items-center justify-center">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">New This Month</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['new_guests']); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-md bg-rose-500 text-white flex items-center justify-center">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Feedback</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['pending_feedback']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="search-input">Search</label>
                <input id="search-input" type="text" placeholder="Search guests..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="vip-filter">VIP Status</label>
                <select id="vip-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Guests</option>
                    <option value="1">VIP Guests</option>
                    <option value="0">Non-VIP</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="status-filter">Stay Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Any Status</option>
                    <option value="active">Currently Stayed</option>
                    <option value="recent">Recent Guest</option>
                    <option value="frequent">Frequent Guest</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="loadGuests()" class="w-full bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Guest Directory</h3>
        </div>
        <div id="guests-table-container">
            <div class="px-6 py-12 text-center">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-3"></i>
                <p class="text-sm text-gray-500">Loading guests...</p>
            </div>
        </div>
    </div>

    <div id="pagination-container" class="mt-6"></div>
</main>

<div id="guest-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Add Guest</h3>
            <button onclick="closeGuestModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="guest-form" class="space-y-6">
            <input type="hidden" id="guest_id" name="guest_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="first_name">First Name *</label>
                    <input id="first_name" name="first_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="last_name">Last Name *</label>
                    <input id="last_name" name="last_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email</label>
                    <input id="email" name="email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="phone">Phone *</label>
                    <input id="phone" name="phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="date_of_birth">Date of Birth</label>
                    <input id="date_of_birth" name="date_of_birth" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="nationality">Nationality</label>
                    <input id="nationality" name="nationality" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="address">Address</label>
                <textarea id="address" name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="id_type">ID Type *</label>
                    <select id="id_type" name="id_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select ID Type</option>
                        <option value="passport">Passport</option>
                        <option value="driver_license">Driver's License</option>
                        <option value="national_id">National ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="id_number">ID Number *</label>
                    <input id="id_number" name="id_number" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center space-x-2">
                    <input id="is_vip" name="is_vip" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_vip" class="text-sm text-gray-700">Mark as VIP guest</label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="preferences">Preferences</label>
                <textarea id="preferences" name="preferences" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="e.g., High floor, late checkout, hypoallergenic pillows"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="service_notes">Service Notes</label>
                <textarea id="service_notes" name="service_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Special instructions or history"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeGuestModal()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                    <i class="fas fa-save mr-2"></i>Save Guest
                </button>
            </div>
        </form>
    </div>
</div>

<div id="guest-details-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Guest Details</h3>
            <button onclick="closeGuestDetailsModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div id="guest-details-content" class="space-y-6 text-sm text-gray-700"></div>
    </div>
</div>

<div id="feedback-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Add Feedback</h3>
            <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="feedback-form" class="space-y-6">
            <input type="hidden" id="feedback_guest_id" name="guest_id">
            <input type="hidden" id="feedback_reservation_id" name="reservation_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="feedback_type">Feedback Type *</label>
                    <select id="feedback_type" name="feedback_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="compliment">Compliment</option>
                        <option value="complaint">Complaint</option>
                        <option value="suggestion">Suggestion</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="category">Category *</label>
                    <select id="category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="room_quality">Room Quality</option>
                        <option value="service">Service</option>
                        <option value="dining">Dining</option>
                        <option value="facilities">Facilities</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="rating">Rating</label>
                <input id="rating" name="rating" type="number" min="1" max="5" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Optional rating (1-5)">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="comments">Comments *</label>
                <textarea id="comments" name="comments" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Describe the feedback"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeFeedbackModal()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Feedback
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
