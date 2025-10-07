<?php
// Simple database seeding script
require_once 'includes/database.php';

echo "Starting database seeding...\n";

try {
    $sql = file_get_contents('booking/database/seed_data.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $success++;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "Seeding completed. Success: $success, Errors: $errors\n";
    
    // Verify users were created
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    echo "Users in database: $count\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT username, name, role FROM users");
        $users = $stmt->fetchAll();
        echo "\nCreated users:\n";
        foreach ($users as $user) {
            echo "- {$user['username']} ({$user['name']}) - {$user['role']}\n";
        }
        echo "\nLogin credentials (password: password):\n";
        echo "- manager1, manager2, sarah.johnson\n";
        echo "- frontdesk1, frontdesk2\n";  
        echo "- housekeeping1, housekeeping2\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
