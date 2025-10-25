<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Check for API key in headers (for AJAX requests)
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;

    if ($apiKey && $apiKey === 'pms_users_api_2024') {
        // Valid API key, create session
        $_SESSION['user_id'] = 1073; // Default manager user
        $_SESSION['user_role'] = 'manager';
        $_SESSION['name'] = 'API User';
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }
}

// Check if bill ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bill ID is required']);
    exit();
}

$bill_id = (int)$_GET['id'];

try {
    // Get bill details with all related information
    $stmt = $pdo->prepare("
        SELECT b.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name,
               g.email as guest_email,
               g.phone as guest_phone,
               r.room_number,
               r.room_type,
               res.check_in_date,
               res.check_out_date,
               res.status as reservation_status,
               res.adults,
               res.children
        FROM bills b
        JOIN reservations res ON b.reservation_id = res.id
        JOIN guests g ON res.guest_id = g.id
        JOIN rooms r ON res.room_id = r.id
        WHERE b.id = ?
    ");
    $stmt->execute([$bill_id]);
    $bill = $stmt->fetch();

    if (!$bill) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Bill not found']);
        exit();
    }

    // Calculate nights
    $nights = (strtotime($bill['check_out_date']) - strtotime($bill['check_in_date'])) / (60 * 60 * 24);
    
    // Generate HTML invoice
    $html = generateInvoiceHTML($bill, $nights);
    
    // Set headers for HTML download (printable as PDF)
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="Bill_' . $bill['bill_number'] . '_' . date('Y-m-d') . '.html"');
    
    // Output HTML that can be printed as PDF
    echo $html;

} catch (Exception $e) {
    error_log("Error generating bill PDF: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error generating PDF: ' . $e->getMessage()
    ]);
}

function generateInvoiceHTML($bill, $nights) {
    // Safely handle missing or null values
    $bill_number = $bill['bill_number'] ?? 'N/A';
    $bill_date = $bill['bill_date'] ?? date('Y-m-d');
    $due_date = $bill['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
    $status = $bill['status'] ?? 'pending';
    $guest_name = $bill['guest_name'] ?? 'Unknown Guest';
    $guest_email = $bill['guest_email'] ?? 'Not provided';
    $guest_phone = $bill['guest_phone'] ?? 'Not provided';
    $room_number = $bill['room_number'] ?? 'N/A';
    $room_type = $bill['room_type'] ?? 'standard';
    $check_in_date = $bill['check_in_date'] ?? date('Y-m-d');
    $check_out_date = $bill['check_out_date'] ?? date('Y-m-d');
    $adults = $bill['adults'] ?? 1;
    $children = $bill['children'] ?? 0;
    $total_amount = $bill['total_amount'] ?? 0;
    
    // Calculate rate per night
    $rate_per_night = $nights > 0 ? $total_amount / $nights : $total_amount;
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Bill #' . htmlspecialchars($bill_number) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .hotel-name { font-size: 24px; font-weight: bold; color: #333; }
            .hotel-address { color: #666; margin: 5px 0; }
            .bill-title { font-size: 20px; font-weight: bold; margin: 20px 0; text-align: center; }
            .section { margin: 20px 0; }
            .section-title { font-weight: bold; font-size: 16px; margin-bottom: 10px; border-bottom: 1px solid #ccc; }
            .info-row { margin: 5px 0; }
            .label { font-weight: bold; display: inline-block; width: 150px; }
            .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .table th { background-color: #f2f2f2; font-weight: bold; }
            .total-row { font-weight: bold; background-color: #f9f9f9; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="hotel-name">HOTEL MANAGEMENT SYSTEM</div>
            <div class="hotel-address">123 Hotel Street, City, Philippines</div>
            <div class="hotel-address">Phone: +63 123 456 7890 | Email: info@hotel.com</div>
        </div>
        
        <div class="bill-title">BILL / INVOICE</div>
        
        <div class="section">
            <div class="section-title">Bill Information</div>
            <div class="info-row"><span class="label">Bill Number:</span>' . htmlspecialchars($bill_number) . '</div>
            <div class="info-row"><span class="label">Bill Date:</span>' . date('F d, Y', strtotime($bill_date)) . '</div>
            <div class="info-row"><span class="label">Due Date:</span>' . date('F d, Y', strtotime($due_date)) . '</div>
            <div class="info-row"><span class="label">Status:</span>' . ucfirst($status) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Guest Information</div>
            <div class="info-row"><span class="label">Guest Name:</span>' . htmlspecialchars($guest_name) . '</div>
            <div class="info-row"><span class="label">Email:</span>' . htmlspecialchars($guest_email) . '</div>
            <div class="info-row"><span class="label">Phone:</span>' . htmlspecialchars($guest_phone) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Reservation Information</div>
            <div class="info-row"><span class="label">Room Number:</span>' . htmlspecialchars($room_number) . '</div>
            <div class="info-row"><span class="label">Room Type:</span>' . ucfirst(str_replace('_', ' ', $room_type)) . '</div>
            <div class="info-row"><span class="label">Check-in:</span>' . date('F d, Y', strtotime($check_in_date)) . '</div>
            <div class="info-row"><span class="label">Check-out:</span>' . date('F d, Y', strtotime($check_out_date)) . '</div>
            <div class="info-row"><span class="label">Nights:</span>' . $nights . ' night(s)</div>
            <div class="info-row"><span class="label">Guests:</span>' . $adults . ' adult(s), ' . $children . ' child(ren)</div>
        </div>
        
        <div class="section">
            <div class="section-title">Bill Items</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Room Charges (' . ucfirst(str_replace('_', ' ', $room_type)) . ')</td>
                        <td class="text-center">' . $nights . ' night(s)</td>
                        <td class="text-right">₱' . number_format($rate_per_night, 2) . '</td>
                        <td class="text-right">₱' . number_format($total_amount, 2) . '</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">TOTAL:</td>
                        <td class="text-right">₱' . number_format($total_amount, 2) . '</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Payment Information</div>
            <div class="info-row"><span class="label">Payment Due:</span>₱' . number_format($total_amount, 2) . '</div>
            <div class="info-row"><span class="label">Due Date:</span>' . date('F d, Y', strtotime($due_date)) . '</div>
            <div class="info-row"><span class="label">Payment Status:</span>' . ucfirst($status) . '</div>
        </div>
        
        <div class="footer">
            <p><strong>Terms and Conditions:</strong></p>
            <ul>
                <li>Payment is due upon receipt of this bill.</li>
                <li>Late payments may incur additional charges.</li>
                <li>All rates are subject to applicable taxes.</li>
                <li>Cancellation policies apply as per hotel terms.</li>
                <li>For questions, please contact the front desk.</li>
            </ul>
            <p class="text-center">Thank you for choosing our hotel!</p>
            <p class="text-center">Generated on ' . date('F d, Y g:i A') . '</p>
            <div class="no-print" style="margin-top: 20px; text-align: center;">
                <button onclick="window.print()" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Print as PDF</button>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>
