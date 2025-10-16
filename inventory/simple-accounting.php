<?php
/**
 * Simple Accounting Module Test
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Simple Accounting Test</title></head><body>";
echo "<h1>Simple Accounting Module Test</h1>";

echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "</p>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User is logged in</p>";
    if ($_SESSION['user_role'] === 'manager') {
        echo "<p style='color: green;'>✅ User is manager</p>";
        echo "<h2>Accounting Module Content</h2>";
        echo "<p>This is a test of the accounting module.</p>";
        echo "<p><a href='index.php'>Back to Dashboard</a></p>";
    } else {
        echo "<p style='color: red;'>❌ User is not manager - role: " . $_SESSION['user_role'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ User is not logged in</p>";
}

echo "</body></html>";
?>
