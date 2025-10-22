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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "
            UPDATE guests SET 
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                is_vip = ?,
                id_type = ?,
                id_number = ?,
                address = ?,
                date_of_birth = ?,
                nationality = ?,
                preferences = ?,
                service_notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            isset($_POST['is_vip']) ? 1 : 0,
            $_POST['id_type'] ?? null,
            $_POST['id_number'] ?? null,
            $_POST['address'] ?? null,
            $_POST['date_of_birth'] ?? null,
            $_POST['nationality'] ?? null,
            $_POST['preferences'] ?? null,
            $_POST['service_notes'] ?? null,
            $guest_id
        ]);
        
        header('Location: guest-management.php?success=1');
        exit();
        
    } catch (PDOException $e) {
        error_log("Error updating guest: " . $e->getMessage());
        $error = "Error updating guest. Please try again.";
    }
}

$page_title = 'Edit Guest';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Guest</h1>
            <p class="text-gray-600">Update guest information</p>
        </div>
        
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); if (isset($error)): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($error); ?>
            </div>
        <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); endif; ?>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['first_name']); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['last_name']); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['email']); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['phone']); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="id_number" class="block text-sm font-medium text-gray-700">ID Number</label>
                        <input type="text" id="id_number" name="id_number"
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['id_number'] ?? ''); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_vip" name="is_vip" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo $guest['is_vip'] ? 'checked' : ''; ?> class="mr-2">
                        <label for="is_vip" class="text-sm font-medium text-gray-700">VIP Guest</label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" id="address" name="address"
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['address'] ?? ''); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['date_of_birth'] ?? ''); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality</label>
                        <input type="text" id="nationality" name="nationality"
                               value="<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['nationality'] ?? ''); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="id_type" class="block text-sm font-medium text-gray-700">ID Type</label>
                        <select id="id_type" name="id_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                            <option value="">Select ID Type</option>
                            <option value="passport" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo ($guest['id_type'] ?? '') === 'passport' ? 'selected' : ''; ?>>Passport</option>
                            <option value="driver_license" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo ($guest['id_type'] ?? '') === 'driver_license' ? 'selected' : ''; ?>>Driver License</option>
                            <option value="national_id" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo ($guest['id_type'] ?? '') === 'national_id' ? 'selected' : ''; ?>>National ID</option>
                            <option value="other" <?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo ($guest['id_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="preferences" class="block text-sm font-medium text-gray-700">Preferences</label>
                        <textarea id="preferences" name="preferences" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['preferences'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="service_notes" class="block text-sm font-medium text-gray-700">Service Notes</label>
                        <textarea id="service_notes" name="service_notes" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"><?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); echo htmlspecialchars($guest['service_notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="guest-management.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark">
                        Update Guest
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start(); include '../../includes/footer.php'; ?>
