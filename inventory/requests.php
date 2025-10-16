<?php
/**
 * Inventory Requests Management
 * Hotel PMS Training System for Students
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
$user_role = $_SESSION['user_role'] ?? 'housekeeping'; // Default to housekeeping if not set
$user_name = $_SESSION['user_name'] ?? 'Unknown User';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_request') {
        // Only housekeeping role can create requests
        if ($user_role !== 'housekeeping') {
            $error_message = "Access denied. Only housekeeping staff can create requests.";
        } else {
            try {
                global $pdo;
                $pdo->beginTransaction();
            
            // Generate request number
            $request_number = 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create request
            $stmt = $pdo->prepare("
                INSERT INTO inventory_requests (request_number, requested_by, department, priority, required_date, notes, status, request_date)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $request_number,
                $user_id,
                $_POST['department'],
                $_POST['priority'],
                $_POST['required_date'],
                $_POST['notes']
            ]);
            
            $request_id = $pdo->lastInsertId();
            
            // Add request items
            $item_ids = $_POST['item_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            
            for ($i = 0; $i < count($item_ids); $i++) {
                if (!empty($item_ids[$i]) && !empty($quantities[$i])) {
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory_request_items (request_id, item_id, quantity_requested)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$request_id, $item_ids[$i], $quantities[$i]]);
                }
            }
            
                $pdo->commit();
                $success_message = "Request created successfully! Request #: " . $request_number;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = "Error creating request: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'approve_request') {
        // Only manager role can approve requests
        if ($user_role !== 'manager') {
            $error_message = "Access denied. Only managers can approve requests.";
        } else {
            try {
                global $pdo;
                $request_id = $_POST['request_id'];
                
                $stmt = $pdo->prepare("
                    UPDATE inventory_requests 
                    SET status = 'approved', approved_by = ?, approved_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$user_id, $request_id]);
                
                $success_message = "Request approved successfully!";
                
            } catch (PDOException $e) {
                $error_message = "Error approving request: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'reject_request') {
        // Only manager role can reject requests
        if ($user_role !== 'manager') {
            $error_message = "Access denied. Only managers can reject requests.";
        } else {
            try {
                global $pdo;
                $request_id = $_POST['request_id'];
                
                $stmt = $pdo->prepare("
                    UPDATE inventory_requests 
                    SET status = 'rejected', approved_by = ?, approved_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$user_id, $request_id]);
                
                $success_message = "Request rejected successfully!";
                
            } catch (PDOException $e) {
                $error_message = "Error rejecting request: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'edit_request') {
        // Only housekeeping role can edit requests, and only their own
        if ($user_role !== 'housekeeping') {
            $error_message = "Access denied. Only housekeeping staff can edit requests.";
        } else {
            try {
                global $pdo;
                $request_id = $_POST['request_id'];
                
                // Check if the request belongs to the current user
                $stmt = $pdo->prepare("SELECT requested_by FROM inventory_requests WHERE id = ?");
                $stmt->execute([$request_id]);
                $request = $stmt->fetch();
                
                if (!$request || $request['requested_by'] != $user_id) {
                    $error_message = "Access denied. You can only edit your own requests.";
                } else {
                    $pdo->beginTransaction();
                    
                    // Update request details
                    $stmt = $pdo->prepare("
                        UPDATE inventory_requests 
                        SET department = ?, priority = ?, required_date = ?, notes = ?, last_updated = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['department'],
                        $_POST['priority'],
                        $_POST['required_date'],
                        $_POST['notes'],
                        $request_id
                    ]);
                    
                    // Delete existing request items
                    $stmt = $pdo->prepare("DELETE FROM inventory_request_items WHERE request_id = ?");
                    $stmt->execute([$request_id]);
                    
                    // Add updated request items
                    $item_ids = $_POST['item_id'] ?? [];
                    $quantities = $_POST['quantity'] ?? [];
                    
                    for ($i = 0; $i < count($item_ids); $i++) {
                        if (!empty($item_ids[$i]) && !empty($quantities[$i])) {
                            $stmt = $pdo->prepare("
                                INSERT INTO inventory_request_items (request_id, item_id, quantity_requested)
                                VALUES (?, ?, ?)
                            ");
                            $stmt->execute([$request_id, $item_ids[$i], $quantities[$i]]);
                        }
                    }
                    
                    $pdo->commit();
                    $success_message = "Request updated successfully!";
                }
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = "Error updating request: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'delete_request') {
        // Only housekeeping role can delete requests, and only their own
        if ($user_role !== 'housekeeping') {
            $error_message = "Access denied. Only housekeeping staff can delete requests.";
        } else {
            try {
                global $pdo;
                $request_id = $_POST['request_id'];
                
                // Check if the request belongs to the current user
                $stmt = $pdo->prepare("SELECT requested_by FROM inventory_requests WHERE id = ?");
                $stmt->execute([$request_id]);
                $request = $stmt->fetch();
                
                if (!$request || $request['requested_by'] != $user_id) {
                    $error_message = "Access denied. You can only delete your own requests.";
                } else {
                    $pdo->beginTransaction();
                    
                    // Delete request items first
                    $stmt = $pdo->prepare("DELETE FROM inventory_request_items WHERE request_id = ?");
                    $stmt->execute([$request_id]);
                    
                    // Delete request
                    $stmt = $pdo->prepare("DELETE FROM inventory_requests WHERE id = ?");
                    $stmt->execute([$request_id]);
                    
                    $pdo->commit();
                    $success_message = "Request deleted successfully!";
                }
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = "Error deleting request: " . $e->getMessage();
            }
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$department_filter = $_GET['department'] ?? '';
$priority_filter = $_GET['priority'] ?? '';

// Get requests with filters
$requests = [];
try {
    global $pdo;
    $sql = "
        SELECT ir.*, u.name as requested_by_name,
               COUNT(iri.id) as item_count
        FROM inventory_requests ir
        JOIN users u ON ir.requested_by = u.id
        LEFT JOIN inventory_request_items iri ON ir.id = iri.request_id
        WHERE 1=1
    ";
    $params = [];
    
    if ($status_filter) {
        $sql .= " AND ir.status = ?";
        $params[] = $status_filter;
    }
    
    if ($department_filter) {
        $sql .= " AND ir.department = ?";
        $params[] = $department_filter;
    }
    
    if ($priority_filter) {
        $sql .= " AND ir.priority = ?";
        $params[] = $priority_filter;
    }
    
    $sql .= " GROUP BY ir.id ORDER BY ir.request_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error getting requests: " . $e->getMessage());
}

// Get items for request creation (schema-adaptive for localhost vs cyberpanel)
$items = [];
try {
    global $pdo;

    // Detect column availability
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM inventory_items");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasItemName = in_array('item_name', $columns, true);
    $hasName = in_array('name', $columns, true);
    $hasStatus = in_array('status', $columns, true);
    $hasUnit = in_array('unit', $columns, true);
    $hasCurrentStock = in_array('current_stock', $columns, true);
    $hasQuantity = in_array('quantity', $columns, true); // fallback some schemas

    // Build SELECT parts
    $nameExpr = $hasItemName ? 'item_name' : ($hasName ? 'name' : "IFNULL(sku, description)");
    $qtyExpr = $hasCurrentStock ? 'current_stock' : ($hasQuantity ? 'quantity' : '0');
    $unitExpr = $hasUnit ? 'unit' : "'' AS unit";

    $sql = "SELECT id, $nameExpr AS name, $qtyExpr AS quantity, $unitExpr FROM inventory_items";
    if ($hasStatus) {
        $sql .= " WHERE status = 'active'";
    }
    $sql .= $hasItemName ? " ORDER BY item_name" : ($hasName ? " ORDER BY name" : " ORDER BY id");

    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error getting items: " . $e->getMessage());
}

// Get request details for viewing
$request_details = null;
if (isset($_GET['view'])) {
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT ir.*, u.name as requested_by_name, a.name as approved_by_name
            FROM inventory_requests ir
            JOIN users u ON ir.requested_by = u.id
            LEFT JOIN users a ON ir.approved_by = a.id
            WHERE ir.id = ?
        ");
        $stmt->execute([$_GET['view']]);
        $request_details = $stmt->fetch();
        
        if ($request_details) {
            // Get request items
            $stmt = $pdo->prepare("
                SELECT iri.*, ii.item_name as item_name, ii.unit
                FROM inventory_request_items iri
                JOIN inventory_items ii ON iri.item_id = ii.id
                WHERE iri.request_id = ?
            ");
            $stmt->execute([$_GET['view']]);
            $request_details['items'] = $stmt->fetchAll();
        }
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
    <title>Inventory Requests - Hotel PMS Training</title>
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
            <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Inventory Requests</h2>
            <div class="flex items-center space-x-4">
                <?php if ($user_role === 'housekeeping'): ?>
                    <button onclick="openCreateModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Create Request
                    </button>
                <?php endif; ?>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-user-tag mr-1"></i>
                    Logged in as: <span class="font-semibold"><?php echo htmlspecialchars($user_name); ?></span> 
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs ml-2">
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

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="fulfilled" <?php echo $status_filter === 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Departments</option>
                            <option value="housekeeping" <?php echo $department_filter === 'housekeeping' ? 'selected' : ''; ?>>Housekeeping</option>
                            <option value="maintenance" <?php echo $department_filter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="front-desk" <?php echo $department_filter === 'front-desk' ? 'selected' : ''; ?>>Front Desk</option>
                            <option value="restaurant" <?php echo $department_filter === 'restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Priorities</option>
                            <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Inventory Requests (<?php echo count($requests); ?>)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($request['request_number']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo ucfirst(str_replace('-', ' ', $request['department'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $priority_colors = [
                                        'low' => 'bg-gray-100 text-gray-800',
                                        'medium' => 'bg-yellow-100 text-yellow-800',
                                        'high' => 'bg-orange-100 text-orange-800',
                                        'urgent' => 'bg-red-100 text-red-800'
                                    ];
                                    $color_class = $priority_colors[$request['priority']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                                        <?php echo ucfirst($request['priority']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'fulfilled' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $color_class = $status_colors[$request['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $request['item_count']; ?> items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($request['requested_by_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($request['request_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <?php if ($user_role === 'manager'): ?>
                                            <!-- Manager can view, approve, and reject -->
                                            <button onclick="viewRequest(<?php echo $request['id']; ?>)" 
                                                    class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                    title="View Request Details">
                                                <i class="fas fa-eye mr-1.5"></i>
                                                <span class="hidden sm:inline">View</span>
                                            </button>
                                            
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button onclick="approveRequest(<?php echo $request['id']; ?>)" 
                                                        class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                        title="Approve Request">
                                                    <i class="fas fa-check mr-1.5"></i>
                                                    <span class="hidden sm:inline">Approve</span>
                                                </button>
                                                
                                                <button onclick="rejectRequest(<?php echo $request['id']; ?>)" 
                                                        class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                        title="Reject Request">
                                                    <i class="fas fa-times mr-1.5"></i>
                                                    <span class="hidden sm:inline">Reject</span>
                                                </button>
                                            <?php endif; ?>
                                            
                                        <?php elseif ($user_role === 'housekeeping'): ?>
                                            <!-- Housekeeping can view, edit, and delete their own requests -->
                                            <button onclick="viewRequest(<?php echo $request['id']; ?>)" 
                                                    class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                    title="View Request Details">
                                                <i class="fas fa-eye mr-1.5"></i>
                                                <span class="hidden sm:inline">View</span>
                                            </button>
                                            
                                            <?php if ($request['requested_by'] == $user_id): ?>
                                                <!-- Only show edit/delete for own requests -->
                                                <button onclick="editRequest(<?php echo $request['id']; ?>)" 
                                                        class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                        title="Edit Request">
                                                    <i class="fas fa-edit mr-1.5"></i>
                                                    <span class="hidden sm:inline">Edit</span>
                                                </button>
                                                
                                                <button onclick="deleteRequest(<?php echo $request['id']; ?>)" 
                                                        class="group relative inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 shadow-sm hover:shadow-md" 
                                                        title="Delete Request">
                                                    <i class="fas fa-trash-alt mr-1.5"></i>
                                                    <span class="hidden sm:inline">Delete</span>
                                                </button>
                                            <?php else: ?>
                                                <!-- Show read-only indicator for other users' requests -->
                                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded">
                                                    <i class="fas fa-lock mr-1"></i>Read Only
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Create Request Modal (Only for Housekeeping) -->
    <?php if ($user_role === 'housekeeping'): ?>
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Create New Request</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="create_request">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                            <select name="department" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Department</option>
                                <option value="housekeeping">Housekeeping</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="front-desk">Front Desk</option>
                                <option value="restaurant">Restaurant</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                            <select name="priority" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date</label>
                            <input type="date" name="required_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <input type="text" name="notes" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Request Items</h4>
                        <div id="itemsContainer">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                    <select name="item_id[]" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Item</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <input type="number" name="quantity[]" min="1" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" onclick="removeItem(this)" 
                                            class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="addItem()" 
                                class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCreateModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary hover:bg-secondary text-white rounded-lg">
                            Create Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Request Modal (Only for Housekeeping) -->
    <?php if ($user_role === 'housekeeping'): ?>
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Edit Request</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="edit_request">
                    <input type="hidden" name="request_id" id="edit_request_id">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                            <select name="department" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Department</option>
                                <option value="housekeeping">Housekeeping</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="front-desk">Front Desk</option>
                                <option value="restaurant">Restaurant</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                            <select name="priority" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date</label>
                            <input type="date" name="required_date" id="edit_required_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <input type="text" name="notes" id="edit_notes"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Request Items</h4>
                        <div id="editItemsContainer">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                    <select name="item_id[]" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Item</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <input type="number" name="quantity[]" min="1" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" onclick="removeEditItem(this)" 
                                            class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="addEditItem()" 
                                class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-primary hover:bg-secondary text-white rounded-lg">
                            Update Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- View Request Modal -->
    <?php if ($request_details): ?>
    <div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Request Details - <?php echo htmlspecialchars($request_details['request_number']); ?></h3>
                </div>
                <div class="p-4 sm:p-6">
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
        function openCreateModal() {
            <?php if ($user_role === 'housekeeping'): ?>
                document.getElementById('createModal').classList.remove('hidden');
            <?php else: ?>
                alert('Access denied. Only housekeeping staff can create requests.');
            <?php endif; ?>
        }
        
        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        function addItem() {
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4';
            newItem.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                    <select name="item_id[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <input type="number" name="quantity[]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeItem(this)" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
        }
        
        function removeItem(button) {
            button.closest('.grid').remove();
        }
        
        function addEditItem(itemId = '', quantity = '') {
            const container = document.getElementById('editItemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4';
            newItem.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                    <select name="item_id[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" ${itemId == <?php echo $item['id']; ?> ? 'selected' : ''}><?php echo htmlspecialchars($item['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <input type="number" name="quantity[]" min="1" value="${quantity}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removeEditItem(this)" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
        }
        
        function removeEditItem(button) {
            button.closest('.grid').remove();
        }
        
        function approveRequest(requestId) {
            <?php if ($user_role === 'manager'): ?>
                if (confirm('Are you sure you want to approve this request?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="approve_request">
                        <input type="hidden" name="request_id" value="${requestId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            <?php else: ?>
                alert('Access denied. Only managers can approve requests.');
            <?php endif; ?>
        }
        
        function rejectRequest(requestId) {
            <?php if ($user_role === 'manager'): ?>
                if (confirm('Are you sure you want to reject this request?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="reject_request">
                        <input type="hidden" name="request_id" value="${requestId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            <?php else: ?>
                alert('Access denied. Only managers can reject requests.');
            <?php endif; ?>
        }
        
        function viewRequest(requestId) {
            window.location.href = `?view=${requestId}`;
        }
        
        function editRequest(requestId) {
            <?php if ($user_role === 'housekeeping'): ?>
                // Fetch request details and populate edit modal
                fetch(`api/get-request-details.php?id=${requestId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Populate form fields
                            document.getElementById('edit_request_id').value = data.request.id;
                            document.querySelector('select[name="department"]').value = data.request.department;
                            document.querySelector('select[name="priority"]').value = data.request.priority;
                            document.getElementById('edit_required_date').value = data.request.required_date;
                            document.getElementById('edit_notes').value = data.request.notes;
                            
                            // Clear existing items
                            const container = document.getElementById('editItemsContainer');
                            container.innerHTML = '';
                            
                            // Add request items
                            data.request.items.forEach((item, index) => {
                                addEditItem(item.item_id, item.quantity_requested);
                            });
                            
                            // If no items, add one empty row
                            if (data.request.items.length === 0) {
                                addEditItem();
                            }
                            
                            // Show modal
                            document.getElementById('editModal').classList.remove('hidden');
                        } else {
                            alert('Error loading request details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading request details');
                    });
            <?php else: ?>
                alert('Access denied. Only housekeeping staff can edit requests.');
            <?php endif; ?>
        }
        
        function deleteRequest(requestId) {
            <?php if ($user_role === 'housekeeping'): ?>
                if (confirm('Are you sure you want to delete this request? This action cannot be undone.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete_request">
                        <input type="hidden" name="request_id" value="${requestId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            <?php else: ?>
                alert('Access denied. Only housekeeping staff can delete requests.');
            <?php endif; ?>
        }
        
        // Close modal when clicking outside
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });
        
        // Close edit modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Handle URL parameters for quick actions
        function handleUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            const status = urlParams.get('status');
            
            if (action === 'create') {
                // Open create modal for housekeeping
                <?php if ($user_role === 'housekeeping'): ?>
                    openCreateModal();
                <?php endif; ?>
            } else if (status === 'pending') {
                // Filter to show pending requests
                filterRequests('pending');
            }
        }
        
        // Call on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleUrlParameters();
        });
    </script>
</body>
</html>
