<?php
/**
 * Add sarah.johnson user to existing database
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=pms_pms_hotel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if sarah.johnson already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['sarah.johnson']);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "✓ User 'sarah.johnson' already exists (ID: {$existingUser['id']})\n";
    } else {
        echo "Adding user 'sarah.johnson'...\n";

        // Add sarah.johnson user with password 'password'
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Sarah Johnson', 'sarah.johnson', $hashedPassword, 'sarah.johnson@hotel.com', 'manager', 1]);

        echo "✓ User 'sarah.johnson' added successfully\n";
    }

    echo "\n🎉 Login credentials ready!\n";
    echo "Username: sarah.johnson\n";
    echo "Password: password\n";
    echo "Role: Manager\n";
    echo "\nLogin URL: http://localhost/pms/booking/login.php\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
