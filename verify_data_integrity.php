<?php
/**
 * Comprehensive Data Integrity Verification Script
 * This script performs thorough checks on all database tables and relationships
 */

require_once 'includes/database.php';

echo "=== COMPREHENSIVE DATA INTEGRITY VERIFICATION ===\n\n";

$total_issues = 0;
$total_checks = 0;

function runIntegrityCheck($description, $query, $expected_result = 0) {
    global $pdo, $total_issues, $total_checks;
    
    $total_checks++;
    echo "Checking: {$description}... ";
    
    try {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch();
        $count = $result['count'] ?? $result[0] ?? 0;
        
        if ($count == $expected_result) {
            echo "✓ PASSED\n";
        } else {
            echo "✗ FAILED (Found: {$count}, Expected: {$expected_result})\n";
            $total_issues++;
        }
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $total_issues++;
    }
}

// 1. Foreign Key Integrity Checks
echo "1. FOREIGN KEY INTEGRITY CHECKS\n";
echo "================================\n";

runIntegrityCheck(
    "Reservations with invalid guest_id",
    "SELECT COUNT(*) as count FROM reservations r LEFT JOIN guests g ON r.guest_id = g.id WHERE g.id IS NULL"
);

runIntegrityCheck(
    "Reservations with invalid room_id", 
    "SELECT COUNT(*) as count FROM reservations r LEFT JOIN rooms rm ON r.room_id = rm.id WHERE rm.id IS NULL"
);

runIntegrityCheck(
    "Reservations with invalid created_by user",
    "SELECT COUNT(*) as count FROM reservations r LEFT JOIN users u ON r.created_by = u.id WHERE u.id IS NULL"
);

runIntegrityCheck(
    "Reservations with invalid checked_in_by user",
    "SELECT COUNT(*) as count FROM reservations r LEFT JOIN users u ON r.checked_in_by = u.id WHERE r.checked_in_by IS NOT NULL AND u.id IS NULL"
);

runIntegrityCheck(
    "Check-ins with invalid reservation_id",
    "SELECT COUNT(*) as count FROM check_ins c LEFT JOIN reservations r ON c.reservation_id = r.id WHERE r.id IS NULL"
);

runIntegrityCheck(
    "Check-ins with invalid checked_in_by user",
    "SELECT COUNT(*) as count FROM check_ins c LEFT JOIN users u ON c.checked_in_by = u.id WHERE u.id IS NULL"
);

runIntegrityCheck(
    "Billing records with invalid reservation_id",
    "SELECT COUNT(*) as count FROM billing b LEFT JOIN reservations r ON b.reservation_id = r.id WHERE r.id IS NULL"
);

runIntegrityCheck(
    "Billing records with invalid guest_id",
    "SELECT COUNT(*) as count FROM billing b LEFT JOIN guests g ON b.guest_id = g.id WHERE g.id IS NULL"
);

// 2. Data Consistency Checks
echo "\n2. DATA CONSISTENCY CHECKS\n";
echo "==========================\n";

runIntegrityCheck(
    "Reservations with check_in_date >= check_out_date",
    "SELECT COUNT(*) as count FROM reservations WHERE check_in_date >= check_out_date"
);

runIntegrityCheck(
    "Reservations with negative total_amount",
    "SELECT COUNT(*) as count FROM reservations WHERE total_amount < 0"
);

runIntegrityCheck(
    "Reservations with zero or negative adults",
    "SELECT COUNT(*) as count FROM reservations WHERE adults <= 0"
);

runIntegrityCheck(
    "Reservations with negative children",
    "SELECT COUNT(*) as count FROM reservations WHERE children < 0"
);

runIntegrityCheck(
    "Rooms with negative rates",
    "SELECT COUNT(*) as count FROM rooms WHERE rate < 0"
);

runIntegrityCheck(
    "Rooms with zero or negative capacity",
    "SELECT COUNT(*) as count FROM rooms WHERE capacity <= 0"
);

runIntegrityCheck(
    "Billing records with negative amounts",
    "SELECT COUNT(*) as count FROM billing WHERE room_charges < 0 OR additional_charges < 0 OR tax_amount < 0 OR total_amount < 0"
);

// 3. Business Logic Checks
echo "\n3. BUSINESS LOGIC CHECKS\n";
echo "========================\n";

runIntegrityCheck(
    "Checked-in reservations without check-in records",
    "SELECT COUNT(*) as count FROM reservations r LEFT JOIN check_ins c ON r.id = c.reservation_id WHERE r.status = 'checked_in' AND c.id IS NULL"
);

runIntegrityCheck(
    "Checked-in reservations without billing records",
    "SELECT COUNT(*) as count FROM reservations r LEFT JOIN billing b ON r.id = b.reservation_id WHERE r.status = 'checked_in' AND b.id IS NULL"
);

runIntegrityCheck(
    "Reservations with checked_in_at but status not checked_in",
    "SELECT COUNT(*) as count FROM reservations WHERE checked_in_at IS NOT NULL AND status != 'checked_in'"
);

runIntegrityCheck(
    "Reservations with checked_out_at but status not checked_out",
    "SELECT COUNT(*) as count FROM reservations WHERE checked_out_at IS NOT NULL AND status != 'checked_out'"
);

runIntegrityCheck(
    "Rooms marked as occupied but no active reservations",
    "SELECT COUNT(*) as count FROM rooms r WHERE r.status = 'occupied' AND r.id NOT IN (SELECT room_id FROM reservations WHERE status = 'checked_in')"
);

// 4. Data Quality Checks
echo "\n4. DATA QUALITY CHECKS\n";
echo "======================\n";

runIntegrityCheck(
    "Users with invalid email format",
    "SELECT COUNT(*) as count FROM users WHERE email IS NOT NULL AND email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'"
);

runIntegrityCheck(
    "Guests with invalid email format",
    "SELECT COUNT(*) as count FROM guests WHERE email IS NOT NULL AND email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'"
);

runIntegrityCheck(
    "Guests with future birth dates",
    "SELECT COUNT(*) as count FROM guests WHERE date_of_birth > CURDATE()"
);

runIntegrityCheck(
    "Guests with birth dates more than 120 years ago",
    "SELECT COUNT(*) as count FROM guests WHERE date_of_birth < DATE_SUB(CURDATE(), INTERVAL 120 YEAR)"
);

runIntegrityCheck(
    "Reservations with check-in dates more than 1 year in the past",
    "SELECT COUNT(*) as count FROM reservations WHERE check_in_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)"
);

runIntegrityCheck(
    "Reservations with check-out dates more than 1 year in the future",
    "SELECT COUNT(*) as count FROM reservations WHERE check_out_date > DATE_ADD(CURDATE(), INTERVAL 1 YEAR)"
);

// 5. Inventory Integrity Checks
echo "\n5. INVENTORY INTEGRITY CHECKS\n";
echo "=============================\n";

runIntegrityCheck(
    "Inventory items with negative quantities",
    "SELECT COUNT(*) as count FROM inventory WHERE quantity < 0"
);

runIntegrityCheck(
    "Inventory items with negative unit costs",
    "SELECT COUNT(*) as count FROM inventory WHERE unit_cost < 0"
);

runIntegrityCheck(
    "Inventory items with negative reorder levels",
    "SELECT COUNT(*) as count FROM inventory WHERE reorder_level < 0"
);

runIntegrityCheck(
    "Inventory transactions with invalid item_id",
    "SELECT COUNT(*) as count FROM inventory_transactions t LEFT JOIN inventory i ON t.item_id = i.id WHERE i.id IS NULL"
);

runIntegrityCheck(
    "Inventory transactions with invalid user_id",
    "SELECT COUNT(*) as count FROM inventory_transactions t LEFT JOIN users u ON t.user_id = u.id WHERE u.id IS NULL"
);

// 6. Summary Statistics
echo "\n6. DATABASE SUMMARY STATISTICS\n";
echo "==============================\n";

$summary_queries = [
    "SELECT COUNT(*) as count FROM users" => "Total Users",
    "SELECT COUNT(*) as count FROM guests" => "Total Guests",
    "SELECT COUNT(*) as count FROM rooms" => "Total Rooms",
    "SELECT COUNT(*) as count FROM reservations" => "Total Reservations",
    "SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'" => "Confirmed Reservations",
    "SELECT COUNT(*) as count FROM reservations WHERE status = 'checked_in'" => "Checked-in Reservations",
    "SELECT COUNT(*) as count FROM reservations WHERE status = 'checked_out'" => "Checked-out Reservations",
    "SELECT COUNT(*) as count FROM billing" => "Billing Records",
    "SELECT COUNT(*) as count FROM check_ins" => "Check-in Records",
    "SELECT COUNT(*) as count FROM inventory" => "Inventory Items",
    "SELECT COUNT(*) as count FROM rooms WHERE status = 'available'" => "Available Rooms",
    "SELECT COUNT(*) as count FROM rooms WHERE status = 'occupied'" => "Occupied Rooms",
    "SELECT COUNT(*) as count FROM rooms WHERE status = 'maintenance'" => "Maintenance Rooms",
    "SELECT COUNT(*) as count FROM guests WHERE is_vip = 1" => "VIP Guests"
];

foreach ($summary_queries as $query => $label) {
    try {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch();
        $count = $result['count'];
        echo "{$label}: {$count}\n";
    } catch (Exception $e) {
        echo "{$label}: ERROR - " . $e->getMessage() . "\n";
    }
}

// 7. Room Occupancy Analysis
echo "\n7. ROOM OCCUPANCY ANALYSIS\n";
echo "==========================\n";

try {
    $stmt = $pdo->query("
        SELECT 
            rt.room_type,
            COUNT(*) as total_rooms,
            SUM(CASE WHEN r.status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN r.status = 'occupied' THEN 1 ELSE 0 END) as occupied,
            SUM(CASE WHEN r.status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
            ROUND(AVG(r.rate), 2) as avg_rate
        FROM rooms r
        GROUP BY rt.room_type
        ORDER BY rt.room_type
    ");
    
    echo "Room Type\tTotal\tAvailable\tOccupied\tMaintenance\tAvg Rate\n";
    echo "---------\t-----\t--------\t--------\t----------\t--------\n";
    
    while ($row = $stmt->fetch()) {
        echo "{$row['room_type']}\t\t{$row['total_rooms']}\t{$row['available']}\t\t{$row['occupied']}\t\t{$row['maintenance']}\t\t\${$row['avg_rate']}\n";
    }
} catch (Exception $e) {
    echo "Room occupancy analysis failed: " . $e->getMessage() . "\n";
}

// 8. Revenue Analysis
echo "\n8. REVENUE ANALYSIS\n";
echo "===================\n";

try {
    $stmt = $pdo->query("
        SELECT 
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as avg_reservation_value,
            COUNT(*) as total_reservations
        FROM reservations
    ");
    $revenue = $stmt->fetch();
    
    echo "Total Revenue: \${$revenue['total_revenue']}\n";
    echo "Average Reservation Value: \${$revenue['avg_reservation_value']}\n";
    echo "Total Reservations: {$revenue['total_reservations']}\n";
} catch (Exception $e) {
    echo "Revenue analysis failed: " . $e->getMessage() . "\n";
}

// 9. Final Report
echo "\n9. INTEGRITY VERIFICATION REPORT\n";
echo "================================\n";

if ($total_issues === 0) {
    echo "✓ ALL INTEGRITY CHECKS PASSED!\n";
    echo "✓ Database is in excellent condition\n";
    echo "✓ All foreign key relationships are valid\n";
    echo "✓ Data consistency is maintained\n";
    echo "✓ Business logic rules are followed\n";
    echo "✓ Data quality standards are met\n";
} else {
    echo "⚠ {$total_issues} INTEGRITY ISSUES FOUND OUT OF {$total_checks} CHECKS\n";
    echo "⚠ Please review and fix the issues listed above\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "Total Checks Performed: {$total_checks}\n";
echo "Issues Found: {$total_issues}\n";
echo "Success Rate: " . round((($total_checks - $total_issues) / $total_checks) * 100, 2) . "%\n";
?>
