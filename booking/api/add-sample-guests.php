<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Add Sample Guest Data
 */

session_start();
require_once dirname(__DIR__, 2) . '/includes/database.php';

try {
    // Check if guests table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'guests'");
    if ($tableCheck->rowCount() == 0) {
        echo "Guests table does not exist. Please run the database setup first.\n";
        exit(); }
    // Check if guests already exist
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM guests");
    $count = $countStmt->fetch()['count'];
    
    if ($count > 0) {
        echo "Guests already exist in database ($count records).\n";
        exit(); }
    // Add sample guests
    $sampleGuests = [
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St, New York, NY',
            'id_type' => 'passport',
            'id_number' => 'PASS123456',
            'date_of_birth' => '1985-06-15',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'High floor, late checkout',
            'service_notes' => 'Frequent guest, prefers room service'
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '+1234567891',
            'address' => '456 Oak Ave, Los Angeles, CA',
            'id_type' => 'driver_license',
            'id_number' => 'DL987654',
            'date_of_birth' => '1990-03-22',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'Quiet room, early check-in',
            'service_notes' => 'Allergic to feathers'
        ],
        [
            'first_name' => 'Michael',
            'last_name' => 'Johnson',
            'email' => 'michael.johnson@example.com',
            'phone' => '+1234567892',
            'address' => '789 Pine St, Chicago, IL',
            'id_type' => 'national_id',
            'id_number' => 'NID456789',
            'date_of_birth' => '1978-11-08',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'Business center access, wake-up calls',
            'service_notes' => 'Corporate client, needs receipt for expenses'
        ],
        [
            'first_name' => 'Sarah',
            'last_name' => 'Williams',
            'email' => 'sarah.williams@example.com',
            'phone' => '+1234567893',
            'address' => '321 Elm St, Miami, FL',
            'id_type' => 'passport',
            'id_number' => 'PASS789012',
            'date_of_birth' => '1992-09-14',
            'nationality' => 'Canadian',
            'is_vip' => 0,
            'preferences' => 'Pool view, spa access',
            'service_notes' => 'Celebrating anniversary'
        ],
        [
            'first_name' => 'David',
            'last_name' => 'Brown',
            'email' => 'david.brown@example.com',
            'phone' => '+1234567894',
            'address' => '654 Maple Dr, Seattle, WA',
            'id_type' => 'driver_license',
            'id_number' => 'DL345678',
            'date_of_birth' => '1987-12-03',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'Executive floor, concierge service',
            'service_notes' => 'VIP member, complimentary upgrade'
        ]
    ];
    
    $sql = "INSERT INTO guests (first_name, last_name, email, phone, address, id_type, id_number, date_of_birth, nationality, is_vip, preferences, service_notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $inserted = 0;
    
    foreach ($sampleGuests as $guest) {
        $stmt->execute([
            $guest['first_name'],
            $guest['last_name'],
            $guest['email'],
            $guest['phone'],
            $guest['address'],
            $guest['id_type'],
            $guest['id_number'],
            $guest['date_of_birth'],
            $guest['nationality'],
            $guest['is_vip'],
            $guest['preferences'],
            $guest['service_notes']
        ]);
        $inserted++; }
    echo "Successfully added $inserted sample guests to the database.\n";
    
} catch (Exception $e) {
    echo "Error adding sample guests: " . $e->getMessage() . "\n"; }
?>






