<?php
// Unified header component that automatically selects the appropriate navbar
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Get school logo and abbreviation from database
$school_logo = null; // Will be set later if needed
$school_abbreviation = 'Hotel PMS'; // Default abbreviation

// Dynamic base URL for the Booking module
// Computes the absolute path to the "/booking/" folder based on the current script
if (!function_exists('booking_base')) {
    function booking_base() {
        $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\','/', $_SERVER['SCRIPT_NAME']) : '';
        $path = $script !== '' ? $script : (isset($_SERVER['PHP_SELF']) ? str_replace('\\','/', $_SERVER['PHP_SELF']) : '/');
        $pos = strpos($path, '/booking/');
        if ($pos !== false) {
            return rtrim(substr($path, 0, $pos + strlen('/booking/')), '/') . '/';
        }
        // Fallback: walk up directories until we reach "booking"
        $dir = str_replace('\\','/', dirname($path));
        $guard = 0;
        while ($dir !== '/' && $dir !== '.' && basename($dir) !== 'booking' && $guard < 10) {
            $dir = dirname($dir);
            $guard++;
        }
        if (basename($dir) === 'booking') {
            return rtrim($dir, '/') . '/';
        }
        return '/booking/';
    }
    function booking_url($relative = '') {
        $base = booking_base();
        return rtrim($base, '/') . '/' . ltrim($relative, '/');
    }
    function booking_asset($relative = '') {
        return booking_url('assets/' . ltrim($relative, '/'));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo htmlspecialchars($school_abbreviation); ?> Hotel PMS</title>
    <!-- Favicon Configuration -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' fill='%234F46E5'/><text x='16' y='22' font-family='Arial' font-size='18' font-weight='bold' text-anchor='middle' fill='white'>H</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Sidebar mobile responsiveness */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        /* Mobile: sidebar starts hidden */
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
                z-index: 50;
            }
            #sidebar.sidebar-open {
                transform: translateX(0);
            }
        }
        
        /* Desktop: sidebar always visible */
        @media (min-width: 1024px) {
            #sidebar {
                transform: translateX(0) !important;
            }
        }
        
        #sidebar-overlay {
            transition: opacity 0.3s ease-in-out;
            z-index: 40;
        }
        
        /* Responsive layout fixes */
        .main-content {
            margin-left: 0;
            padding-top: 4rem; /* 64px for navbar */
        }
        
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem; /* 256px for sidebar */
            }
        }
        
        /* Mobile sidebar improvements */
        @media (max-width: 1023px) {
            #sidebar {
                z-index: 50;
            }
            #sidebar-overlay {
                z-index: 40;
            }
        }
    </style>
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
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>
        <?php
        // Include the appropriate navbar based on user role
        switch ($user_role) {
            case 'manager':
                include 'navbar-manager.php';
                break;
            case 'front_desk':
                include 'navbar-frontdesk.php';
                break;
            case 'housekeeping':
                include 'navbar-housekeeping.php';
                break;
            default:
                // Fallback to generic navbar
                include 'header.php';
                break;
        }
        ?>
