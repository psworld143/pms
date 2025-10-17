<?php
// Complete User Creation Script for PMS System
require_once 'includes/database.php';

echo "Creating all demo users for PMS System...\n\n";

$demo_users = [
    // Management users
    ['name' => 'David Johnson', 'username' => 'manager1', 'email' => 'david@hotel.com', 'role' => 'manager'],
    ['name' => 'Emily Chen', 'username' => 'manager2', 'email' => 'emily@hotel.com', 'role' => 'manager'],
    ['name' => 'Sarah Johnson', 'username' => 'sarah.johnson', 'email' => 'sarah.johnson@hotel.com', 'role' => 'manager'],

    // Front Desk users
    ['name' => 'John Smith', 'username' => 'frontdesk1', 'email' => 'john@hotel.com', 'role' => 'front_desk'],
    ['name' => 'Sarah Wilson', 'username' => 'frontdesk2', 'email' => 'sarah@hotel.com', 'role' => 'front_desk'],
    ['name' => 'Elena Rodriguez', 'username' => 'elena.rodriguez', 'email' => 'elena@hotel.com', 'role' => 'front_desk'],
    ['name' => 'David Park', 'username' => 'david.park', 'email' => 'david@hotel.com', 'role' => 'front_desk'],

    // Housekeeping users
    ['name' => 'Maria Garcia', 'username' => 'housekeeping1', 'email' => 'maria@hotel.com', 'role' => 'housekeeping'],
    ['name' => 'Carlos Rodriguez', 'username' => 'housekeeping2', 'email' => 'carlos@hotel.com', 'role' => 'housekeeping'],
    ['name' => 'James Wilson', 'username' => 'james.wilson', 'email' => 'james@hotel.com', 'role' => 'housekeeping'],
    ['name' => 'Michael Chen', 'username' => 'michael.chen', 'email' => 'michael@hotel.com', 'role' => 'housekeeping'],
    ['name' => 'Lisa Thompson', 'username' => 'lisa.thompson', 'email' => 'lisa@hotel.com', 'role' => 'housekeeping'],

    // Student users for tutorials
    ['name' => 'Demo Student', 'username' => 'demo_student', 'email' => 'demo@student.com', 'role' => 'student'],
    ['name' => 'John Student', 'username' => 'john_student', 'email' => 'john@student.com', 'role' => 'student'],
    ['name' => 'Jane Learner', 'username' => 'jane_learner', 'email' => 'jane@student.com', 'role' => 'student'],
    ['name' => 'Student One', 'username' => 'student1', 'email' => 'student1@demo.com', 'role' => 'student'],
    ['name' => 'Student Two', 'username' => 'student2', 'email' => 'student2@demo.com', 'role' => 'student'],
    ['name' => 'Student Three', 'username' => 'student3', 'email' => 'student3@demo.com', 'role' => 'student'],
];

try {
    foreach ($demo_users as $user) {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$user['username'], $user['email']]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            echo "✓ User {$user['username']} already exists (ID: {$existingUser['id']})\n";
        } else {
            // Create new user
            $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['name'],
                $user['username'],
                $hashedPassword,
                $user['email'],
                $user['role'],
                1
            ]);

            echo "✓ Created user: {$user['username']} ({$user['name']}) - {$user['role']}\n";
        }
    }

    echo "\n🎉 All demo users created successfully!\n\n";

    // Display login credentials for each module
    echo "=== LOGIN CREDENTIALS ===\n\n";

    echo "📋 BOOKING SYSTEM (http://localhost/pms/booking/login.php):\n";
    echo "Username: sarah.johnson, Password: password (Manager)\n";
    echo "Username: manager1, Password: password (Manager)\n";
    echo "Username: manager2, Password: password (Manager)\n";
    echo "Username: frontdesk1, Password: password (Front Desk)\n";
    echo "Username: frontdesk2, Password: password (Front Desk)\n";
    echo "Username: housekeeping1, Password: password (Housekeeping)\n";
    echo "Username: housekeeping2, Password: password (Housekeeping)\n\n";

    echo "🏪 POS SYSTEM (http://localhost/pms/pos/login.php):\n";
    echo "Username: manager1, Password: password (Manager)\n";
    echo "Username: frontdesk1, Password: password (Front Desk)\n";
    echo "Username: housekeeping1, Password: password (Housekeeping)\n\n";

    echo "📦 INVENTORY SYSTEM (http://localhost/pms/inventory/login.php):\n";
    echo "Username: manager1, Password: password (Manager)\n";
    echo "Username: frontdesk1, Password: password (Front Desk)\n";
    echo "Username: housekeeping1, Password: password (Housekeeping)\n\n";

    echo "🎓 TUTORIALS SYSTEM (http://localhost/pms/tutorials/login.php):\n";
    echo "Email: demo@student.com, Password: password (Demo Student)\n";
    echo "Email: john@student.com, Password: password (John Student)\n";
    echo "Email: jane@student.com, Password: password (Jane Learner)\n";
    echo "Email: student1@demo.com, Password: password (Student One)\n";
    echo "Email: student2@demo.com, Password: password (Student Two)\n";
    echo "Email: student3@demo.com, Password: password (Student Three)\n\n";

    echo "💡 TIP: All passwords are 'password' for easy testing!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
