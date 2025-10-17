<?php
// Debug sarah.johnson login issue
require_once 'includes/database.php';

echo "Debugging sarah.johnson login issue...\n\n";

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE username = ?");
    $stmt->execute(['sarah.johnson']);
    $user = $stmt->fetch();

    if ($user) {
        echo "✅ User found:\n";
        echo "- ID: {$user['id']}\n";
        echo "- Name: {$user['name']}\n";
        echo "- Username: {$user['username']}\n";
        echo "- Role: {$user['role']}\n";
        echo "- Password hash: " . substr($user['password'], 0, 20) . "...\n\n";

        // Test password verification
        $test_password = 'password';
        $is_valid = password_verify($test_password, $user['password']);

        echo "Password verification test:\n";
        echo "- Test password: '$test_password'\n";
        echo "- Hash matches: " . ($is_valid ? '✅ YES' : '❌ NO') . "\n";

        // Check if password needs rehashing
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            echo "- Password needs rehashing: YES\n";

            // Rehash the password
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            echo "- New hash: " . substr($new_hash, 0, 20) . "...\n";

            // Update the password in database
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->execute([$new_hash, $user['id']]);
            echo "- Password updated in database\n";

            // Test again
            $is_valid_after = password_verify($test_password, $new_hash);
            echo "- New hash verification: " . ($is_valid_after ? '✅ YES' : '❌ NO') . "\n";
        } else {
            echo "- Password hash is current\n";
        }

    } else {
        echo "❌ User 'sarah.johnson' not found\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
