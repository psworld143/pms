<?php
/**
 * Enhanced Room Inventory – Role-Based (Manager + Housekeeping)
 * Hotel PMS Inventory Module
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/config/database.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Role check: allow manager and housekeeping
$user_role = $_SESSION['user_role'] ?? '';
if (!in_array($user_role, ['manager', 'housekeeping'], true)) {
    header('Location: login.php?error=access_denied');
    exit();
}

$is_manager = ($user_role === 'manager');
$is_housekeeping = ($user_role === 'housekeeping');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Room Inventory</title>
	<link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
		#sidebar { transition: transform 0.3s ease-in-out; }
		@media (max-width: 1023px) { #sidebar { transform: translateX(-100%); z-index: 50; } #sidebar.sidebar-open { transform: translateX(0); } }
		@media (min-width: 1024px) { #sidebar { transform: translateX(0) !important; } }
		#sidebar-overlay { transition: opacity 0.3s ease-in-out; z-index: 40; }
		.main-content { margin-left: 0; padding-top: 4rem; }
		@media (min-width: 1024px) { .main-content { margin-left: 16rem; } }
    </style>
    </head>
<body class="bg-gray-50">
	<div class="flex min-h-screen">
		<!-- Sidebar Overlay for Mobile -->
		<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
		
		<!-- Header and Sidebar -->
		<?php include 'includes/inventory-header.php'; ?>
		<?php include 'includes/sidebar-inventory.php'; ?>

		<!-- Main Content -->
		<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
            <div>
					<h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">
						<?php if ($is_housekeeping): ?>
							<i class="fas fa-bed mr-2 text-blue-600"></i>Room Inventory - Housekeeping
						<?php else: ?>
							<i class="fas fa-cogs mr-2 text-green-600"></i>Room Inventory Management
						<?php endif; ?>
					</h2>
					<p class="text-sm text-gray-600 mt-1">
						<?php if ($is_housekeeping): ?>
							View and update room inventory for the rooms you clean
						<?php else: ?>
							Complete inventory management and monitoring system
                <?php endif; ?>
					</p>
				</div>
				<div class="flex items-center space-x-4">
					<?php if ($is_housekeeping): ?>
						<button id="check-rooms-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
							<i class="fas fa-clipboard-check mr-2"></i>Check Rooms
						</button>
						<button id="request-supplies-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
							<i class="fas fa-shopping-cart mr-2"></i>Request Supplies
						</button>
                <?php endif; ?>
            </div>
        </div>

			<!-- Statistics -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
				<?php if ($is_housekeeping): ?>
					<div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border border-blue-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-bed text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">My Rooms</p>
									<p class="text-3xl font-bold text-blue-900" id="my-rooms">Loading...</p>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg p-6 border border-green-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-clipboard-check text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-green-700 uppercase tracking-wide">Items Used</p>
									<p class="text-3xl font-bold text-green-900" id="items-used">Loading...</p>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg p-6 border border-yellow-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-exclamation-triangle text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-yellow-700 uppercase tracking-wide">Missing Items</p>
									<p class="text-3xl font-bold text-yellow-900" id="missing-items">Loading...</p>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg p-6 border border-purple-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-shopping-cart text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-purple-700 uppercase tracking-wide">My Requests</p>
									<p class="text-3xl font-bold text-purple-900" id="my-requests">Loading...</p>
								</div>
							</div>
						</div>
					</div>
				<?php else: ?>
					<div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border border-blue-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-bed text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">Total Rooms</p>
									<p class="text-3xl font-bold text-blue-900" id="total-rooms">Loading...</p>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg p-6 border border-green-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-check-circle text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-green-700 uppercase tracking-wide">Fully Stocked</p>
									<p class="text-3xl font-bold text-green-900" id="fully-stocked">Loading...</p>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg p-6 border border-yellow-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-exclamation-triangle text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-yellow-700 uppercase tracking-wide">Need Restocking</p>
									<p class="text-3xl font-bold text-yellow-900" id="need-restocking">Loading...</p>
								</div>
							</div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-lg p-6 border border-gray-200">
						<div class="flex items-center justify-between">
							<div class="flex items-center">
								<div class="w-12 h-12 bg-gradient-to-r from-gray-500 to-gray-600 rounded-xl flex items-center justify-center shadow-lg">
									<i class="fas fa-question-circle text-white text-lg"></i>
								</div>
								<div class="ml-4">
									<p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Unknown</p>
									<p class="text-3xl font-bold text-gray-900" id="unknown-rooms">Loading...</p>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Floor Selection -->
			<div class="bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
				<div class="flex items-center justify-between mb-6">
					<h3 class="text-xl font-bold text-gray-800 flex items-center">
						<i class="fas fa-building mr-3 text-blue-600"></i>
						Select Floor
					</h3>
					<div class="text-sm text-gray-500">
						<i class="fas fa-info-circle mr-1"></i>
						Click a floor to view room inventory
					</div>
				</div>
				<div class="flex flex-wrap gap-3" id="floor-buttons">
					<div class="flex items-center justify-center w-full py-8 text-gray-500">
						<i class="fas fa-spinner fa-spin mr-2"></i>
						Loading floors...
					</div>
				</div>
			</div>

			<!-- Room Inventory Grid -->
			<div id="room-inventory-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
				<div class="col-span-full flex items-center justify-center py-12 text-gray-500">
					<div class="text-center">
						<i class="fas fa-bed text-4xl mb-4 text-gray-300"></i>
						<p class="text-lg font-medium">Select a floor to view room inventory</p>
						<p class="text-sm">Choose a floor from the options above to see room details</p>
					</div>
				</div>
        </div>


		</main>

		<!-- Room Details Modal -->
		<div id="room-details-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden backdrop-blur-sm">
			<div class="flex items-center justify-center min-h-screen p-4">
				<div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[95vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
					<div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
						<div class="flex justify-between items-center">
							<div class="flex items-center space-x-3">
								<div class="bg-white bg-opacity-20 p-3 rounded-xl">
									<i class="fas fa-bed text-2xl"></i>
								</div>
								<div>
									<h3 class="text-2xl font-bold" id="modal-room-title">Room Details</h3>
									<p class="text-blue-100 text-sm">Complete inventory information</p>
								</div>
							</div>
							<button id="close-modal" class="bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-xl transition-all duration-200 transform hover:scale-110">
								<i class="fas fa-times text-xl"></i>
							</button>
						</div>
					</div>
					<div class="p-6 bg-gray-50 max-h-[80vh] overflow-y-auto">
						<div id="room-details-content"></div>
					</div>
				</div>
			</div>
		</div>
		<?php include '../includes/pos-footer.php'; ?>
        </div>

	<script>
	// Global functions defined before document ready
	window.loadRoomInventoryStats = function() {
		const userRole = '<?php echo $user_role; ?>';
		if (userRole === 'housekeeping') {
			const apiUrl = 'api/get-housekeeping-stats.php';
			$.ajax({ url: apiUrl, method: 'GET', dataType: 'json', xhrFields: { withCredentials: true }, success: function(response){ if (response.success) { const stats = response.data || response.statistics || {}; $('#my-rooms').text(stats.total_rooms || 0); $('#items-used').text(stats.total_items || 0); $('#missing-items').text(stats.missing_items || 0); $('#my-requests').text(stats.pending_requests || 0); } } });
			return;
		}

		// Manager: try simple stats endpoint first
		$.ajax({
			url: 'api/get-room-inventory-stats.php',
			method: 'GET',
			dataType: 'json',
			xhrFields: { withCredentials: true },
			success: function(r){
				const s = r && (r.statistics || r.data) ? (r.statistics || r.data) : {};
				if (s.fully_stocked !== undefined || s.need_restocking !== undefined || s.unknown_rooms !== undefined) {
					$('#total-rooms').text(s.total_rooms || 0);
					$('#fully-stocked').text(s.fully_stocked || 0);
					$('#need-restocking').text(s.need_restocking || 0);
					$('#unknown-rooms').text(s.unknown_rooms || 0);
					return;
				}
				// Fallback: aggregate by floors/rooms
				window.aggregateManagerStats();
			},
			error: function(){ aggregateManagerStats(); }
		});
	}

	window.aggregateManagerStats = function(){
		// Load room stats
		$.ajax({ url: 'api/get-hotel-floors.php', method: 'GET', dataType: 'json', xhrFields: { withCredentials: true }, success: function(resp){
			const floors = resp.floors || [];
			if (!floors.length) { $('#total-rooms').text(0); $('#fully-stocked').text(0); $('#need-restocking').text(0); $('#unknown-rooms').text(0); return; }
			let totalRooms = 0, fully = 0, need = 0, unknown = 0;
			let remaining = floors.length;
			floors.forEach(function(f){
				const floorNum = (typeof f === 'object') ? (f.floor_number || f.id || f.floor || f) : f;
				$.ajax({ url: 'api/get-rooms-for-floor.php', method: 'GET', data: { floor: floorNum }, dataType: 'json', xhrFields: { withCredentials: true }, complete: function(){ remaining--; if (remaining === 0) { $('#total-rooms').text(totalRooms); $('#fully-stocked').text(fully); $('#need-restocking').text(need); $('#unknown-rooms').text(unknown); } }, success: function(r){ if (r.success) { const rooms = r.rooms || []; totalRooms += rooms.length; rooms.forEach(function(room){ switch(room.stock_status){ case 'fully_stocked': fully++; break; case 'needs_restocking': need++; break; case 'critical_stock': unknown++; break; case 'no_inventory': unknown++; break; default: unknown++; break; } }); } } });
			});
		}});
	}

	$(document).ready(function() {
		let currentFloor = 2; // default floor
		const userRole = '<?php echo $user_role; ?>';

		window.loadRoomInventoryStats();
		loadFloors();
		// remove eager load that assumed floor 2; initial load will happen in loadFloors when data arrives
		// loadRoomsForFloor(currentFloor);

		$(document).on('click', '.floor-btn', function() {
			const floorId = $(this).data('floor-id');
			currentFloor = floorId;
			loadRoomsForFloor(floorId);
			$('.floor-btn').removeClass('bg-blue-600 text-white shadow-lg').addClass('bg-gray-200 text-gray-700 hover:bg-gray-300');
			$(this).removeClass('bg-gray-200 text-gray-700 hover:bg-gray-300').addClass('bg-blue-600 text-white shadow-lg');
		});

		$(document).on('click', '.room-card', function() {
			const roomId = $(this).data('room-id');
			showRoomDetails(roomId);
		});

		$('#close-modal').click(function() {
			$('#modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
			setTimeout(() => { $('#room-details-modal').addClass('hidden'); }, 300);
		});

		$('#room-details-modal').click(function(e) {
			if (e.target === this) {
				$('#modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
				setTimeout(() => { $(this).addClass('hidden'); }, 300);
			}
		});

		$('#check-rooms-btn').click(function() { startRoomCheck(); });
		$('#request-supplies-btn').click(function() { openRequestModal(); });
		$(document).on('click', '#request-replacement-btn', function(){ const rn = $(this).data('room-number'); openRequestModal(rn); });
		$(document).on('click', '#check-room-btn', function(){ 
			const roomId = $(this).data('room-id');
			checkSingleRoom(roomId);
		});

		function loadFloors() {
			$.ajax({
				url: 'api/get-hotel-floors.php',
				method: 'GET',
				dataType: 'json',
				xhrFields: { withCredentials: true },
				success: function(response) {
					const floorButtons = $('#floor-buttons');
					floorButtons.empty();
					if (response.success) {
						const floors = response.floors || [];
						floors.forEach(function(floor, index) {
							const floorId = (typeof floor === 'object') ? (floor.id || floor.floor_number || floor.floor) : floor;
							const floorLabelNum = (typeof floor === 'object') ? (floor.floor_number || floor.id || '') : floor;
							const isActive = index === 0;
							const buttonClass = isActive ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300';
							const label = (typeof floor === 'object' && floor.floor_name) ? floor.floor_name : (`Floor ${floorLabelNum}`);
							const button = `
								<button class="floor-btn px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 ${buttonClass}" data-floor-id="${floorId}">
									<i class="fas fa-layer-group mr-2"></i>
									${label}
								</button>
							`;
							floorButtons.append(button);
							if (index === 0) { currentFloor = floorId; }
						});
						if (floors.length) { loadRoomsForFloor(currentFloor); }
					} else {
						floorButtons.html('<div class="text-red-500">Error loading floors</div>');
					}
				},
				error: function() {
					$('#floor-buttons').html('<div class="text-red-500">Error loading floors</div>');
				}
			});
		}

		function loadRoomsForFloor(floorId) {
			$.ajax({
				url: 'api/get-rooms-for-floor.php',
				method: 'GET',
				data: { floor: floorId },
				dataType: 'json',
				xhrFields: { withCredentials: true },
				success: function(response) {
					if (response.success) {
						displayRooms(response.rooms || []);
					} else {
						console.error('Error loading rooms:', response.message);
					}
				}
			});
		}

		function displayRooms(rooms) {
			const grid = $('#room-inventory-grid');
			grid.empty();
			if (!rooms || rooms.length === 0) {
				grid.html(`
					<div class="col-span-full flex items-center justify-center py-12 text-gray-500">
						<div class="text-center">
							<i class="fas fa-bed text-4xl mb-4 text-gray-300"></i>
							<p class="text-lg font-medium">No rooms found on this floor</p>
							<p class="text-sm">Try selecting a different floor</p>
						</div>
					</div>
				`);
				return;
			}
			rooms.forEach(function(room) {
				const statusClass = getStatusClass(room.status);
				const stockStatus = getStockStatus(room.stock_status);
				const stockIcon = getStockIcon(room.stock_status);
				const roomCard = `
					<div class="room-card bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 cursor-pointer transform hover:scale-105 border border-gray-200 group" data-room-id="${room.id}">
						<div class="flex items-center justify-between mb-4">
							<h4 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">
								<i class="fas fa-door-open mr-2"></i>Room ${room.room_number}
							</h4>
							<span class="px-3 py-1 text-xs font-bold rounded-full ${statusClass}">
								${room.status ? (room.status.charAt(0).toUpperCase() + room.status.slice(1)) : 'Unknown'}
							</span>
						</div>
						<div class="space-y-3 mb-4">
							<div class="flex justify-between items-center">
								<span class="text-sm font-medium text-gray-600">Type:</span>
								<span class="text-sm font-semibold text-gray-900 capitalize bg-gray-100 px-2 py-1 rounded">${room.room_type || 'N/A'}</span>
							</div>
							<div class="flex justify-between items-center">
								<span class="text-sm font-medium text-gray-600">Stock Status:</span>
								<span class="flex items-center font-semibold ${stockStatus.class}">
									<i class="fas ${stockIcon} mr-1"></i>
									${stockStatus.text}
								</span>
							</div>
							<div class="flex justify-between items-center">
								<span class="text-sm font-medium text-gray-600">Items:</span>
								<span class="text-sm font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">${room.total_items || 0}</span>
							</div>
						</div>
						<div class="pt-4 border-t border-gray-200">
							<button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
								<i class="fas fa-eye mr-2"></i>View Details
							</button>
						</div>
					</div>
				`;
				grid.append(roomCard);
			});
		}

		function getStatusClass(status) {
			switch(status) {
				case 'available': return 'bg-green-100 text-green-800';
				case 'occupied': return 'bg-blue-100 text-blue-800';
				case 'maintenance': return 'bg-yellow-100 text-yellow-800';
				case 'out_of_order': return 'bg-red-100 text-red-800';
				default: return 'bg-gray-100 text-gray-800';
			}
		}

		function getStockStatus(stockStatus) {
			switch(stockStatus) {
				case 'fully_stocked': return { class: 'text-green-600', text: 'Fully Stocked' };
				case 'needs_restocking': return { class: 'text-yellow-600', text: 'Needs Restocking' };
				case 'critical_stock': return { class: 'text-red-600', text: 'Critical Stock' };
				default: return { class: 'text-gray-600', text: 'Unknown' };
			}
		}

		function getStockIcon(stockStatus) {
			switch(stockStatus) {
				case 'fully_stocked': return 'fa-check-circle';
				case 'needs_restocking': return 'fa-exclamation-triangle';
				case 'critical_stock': return 'fa-times-circle';
				default: return 'fa-question-circle';
			}
		}


		window.displayRoomDetails = function(room) {
			$('#modal-room-title').text(`Room ${room.room_number} - Inventory Details`);
			const getStatusBadgeClass = (status) => {
				switch(status) {
					case 'available': return 'bg-green-100 text-green-800';
					case 'occupied': return 'bg-blue-100 text-blue-800';
					case 'maintenance': return 'bg-yellow-100 text-yellow-800';
					case 'out_of_service': return 'bg-red-100 text-red-800';
					default: return 'bg-gray-100 text-gray-800';
				}
			};
			const getStockStatusInfo = (current, par) => {
				if (current === 0) return { text: 'Out of Stock', class: 'bg-red-100 text-red-800' };
				if (current < par) return { text: 'Low Stock', class: 'bg-yellow-100 text-yellow-800' };
				if (current >= par) return { text: 'In Stock', class: 'bg-green-100 text-green-800' };
				return { text: 'Unknown', class: 'bg-gray-100 text-gray-800' };
			};

			let content = `
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
					<div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white shadow-lg">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-blue-100 text-sm font-medium">Room Number</p>
								<p class="text-2xl font-bold">${room.room_number}</p>
							</div>
							<div class="bg-white bg-opacity-20 p-3 rounded-lg"><i class="fas fa-bed text-2xl"></i></div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white shadow-lg">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-purple-100 text-sm font-medium">Room Type</p>
								<p class="text-xl font-bold capitalize">${room.room_type || 'N/A'}</p>
							</div>
							<div class="bg-white bg-opacity-20 p-3 rounded-lg"><i class="fas fa-home text-2xl"></i></div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl text-white shadow-lg">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-green-100 text-sm font-medium">Status</p>
								<p class="text-xl font-bold capitalize">${room.status || 'N/A'}</p>
							</div>
							<div class="bg-white bg-opacity-20 p-3 rounded-lg"><i class="fas fa-check-circle text-2xl"></i></div>
						</div>
					</div>
					<div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-xl text-white shadow-lg">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-orange-100 text-sm font-medium">Max Occupancy</p>
								<p class="text-2xl font-bold">${room.max_occupancy || 'N/A'}</p>
							</div>
							<div class="bg-white bg-opacity-20 p-3 rounded-lg"><i class="fas fa-users text-2xl"></i></div>
						</div>
					</div>
				</div>
				<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
					<div class="flex items-center mb-6">
						<div class="bg-blue-100 p-3 rounded-lg mr-4"><i class="fas fa-info-circle text-blue-600 text-xl"></i></div>
						<h4 class="text-xl font-bold text-gray-800">Room Information</h4>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-4">
							<div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"><span class="text-gray-600 font-medium">Room Type</span><span class="text-gray-900 font-semibold capitalize">${room.room_type || 'N/A'}</span></div>
							<div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"><span class="text-gray-600 font-medium">Status</span><span class="px-3 py-1 rounded-full text-sm font-medium ${getStatusBadgeClass(room.status)}">${room.status || 'N/A'}</span></div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"><span class="text-gray-600 font-medium">Max Occupancy</span><span class="text-gray-900 font-semibold">${room.max_occupancy || 'N/A'}</span></div>
							<div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"><span class="text-gray-600 font-medium">Last Audited</span><span class="text-gray-900 font-semibold">${room.last_audited ? new Date(room.last_audited).toLocaleDateString() : 'Never'}</span></div>
						</div>
					</div>
				</div>
				<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
					<div class="flex items-center">
						<div class="bg-purple-100 p-3 rounded-lg mr-4"><i class="fas fa-tasks text-purple-600 text-xl"></i></div>
						<h4 class="text-xl font-bold text-gray-800">Room Actions</h4>
					</div>
					<div class="mt-4 flex space-x-3">
						<button id="check-room-btn" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg" data-room-id="${room.id}"><i class="fas fa-clipboard-check mr-2"></i>Check Room</button>
						<?php if ($is_housekeeping): ?>
							<button id="request-replacement-btn" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg" data-room-id="${room.id}" data-room-number="${room.room_number}"><i class="fas fa-shopping-cart mr-2"></i>Request Items</button>
						<?php endif; ?>
					</div>
				</div>
				<div class="bg-white rounded-xl shadow-lg p-6">
					<div class="flex items-center justify-between mb-6">
						<div class="flex items-center">
							<div class="bg-green-100 p-3 rounded-lg mr-4"><i class="fas fa-boxes text-green-600 text-xl"></i></div>
							<h4 class="text-xl font-bold text-gray-800">Inventory Items</h4>
						</div>
						<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">${room.inventory_items ? room.inventory_items.length : 0} Items</span>
					</div>
					<?php if ($is_manager): ?>
						<div class="mb-6">
							<button id="toggle-assign-form" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"><i class="fas fa-plus-circle mr-1"></i>Add Item to Room</button>
							<div id="assign-item-panel" class="mt-3 hidden bg-gray-50 border border-gray-200 rounded p-4">
								<div class="grid grid-cols-1 md:grid-cols-5 gap-3">
									<select id="assign-item-select" class="border border-gray-300 rounded px-2 py-2 md:col-span-2"></select>
									<input id="assign-allocated" type="number" min="0" placeholder="Allocated" class="border border-gray-300 rounded px-2 py-2" />
									<input id="assign-current" type="number" min="0" placeholder="Current" class="border border-gray-300 rounded px-2 py-2" />
									<input id="assign-par" type="number" min="0" placeholder="Par" class="border border-gray-300 rounded px-2 py-2" />
									<button id="assign-item-btn" data-room-id="${room.id}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"><i class="fas fa-check mr-1"></i>Assign</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
					${room.inventory_items && room.inventory_items.length > 0 ? `
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
							${room.inventory_items.map(item => {
								const stockInfo = getStockStatusInfo(item.quantity_current, item.par_level);
								return `
									<div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 hover:shadow-lg transition-all duration-200">
										<div class="flex items-start justify-between mb-4">
											<div class="flex items-center">
												<div class="bg-blue-100 p-3 rounded-lg mr-3"><i class="fas fa-box text-blue-600"></i></div>
												<div>
													<h5 class="font-bold text-gray-800 text-lg">${item.item_name}</h5>
													<p class="text-gray-500 text-sm">SKU: ${item.sku}</p>
												</div>
											</div>
											<span class="px-3 py-1 rounded-full text-xs font-medium ${stockInfo.class}">${stockInfo.text}</span>
										</div>
										<div class="space-y-3">
											<div class="flex justify-between items-center"><span class="text-gray-600 text-sm">Allocated:</span><span class="font-semibold text-gray-800">${item.quantity_allocated} ${item.unit}</span></div>
											<div class="flex justify-between items-center"><span class="text-gray-600 text-sm">Current:</span><span class="font-semibold text-gray-800">${item.quantity_current} ${item.unit}</span></div>
											<div class="flex justify-between items-center"><span class="text-gray-600 text-sm">Par Level:</span><span class="font-semibold text-gray-800">${item.par_level} ${item.unit}</span></div>
										</div>
										<div class="mt-4 pt-4 border-t border-gray-200">
											<div class="flex justify-between items-center mb-3">
												<span class="text-gray-500 text-xs">Last Updated:</span>
												<span class="text-gray-500 text-xs">${item.last_updated ? new Date(item.last_updated).toLocaleDateString() : 'Never'}</span>
											</div>
											<?php if ($is_housekeeping): ?>
												<div class="flex space-x-2">
													<button onclick="updateItemStatus(${item.id}, 'used')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs"><i class="fas fa-check mr-1"></i>Used</button>
													<button onclick="updateItemStatus(${item.id}, 'missing')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs"><i class="fas fa-exclamation-triangle mr-1"></i>Missing</button>
													<button onclick="updateItemStatus(${item.id}, 'damaged')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs"><i class="fas fa-times mr-1"></i>Damaged</button>
												</div>
											<?php else: ?>
												<div class="flex space-x-2">
													<button onclick="editRoomItem(${item.id}, ${item.quantity_allocated || 0}, ${item.quantity_current || 0}, ${item.par_level || 0})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs"><i class="fas fa-edit mr-1"></i>Edit</button>
													<button onclick="removeRoomItem(${item.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs"><i class="fas fa-trash mr-1"></i>Remove</button>
												</div>
											<?php endif; ?>
										</div>
									</div>
								`;
							}).join('')}
						</div>
					` : `
						<div class="text-center py-12">
							<div class="bg-gray-100 p-6 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
								<i class="fas fa-box-open text-gray-400 text-3xl"></i>
							</div>
							<h5 class="text-lg font-semibold text-gray-600 mb-2">No Inventory Items</h5>
							<p class="text-gray-500 mb-4">This room doesn't have any inventory items assigned yet.</p>
						</div>
					`}
				</div>
			`;
			$('#room-details-content').html(content);
			if ($('#assign-item-select').length) {
				loadAssignableItems();
				$('#assign-item-btn').off('click').on('click', function() {
					const roomId = $(this).data('room-id');
					const itemId = $('#assign-item-select').val();
					const allocated = parseInt($('#assign-allocated').val() || '0', 10);
					const current = parseInt($('#assign-current').val() || '0', 10);
					const par = parseInt($('#assign-par').val() || '0', 10);
					if (!itemId) { alert('Please select an item'); return; }
					$.ajax({
						url: 'api/add-room-item.php',
						method: 'POST',
						dataType: 'json',
						data: { room_id: roomId, item_id: itemId, quantity_allocated: allocated, quantity_current: current, par_level: par },
						xhrFields: { withCredentials: true },
						success: function(res){ if (res.success) { alert('Item added to room'); showRoomDetails(roomId); } else { alert('Error: ' + res.message); } },
						error: function(xhr){ alert('Error: ' + xhr.responseText); }
					});
				});
				$('#toggle-assign-form').off('click').on('click', function(){ $('#assign-item-panel').toggleClass('hidden'); });
			}
			setTimeout(() => { $('#modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
		}

		function loadAssignableItems(){
			$.ajax({
				url: 'api/list-items-simple.php',
				method: 'GET',
				dataType: 'json',
				success: function(resp){
					const sel = $('#assign-item-select');
					if (!sel.length) return;
					sel.empty();
					const items = resp.items || resp.data || [];
					sel.append('<option value="">Select Item</option>');
					items.slice(0, 200).forEach(function(it){
						const id = it.id || it.item_id;
						const name = it.label || it.item_name || it.name || ('Item #' + id);
						sel.append(`<option value="${id}">${name}</option>`);
					});
				},
				error: function(){ const sel = $('#assign-item-select'); if (sel.length) sel.html('<option value="">Unable to load items</option>'); }
			});
		}

		// Housekeeping functions
		function startRoomCheck() {
			if (confirm('Start room check for your assigned rooms?')) {
				$.ajax({ url: 'api/start-room-check.php', method: 'POST', dataType: 'json', xhrFields: { withCredentials: true }, success: function(r){ if (r.success) { alert('Room check started successfully!'); loadRoomInventoryStats(); loadRoomsForFloor(currentFloor); } else { alert('Error: ' + r.message); } }, error: function(xhr){ alert('Error: ' + xhr.responseText); } });
			}
		}
		function openRequestModal(roomNum) {
			const modal = `
				<div id="request-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
					<div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
						<div class="p-6 border-b border-gray-200"><h3 class="text-xl font-bold text-gray-800">Request Supplies</h3></div>
						<div class="p-6">
							<form id="request-form">
								<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Item</label><select id="request-item" class="w-full border border-gray-300 rounded px-3 py-2" required><option value="">Select Item</option></select></div>
								<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label><input type="number" id="request-quantity" class="w-full border border-gray-300 rounded px-3 py-2" min="1" required></div>
								<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Room Number</label><input type="text" id="request-room" class="w-full border border-gray-300 rounded px-3 py-2" required value="${roomNum || ''}"></div>
								<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Reason</label><select id="request-reason" class="w-full border border-gray-300 rounded px-3 py-2" required><option value="">Select Reason</option><option value="missing">Missing</option><option value="damaged">Damaged</option><option value="low_stock">Low Stock</option><option value="replacement">Replacement</option></select></div>
								<div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Notes</label><textarea id="request-notes" class="w-full border border-gray-300 rounded px-3 py-2" rows="3"></textarea></div>
							</form>
						</div>
						<div class="p-6 border-t border-gray-200 flex justify-end space-x-3"><button id="cancel-request" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button><button id="submit-request" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Submit Request</button></div>
					</div>
				</div>`;
			$('body').append(modal);
			loadRequestItems();
			$('#submit-request').click(function() { submitRequest(); });
			$('#cancel-request').click(function() { $('#request-modal').remove(); });
			$('#request-modal').click(function(e) { if (e.target === this) { $(this).remove(); } });
		}
		function loadRequestItems() { $.ajax({ url: 'api/list-items-simple.php', method: 'GET', dataType: 'json', success: function(response){ const select = $('#request-item'); select.empty(); select.append('<option value="">Select Item</option>'); const items = response.items || response.data || []; items.forEach(function(item){ const id = item.id || item.item_id; const name = item.label || item.item_name || item.name || ('Item #' + id); select.append(`<option value="${id}">${name}</option>`); }); }, error: function(){ $('#request-item').html('<option value="">Unable to load items</option>'); } }); }
		function submitRequest() {
			const itemId = $('#request-item').val();
			const quantity = $('#request-quantity').val();
			const room = $('#request-room').val();
			const reason = $('#request-reason').val();
			const notes = $('#request-notes').val();
			if (!itemId || !quantity || !room || !reason) { alert('Please fill in all required fields'); return; }
			$.ajax({ url: 'api/create-supply-request.php', method: 'POST', dataType: 'json', data: { item_id: itemId, quantity: quantity, room_number: room, reason: reason, notes: notes }, xhrFields: { withCredentials: true }, success: function(response){ if (response.success) { alert('Request submitted successfully!'); $('#request-modal').remove(); loadRoomInventoryStats(); } else { alert('Error submitting request: ' + response.message); } }, error: function(xhr){ alert('Error submitting request: ' + xhr.responseText); } });
		}

		// Silence Tailwind CDN production warning in console (visual only)
		(function(){
			const _warn = console.warn;
			console.warn = function(){
				try {
					const msg = arguments && arguments[0] ? String(arguments[0]) : '';
					if (msg.indexOf('cdn.tailwindcss.com should not be used in production') !== -1) { return; }
				} catch (e) {}
				return _warn.apply(console, arguments);
			};
		})();

		// Global functions for housekeeping and manager operations
		window.showRoomDetails = function(roomId) {
			$.ajax({
				url: 'api/get-room-details.php',
				method: 'GET',
				data: { room_id: roomId },
				dataType: 'json',
				xhrFields: { withCredentials: true },
				success: function(response) {
					if (response.success) {
						displayRoomDetails(response.room);
						$('#room-details-modal').removeClass('hidden');
						setTimeout(() => { $('#modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
					} else {
						alert('Error loading room details: ' + response.message);
					}
				},
				error: function(xhr) {
					alert('Error loading room details: ' + xhr.responseText);
				}
			});
		}

		window.updateItemStatus = function(itemId, status) {
			if (confirm(`Mark this item as ${status}?`)) {
				$.ajax({ 
					url: 'api/update-item-status.php', 
					method: 'POST', 
					dataType: 'json', 
					data: { item_id: itemId, status: status }, 
					xhrFields: { withCredentials: true }, 
					success: function(response){ 
						if (response.success) { 
							alert(`Item marked as ${status} successfully!`); 
							const ridBtn = $('#assign-item-btn'); 
							const roomId = ridBtn.length ? ridBtn.data('room-id') : null; 
							if (roomId) { 
								showRoomDetails(roomId); 
							} 
							window.loadRoomInventoryStats(); 
						} else { 
							alert('Error updating item status: ' + response.message); 
						} 
					}, 
					error: function(xhr){ 
						alert('Error updating item status: ' + xhr.responseText); 
					} 
				});
			}
		}

		window.checkSingleRoom = function(roomId) {
			if (confirm('Start room check for this specific room?')) {
				$.ajax({ 
					url: 'api/check-single-room.php', 
					method: 'POST', 
					dataType: 'json', 
					data: { room_id: roomId }, 
					xhrFields: { withCredentials: true }, 
					success: function(response){ 
						if (response.success) { 
							alert('Room check started successfully!'); 
							window.loadRoomInventoryStats(); 
							// Refresh the room details if modal is open
							showRoomDetails(roomId);
						} else { 
							alert('Error starting room check: ' + response.message); 
						} 
					}, 
					error: function(xhr){ 
						alert('Error starting room check: ' + xhr.responseText); 
					} 
				});
			}
		}

		// Manager item ops - moved outside document ready for global access
		window.editRoomItem = function(itemId, allocated, current, par) {
			const modal = `
				<div id="edit-item-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
					<div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
						<div class="p-6 border-b border-gray-200"><h3 class="text-xl font-bold text-gray-800">Edit Room Item</h3></div>
						<div class="p-6 space-y-4">
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Allocated</label>
								<input id="edit-allocated" type="number" min="0" class="w-full border border-gray-300 rounded px-3 py-2" value="${allocated}">
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Current</label>
								<input id="edit-current" type="number" min="0" class="w-full border border-gray-300 rounded px-3 py-2" value="${current}">
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Par Level</label>
								<input id="edit-par" type="number" min="0" class="w-full border border-gray-300 rounded px-3 py-2" value="${par}">
							</div>
						</div>
						<div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
							<button id="cancel-edit-item" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
							<button id="save-edit-item" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
        </div>
    </div>
				</div>`;
			$('body').append(modal);
			$('#cancel-edit-item').click(function(){ $('#edit-item-modal').remove(); });
			$('#edit-item-modal').click(function(e){ if (e.target === this) { $(this).remove(); } });
			$('#save-edit-item').click(function(){
				const newAllocated = parseInt($('#edit-allocated').val() || '0', 10);
				const newCurrent = parseInt($('#edit-current').val() || '0', 10);
				const newPar = parseInt($('#edit-par').val() || '0', 10);
				$.ajax({ 
					url: 'api/update-room-item.php', 
					method: 'POST', 
					dataType: 'json', 
					data: { item_id: itemId, allocated: newAllocated, current: newCurrent, par: newPar }, 
					xhrFields: { withCredentials: true }, 
					success: function(r){ 
						if (r.success) { 
							alert('Item updated successfully!'); 
							$('#edit-item-modal').remove(); 
							// refresh modal if open
							const roomId = $('#assign-item-btn').data('room-id'); 
							if (roomId) { 
								showRoomDetails(roomId); 
							} 
							window.loadRoomInventoryStats();
						} else { 
							alert('Error: ' + (r.message || 'Unable to update item')); 
						} 
					}, 
					error: function(xhr){ 
						alert('Error updating item: ' + xhr.responseText); 
					} 
				});
			});
		}
		
		window.removeRoomItem = function(itemId) {
			if (confirm('Remove this item from the room?')) {
				$.ajax({ 
					url: 'api/remove-room-item.php', 
					method: 'POST', 
					dataType: 'json', 
					data: { item_id: itemId }, 
					xhrFields: { withCredentials: true }, 
					success: function(response){ 
						if (response.success) { 
							alert('Item removed from room successfully!'); 
							// refresh modal if open
							const roomId = $('#assign-item-btn').data('room-id'); 
							if (roomId) { 
								showRoomDetails(roomId); 
							} 
							window.loadRoomInventoryStats(); 
						} else { 
							alert('Error removing item: ' + (response.message || 'Unable to remove item')); 
						} 
					}, 
					error: function(xhr){ 
						alert('Error removing item: ' + xhr.responseText); 
					} 
				});
			}
		}


	});
	</script>
</body>
</html>


