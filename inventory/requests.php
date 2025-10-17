<?php
/**
 * Inventory Requests Management - Manager Interface
 * Hotel PMS Training System for Students
 * Manager can view, approve, and reject all housekeeping requests for room inventory items
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user role and information
$user_role = $_SESSION['user_role'] ?? 'manager'; // Default to manager for this interface
$user_name = $_SESSION['user_name'] ?? 'Unknown User';

// Only allow manager access
if ($user_role !== 'manager') {
    header('Location: ../booking/login.php');
    exit();
}

// Handle form submissions - Manager Actions Only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve_supply_request') {
            try {
                global $pdo;
                $request_id = $_POST['request_id'];
            $notes = $_POST['notes'] ?? '';
                
                $stmt = $pdo->prepare("
                UPDATE supply_requests 
                SET status = 'approved', approved_by = ?, approved_at = NOW(), approval_notes = ?
                    WHERE id = ?
                ");
            $stmt->execute([$user_id, $notes, $request_id]);
                
            $success_message = "Supply request approved successfully!";
                
            } catch (PDOException $e) {
                $error_message = "Error approving request: " . $e->getMessage();
        }
    }
    
    if ($action === 'reject_supply_request') {
            try {
                global $pdo;
                $request_id = $_POST['request_id'];
            $notes = $_POST['notes'] ?? '';
                
                $stmt = $pdo->prepare("
                UPDATE supply_requests 
                SET status = 'rejected', approved_by = ?, approved_at = NOW(), approval_notes = ?
                    WHERE id = ?
                ");
            $stmt->execute([$user_id, $notes, $request_id]);
                
            $success_message = "Supply request rejected successfully!";
                
            } catch (PDOException $e) {
                $error_message = "Error rejecting request: " . $e->getMessage();
        }
    }
    
    // Removed general inventory request actions per requirements
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
// Optional deep-link filters from housekeeping page
$filter_room_number = isset($_GET['room_number']) ? trim($_GET['room_number']) : '';
$filter_requested_by = isset($_GET['requested_by']) ? (int)$_GET['requested_by'] : 0;
$request_type_filter = $_GET['request_type'] ?? '';

// Detect inventory_items schema for adaptive item name/unit selection
$nameExprSql = 'ii.item_name';
$unitExprSql = "''";
try {
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $columns, true);
    $hasName = in_array('name', $columns, true);
    $hasUnit = in_array('unit', $columns, true);
    if ($hasItemName) {
        $nameExprSql = 'ii.item_name';
    } elseif ($hasName) {
        $nameExprSql = 'ii.name';
                } else {
        $nameExprSql = 'IFNULL(ii.sku, ii.description)';
    }
    $unitExprSql = $hasUnit ? 'ii.unit' : "''";
} catch (Throwable $e) {
    // Fallback silently; queries below will still work
}

// Get supply requests (room inventory requests)
$supply_requests = [];
// Detect supply requests table name across schemas
$supplyTable = 'supply_requests';
try {
	$candidates = ['supply_requests', 'inventory_supply_requests', 'room_supply_requests', 'housekeeping_supply_requests', 'room_item_requests'];
	$detected = '';
	foreach ($candidates as $candidate) {
		$stmt = $pdo->prepare("SHOW TABLES LIKE ?");
		$stmt->execute([$candidate]);
		if ($stmt->fetchColumn()) { $detected = $candidate; break; }
	}
	if ($detected) {
		$supplyTable = $detected;
	} else {
		// Fallback: scan all tables and pick one that looks like a supply requests table
		$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN, 0);
		foreach ($tables as $tbl) {
			try {
				$colsStmt = $pdo->prepare("SHOW COLUMNS FROM `{$tbl}`");
				$colsStmt->execute();
				$cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
				$hasItemId = in_array('item_id', $cols, true);
				$hasQty = in_array('quantity_requested', $cols, true) || in_array('quantity', $cols, true) || in_array('qty_requested', $cols, true);
				$hasRoomRef = in_array('room_number', $cols, true) || in_array('room', $cols, true) || in_array('room_id', $cols, true);
				$hasRequester = in_array('requested_by', $cols, true) || in_array('user_id', $cols, true);
				if ($hasItemId && $hasQty && $hasRoomRef && $hasRequester) { $supplyTable = $tbl; break; }
			} catch (Throwable $ignore) { /* continue */ }
		}
	}
} catch (Throwable $e) { /* keep default */ }

// Introspect supply table columns for adaptive SQL
$qtyExprSql = 'COALESCE(sr.quantity_requested, sr.quantity, sr.qty_requested, 0)';
$reasonExprSql = 'COALESCE(sr.reason, sr.request_reason, sr.issue_type, \'\')';
$statusExprSql = 'COALESCE(sr.status, \'pending\')';
$createdExprSql = 'COALESCE(sr.created_at, sr.request_date, sr.date_created)';
$approvedExprSql = 'COALESCE(sr.approved_at, sr.approved_date)';
$requestedByColSql = 'sr.requested_by';
$roomNumberSelectSql = 'sr.room_number AS room_number';
$roomJoinSql = 'sr.room_number = r.room_number';
try {
	$srColsStmt = $pdo->prepare("SHOW COLUMNS FROM {$supplyTable}");
	$srColsStmt->execute();
	$srCols = $srColsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
	$hasQtyRequested = in_array('quantity_requested', $srCols, true);
	$hasQuantity = in_array('quantity', $srCols, true);
	$hasQtyRequestedAlt = in_array('qty_requested', $srCols, true);
	$qtyExprSql = $hasQtyRequested ? 'sr.quantity_requested' : ($hasQuantity ? 'sr.quantity' : ($hasQtyRequestedAlt ? 'sr.qty_requested' : '0'));
	$reasonExprSql = in_array('reason', $srCols, true) ? 'sr.reason' : (in_array('request_reason', $srCols, true) ? 'sr.request_reason' : (in_array('issue_type', $srCols, true) ? 'sr.issue_type' : "''"));
	$statusExprSql = in_array('status', $srCols, true) ? 'sr.status' : "'pending'";
	$createdExprSql = in_array('created_at', $srCols, true) ? 'sr.created_at' : (in_array('request_date', $srCols, true) ? 'sr.request_date' : (in_array('date_created', $srCols, true) ? 'sr.date_created' : 'NULL'));
	$approvedExprSql = in_array('approved_at', $srCols, true) ? 'sr.approved_at' : (in_array('approved_date', $srCols, true) ? 'sr.approved_date' : 'NULL');
	$requestedByColSql = in_array('requested_by', $srCols, true) ? 'sr.requested_by' : (in_array('user_id', $srCols, true) ? 'sr.user_id' : 'NULL');
	$hasRoomNumber = in_array('room_number', $srCols, true) || in_array('room', $srCols, true);
	$roomNumberCol = in_array('room_number', $srCols, true) ? 'sr.room_number' : (in_array('room', $srCols, true) ? 'sr.room' : '');
	$roomIdCol = in_array('room_id', $srCols, true) ? 'sr.room_id' : '';
	if ($roomIdCol) {
		$roomNumberSelectSql = 'r.room_number AS room_number';
		$roomJoinSql = "$roomIdCol = r.id";
	} elseif ($hasRoomNumber) {
		$roomNumberSelectSql = $roomNumberCol . ' AS room_number';
		$roomJoinSql = $roomNumberCol . ' = r.room_number';
	} else {
		$roomNumberSelectSql = 'r.room_number AS room_number';
		$roomJoinSql = '0=1'; // no joinable room info, keep LEFT JOIN harmless
	}
} catch (Throwable $e) {
	// keep defaults
}
try {
	global $pdo;
	$sql = "
		SELECT sr.id,
			{$roomNumberSelectSql},
			{$nameExprSql} AS item_name, {$unitExprSql} AS unit,
			{$qtyExprSql} AS quantity_requested,
			{$reasonExprSql} AS reason,
			{$statusExprSql} AS status,
			{$createdExprSql} AS created_at,
			{$approvedExprSql} AS approved_at,
			u.name as requested_by_name, r.room_type
		FROM {$supplyTable} sr
		LEFT JOIN users u ON {$requestedByColSql} = u.id
		LEFT JOIN inventory_items ii ON sr.item_id = ii.id
		LEFT JOIN rooms r ON {$roomJoinSql}
		WHERE 1=1
	";
	$params = [];
	
	if ($status_filter) {
		$sql .= " AND {$statusExprSql} = ?";
		$params[] = $status_filter;
	}
	if ($filter_room_number !== '') {
		if (strpos($roomNumberSelectSql, 'r.room_number') !== false) {
			$sql .= " AND r.room_number = ?";
		} else {
			$sql .= " AND {$roomNumberSelectSql} = ?";
		}
		$params[] = $filter_room_number;
	}
	if ($filter_requested_by > 0 && $requestedByColSql !== 'NULL') {
		$sql .= " AND {$requestedByColSql} = ?";
		$params[] = $filter_requested_by;
	}
	
	$sql .= " ORDER BY {$createdExprSql} DESC";
	
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$supply_requests = $stmt->fetchAll();

	// Fallback: if no rows, fetch with minimal dependencies but include room via join when possible
	if (!$supply_requests) {
		$fallbackSql = "
			SELECT sr.id,
				" . ($roomNumberSelectSql ?: 'r.room_number AS room_number') . ",
				{$qtyExprSql} AS quantity_requested,
				{$reasonExprSql} AS reason,
				{$statusExprSql} AS status,
				{$createdExprSql} AS created_at
			FROM {$supplyTable} sr
			LEFT JOIN rooms r ON {$roomJoinSql}
			WHERE 1=1
			" . ($filter_room_number !== '' ? (strpos($roomNumberSelectSql, 'r.room_number') !== false ? " AND r.room_number = :room" : " AND {$roomNumberSelectSql} = :room") : '') . "
			" . ($filter_requested_by > 0 && $requestedByColSql !== 'NULL' ? " AND {$requestedByColSql} = :uid" : '') . "
			ORDER BY {$createdExprSql} DESC
		";
		$stmt = $pdo->prepare($fallbackSql);
		if ($filter_room_number !== '') { $stmt->bindValue(':room', $filter_room_number); }
		if ($filter_requested_by > 0 && $requestedByColSql !== 'NULL') { $stmt->bindValue(':uid', $filter_requested_by, PDO::PARAM_INT); }
		$stmt->execute();
		$supply_requests = $stmt->fetchAll();
	}
	
} catch (PDOException $e) {
	error_log("Error getting supply requests (primary): " . $e->getMessage());
	$supply_requests = [];
}

// Get inventory requests (general inventory requests)
$inventory_requests = [];
// Removed loading of general inventory requests per requirements

// Get request details for viewing
$request_details = null;
$request_type = null;
if (isset($_GET['view'])) {
	try {
		global $pdo;
		$request_id = $_GET['view'];
		// rebuild adaptive pieces for details
		$detailsQty = $qtyExprSql; $detailsReason = $reasonExprSql; $detailsStatus = $statusExprSql; $detailsCreated = $createdExprSql;
		$detailsRoomNumber = $roomNumberSelectSql ?: 'r.room_number AS room_number';
		$detailsRoomJoin = $roomJoinSql;
		$detailsRequesterCol = $requestedByColSql;
		$stmt = $pdo->prepare(" 
			SELECT sr.id,
				{$detailsRoomNumber},
				{$nameExprSql} AS item_name, {$unitExprSql} AS unit,
				{$detailsQty} AS quantity_requested,
				{$detailsReason} AS reason,
				{$detailsStatus} AS status,
				{$detailsCreated} AS created_at,
				u.name as requested_by_name, a.name as approved_by_name, r.room_type, {$approvedExprSql} AS approved_at
			FROM {$supplyTable} sr
			LEFT JOIN users u ON {$detailsRequesterCol} = u.id
			LEFT JOIN users a ON sr.approved_by = a.id
			LEFT JOIN inventory_items ii ON sr.item_id = ii.id
			LEFT JOIN rooms r ON {$detailsRoomJoin}
			WHERE sr.id = ?
		");
		$stmt->execute([$request_id]);
		$request_details = $stmt->fetch();
		if ($request_details) { $request_type = 'supply'; }
	} catch (PDOException $e) {
		error_log("Error getting request details: " . $e->getMessage());
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Inventory Requests - Manager Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        secondary: '#059669'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Include unified inventory header and sidebar -->
    <?php include 'includes/inventory-header.php'; ?>
    <?php include 'includes/sidebar-inventory.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
            <div>
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Room Inventory Requests</h2>
                <p class="text-gray-600 mt-1">Manage all housekeeping requests for room inventory items</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-user-tag mr-1"></i>
                    Logged in as: <span class="font-semibold"><?php echo htmlspecialchars($user_name); ?></span> 
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs ml-2">
                        <?php echo ucfirst($user_role); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Requests Content -->
        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Requests</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php echo count(array_filter($supply_requests, fn($r) => $r['status'] === 'pending')); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Approved Today</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php 
                            $today = date('Y-m-d');
                            $approved_today = count(array_filter($supply_requests, fn($r) => $r['status'] === 'approved' && date('Y-m-d', strtotime($r['approved_at'] ?? '')) === $today));
                            echo $approved_today;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-times text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rejected Today</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php 
                            $rejected_today = count(array_filter($supply_requests, fn($r) => $r['status'] === 'rejected' && date('Y-m-d', strtotime($r['approved_at'] ?? '')) === $today));
                            echo $rejected_today;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Requests</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php echo count($supply_requests); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <!-- Removed Request Type filter (only supply shown) -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Supply Requests Table -->
        <?php /* Always show supply requests */ ?>
        <?php if (true): ?>
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-bed mr-2 text-blue-600"></i>
                    Room Supply Requests (<?php echo count($supply_requests); ?>)
                </h3>
                <p class="text-sm text-gray-600 mt-1">Requests for specific items in specific rooms</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($supply_requests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                            <i class="fas fa-bed text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">Room <?php echo htmlspecialchars($request['room_number'] ?? '-'); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['room_type'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['item_name'] ?? ('Item #' . ($request['item_id'] ?? '?'))); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['unit'] ?? ''); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 bg-gray-100 rounded-full text-sm font-medium">
                                        <?php echo $request['quantity_requested']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php 
                                        echo match($request['reason'] ?? '') {
                                            'missing' => 'bg-red-100 text-red-800',
                                            'damaged' => 'bg-orange-100 text-orange-800',
                                            'low_stock' => 'bg-yellow-100 text-yellow-800',
                                            'replacement' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $request['reason'] ?? '')); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php 
                                        echo match($request['status'] ?? '') {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($request['status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($request['requested_by_name'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo isset($request['created_at']) ? date('M j, Y H:i', strtotime($request['created_at'])) : ''; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="viewSupplyRequest(<?php echo $request['id']; ?>)" 
                                                    class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                    title="View Request Details">
                                                <i class="fas fa-eye mr-1.5"></i>
                                                <span class="hidden sm:inline">View</span>
                                            </button>
                                            
                                            <?php if ($request['status'] === 'pending'): ?>
                                            <button onclick="approveSupplyRequest(<?php echo $request['id']; ?>)" 
                                                        class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                        title="Approve Request">
                                                    <i class="fas fa-check mr-1.5"></i>
                                                    <span class="hidden sm:inline">Approve</span>
                                                </button>
                                                
                                            <button onclick="rejectSupplyRequest(<?php echo $request['id']; ?>)" 
                                                        class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                        title="Reject Request">
                                                    <i class="fas fa-times mr-1.5"></i>
                                                    <span class="hidden sm:inline">Reject</span>
                                                </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Removed General Inventory Requests table per requirements -->
    </main>

    <!-- Approval/Rejection Modal -->
    <div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900" id="actionModalTitle">Action Required</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" id="actionType">
                    <input type="hidden" name="request_id" id="actionRequestId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" id="actionNotes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Add any notes about this decision..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeActionModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" id="actionSubmitBtn"
                                class="px-4 py-2 text-white rounded-lg">
                            Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Request Modal -->
    <?php if ($request_details): ?>
    <div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Request Details - 
                        <?php if ($request_type === 'supply'): ?>
                            Room <?php echo htmlspecialchars($request_details['room_number']); ?> Supply Request
                        <?php else: ?>
                            <?php echo htmlspecialchars($request_details['request_number']); ?>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="p-4 sm:p-6">
                    <?php if ($request_type === 'supply'): ?>
                        <!-- Supply Request Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                        <div>
                                <h4 class="font-medium text-gray-900 mb-2">Room Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Room Number:</span> <?php echo htmlspecialchars($request_details['room_number']); ?></p>
                                    <p><span class="font-medium">Room Type:</span> <?php echo htmlspecialchars($request_details['room_type'] ?? 'Standard'); ?></p>
                                    <p><span class="font-medium">Item Requested:</span> <?php echo htmlspecialchars($request_details['item_name']); ?></p>
                                    <p><span class="font-medium">Quantity:</span> <?php echo $request_details['quantity_requested']; ?> <?php echo htmlspecialchars($request_details['unit'] ?? 'pcs'); ?></p>
                                    <p><span class="font-medium">Reason:</span> <?php echo ucfirst(str_replace('_', ' ', $request_details['reason'])); ?></p>
                        </div>
                        </div>
                        <div>
                                <h4 class="font-medium text-gray-900 mb-2">Request Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Status:</span> 
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                            echo match($request_details['status']) {
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst($request_details['status']); ?>
                                        </span>
                                    </p>
                                    <p><span class="font-medium">Requested By:</span> <?php echo htmlspecialchars($request_details['requested_by_name']); ?></p>
                                    <p><span class="font-medium">Request Date:</span> <?php echo date('M j, Y H:i', strtotime($request_details['created_at'])); ?></p>
                                    <?php if ($request_details['approved_by_name']): ?>
                                        <p><span class="font-medium">Approved By:</span> <?php echo htmlspecialchars($request_details['approved_by_name']); ?></p>
                                        <p><span class="font-medium">Approved At:</span> <?php echo date('M j, Y H:i', strtotime($request_details['approved_at'])); ?></p>
                                    <?php endif; ?>
                        </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <!-- Inventory Request Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Request Information</h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium">Department:</span> <?php echo ucfirst(str_replace('-', ' ', $request_details['department'])); ?></p>
                                <p><span class="font-medium">Priority:</span> <?php echo ucfirst($request_details['priority']); ?></p>
                                <p><span class="font-medium">Status:</span> <?php echo ucfirst($request_details['status']); ?></p>
                                <p><span class="font-medium">Requested By:</span> <?php echo htmlspecialchars($request_details['requested_by_name']); ?></p>
                                <p><span class="font-medium">Request Date:</span> <?php echo date('M j, Y', strtotime($request_details['request_date'])); ?></p>
                                <?php if ($request_details['required_date']): ?>
                                    <p><span class="font-medium">Required Date:</span> <?php echo date('M j, Y', strtotime($request_details['required_date'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Request Items</h4>
                            <div class="space-y-2">
                                <?php foreach ($request_details['items'] as $item): ?>
                                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                        <span class="text-sm"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                        <span class="text-sm font-medium"><?php echo $item['quantity_requested']; ?> <?php echo htmlspecialchars($item['unit']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($request_details['notes']): ?>
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-2">Notes</h4>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($request_details['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-end">
                        <a href="requests.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
                            Close
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Supply Request Functions
        function viewSupplyRequest(requestId) {
            window.location.href = `?view=${requestId}`;
        }
        
        function approveSupplyRequest(requestId) {
            openActionModal('approve_supply_request', requestId, 'Approve Supply Request', 'bg-green-600 hover:bg-green-700');
        }
        
        function rejectSupplyRequest(requestId) {
            openActionModal('reject_supply_request', requestId, 'Reject Supply Request', 'bg-red-600 hover:bg-red-700');
        }
        
        // Removed Inventory Request Functions per requirements
        
        // Action Modal Functions
        function openActionModal(action, requestId, title, buttonClass) {
            document.getElementById('actionType').value = action;
            document.getElementById('actionRequestId').value = requestId;
            document.getElementById('actionModalTitle').textContent = title;
            document.getElementById('actionSubmitBtn').textContent = action.includes('approve') ? 'Approve' : 'Reject';
            document.getElementById('actionSubmitBtn').className = `px-4 py-2 text-white rounded-lg ${buttonClass}`;
            document.getElementById('actionNotes').value = '';
            document.getElementById('actionModal').classList.remove('hidden');
        }
        
        function closeActionModal() {
            document.getElementById('actionModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('actionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeActionModal();
            }
        });
        
        // Auto-refresh page every 30 seconds to show updated data
        setInterval(function() {
            // Only refresh if no modals are open
            if (document.getElementById('actionModal').classList.contains('hidden') && 
                document.getElementById('viewModal').classList.contains('hidden')) {
                window.location.reload();
            }
        }, 30000);
        
        // Handle URL parameters for quick actions
        function handleUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const view = urlParams.get('view');
            const status = urlParams.get('status');
            
            if (view) {
                // View request details (handled by PHP)
                return;
            }
            
            if (status) {
                // Filter to show specific status
                const statusSelect = document.querySelector('select[name="status"]');
                if (statusSelect) {
                    statusSelect.value = status;
                }
            }
        }
        
        // Call on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleUrlParameters();
        });
    </script>
</body>
</html>
