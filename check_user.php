<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pms_pms_hotel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT username FROM users WHERE username = "sarah.johnson"');
    $user = $stmt->fetch();

    if ($user) {
        echo '✓ User sarah.johnson found in database' . "\n";
        echo 'You can now login with:' . "\n";
        echo '- Username: sarah.johnson' . "\n";
        echo '- Password: password' . "\n";
        echo 'Login URL: http://localhost/pms/booking/login.php' . "\n";
    } else {
        echo '✗ User sarah.johnson not found. Need to run seed data.' . "\n";
    }
} catch (Exception $e) {
    echo 'Database error: ' . $e->getMessage() . "\n";
}
?>
