<?php
/**
 * Test create maintenance request API with valid room ID
 */

echo "Testing create maintenance request API with valid room ID...\n";

// Test the API endpoint with session bypass
$testScript = '<?php
session_start();
$_SESSION["user_id"] = 1;
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "Test User";

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
?>';

file_put_contents('test-create-maintenance-valid.php', $testScript);

$output = shell_exec('php test-create-maintenance-valid.php 2>&1');
echo "API Output:\n";
echo $output . "\n";

// Clean up
unlink('test-create-maintenance-valid.php');
?>

