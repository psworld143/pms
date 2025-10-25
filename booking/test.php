<?php
/**
 * Simple Test File - Check if PHP is working
 * Visit: https://pms.seait.edu.ph/booking/test.php
 */

// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>✅ PHP is Working!</h1>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";

echo "<h2>File Checks:</h2>";
echo "<ul>";
echo "<li>database.php: " . (file_exists('../includes/database.php') ? '✅ EXISTS' : '❌ NOT FOUND') . "</li>";
echo "<li>functions.php: " . (file_exists('includes/functions.php') ? '✅ EXISTS' : '❌ NOT FOUND') . "</li>";
echo "<li>vps_session_fix.php: " . (file_exists('../vps_session_fix.php') ? '✅ EXISTS' : '❌ NOT FOUND') . "</li>";
echo "</ul>";

echo "<h2>Next Steps:</h2>";
if (!file_exists('../includes/database.php')) {
    echo "<p style='color:red;'>⚠️ Files are NOT deployed! Run: <code>git clone https://github.com/psworld143/pms.git .</code></p>";
} else {
    echo "<p style='color:green;'>✅ Files appear to be deployed. Try <a href='login.php'>login.php</a></p>";
}

echo "<hr><p><a href='../server_status.php'>Check Full Server Status</a></p>";
?>

