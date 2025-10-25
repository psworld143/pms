<?php
/**
 * Test Export Format
 */

// Sample journal entries data (similar to what would come from database)
$sample_entries = [
    [
        'id' => 1,
        'created_at' => '2025-01-16 09:00:01',
        'reference_number' => 'INV-40',
        'account_code' => '5000',
        'description' => 'COGS - fggg',
        'debit_amount' => 2000.00,
        'credit_amount' => 0.00,
        'status' => 'posted'
    ],
    [
        'id' => 2,
        'created_at' => '2025-01-16 09:00:01',
        'reference_number' => 'INV-40',
        'account_code' => '1200',
        'description' => 'Inventory Usage - fggg',
        'debit_amount' => 0.00,
        'credit_amount' => 2000.00,
        'status' => 'posted'
    ],
    [
        'id' => 3,
        'created_at' => '2025-01-16 09:00:01',
        'reference_number' => 'INV-39',
        'account_code' => '5000',
        'description' => 'COGS - adriane',
        'debit_amount' => 190.00,
        'credit_amount' => 0.00,
        'status' => 'posted'
    ],
    [
        'id' => 4,
        'created_at' => '2025-01-16 09:00:01',
        'reference_number' => 'INV-39',
        'account_code' => '1200',
        'description' => 'Inventory Usage - adriane',
        'debit_amount' => 0.00,
        'credit_amount' => 190.00,
        'status' => 'posted'
    ]
];

// Create CSV content with proper headers and formatting
$csv_content = "Date,Reference,Account Code,Description,Debit,Credit,Status\n";

foreach ($sample_entries as $entry) {
    // Format date properly for Excel compatibility
    $formatted_date = date('Y-m-d H:i:s', strtotime($entry['created_at']));
    
    // Format debit amount - show as number only, no currency symbol for Excel
    $debit_amount = $entry['debit_amount'] > 0 ? number_format($entry['debit_amount'], 2) : '';
    
    // Format credit amount - show as number only, no currency symbol for Excel
    $credit_amount = $entry['credit_amount'] > 0 ? number_format($entry['credit_amount'], 2) : '';
    
    // Clean description - remove special characters that cause encoding issues
    $clean_description = preg_replace('/[^\x20-\x7E]/', '', $entry['description']);
    $clean_description = str_replace([',', '"', "\n", "\r"], [';', "'", ' ', ' '], $clean_description);
    
    // Format status
    $status = ucfirst($entry['status']);
    
    // Build CSV row with proper escaping
    $csv_content .= sprintf(
        '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
        $formatted_date,
        $entry['reference_number'] ?: 'N/A',
        $entry['account_code'],
        $clean_description,
        $debit_amount,
        $credit_amount,
        $status
    );
}

// Add BOM for UTF-8 to ensure proper encoding in Excel
$bom = "\xEF\xBB\xBF";
$csv_content = $bom . $csv_content;

// Create test file
$filename = 'test_journal_entries_' . date('Y-m-d_H-i-s') . '.csv';
$filepath = __DIR__ . '/tmp/' . $filename;

// Ensure tmp directory exists
if (!is_dir(__DIR__ . '/tmp/')) {
    mkdir(__DIR__ . '/tmp/', 0755, true);
}

// Write file
if (file_put_contents($filepath, $csv_content)) {
    echo "<h1>Export Format Test</h1>";
    echo "<p style='color: green;'>✅ Test file created successfully!</p>";
    echo "<p><strong>File:</strong> " . $filename . "</p>";
    echo "<p><a href='tmp/" . $filename . "' target='_blank'>Download Test File</a></p>";
    
    echo "<h2>CSV Content Preview:</h2>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($csv_content);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Failed to create test file</p>";
}

echo "<p><a href='accounting-integration.php'>Back to Accounting Module</a></p>";
?>
