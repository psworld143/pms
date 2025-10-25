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

$existingSessionId = null;
$existingSessionData = null;
if (session_status() === PHP_SESSION_ACTIVE) {
    $existingSessionId = session_id();
    $existingSessionData = $_SESSION;
    session_write_close();
}

$workingSessionPath = null;
foreach ($sessionPaths as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }

    if (is_dir($path) && is_writable($path)) {
        $workingSessionPath = $path;
        break;
    }
}

if ($workingSessionPath) {
    ini_set('session.save_path', $workingSessionPath);
}

if ($existingSessionId !== null && $existingSessionId !== '') {
    session_id($existingSessionId);
}

session_start();

if ($existingSessionData !== null) {
    if (empty($_SESSION)) {
        $_SESSION = $existingSessionData;
    } else {
        $_SESSION = array_replace($existingSessionData, $_SESSION);
    }
}

if (!isset($_SESSION['pms_test'])) {
    $_SESSION['pms_test'] = 'session_working';
}
?>
