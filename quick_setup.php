<?php
/**
 * Simple Database Setup Script
 * Creates database and runs schema/seed data
 */

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS pms_pms_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'pms_pms_hotel' created successfully\n";

    // Use the database
    $pdo->exec("USE pms_pms_hotel");

    // Read and execute schema
    $schemaFile = __DIR__ . '/booking/database/schema.sql';
    if (file_exists($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        $pdo->exec($schema);
        echo "✓ Schema executed successfully\n";
    }

    // Read and execute seed data
    $seedFile = __DIR__ . '/booking/database/seed_data.sql';
    if (file_exists($seedFile)) {
        $seed = file_get_contents($seedFile);
        $pdo->exec($seed);
        echo "✓ Seed data inserted successfully\n";
    }

    echo "\n🎉 Database setup completed!\n";
    echo "You can now login with:\n";
    echo "- Username: sarah.johnson\n";
    echo "- Password: password\n";
    echo "\nLogin URL: http://localhost/pms/booking/login.php\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
