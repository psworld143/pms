<?php
/**
 * Test assign housekeeping task API
 */

echo "Testing assign housekeeping task API...\n";

// Test the API endpoint with session bypass
$testScript = '<?php
session_start();
$_SESSION["user_id"] = 1073; // Valid user ID
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "David Johnson";

// Simulate POST data
$_SERVER["CONTENT_TYPE"] = "application/json";
$_SERVER["REQUEST_METHOD"] = "POST";

// Create a test task first
require_once "config/database.php";
$stmt = $pdo->prepare("INSERT INTO housekeeping_tasks (room_id, task_type, status, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt->execute([47, "cleaning", "pending", 1073]);
$taskId = $pdo->lastInsertId();
echo "Created test task ID: $taskId\n";

// Simulate JSON input
$input = json_encode([
    "task_id" => $taskId,
    "user_id" => 1072 // Maria Garcia
]);

// Override php://input
$GLOBALS["test_input"] = $input;

// Override file_get_contents for php://input
function file_get_contents($filename) {
    if ($filename === "php://input") {
        return $GLOBALS["test_input"];
    }
    return \file_get_contents($filename);
}

require_once "api/assign-housekeeping-task.php";
?>';

file_put_contents('test-assign-task.php', $testScript);

$output = shell_exec('php test-assign-task.php 2>&1');
echo "API Output:\n";
echo $output . "\n";

// Clean up
unlink('test-assign-task.php');
?>
