<?php
/**
 * Check existing users and add sarah.johnson if needed
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=pms_pms_hotel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking existing users...\n";

    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (empty($tables)) {
        echo "✗ Users table doesn't exist. Creating it...\n";

        // Create users table
        $pdo->exec("
            CREATE TABLE users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE,
                role ENUM('front_desk', 'housekeeping', 'manager') NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "✓ Users table created\n";
    }

    // Check existing users
    $stmt = $pdo->query("SELECT id, name, username, role FROM users");
    $users = $stmt->fetchAll();

    echo "\nExisting users:\n";
    if (empty($users)) {
        echo "No users found.\n";
    } else {
        foreach ($users as $user) {
            echo "- ID: {$user['id']}, Name: {$user['name']}, Username: {$user['username']}, Role: {$user['role']}\n";
        }
    }

    // Check if sarah.johnson exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['sarah.johnson']);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "\n✓ User 'sarah.johnson' already exists (ID: {$existingUser['id']})\n";
    } else {
        echo "\n✗ User 'sarah.johnson' not found. Adding it...\n";

        // Add sarah.johnson user
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Sarah Johnson', 'sarah.johnson', $hashedPassword, 'sarah.johnson@hotel.com', 'manager', 1]);

        echo "✓ User 'sarah.johnson' added successfully\n";
    }

    echo "\n🎉 Setup completed!\n";
    echo "You can now login with:\n";
    echo "- Username: sarah.johnson\n";
    echo "- Password: password\n";
    echo "- Login URL: http://localhost/pms/booking/login.php\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
