<?php
session_start();
$_SESSION["user_id"] = 1073; // Valid user ID
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "David Johnson";

// Simulate POST data with valid room ID
$_POST["room_id"] = "47"; // Valid room ID
$_POST["issue_type"] = "hvac";
$_POST["priority"] = "high";
$_POST["description"] = "Test maintenance request";
$_POST["estimated_cost"] = "50.00";

// Set content type
$_SERVER["CONTENT_TYPE"] = "application/x-www-form-urlencoded";
$_SERVER["REQUEST_METHOD"] = "POST";

require_once "api/create-maintenance-request.php";
?>
