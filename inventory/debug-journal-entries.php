<?php
/**
 * Debug Journal Entries
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: text/html');

if (!isset($_SESSION['user_id'])) {
    echo "Not logged in";
    exit();
}

try {
    global $pdo;
    
    echo "<h1>Journal Entries Debug</h1>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory_journal_entries'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Table 'inventory_journal_entries' does not exist</p>";
        
        // Create the table
        echo "<p>Creating table...</p>";
        $create_table_sql = "
            CREATE TABLE inventory_journal_entries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference_number VARCHAR(50),
                account_code VARCHAR(20) NOT NULL,
                description TEXT NOT NULL,
                debit_amount DECIMAL(10,2) DEFAULT 0.00,
                credit_amount DECIMAL(10,2) DEFAULT 0.00,
                status ENUM('pending', 'posted', 'reversed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                posted_at TIMESTAMP NULL
            )
        ";
        
        $pdo->exec($create_table_sql);
        echo "<p style='color: green;'>✅ Table created successfully</p>";
        
        // Insert sample data
        echo "<p>Inserting sample data...</p>";
        $sample_data = [
            ['INV-40', '5000', 'COGS - fggg', 2000.00, 0.00, 'posted'],
            ['INV-40', '1200', 'Inventory Usage - fggg', 0.00, 2000.00, 'posted'],
            ['INV-39', '5000', 'COGS - adriane', 190.00, 0.00, 'posted'],
            ['INV-39', '1200', 'Inventory Usage - adriane', 0.00, 190.00, 'posted'],
            ['INV-38', '5000', 'COGS - File Folders (Manila)', 1020.00, 0.00, 'posted'],
            ['INV-38', '1200', 'Inventory Usage - File Folders (Manila)', 0.00, 1020.00, 'posted']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO inventory_journal_entries 
            (reference_number, account_code, description, debit_amount, credit_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sample_data as $data) {
            $stmt->execute($data);
        }
        
        echo "<p style='color: green;'>✅ Sample data inserted</p>";
    } else {
        echo "<p style='color: green;'>✅ Table 'inventory_journal_entries' exists</p>";
    }
    
    // Get all journal entries
    $stmt = $pdo->query("SELECT * FROM inventory_journal_entries ORDER BY created_at DESC");
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Journal Entries (" . count($entries) . " entries)</h2>";
    
    if (count($entries) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Date</th><th>Reference</th><th>Account Code</th><th>Description</th><th>Debit</th><th>Credit</th><th>Status</th>";
        echo "</tr>";
        
        foreach ($entries as $entry) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($entry['id']) . "</td>";
            echo "<td>" . htmlspecialchars($entry['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($entry['reference_number']) . "</td>";
            echo "<td>" . htmlspecialchars($entry['account_code']) . "</td>";
            echo "<td>" . htmlspecialchars($entry['description']) . "</td>";
            echo "<td>" . ($entry['debit_amount'] > 0 ? '₱' . number_format($entry['debit_amount'], 2) : '') . "</td>";
            echo "<td>" . ($entry['credit_amount'] > 0 ? '₱' . number_format($entry['credit_amount'], 2) : '') . "</td>";
            echo "<td>" . htmlspecialchars($entry['status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No journal entries found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='accounting-integration.php'>Back to Accounting Module</a></p>";
?>
