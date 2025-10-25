<?php
session_start();
$_SESSION["user_id"] = 1;
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "Test User";

require_once "api/get-maintenance-requests.php";
?>
