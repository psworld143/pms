<?php
/**
 * Inventory Module API Index
 * Lists all available API endpoints
 */

header('Content-Type: application/json');

$api_endpoints = [
    'items' => [
        'GET' => '/api/items.php',
        'POST' => '/api/create-item.php',
        'PUT' => '/api/update-item.php',
        'DELETE' => '/api/delete-item.php'
    ],
    'transactions' => [
        'GET' => '/api/transactions.php',
        'POST' => '/api/create-transaction.php'
    ],
    'requests' => [
        'GET' => '/api/requests.php',
        'POST' => '/api/create-request.php',
        'PUT' => '/api/update-request.php'
    ],
    'categories' => [
        'GET' => '/api/categories.php',
        'POST' => '/api/create-category.php'
    ],
    'training' => [
        'GET' => '/api/scenarios.php',
        'POST' => '/api/complete-scenario.php'
    ]
];

echo json_encode([
    'status' => 'success',
    'message' => 'Inventory Module API',
    'version' => '1.0.0',
    'endpoints' => $api_endpoints
]);
?>
