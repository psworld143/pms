<?php
function booking_base() {
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\','/', $_SERVER['SCRIPT_NAME']) : '';
    $path = $script !== '' ? $script : (isset($_SERVER['PHP_SELF']) ? str_replace('\\','/', $_SERVER['PHP_SELF']) : '/');
    $pos = strpos($path, '/booking/');
    if ($pos !== false) {
        return rtrim(substr($path, 0, $pos + strlen('/booking/')), '/') . '/';
    }
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
    return rtrim(booking_base(), '/') . '/' . ltrim($relative, '/');
}

// Test the function
echo 'Booking base: ' . booking_base() . PHP_EOL;
echo 'Dashboard URL: ' . booking_url('index.php') . PHP_EOL;
echo 'Management URL: ' . booking_url('modules/management/user-management.php') . PHP_EOL;
echo 'Front desk URL: ' . booking_url('modules/front-desk/check-in.php') . PHP_EOL;
?>
