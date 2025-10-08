<?php
session_id('cli-render');
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';
$_SESSION['user_name'] = 'CLI Manager';

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/booking/modules/management/user-management.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/booking/modules/management/user-management.php';
$_SERVER['PHP_SELF'] = '/booking/modules/management/user-management.php';

require __DIR__ . '/booking/includes/booking-paths.php';
booking_initialize_paths();

ob_start();
require __DIR__ . '/booking/modules/management/user-management.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/render_user_management.html', $html);

echo "rendered";
