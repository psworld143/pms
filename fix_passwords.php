<?php
/**
 * Password Fix Script
 * Fix password hashes for existing users if they're incorrect
 */

require_once 'includes/database.php';

echo "<h1>Password Hash Fix Tool</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;}</style>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Password Fixes...</h2>";

    $fixAll = isset($_POST['fix_all']);

    if ($fixAll) {
        // Fix all demo users' passwords
        $usersToFix = [
            'manager1', 'manager2', 'sarah.johnson', 'frontdesk1', 'frontdesk2',
            'housekeeping1', 'housekeeping2', 'demo_student', 'student1', 'student2', 'student3'
        ];

        $fixed = 0;
        foreach ($usersToFix as $username) {
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                $currentHash = $user['password'];
                $correctHash = password_hash('password', PASSWORD_DEFAULT);

                if ($currentHash !== $correctHash && !password_verify('password', $currentHash)) {
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$correctHash, $user['id']]);
                    echo "<span class='success'>✅ Fixed password for $username</span><br>";
                    $fixed++;
                } else {
                    echo "<span class='success'>✅ Password already correct for $username</span><br>";
                }
            } else {
                echo "<span class='error'>❌ User $username not found</span><br>";
            }
        }
        echo "<p><strong>Total passwords fixed: $fixed</strong></p>";
    }
}

echo "<h2>Current Password Status</h2>";
echo "<p>This tool will check and fix password hashes if they're incorrect.</p>";

$demoUsers = [
    'manager1' => 'Manager',
    'frontdesk1' => 'Front Desk',
    'housekeeping1' => 'Housekeeping',
    'sarah.johnson' => 'Manager',
    'demo_student' => 'Student'
];

echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
echo "<tr style='background:#f0f0f0;'><th>Username</th><th>Type</th><th>Password Status</th></tr>";

foreach ($demoUsers as $username => $type) {
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = $user['password'];
            $isCorrect = password_verify('password', $hash);
            $status = $isCorrect ?
                "<span class='success'>✅ Correct</span>" :
                "<span class='error'>❌ Incorrect</span>";
        } else {
            $status = "<span class='error'>❌ User not found</span>";
        }

        echo "<tr><td>$username</td><td>$type</td><td>$status</td></tr>";
    } catch (Exception $e) {
        echo "<tr><td>$username</td><td>$type</td><td><span class='error'>Error: " . $e->getMessage() . "</span></td></tr>";
    }
}
echo "</table>";

echo "<h2>Fix All Passwords</h2>";
echo "<p>Click the button below to fix all password hashes for demo users:</p>";
echo "<form method='POST'>";
echo "<input type='hidden' name='fix_all' value='1'>";
echo "<button type='submit' onclick='return confirm(\"This will update password hashes for all demo users. Continue?\")' style='background:#007cba;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;'>🔧 Fix All Password Hashes</button>";
echo "</form>";

echo "<hr>";
echo "<h2>Manual Password Check</h2>";
echo "<p>Test a specific user's password:</p>";
echo "<form method='GET'>";
echo "Username: <input type='text' name='test_user' value='" . ($_GET['test_user'] ?? '') . "'>";
echo "<button type='submit'>Test Password</button>";
echo "</form>";

if (isset($_GET['test_user']) && !empty($_GET['test_user'])) {
    $testUsername = $_GET['test_user'];
    try {
        $stmt = $pdo->prepare("SELECT name, password FROM users WHERE username = ?");
        $stmt->execute([$testUsername]);
        $user = $stmt->fetch();

        if ($user) {
            $isCorrect = password_verify('password', $user['password']);
            echo "<h3>Results for '$testUsername':</h3>";
            echo "User: {$user['name']}<br>";
            echo "Password 'password' verification: " . ($isCorrect ? "<span class='success'>✅ CORRECT</span>" : "<span class='error'>❌ FAILED</span>") . "<br>";
            echo "Stored hash: " . substr($user['password'], 0, 30) . "...<br>";
        } else {
            echo "<span class='error'>❌ User '$testUsername' not found</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
    }
}
?>
