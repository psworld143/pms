<?php
session_id('cli-diagnose');
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';
$_SESSION['user_name'] = 'CLI Manager';

$_SERVER['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/booking/modules/management/user-management.php';
$_SERVER['SCRIPT_NAME'] = '/booking/modules/management/user-management.php';
$_SERVER['PHP_SELF'] = '/booking/modules/management/user-management.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require __DIR__ . '/booking/includes/booking-paths.php';
booking_initialize_paths();

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
require __DIR__ . '/booking/modules/management/user-management.php';
$output = ob_get_clean();

file_put_contents(__DIR__ . '/diagnose_output.html', $output);

echo "Completed\n";
