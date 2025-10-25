<?php
/**
 * POS Module API Index
 * Lists all available API endpoints
 */

header('Content-Type: application/json');

$api_endpoints = [
    'transactions' => [
        'GET' => '/api/transactions.php',
        'POST' => '/api/create-transaction.php'
    ],
    'menu' => [
        'GET' => '/api/menu.php',
        'POST' => '/api/create-menu-item.php',
        'PUT' => '/api/update-menu-item.php'
    ],
    'orders' => [
        'GET' => '/api/orders.php',
        'POST' => '/api/create-order.php',
        'PUT' => '/api/update-order.php'
    ],
    'payments' => [
        'GET' => '/api/payments.php',
        'POST' => '/api/process-payment.php'
    ],
    'tables' => [
        'GET' => '/api/tables.php',
        'POST' => '/api/update-table.php'
    ]
];

echo json_encode([
    'status' => 'success',
    'message' => 'POS Module API',
    'version' => '1.0.0',
    'endpoints' => $api_endpoints
]);
?>
