<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=hotel_pms_clean;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if reservations table exists
    $stmt = $pdo->query('SHOW TABLES LIKE "reservations"');
    if ($stmt->rowCount() > 0) {
        echo "SUCCESS: Reservations table exists!\n";

        // Check sample data
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM reservations');
        $count = $stmt->fetch()['count'];
        echo "SUCCESS: Found $count reservations in database\n";
    } else {
        echo "ERROR: Reservations table not found\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
