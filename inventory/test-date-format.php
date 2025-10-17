<?php
/**
 * Test Date Format for Excel
 */

// Test different date formats
$test_date = '2025-01-16 09:00:01';

echo "<h1>Date Format Test for Excel</h1>";
echo "<p><strong>Original Date:</strong> " . $test_date . "</p>";

echo "<h2>Different Date Formats:</h2>";

$formats = [
    'Y-m-d H:i:s' => date('Y-m-d H:i:s', strtotime($test_date)),
    'm/d/Y H:i:s' => date('m/d/Y H:i:s', strtotime($test_date)),
    'd/m/Y H:i:s' => date('d/m/Y H:i:s', strtotime($test_date)),
    'Y-m-d' => date('Y-m-d', strtotime($test_date)),
    'm/d/Y' => date('m/d/Y', strtotime($test_date)),
    'd/m/Y' => date('d/m/Y', strtotime($test_date)),
    'Y-m-d H:i' => date('Y-m-d H:i', strtotime($test_date)),
    'm/d/Y H:i' => date('m/d/Y H:i', strtotime($test_date))
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>Format</th><th>Result</th><th>Excel Compatible</th>";
echo "</tr>";

foreach ($formats as $format => $result) {
    $excel_compatible = '';
    if (strpos($format, 'm/d/Y') !== false) {
        $excel_compatible = '✅ Yes (US format)';
    } elseif (strpos($format, 'd/m/Y') !== false) {
        $excel_compatible = '✅ Yes (EU format)';
    } elseif (strpos($format, 'Y-m-d') !== false) {
        $excel_compatible = '⚠️ Maybe (ISO format)';
    } else {
        $excel_compatible = '❌ No';
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($format) . "</td>";
    echo "<td>" . htmlspecialchars($result) . "</td>";
    echo "<td>" . $excel_compatible . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test CSV export with different date formats
echo "<h2>CSV Export Test:</h2>";

$test_entries = [
    [
        'created_at' => '2025-01-16 09:00:01',
        'reference_number' => 'INV-40',
        'account_code' => '5000',
        'description' => 'COGS - fggg',
        'debit_amount' => 2000.00,
        'credit_amount' => 0.00,
        'status' => 'posted'
    ],
    [
        'created_at' => '2025-01-16 09:00:01',
        'reference_number' => 'INV-40',
        'account_code' => '1200',
        'description' => 'Inventory Usage - fggg',
        'debit_amount' => 0.00,
        'credit_amount' => 2000.00,
        'status' => 'posted'
    ]
];

// Test with m/d/Y format
$csv_content = "Date,Reference,Account Code,Description,Debit,Credit,Status\n";

foreach ($test_entries as $entry) {
    $formatted_date = date('m/d/Y H:i:s', strtotime($entry['created_at']));
    $debit_amount = $entry['debit_amount'] > 0 ? number_format($entry['debit_amount'], 2) : '';
    $credit_amount = $entry['credit_amount'] > 0 ? number_format($entry['credit_amount'], 2) : '';
    
    $csv_content .= sprintf(
        '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
        $formatted_date,
        $entry['reference_number'],
        $entry['account_code'],
        $entry['description'],
        $debit_amount,
        $credit_amount,
        ucfirst($entry['status'])
    );
}

// Add BOM for UTF-8
$bom = "\xEF\xBB\xBF";
$csv_content = $bom . $csv_content;

// Save test file
$filename = 'test_date_format_' . date('Y-m-d_H-i-s') . '.xls';
$filepath = __DIR__ . '/tmp/' . $filename;

if (!is_dir(__DIR__ . '/tmp/')) {
    mkdir(__DIR__ . '/tmp/', 0755, true);
}

if (file_put_contents($filepath, $csv_content)) {
    echo "<p style='color: green;'>✅ Test file created successfully!</p>";
    echo "<p><strong>File:</strong> " . $filename . "</p>";
    echo "<p><a href='tmp/" . $filename . "' target='_blank'>Download Test File</a></p>";
    
    echo "<h3>CSV Content Preview:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($csv_content);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Failed to create test file</p>";
}

echo "<p><a href='accounting-integration.php'>Back to Accounting Module</a></p>";
?>
