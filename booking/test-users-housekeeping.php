<?php
session_start();
$_SESSION["user_id"] = 1073; // Valid user ID
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "David Johnson";

// Set the GET parameters
$_GET["role"] = "housekeeping";
$_GET["status"] = "active";

require_once "api/get-users.php";
