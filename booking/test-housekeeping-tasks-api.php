<?php
session_start();
$_SESSION["user_id"] = 1073; // Valid user ID
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "David Johnson";

require_once "api/get-recent-housekeeping-tasks.php";
