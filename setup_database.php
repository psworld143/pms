<?php
/**
 * Database Schema Setup Script
 * This script will create the hotel_pms_clean database and all required tables
 */

echo "<h1>Hotel PMS Database Setup</h1>";
echo "<p>Setting up the database schema...</p>";

try {
    // Include the main database configuration
    require_once __DIR__ . '/../includes/database.php';

    // Read the schema SQL file
    $schemaFile = __DIR__ . '/booking/database/schema.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $sql = file_get_contents($schemaFile);

    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    echo "<h3>Executing Schema Statements:</h3>";
    echo "<ul>";

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, 'CREATE DATABASE') === 0) {
            continue; // Skip comments, empty lines, and database creation (handled by connection)
        }

        try {
            $pdo->exec($statement);
            $successCount++;
            echo "<li style='color: green;'>✓ Executed statement successfully</li>";
        } catch (PDOException $e) {
            $errorCount++;
            $errors[] = $e->getMessage();
            echo "<li style='color: red;'>✗ Error: " . $e->getMessage() . "</li>";
        }
    }

    echo "</ul>";

    echo "<h3>Schema Creation Results:</h3>";
    echo "<p><strong>Successful statements:</strong> $successCount</p>";
    echo "<p><strong>Failed statements:</strong> $errorCount</p>";

    if ($errorCount > 0) {
        echo "<h3>Errors:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
        echo "</ul>";
    }

    // Verify tables were created
    echo "<h3>Table Verification:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Table</th><th>Status</th></tr>";

    $tables = [
        'users', 'rooms', 'guests', 'reservations', 'check_ins', 'billing',
        'additional_services', 'service_charges', 'inventory'
    ];

    foreach ($tables as $tableName) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
            if ($stmt->rowCount() > 0) {
                echo "<tr><td>$tableName</td><td style='color: green;'>✓ Created</td></tr>";
            } else {
                echo "<tr><td>$tableName</td><td style='color: red;'>✗ Not found</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td>$tableName</td><td style='color: red;'>✗ Error: " . $e->getMessage() . "</td></tr>";
        }
    }

    echo "</table>";

    if ($errorCount === 0) {
        echo "<h2 style='color: green;'>✅ Database schema created successfully!</h2>";
        echo "<p>All required tables have been created in the hotel_pms_clean database.</p>";
        echo "<p><a href='booking/database/run_seed.php'>Next: Run Database Seeding Script</a></p>";
    } else {
        echo "<h2 style='color: orange;'>⚠️ Database schema created with errors</h2>";
        echo "<p>Some tables may not have been created correctly. Please check the errors above.</p>";
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Database setup failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please ensure your database server is running and the connection details are correct.</p>";
}

echo "<p><a href='index.php'>Back to Main Page</a></p>";
?>
