<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once dirname(__DIR__, 2) . '/../vps_session_fix.php';
require_once dirname(__DIR__, 2) . '/../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
    header('Location: ../../login.php');
    exit();
}

$guest_id = $_GET['id'] ?? null;

if (!$guest_id) {
    header('Location: guest-management.php');
    exit();
}

// Get guest details
try {
    $sql = "SELECT * FROM guests WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guest_id]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$guest) {
        header('Location: guest-management.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error getting guest: " . $e->getMessage());
    header('Location: guest-management.php');
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if guest has active reservations
        $activeReservationsSql = "
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE guest_id = ? AND status IN ('confirmed', 'checked_in')
        ";
        
        $activeStmt = $pdo->prepare($activeReservationsSql);
        $activeStmt->execute([$guest_id]);
        $activeCount = $activeStmt->fetch()['count'];
        
        if ($activeCount > 0) {
            header('Location: guest-management.php?error=active_reservations');
            exit();
        }
        
        // Delete guest
        $deleteSql = "DELETE FROM guests WHERE id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$guest_id]);
        
        header('Location: guest-management.php?success=deleted');
        exit();
        
    } catch (PDOException $e) {
        error_log("Error deleting guest: " . $e->getMessage());
        header('Location: guest-management.php?error=delete_failed');
        exit();
    }
}

$page_title = 'Delete Guest';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Delete Guest</h1>
            <p class="text-gray-600">Confirm guest deletion</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
                        </h3>
                        <p class="text-sm text-gray-500"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['email']); ?></p>
                    </div>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Warning</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>This action cannot be undone. Deleting this guest will permanently remove all their information from the system.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="flex justify-end space-x-3">
                <a href="guest-management.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Delete Guest
                </button>
            </form>
        </div>
    </div>
</main>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); include '../../includes/footer.php'; ?>
