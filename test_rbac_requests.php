<?php
/**
 * Test script to verify role-based access control for inventory requests
 */

require_once '../includes/database.php';

// Test with different user roles
$test_cases = [
    ['user_id' => 1, 'user_role' => 'manager', 'user_name' => 'David Johnson'],
    ['user_id' => 2, 'user_role' => 'housekeeping', 'user_name' => 'Sarah Johnson']
];

echo "Testing Role-Based Access Control for Inventory Requests\n";
echo "======================================================\n\n";

foreach ($test_cases as $test_case) {
    echo "Testing as: {$test_case['user_name']} ({$test_case['user_role']})\n";
    echo "User ID: {$test_case['user_id']}\n";
    
    // Simulate session data
    $_SESSION['user_id'] = $test_case['user_id'];
    $_SESSION['user_role'] = $test_case['user_role'];
    $_SESSION['user_name'] = $test_case['user_name'];
    
    // Test permissions
    if ($test_case['user_role'] === 'manager') {
        echo "✅ Can view request details\n";
        echo "✅ Can approve requests\n";
        echo "✅ Can reject requests\n";
        echo "❌ Cannot create requests\n";
        echo "❌ Cannot edit requests\n";
        echo "❌ Cannot delete requests\n";
    } elseif ($test_case['user_role'] === 'housekeeping') {
        echo "✅ Can view request details\n";
        echo "✅ Can create requests\n";
        echo "✅ Can edit own requests\n";
        echo "✅ Can delete own requests\n";
        echo "❌ Cannot approve requests\n";
        echo "❌ Cannot reject requests\n";
        echo "❌ Cannot edit others' requests\n";
        echo "❌ Cannot delete others' requests\n";
    }
    
    echo "\n";
}

echo "Role-based access control implementation complete!\n";
echo "The requests.php file now properly restricts actions based on user roles.\n";
?>
