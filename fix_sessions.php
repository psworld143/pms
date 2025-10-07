<?php
/**
 * Session Fix Script for VPS
 * Diagnose and fix session permission issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>VPS Session Fix Tool</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

echo "<h2>Current Session Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session Save Path: " . ini_get('session.save_path') . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

echo "<h2>Testing Session Directory</h2>";

$currentPath = ini_get('session.save_path');
$testPath = '/tmp/sessions';

if (!is_dir($currentPath)) {
    echo "<p>❌ Current session path doesn't exist: $currentPath</p>";
} else {
    echo "<p>✅ Current session path exists: $currentPath</p>";
    $writable = is_writable($currentPath) ? '✅ Writable' : '❌ Not Writable';
    echo "<p>📁 Directory permissions: $writable</p>";
}

// Try to create a custom session directory in public_html
echo "<h2>Creating Custom Session Directory</h2>";

$customSessionPath = $_SERVER['DOCUMENT_ROOT'] . '/tmp_sessions';

if (!is_dir($customSessionPath)) {
    if (mkdir($customSessionPath, 0755, true)) {
        echo "<p>✅ Created custom session directory: $customSessionPath</p>";

        // Create .htaccess to protect session files
        $htaccessContent = "Order deny,allow\nDeny from all";
        if (file_put_contents($customSessionPath . '/.htaccess', $htaccessContent)) {
            echo "<p>✅ Created .htaccess to protect session files</p>";
        } else {
            echo "<p>⚠️ Could not create .htaccess file</p>";
        }
    } else {
        echo "<p>❌ Failed to create custom session directory</p>";
    }
} else {
    echo "<p>✅ Custom session directory already exists: $customSessionPath</p>";
}

// Generate PHP code to fix session path
echo "<h2>Fix Session Configuration</h2>";
echo "<p>Add this code to the top of your login.php files (before session_start):</p>";
echo '<pre style="background:#f5f5f5;padding:10px;border:1px solid #ddd;">' .
     htmlspecialchars('// Set custom session save path
if (!is_dir(\'' . $customSessionPath . '\')) {
    mkdir(\'' . $customSessionPath . '\', 0755, true);
}
ini_set(\'session.save_path\', \'' . $customSessionPath . '\');
session_start();') .
     '</pre>';

echo "<p><strong>Or create a session configuration file:</strong></p>";
echo "<p>Create a file called <code>session_config.php</code> in your PMS root:</p>";
echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;'><?php
// Session configuration for VPS
$sessionPath = __DIR__ . '/tmp_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
ini_set('session.save_path', $sessionPath);

// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // Set to your domain if needed
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
?></pre>";

echo "<p>Then include it at the top of your login files:</p>";
echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;'>require_once 'session_config.php';</pre>";

echo "<h2>Test Session After Fix</h2>";
echo "<p>Try this test after implementing the fix:</p>";
echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;'><?php
require_once 'session_config.php';
echo 'Session ID: ' . session_id() . '<br>';
echo 'Session Path: ' . ini_get('session.save_path') . '<br>';
echo 'Session working: ' . (isset($_SESSION) ? 'YES' : 'NO');
?></pre>";

echo "<h2>Alternative VPS Session Fixes</h2>";
echo "<p>If the above doesn't work, try these VPS-specific solutions:</p>";
echo "<ul>";
echo "<li><strong>Check CyberPanel PHP Configuration</strong> - Look for session.save_path in PHP settings</li>";
echo "<li><strong>Contact Hostinger Support</strong> - Ask them to fix session directory permissions</li>";
echo "<li><strong>Use Database Sessions</strong> - Store sessions in MySQL instead of files</li>";
echo "<li><strong>Check File Ownership</strong> - Ensure files are owned by the correct user</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Create the session_config.php file as shown above</li>";
echo "<li>Include it at the top of all login.php files</li>";
echo "<li>Test the diagnostic script again</li>";
echo "<li>If still failing, try the alternative solutions</li>";
echo "</ol>";
?>
