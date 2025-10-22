<?php
/**
 * Test get-users.php API with session bypass and API key
 */

echo "Testing get-users.php API with session bypass and API key...\n";

// Test the API endpoint with session bypass
$testScript = '<?php
session_start();
$_SESSION["user_id"] = 1073; // Valid user ID
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "David Johnson";

// Set the GET parameters
$_GET["role"] = "housekeeping";
$_GET["status"] = "active";

// Set API key header
$_SERVER["HTTP_X_API_KEY"] = "pms_users_api_2024";

require_once "api/get-users.php";
?>';

file_put_contents('test-users-session.php', $testScript);

$output = shell_exec('php test-users-session.php 2>&1');
echo "API Output:\n";
echo $output . "\n";

// Clean up
unlink('test-users-session.php');
?>
