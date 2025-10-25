<?php
/**
 * Update Database with Realistic Data
 * This script updates all tables with realistic, comprehensive data
 * and checks for data integrity issues
 */

require_once 'includes/database.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

echo "Starting database update with realistic data...\n";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // 1. Update Users with realistic hotel staff data
    echo "Updating users table...\n";
    $users_data = [
        [
            'id' => 1,
            'name' => 'Sarah Johnson',
            'username' => 'sarah.johnson',
            'email' => 'sarah.johnson@grandhotel.com',
            'role' => 'manager'
        ],
        [
            'id' => 2,
            'name' => 'Michael Chen',
            'username' => 'michael.chen',
            'email' => 'michael.chen@grandhotel.com',
            'role' => 'front_desk'
        ],
        [
            'id' => 3,
            'name' => 'Elena Rodriguez',
            'username' => 'elena.rodriguez',
            'email' => 'elena.rodriguez@grandhotel.com',
            'role' => 'front_desk'
        ],
        [
            'id' => 4,
            'name' => 'James Wilson',
            'username' => 'james.wilson',
            'email' => 'james.wilson@grandhotel.com',
            'role' => 'housekeeping'
        ],
        [
            'id' => 5,
            'name' => 'Lisa Thompson',
            'username' => 'lisa.thompson',
            'email' => 'lisa.thompson@grandhotel.com',
            'role' => 'housekeeping'
        ],
        [
            'id' => 6,
            'name' => 'David Park',
            'username' => 'david.park',
            'email' => 'david.park@grandhotel.com',
            'role' => 'front_desk'
        ],
        [
            'id' => 7,
            'name' => 'Maria Santos',
            'username' => 'maria.santos',
            'email' => 'maria.santos@grandhotel.com',
            'role' => 'housekeeping'
        ]
    ];
    
    foreach ($users_data as $user) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, username = ?, email = ?, role = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$user['name'], $user['username'], $user['email'], $user['role'], $user['id']]);
    }
    
    // 2. Update Rooms with realistic hotel room data
    echo "Updating rooms table...\n";
    $rooms_data = [
        // Standard Rooms (101-103)
        ['id' => 1, 'room_number' => '101', 'room_type' => 'standard', 'floor' => 1, 'capacity' => 2, 'rate' => 180.00, 'status' => 'available', 'housekeeping_status' => 'clean'],
        ['id' => 2, 'room_number' => '102', 'room_type' => 'standard', 'floor' => 1, 'capacity' => 2, 'rate' => 180.00, 'status' => 'occupied', 'housekeeping_status' => 'dirty'],
        ['id' => 3, 'room_number' => '103', 'room_type' => 'standard', 'floor' => 1, 'capacity' => 2, 'rate' => 180.00, 'status' => 'available', 'housekeeping_status' => 'clean'],
        
        // Deluxe Rooms (201-202)
        ['id' => 4, 'room_number' => '201', 'room_type' => 'deluxe', 'floor' => 2, 'capacity' => 3, 'rate' => 280.00, 'status' => 'occupied', 'housekeeping_status' => 'dirty'],
        ['id' => 5, 'room_number' => '202', 'room_type' => 'deluxe', 'floor' => 2, 'capacity' => 3, 'rate' => 280.00, 'status' => 'available', 'housekeeping_status' => 'clean'],
        
        // Suites (203, 301-302)
        ['id' => 6, 'room_number' => '203', 'room_type' => 'suite', 'floor' => 2, 'capacity' => 4, 'rate' => 450.00, 'status' => 'occupied', 'housekeeping_status' => 'dirty'],
        ['id' => 7, 'room_number' => '301', 'room_type' => 'suite', 'floor' => 3, 'capacity' => 4, 'rate' => 450.00, 'status' => 'available', 'housekeeping_status' => 'clean'],
        ['id' => 8, 'room_number' => '302', 'room_type' => 'suite', 'floor' => 3, 'capacity' => 4, 'rate' => 450.00, 'status' => 'occupied', 'housekeeping_status' => 'dirty'],
        
        // Presidential Suite (401-402)
        ['id' => 9, 'room_number' => '401', 'room_type' => 'presidential', 'floor' => 4, 'capacity' => 6, 'rate' => 850.00, 'status' => 'available', 'housekeeping_status' => 'clean'],
        ['id' => 10, 'room_number' => '402', 'room_type' => 'presidential', 'floor' => 4, 'capacity' => 6, 'rate' => 850.00, 'status' => 'available', 'housekeeping_status' => 'clean']
    ];
    
    foreach ($rooms_data as $room) {
        $amenities = match($room['room_type']) {
            'standard' => 'WiFi, Flat-screen TV, Air Conditioning, Mini Fridge, Coffee Maker, Safe',
            'deluxe' => 'WiFi, 55" Smart TV, Air Conditioning, Mini Bar, Coffee Maker, Safe, Balcony, City View',
            'suite' => 'WiFi, 65" Smart TV, Air Conditioning, Full Bar, Espresso Machine, Safe, Private Balcony, Ocean View, Separate Living Area',
            'presidential' => 'WiFi, 75" Smart TV, Air Conditioning, Premium Bar, Espresso Machine, Safe, Private Terrace, Panoramic View, Separate Living & Dining Areas, Butler Service'
        };
        
        $stmt = $pdo->prepare("
            UPDATE rooms 
            SET room_number = ?, room_type = ?, floor = ?, capacity = ?, rate = ?, 
                status = ?, housekeeping_status = ?, amenities = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $room['room_number'], $room['room_type'], $room['floor'], $room['capacity'], 
            $room['rate'], $room['status'], $room['housekeeping_status'], $amenities, $room['id']
        ]);
    }
    
    // 3. Update Guests with realistic guest data
    echo "Updating guests table...\n";
    $guests_data = [
        [
            'id' => 1,
            'first_name' => 'Alexander',
            'last_name' => 'Thompson',
            'email' => 'alex.thompson@email.com',
            'phone' => '+1-555-0123',
            'address' => '123 Park Avenue, New York, NY 10001',
            'id_type' => 'passport',
            'id_number' => 'US123456789',
            'date_of_birth' => '1985-03-15',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'Non-smoking room, High floor, King bed',
            'service_notes' => 'Business traveler, prefers early check-in'
        ],
        [
            'id' => 2,
            'first_name' => 'Isabella',
            'last_name' => 'Martinez',
            'email' => 'isabella.martinez@email.com',
            'phone' => '+1-555-0456',
            'address' => '456 Sunset Boulevard, Los Angeles, CA 90210',
            'id_type' => 'driver_license',
            'id_number' => 'CA987654321',
            'date_of_birth' => '1990-07-22',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'Ocean view, Late check-out, Spa services',
            'service_notes' => 'VIP guest, anniversary celebration, champagne on arrival'
        ],
        [
            'id' => 3,
            'first_name' => 'Benjamin',
            'last_name' => 'Anderson',
            'email' => 'benjamin.anderson@email.com',
            'phone' => '+1-555-0789',
            'address' => '789 Michigan Avenue, Chicago, IL 60601',
            'id_type' => 'national_id',
            'id_number' => 'IL456789123',
            'date_of_birth' => '1978-11-08',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'Quiet room, Business center access',
            'service_notes' => 'Corporate client, frequent guest'
        ],
        [
            'id' => 4,
            'first_name' => 'Sophia',
            'last_name' => 'Williams',
            'email' => 'sophia.williams@email.com',
            'phone' => '+1-555-0321',
            'address' => '321 Collins Avenue, Miami, FL 33139',
            'id_type' => 'passport',
            'id_number' => 'US789123456',
            'date_of_birth' => '1992-05-14',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'Beach view, Pool access, Concierge services',
            'service_notes' => 'VIP guest, honeymoon celebration'
        ],
        [
            'id' => 5,
            'first_name' => 'William',
            'last_name' => 'Brown',
            'email' => 'william.brown@email.com',
            'phone' => '+1-555-0654',
            'address' => '654 Market Street, San Francisco, CA 94102',
            'id_type' => 'driver_license',
            'id_number' => 'CA321654987',
            'date_of_birth' => '1987-09-30',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'City view, Fitness center access',
            'service_notes' => 'Tech executive, prefers suite upgrades'
        ],
        [
            'id' => 6,
            'first_name' => 'Emma',
            'last_name' => 'Davis',
            'email' => 'emma.davis@email.com',
            'phone' => '+1-555-0987',
            'address' => '987 Broadway, Seattle, WA 98101',
            'id_type' => 'passport',
            'id_number' => 'US654987321',
            'date_of_birth' => '1995-12-03',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'Pet-friendly room, Room service',
            'service_notes' => 'Traveling with small dog, requires pet amenities'
        ],
        [
            'id' => 7,
            'first_name' => 'James',
            'last_name' => 'Wilson',
            'email' => 'james.wilson@email.com',
            'phone' => '+1-555-0135',
            'address' => '135 Peachtree Street, Atlanta, GA 30309',
            'id_type' => 'national_id',
            'id_number' => 'GA135792468',
            'date_of_birth' => '1983-04-18',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'Executive floor, Business services',
            'service_notes' => 'VIP guest, corporate account, requires meeting room access'
        ],
        [
            'id' => 8,
            'first_name' => 'Olivia',
            'last_name' => 'Garcia',
            'email' => 'olivia.garcia@email.com',
            'phone' => '+1-555-0246',
            'address' => '246 Bourbon Street, New Orleans, LA 70112',
            'id_type' => 'driver_license',
            'id_number' => 'LA246813579',
            'date_of_birth' => '1991-08-25',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'Historic district view, Local restaurant recommendations',
            'service_notes' => 'Food blogger, interested in local cuisine'
        ],
        [
            'id' => 9,
            'first_name' => 'Michael',
            'last_name' => 'Rodriguez',
            'email' => 'michael.rodriguez@email.com',
            'phone' => '+1-555-0369',
            'address' => '369 Las Vegas Boulevard, Las Vegas, NV 89101',
            'id_type' => 'passport',
            'id_number' => 'US369258147',
            'date_of_birth' => '1989-01-12',
            'nationality' => 'American',
            'is_vip' => 1,
            'preferences' => 'Strip view, Casino access, VIP services',
            'service_notes' => 'High roller, requires premium services'
        ],
        [
            'id' => 10,
            'last_name' => 'Taylor',
            'first_name' => 'Johnson',
            'email' => 'taylor.johnson@email.com',
            'phone' => '+1-555-0482',
            'address' => '482 Beacon Street, Boston, MA 02115',
            'id_type' => 'national_id',
            'id_number' => 'MA482591736',
            'date_of_birth' => '1994-06-07',
            'nationality' => 'American',
            'is_vip' => 0,
            'preferences' => 'Historic view, Walking distance to attractions',
            'service_notes' => 'Student group leader, requires multiple room bookings'
        ]
    ];
    
    foreach ($guests_data as $guest) {
        $stmt = $pdo->prepare("
            UPDATE guests 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, 
                id_type = ?, id_number = ?, date_of_birth = ?, nationality = ?, 
                is_vip = ?, preferences = ?, service_notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $guest['first_name'], $guest['last_name'], $guest['email'], $guest['phone'], 
            $guest['address'], $guest['id_type'], $guest['id_number'], $guest['date_of_birth'], 
            $guest['nationality'], $guest['is_vip'], $guest['preferences'], $guest['service_notes'], $guest['id']
        ]);
    }
    
    // 4. Update Reservations with realistic reservation data
    echo "Updating reservations table...\n";
    $reservations_data = [
        [
            'id' => 1,
            'reservation_number' => 'RES20250101001',
            'guest_id' => 1,
            'room_id' => 2,
            'check_in_date' => '2025-01-15',
            'check_out_date' => '2025-01-18',
            'adults' => 2,
            'children' => 0,
            'total_amount' => 540.00,
            'special_requests' => 'Late arrival after 10 PM, Non-smoking room',
            'booking_source' => 'online',
            'status' => 'checked_in',
            'checked_in_at' => '2025-01-15 22:30:00',
            'checked_in_by' => 2
        ],
        [
            'id' => 2,
            'reservation_number' => 'RES20250101002',
            'guest_id' => 2,
            'room_id' => 6,
            'check_in_date' => '2025-01-15',
            'check_out_date' => '2025-01-20',
            'adults' => 2,
            'children' => 1,
            'total_amount' => 2250.00,
            'special_requests' => 'Anniversary celebration, champagne on arrival, ocean view',
            'booking_source' => 'phone',
            'status' => 'checked_in',
            'checked_in_at' => '2025-01-15 15:00:00',
            'checked_in_by' => 3
        ],
        [
            'id' => 3,
            'reservation_number' => 'RES20250102001',
            'guest_id' => 3,
            'room_id' => 8,
            'check_in_date' => '2025-01-16',
            'check_out_date' => '2025-01-19',
            'adults' => 1,
            'children' => 0,
            'total_amount' => 1350.00,
            'special_requests' => 'Business trip, quiet room preferred, high-speed internet',
            'booking_source' => 'walk_in',
            'status' => 'checked_in',
            'checked_in_at' => '2025-01-16 14:00:00',
            'checked_in_by' => 2
        ],
        [
            'id' => 4,
            'reservation_number' => 'RES20250102002',
            'guest_id' => 4,
            'room_id' => 9,
            'check_in_date' => '2025-01-16',
            'check_out_date' => '2025-01-22',
            'adults' => 2,
            'children' => 0,
            'total_amount' => 5100.00,
            'special_requests' => 'Honeymoon celebration, beach view, spa services',
            'booking_source' => 'online',
            'status' => 'checked_in',
            'checked_in_at' => '2025-01-16 16:00:00',
            'checked_in_by' => 3
        ],
        [
            'id' => 5,
            'reservation_number' => 'RES20250103001',
            'guest_id' => 5,
            'room_id' => 4,
            'check_in_date' => '2025-01-17',
            'check_out_date' => '2025-01-21',
            'adults' => 2,
            'children' => 0,
            'total_amount' => 1120.00,
            'special_requests' => 'City view, fitness center access, late check-out',
            'booking_source' => 'travel_agent',
            'status' => 'checked_in',
            'checked_in_at' => '2025-01-17 12:00:00',
            'checked_in_by' => 2
        ],
        [
            'id' => 6,
            'reservation_number' => 'RES20250103002',
            'guest_id' => 6,
            'room_id' => 1,
            'check_in_date' => '2025-01-18',
            'check_out_date' => '2025-01-20',
            'adults' => 1,
            'children' => 0,
            'total_amount' => 360.00,
            'special_requests' => 'Pet-friendly room, ground floor preferred',
            'booking_source' => 'online',
            'status' => 'confirmed',
            'checked_in_at' => null,
            'checked_in_by' => null
        ],
        [
            'id' => 7,
            'reservation_number' => 'RES20250104001',
            'guest_id' => 7,
            'room_id' => 10,
            'check_in_date' => '2025-01-19',
            'check_out_date' => '2025-01-25',
            'adults' => 4,
            'children' => 2,
            'total_amount' => 5100.00,
            'special_requests' => 'Executive meeting room access, premium services',
            'booking_source' => 'phone',
            'status' => 'confirmed',
            'checked_in_at' => null,
            'checked_in_by' => null
        ],
        [
            'id' => 8,
            'reservation_number' => 'RES20250104002',
            'guest_id' => 8,
            'room_id' => 3,
            'check_in_date' => '2025-01-20',
            'check_out_date' => '2025-01-23',
            'adults' => 2,
            'children' => 0,
            'total_amount' => 540.00,
            'special_requests' => 'Historic district view, local restaurant recommendations',
            'booking_source' => 'walk_in',
            'status' => 'confirmed',
            'checked_in_at' => null,
            'checked_in_by' => null
        ],
        [
            'id' => 9,
            'reservation_number' => 'RES20250105001',
            'guest_id' => 9,
            'room_id' => 7,
            'check_in_date' => '2025-01-21',
            'check_out_date' => '2025-01-24',
            'adults' => 2,
            'children' => 0,
            'total_amount' => 1350.00,
            'special_requests' => 'Strip view, casino access, VIP services',
            'booking_source' => 'online',
            'status' => 'confirmed',
            'checked_in_at' => null,
            'checked_in_by' => null
        ],
        [
            'id' => 10,
            'reservation_number' => 'RES20250105002',
            'guest_id' => 10,
            'room_id' => 5,
            'check_in_date' => '2025-01-22',
            'check_out_date' => '2025-01-25',
            'adults' => 3,
            'children' => 1,
            'total_amount' => 840.00,
            'special_requests' => 'Historic view, walking distance to attractions',
            'booking_source' => 'travel_agent',
            'status' => 'confirmed',
            'checked_in_at' => null,
            'checked_in_by' => null
        ]
    ];
    
    foreach ($reservations_data as $reservation) {
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET reservation_number = ?, guest_id = ?, room_id = ?, check_in_date = ?, 
                check_out_date = ?, adults = ?, children = ?, total_amount = ?, 
                special_requests = ?, booking_source = ?, status = ?, 
                checked_in_at = ?, checked_in_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $reservation['reservation_number'], $reservation['guest_id'], $reservation['room_id'], 
            $reservation['check_in_date'], $reservation['check_out_date'], $reservation['adults'], 
            $reservation['children'], $reservation['total_amount'], $reservation['special_requests'], 
            $reservation['booking_source'], $reservation['status'], $reservation['checked_in_at'], 
            $reservation['checked_in_by'], $reservation['id']
        ]);
    }
    
    // 5. Create realistic billing records for checked-in guests
    echo "Creating billing records...\n";
    $billing_data = [
        [
            'reservation_id' => 1,
            'guest_id' => 1,
            'room_charges' => 540.00,
            'additional_charges' => 25.00,
            'tax_amount' => 56.50,
            'total_amount' => 621.50,
            'payment_status' => 'pending'
        ],
        [
            'reservation_id' => 2,
            'guest_id' => 2,
            'room_charges' => 1400.00,
            'additional_charges' => 150.00,
            'tax_amount' => 155.00,
            'total_amount' => 1705.00,
            'payment_status' => 'partial'
        ],
        [
            'reservation_id' => 3,
            'guest_id' => 3,
            'room_charges' => 840.00,
            'additional_charges' => 45.00,
            'tax_amount' => 88.50,
            'total_amount' => 973.50,
            'payment_status' => 'paid'
        ],
        [
            'reservation_id' => 4,
            'guest_id' => 4,
            'room_charges' => 2700.00,
            'additional_charges' => 300.00,
            'tax_amount' => 300.00,
            'total_amount' => 3300.00,
            'payment_status' => 'pending'
        ],
        [
            'reservation_id' => 5,
            'guest_id' => 5,
            'room_charges' => 1800.00,
            'additional_charges' => 120.00,
            'tax_amount' => 192.00,
            'total_amount' => 2112.00,
            'payment_status' => 'pending'
        ]
    ];
    
    foreach ($billing_data as $billing) {
        $stmt = $pdo->prepare("
            INSERT INTO billing (reservation_id, guest_id, room_charges, additional_charges, 
                               tax_amount, total_amount, payment_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            room_charges = VALUES(room_charges),
            additional_charges = VALUES(additional_charges),
            tax_amount = VALUES(tax_amount),
            total_amount = VALUES(total_amount),
            payment_status = VALUES(payment_status),
            updated_at = NOW()
        ");
        $stmt->execute([
            $billing['reservation_id'], $billing['guest_id'], $billing['room_charges'], 
            $billing['additional_charges'], $billing['tax_amount'], $billing['total_amount'], 
            $billing['payment_status']
        ]);
    }
    
    // 6. Create check-in records for checked-in guests
    echo "Creating check-in records...\n";
    $checkin_data = [
        ['reservation_id' => 1, 'room_key_issued' => 1, 'welcome_amenities' => 1, 'checked_in_by' => 2],
        ['reservation_id' => 2, 'room_key_issued' => 1, 'welcome_amenities' => 1, 'checked_in_by' => 3],
        ['reservation_id' => 3, 'room_key_issued' => 1, 'welcome_amenities' => 0, 'checked_in_by' => 2],
        ['reservation_id' => 4, 'room_key_issued' => 1, 'welcome_amenities' => 1, 'checked_in_by' => 3],
        ['reservation_id' => 5, 'room_key_issued' => 1, 'welcome_amenities' => 1, 'checked_in_by' => 2]
    ];
    
    foreach ($checkin_data as $checkin) {
        $stmt = $pdo->prepare("
            INSERT INTO check_ins (reservation_id, room_key_issued, welcome_amenities, 
                                 checked_in_by, checked_in_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            room_key_issued = VALUES(room_key_issued),
            welcome_amenities = VALUES(welcome_amenities),
            checked_in_by = VALUES(checked_in_by),
            checked_in_at = NOW()
        ");
        $stmt->execute([$checkin['reservation_id'], $checkin['room_key_issued'], 
                       $checkin['welcome_amenities'], $checkin['checked_in_by']]);
    }
    
    // 7. Create realistic inventory data
    echo "Creating inventory data...\n";
    $inventory_data = [
        ['item_name' => 'Bath Towels', 'category' => 'linens', 'quantity' => 150, 'unit' => 'pieces', 'reorder_level' => 20, 'unit_cost' => 8.50, 'supplier' => 'Luxury Linens Inc.'],
        ['item_name' => 'Hand Towels', 'category' => 'linens', 'quantity' => 200, 'unit' => 'pieces', 'reorder_level' => 30, 'unit_cost' => 4.25, 'supplier' => 'Luxury Linens Inc.'],
        ['item_name' => 'Bathrobes', 'category' => 'linens', 'quantity' => 75, 'unit' => 'pieces', 'reorder_level' => 15, 'unit_cost' => 35.00, 'supplier' => 'Luxury Linens Inc.'],
        ['item_name' => 'Shampoo Bottles', 'category' => 'amenities', 'quantity' => 300, 'unit' => 'bottles', 'reorder_level' => 50, 'unit_cost' => 2.75, 'supplier' => 'Premium Amenities Co.'],
        ['item_name' => 'Conditioner Bottles', 'category' => 'amenities', 'quantity' => 300, 'unit' => 'bottles', 'reorder_level' => 50, 'unit_cost' => 2.75, 'supplier' => 'Premium Amenities Co.'],
        ['item_name' => 'Body Lotion', 'category' => 'amenities', 'quantity' => 250, 'unit' => 'bottles', 'reorder_level' => 40, 'unit_cost' => 3.25, 'supplier' => 'Premium Amenities Co.'],
        ['item_name' => 'All-Purpose Cleaner', 'category' => 'cleaning_supplies', 'quantity' => 25, 'unit' => 'bottles', 'reorder_level' => 5, 'unit_cost' => 12.50, 'supplier' => 'Clean Solutions Ltd.'],
        ['item_name' => 'Glass Cleaner', 'category' => 'cleaning_supplies', 'quantity' => 20, 'unit' => 'bottles', 'reorder_level' => 5, 'unit_cost' => 8.75, 'supplier' => 'Clean Solutions Ltd.'],
        ['item_name' => 'Vacuum Bags', 'category' => 'cleaning_supplies', 'quantity' => 100, 'unit' => 'bags', 'reorder_level' => 20, 'unit_cost' => 1.25, 'supplier' => 'Clean Solutions Ltd.'],
        ['item_name' => 'Light Bulbs', 'category' => 'maintenance', 'quantity' => 50, 'unit' => 'pieces', 'reorder_level' => 10, 'unit_cost' => 5.50, 'supplier' => 'Maintenance Supplies Inc.'],
        ['item_name' => 'Coffee Beans', 'category' => 'food_beverage', 'quantity' => 15, 'unit' => 'kg', 'reorder_level' => 3, 'unit_cost' => 25.00, 'supplier' => 'Gourmet Coffee Co.'],
        ['item_name' => 'Tea Bags', 'category' => 'food_beverage', 'quantity' => 200, 'unit' => 'boxes', 'reorder_level' => 30, 'unit_cost' => 3.50, 'supplier' => 'Gourmet Coffee Co.']
    ];
    
    foreach ($inventory_data as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO inventory (item_name, category, description, quantity, unit, 
                                 reorder_level, unit_cost, supplier, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            quantity = VALUES(quantity),
            unit_cost = VALUES(unit_cost),
            supplier = VALUES(supplier),
            updated_at = NOW()
        ");
        $description = "High-quality {$item['item_name']} for hotel operations";
        $stmt->execute([
            $item['item_name'], $item['category'], $description, $item['quantity'], 
            $item['unit'], $item['reorder_level'], $item['unit_cost'], $item['supplier']
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    echo "Database update completed successfully!\n";
    
    // 8. Check data integrity
    echo "Checking data integrity...\n";
    
    // Check foreign key constraints
    $integrity_checks = [
        "SELECT COUNT(*) as count FROM reservations r LEFT JOIN guests g ON r.guest_id = g.id WHERE g.id IS NULL" => "Reservations with invalid guest_id",
        "SELECT COUNT(*) as count FROM reservations r LEFT JOIN rooms rm ON r.room_id = rm.id WHERE rm.id IS NULL" => "Reservations with invalid room_id",
        "SELECT COUNT(*) as count FROM reservations r LEFT JOIN users u ON r.created_by = u.id WHERE u.id IS NULL" => "Reservations with invalid created_by",
        "SELECT COUNT(*) as count FROM check_ins c LEFT JOIN reservations r ON c.reservation_id = r.id WHERE r.id IS NULL" => "Check-ins with invalid reservation_id",
        "SELECT COUNT(*) as count FROM billing b LEFT JOIN reservations r ON b.reservation_id = r.id WHERE r.id IS NULL" => "Billing records with invalid reservation_id"
    ];
    
    $integrity_issues = 0;
    foreach ($integrity_checks as $query => $description) {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            echo "WARNING: {$description}: {$result['count']} records\n";
            $integrity_issues++;
        }
    }
    
    if ($integrity_issues === 0) {
        echo "Data integrity check passed! No issues found.\n";
    } else {
        echo "Data integrity check completed with {$integrity_issues} issues found.\n";
    }
    
    // Display summary
    echo "\n=== DATABASE UPDATE SUMMARY ===\n";
    $summary_queries = [
        "SELECT COUNT(*) as count FROM users" => "Users",
        "SELECT COUNT(*) as count FROM guests" => "Guests", 
        "SELECT COUNT(*) as count FROM rooms" => "Rooms",
        "SELECT COUNT(*) as count FROM reservations" => "Reservations",
        "SELECT COUNT(*) as count FROM billing" => "Billing Records",
        "SELECT COUNT(*) as count FROM check_ins" => "Check-in Records",
        "SELECT COUNT(*) as count FROM inventory" => "Inventory Items"
    ];
    
    foreach ($summary_queries as $query => $label) {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch();
        echo "{$label}: {$result['count']}\n";
    }
    
    echo "\nDatabase has been updated with realistic data!\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error updating database: " . $e->getMessage() . "\n";
    error_log("Database update error: " . $e->getMessage());
}
?>
