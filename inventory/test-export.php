<?php
/**
 * Test Export Functionality
 */

// Test if tmp directory exists and is writable
$tmp_dir = __DIR__ . '/tmp/';

echo "<h1>Export Test</h1>";
echo "<p>Testing export functionality...</p>";

// Check if tmp directory exists
if (!is_dir($tmp_dir)) {
    echo "<p>Creating tmp directory...</p>";
    if (mkdir($tmp_dir, 0755, true)) {
        echo "<p style='color: green;'>✅ Tmp directory created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create tmp directory</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Tmp directory exists</p>";
}

// Check if directory is writable
if (is_writable($tmp_dir)) {
    echo "<p style='color: green;'>✅ Tmp directory is writable</p>";
} else {
    echo "<p style='color: red;'>❌ Tmp directory is not writable</p>";
}

// Test file creation
$test_file = $tmp_dir . 'test_export.csv';
$test_content = "Date,Reference,Account Code,Description,Debit,Credit,Status\n";
$test_content .= "2025-01-01,INV-001,1200,Test Entry,₱100.00,₱0.00,Posted\n";

if (file_put_contents($test_file, $test_content)) {
    echo "<p style='color: green;'>✅ Test file created successfully</p>";
    echo "<p>Test file path: " . $test_file . "</p>";
    echo "<p><a href='tmp/test_export.csv' target='_blank'>Download Test File</a></p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create test file</p>";
}

echo "<p><a href='accounting-integration.php'>Back to Accounting Module</a></p>";
?>
