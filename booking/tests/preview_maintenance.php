<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';
require __DIR__ . '/../modules/management/maintenance-management.php';
