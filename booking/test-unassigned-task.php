<?php
/**
 * Test creating an unassigned housekeeping task
 */

echo "Testing unassigned task creation...\n";

// Test the API endpoint with session bypass
$testScript = '<?php
session_start();
$_SESSION["user_id"] = 1073; // Valid user ID
$_SESSION["user_role"] = "manager";
$_SESSION["name"] = "David Johnson";

// Set the POST data for unassigned task
$_SERVER["CONTENT_TYPE"] = "application/json";
$_SERVER["REQUEST_METHOD"] = "POST";

// Create test data
$input = json_encode([
    "room_id" => 47,
    "task_type" => "daily_cleaning",
    "assigned_to" => null, // Unassigned
    "scheduled_time" => "2025-10-22 10:00:00",
    "notes" => "Test unassigned task"
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

require_once "api/create-housekeeping-task.php";
?>';

file_put_contents('test-unassigned-task.php', $testScript);

$output = shell_exec('php test-unassigned-task.php 2>&1');
echo "API Output:\n";
echo $output . "\n";

// Clean up
unlink('test-unassigned-task.php');

// Check if the task was created as unassigned
echo "\nChecking created task...\n";
require_once 'config/database.php';

$stmt = $pdo->query("
    SELECT 
        ht.id,
        ht.task_type,
        ht.status,
        ht.assigned_to,
        r.room_number
    FROM housekeeping_tasks ht
    LEFT JOIN rooms r ON ht.room_id = r.id
    ORDER BY ht.id DESC
    LIMIT 1
");

$task = $stmt->fetch(PDO::FETCH_ASSOC);
if ($task) {
    echo "Latest task: ID {$task['id']}, Type: {$task['task_type']}, Room: {$task['room_number']}, Assigned: " . ($task['assigned_to'] ? "YES" : "NO") . "\n";
} else {
    echo "No tasks found\n";
}
?>
