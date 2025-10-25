<?php
require_once 'includes/database.php';

echo "Checking all users in database:\n\n";

try {
    $stmt = $pdo->query("SELECT id, name, username, role, is_active FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "No users found in database.\n";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "Name: {$user['name']}\n";
            echo "Username: {$user['username']}\n";
            echo "Role: {$user['role']}\n";
            echo "Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
