<?php
/**
 * Export Journal Entries
 * Hotel PMS Training System - Inventory Module
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    global $pdo;
    
    // Get all journal entries
    $stmt = $pdo->query("
        SELECT * FROM inventory_journal_entries 
        ORDER BY created_at DESC
    ");
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create CSV content with proper headers and formatting
    $csv_content = "Date,Reference,Account Code,Description,Debit,Credit,Status\n";
    
    foreach ($entries as $entry) {
        // Format date for Excel compatibility - use Excel-friendly format
        $formatted_date = date('m/d/Y H:i:s', strtotime($entry['created_at']));
        
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
    
    // Create temporary file with .xls extension for better Excel compatibility
    $filename = 'journal_entries_' . date('Y-m-d_H-i-s') . '.xls';
    $tmp_dir = __DIR__ . '/../tmp/';
    $filepath = $tmp_dir . $filename;
    
    // Ensure tmp directory exists
    if (!is_dir($tmp_dir)) {
        mkdir($tmp_dir, 0755, true);
    }
    
    // Add BOM for UTF-8 to ensure proper encoding in Excel
    $bom = "\xEF\xBB\xBF";
    $csv_content = $bom . $csv_content;
    
    // Write CSV content to file
    if (file_put_contents($filepath, $csv_content) === false) {
        throw new Exception('Failed to write CSV file');
    }
    
    // Return download URL (relative to inventory directory)
    $download_url = 'tmp/' . $filename;
    
    echo json_encode([
        'success' => true,
        'download_url' => $download_url,
        'filename' => $filename,
        'message' => 'Journal entries exported successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
