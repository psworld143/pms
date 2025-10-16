<?php
/**
 * Export Journal Entries to PDF
 * Hotel PMS Training System - Inventory Module
 */

require_once '../../vps_session_fix.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only managers can export journal entries.']);
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
    
    // Create PDF content using simple HTML that can be printed as PDF
    $pdf_content = generatePDFContent($entries);
    
    // Create temporary file with .html extension for now
    $filename = 'journal_entries_' . date('Y-m-d_H-i-s') . '.html';
    $tmp_dir = __DIR__ . '/../tmp/';
    $filepath = $tmp_dir . $filename;
    
    // Ensure tmp directory exists
    if (!is_dir($tmp_dir)) {
        mkdir($tmp_dir, 0755, true);
    }
    
    // Write HTML content to file
    if (file_put_contents($filepath, $pdf_content) === false) {
        throw new Exception('Failed to write PDF file');
    }
    
    // Return download URL
    $download_url = 'tmp/' . $filename;
    
    echo json_encode([
        'success' => true,
        'download_url' => $download_url,
        'filename' => $filename,
        'message' => 'Journal entries exported to PDF successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function generatePDFContent($entries) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Journal Entries Report</title>
        <style>
            @media print {
                body { margin: 0; padding: 10px; }
                .page-break { page-break-before: always; }
            }
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f8f9fa;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding: 20px;
                background: linear-gradient(135deg, #10B981, #059669);
                color: white;
                border-radius: 10px;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: bold;
            }
            .header p {
                margin: 5px 0 0 0;
                font-size: 14px;
                opacity: 0.9;
            }
            .summary {
                display: flex;
                justify-content: space-around;
                margin-bottom: 30px;
                flex-wrap: wrap;
            }
            .summary-card {
                background: white;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                text-align: center;
                margin: 5px;
                min-width: 150px;
            }
            .summary-card h3 {
                margin: 0 0 5px 0;
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
            }
            .summary-card .value {
                font-size: 20px;
                font-weight: bold;
                color: #10B981;
            }
            .table-container {
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background: #f8f9fa;
                color: #374151;
                font-weight: 600;
                padding: 12px 8px;
                text-align: left;
                border-bottom: 2px solid #e5e7eb;
                font-size: 12px;
            }
            td {
                padding: 10px 8px;
                border-bottom: 1px solid #e5e7eb;
                font-size: 11px;
            }
            tr:nth-child(even) {
                background-color: #f9fafb;
            }
            .status-posted {
                background: #d1fae5;
                color: #065f46;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: 600;
            }
            .status-pending {
                background: #fef3c7;
                color: #92400e;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: 600;
            }
            .debit-amount {
                color: #dc2626;
                font-weight: 600;
            }
            .credit-amount {
                color: #059669;
                font-weight: 600;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                color: #6b7280;
                font-size: 12px;
            }
            .page-break {
                page-break-before: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Hotel PMS Inventory Management</h1>
            <p>Journal Entries Report - Generated on ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <h3>Total Entries</h3>
                <div class="value">' . count($entries) . '</div>
            </div>
            <div class="summary-card">
                <h3>Posted Entries</h3>
                <div class="value">' . count(array_filter($entries, function($e) { return $e['status'] === 'posted'; })) . '</div>
            </div>
            <div class="summary-card">
                <h3>Pending Entries</h3>
                <div class="value">' . count(array_filter($entries, function($e) { return $e['status'] === 'pending'; })) . '</div>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Account Code</th>
                        <th>Description</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($entries as $entry) {
        $formatted_date = date('M j, Y H:i', strtotime($entry['created_at']));
        $debit_amount = $entry['debit_amount'] > 0 ? '₱' . number_format($entry['debit_amount'], 2) : '-';
        $credit_amount = $entry['credit_amount'] > 0 ? '₱' . number_format($entry['credit_amount'], 2) : '-';
        $status_class = $entry['status'] === 'posted' ? 'status-posted' : 'status-pending';
        $status_text = ucfirst($entry['status']);
        
        $debit_class = $entry['debit_amount'] > 0 ? 'debit-amount' : '';
        $credit_class = $entry['credit_amount'] > 0 ? 'credit-amount' : '';
        
        $html .= '
                    <tr>
                        <td>' . htmlspecialchars($formatted_date) . '</td>
                        <td>' . htmlspecialchars($entry['reference_number'] ?: 'N/A') . '</td>
                        <td>' . htmlspecialchars($entry['account_code']) . '</td>
                        <td>' . htmlspecialchars($entry['description']) . '</td>
                        <td class="' . $debit_class . '">' . $debit_amount . '</td>
                        <td class="' . $credit_class . '">' . $credit_amount . '</td>
                        <td><span class="' . $status_class . '">' . $status_text . '</span></td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>This report was generated by Hotel PMS Inventory Management System</p>
            <p>For questions or support, please contact the system administrator</p>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="background: #10B981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
                <i class="fas fa-print"></i> Print as PDF
            </button>
        </div>
        
        <script>
            // Auto-print when page loads
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            };
        </script>
    </body>
    </html>';
    
    return $html;
}
?>
