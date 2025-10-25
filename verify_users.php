<?php
/**
 * User Verification Script
 * Check if all demo users exist and have correct data
 */

require_once 'includes/database.php';

echo "<h1>User Verification Tool</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

try {
    // Check users table structure
    echo "<h2>Database Users Check</h2>";

    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Users Table Structure:</h3><ul>";
    foreach ($columns as $col) {
        echo "<li>{$col['Field']} ({$col['Type']}) - " . ($col['Key'] === 'PRI' ? 'Primary Key' : ($col['Key'] === 'UNI' ? 'Unique' : 'Normal')) . "</li>";
    }
    echo "</ul>";

    // Check existing users
    echo "<h2>Current Users in Database</h2>";
    $stmt = $pdo->query("SELECT id, name, username, email, role, is_active, password FROM users ORDER BY username");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($allUsers)) {
        echo "<div class='error'>❌ No users found in database!</div>";
        echo "<p>You need to run the SQL INSERT statements first.</p>";
    } else {
        echo "<p>📊 Total users: " . count($allUsers) . "</p>";
        echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
        echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Password Hash</th></tr>";

        foreach ($allUsers as $user) {
            $isActive = $user['is_active'] ? '✅ Yes' : '❌ No';
            $hashPreview = substr($user['password'], 0, 15) . '...';
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>$isActive</td>";
            echo "<td><small>$hashPreview</small></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Check for specific demo users
    echo "<h2>Demo Users Status</h2>";
    $demoUsers = [
        'manager1' => ['name' => 'David Johnson', 'role' => 'manager'],
        'manager2' => ['name' => 'Emily Chen', 'role' => 'manager'],
        'sarah.johnson' => ['name' => 'Sarah Johnson', 'role' => 'manager'],
        'frontdesk1' => ['name' => 'John Smith', 'role' => 'front_desk'],
        'frontdesk2' => ['name' => 'Sarah Wilson', 'role' => 'front_desk'],
        'housekeeping1' => ['name' => 'Maria Garcia', 'role' => 'housekeeping'],
        'housekeeping2' => ['name' => 'Carlos Rodriguez', 'role' => 'housekeeping'],
        'demo_student' => ['name' => 'Demo Student', 'role' => 'student'],
        'student1' => ['name' => 'Student One', 'role' => 'student'],
        'student2' => ['name' => 'Student Two', 'role' => 'student'],
        'student3' => ['name' => 'Student Three', 'role' => 'student'],
    ];

    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr style='background:#f0f0f0;'><th>Username</th><th>Expected Name</th><th>Expected Role</th><th>Status</th><th>Actions</th></tr>";

    foreach ($demoUsers as $username => $expected) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, name, role, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $status = ($user['name'] === $expected['name'] && $user['role'] === $expected['role'] && $user['is_active'])
                     ? "<span class='success'>✅ Complete</span>"
                     : "<span class='warning'>⚠️ Data Mismatch</span>";

            $actions = "<button onclick=\"fixUser('$username')\">Fix</button>";
        } else {
            $status = "<span class='error'>❌ Missing</span>";
            $actions = "<button onclick=\"createUser('$username')\">Create</button>";
        }

        echo "<tr>";
        echo "<td>$username</td>";
        echo "<td>{$expected['name']}</td>";
        echo "<td>{$expected['role']}</td>";
        echo "<td>$status</td>";
        echo "<td>$actions</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Password test
    echo "<h2>Password Verification Test</h2>";
    $testPassword = 'password';
    $expectedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    if (password_verify($testPassword, $expectedHash)) {
        echo "<span class='success'>✅ Password hash is correct for '$testPassword'</span><br>";
    } else {
        echo "<span class='error'>❌ Password hash verification failed!</span><br>";
        echo "Expected hash verification: " . (password_verify($testPassword, $expectedHash) ? '✅' : '❌') . "<br>";
        echo "New hash for '$testPassword': " . password_hash($testPassword, PASSWORD_DEFAULT) . "<br>";
    }

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<script>
function fixUser(username) {
    alert('To fix user ' + username + ', run the SQL INSERT statement for that user');
}

function createUser(username) {
    alert('To create user ' + username + ', run the SQL INSERT statement for that user');
}
</script>";
?>
