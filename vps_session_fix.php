<?php
/**
 * Session Fix for VPS
 * Robust session configuration for LiteSpeed/CyberPanel
 */

// Try multiple session paths until one works
$sessionPaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/tmp_sessions',
    '/tmp/pms_sessions',
    $_SERVER['DOCUMENT_ROOT'] . '/../tmp_sessions',
    sys_get_temp_dir() . '/pms_sessions'
];

$workingSessionPath = null;
foreach ($sessionPaths as $path) {
    if (mkdir($path, 0755, true) || is_dir($path)) {
        if (is_writable($path)) {
            $workingSessionPath = $path;
            break;
        }
    }
}

if ($workingSessionPath) {
    ini_set('session.save_path', $workingSessionPath);
    session_start();

    // Test if session works
    $_SESSION['pms_test'] = 'session_working';
    $sessionWorking = isset($_SESSION['pms_test']);

    if ($sessionWorking) {
        echo "<p>✅ Session configured successfully</p>";
        echo "<p>📍 Session path: $workingSessionPath</p>";
        echo "<p>🔑 Session ID: " . session_id() . "</p>";
    } else {
        echo "<p>❌ Session configuration failed</p>";
    }
} else {
    echo "<p>❌ Could not find/create writable session directory</p>";
    // Fallback to default session handling
    session_start();
}
?>
