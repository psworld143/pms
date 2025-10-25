<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';
require __DIR__ . '/../api/get-analytics-kpis.php';
