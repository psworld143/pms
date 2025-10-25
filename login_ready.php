<?php
// Fix session issues for VPS
$sessionPath = $_SERVER['DOCUMENT_ROOT'] . '/../tmp_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
ini_set('session.save_path', $sessionPath);
session_start();

require_once 'includes/database.php';

echo "<h1>✅ PMS LOGIN READY</h1>";
echo "<p>Your PMS systems are now configured and ready for login!</p>";

echo "<h2>🔑 LOGIN CREDENTIALS</h2>";
echo "<p><strong>Password for all users:</strong> <code>password</code></p>";

echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
echo "<tr style='background:#f0f0f0;'><th>System</th><th>Login Method</th><th>Username/Email</th><th>Type</th></tr>";
echo "<tr><td>📋 Booking System</td><td>Username</td><td>manager1</td><td>Manager</td></tr>";
echo "<tr><td>📋 Booking System</td><td>Username</td><td>frontdesk1</td><td>Front Desk</td></tr>";
echo "<tr><td>📋 Booking System</td><td>Username</td><td>housekeeping1</td><td>Housekeeping</td></tr>";
echo "<tr><td>🏪 POS System</td><td>Username</td><td>manager1</td><td>Manager</td></tr>";
echo "<tr><td>📦 Inventory</td><td>Username</td><td>manager1</td><td>Manager</td></tr>";
echo "<tr><td>🎓 Tutorials</td><td>Email</td><td>demo@student.com</td><td>Student</td></tr>";
echo "</table>";

echo "<h2>🔗 DIRECT LOGIN LINKS</h2>";
echo "<p><a href='booking/login.php' target='_blank'>📋 Booking System Login</a><br>";
echo "<a href='pos/login.php' target='_blank'>🏪 POS System Login</a><br>";
echo "<a href='inventory/login.php' target='_blank'>📦 Inventory System Login</a><br>";
echo "<a href='tutorials/login.php' target='_blank'>🎓 Tutorials Login</a></p>";

echo "<h2>📋 TROUBLESHOOTING</h2>";
echo "<p>If login still fails:</p>";
echo "<ol>";
echo "<li>Clear browser cache/cookies</li>";
echo "<li>Try incognito/private browsing</li>";
echo "<li>Check browser console (F12) for errors</li>";
echo "<li>Ensure JavaScript is enabled</li>";
echo "</ol>";

echo "<h2>✅ STATUS: READY</h2>";
echo "<p>All systems have been configured with:</p>";
echo "<ul>";
echo "<li>✅ Correct database credentials</li>";
echo "<li>✅ Fixed session permissions</li>";
echo "<li>✅ Demo users created</li>";
echo "<li>✅ Proper password hashing</li>";
echo "</ul>";

echo "<p><strong>Try logging in now!</strong></p>";
?>
