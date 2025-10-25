<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pms_pms_hotel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id, name, username, role FROM users WHERE username = 'sarah.johnson'");
    $user = $stmt->fetch();

    if ($user) {
        echo "✅ SUCCESS! User found:\n";
        echo "- ID: {$user['id']}\n";
        echo "- Name: {$user['name']}\n";
        echo "- Username: {$user['username']}\n";
        echo "- Role: {$user['role']}\n";
        echo "\n🔑 Login Credentials:\n";
        echo "Username: sarah.johnson\n";
        echo "Password: password\n";
        echo "\n🌐 Login at: http://localhost/pms/booking/login.php\n";
    } else {
        echo "❌ User 'sarah.johnson' not found\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
