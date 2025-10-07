<?php
// Login Test Script
require_once 'includes/database.php';

echo "Testing login functionality...\n\n";

$test_credentials = [
    ['username' => 'manager1', 'password' => 'password', 'expected_role' => 'manager'],
    ['username' => 'frontdesk1', 'password' => 'password', 'expected_role' => 'front_desk'],
    ['username' => 'housekeeping1', 'password' => 'password', 'expected_role' => 'housekeeping'],
    ['username' => 'sarah.johnson', 'password' => 'password', 'expected_role' => 'manager'],
    ['username' => 'student1', 'password' => 'password', 'expected_role' => 'student'],
];

foreach ($test_credentials as $cred) {
    try {
        $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$cred['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($cred['password'], $user['password'])) {
            $role_matches = $user['role'] === $cred['expected_role'];
            echo "✅ {$cred['username']}: LOGIN SUCCESS (Role: {$user['role']}" . ($role_matches ? ' ✓' : ' ⚠️ Expected: ' . $cred['expected_role']) . ")\n";
        } else {
            echo "❌ {$cred['username']}: LOGIN FAILED\n";
        }
    } catch (Exception $e) {
        echo "❌ {$cred['username']}: ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Login testing completed!\n";
echo "All users should now be able to login with password 'password'\n";
?>
