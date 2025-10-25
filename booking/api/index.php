<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
/**
 * Booking Module API Index
 * Lists all available API endpoints
 */

header('Content-Type: application/json');

$api_endpoints = [
    'reservations' => [
        'GET' => '/api/reservations.php',
        'POST' => '/api/create-reservation.php',
        'PUT' => '/api/update-reservation.php',
        'DELETE' => '/api/delete-reservation.php'
    ],
    'rooms' => [
        'GET' => '/api/rooms.php',
        'POST' => '/api/create-room.php',
        'PUT' => '/api/update-room.php'
    ],
    'guests' => [
        'GET' => '/api/guests.php',
        'POST' => '/api/create-guest.php',
        'PUT' => '/api/update-guest.php'
    ],
    'check_in' => [
        'POST' => '/api/check-in.php'
    ],
    'check_out' => [
        'POST' => '/api/check-out.php'
    ]
];

echo json_encode([
    'status' => 'success',
    'message' => 'Booking Module API',
    'version' => '1.0.0',
    'endpoints' => $api_endpoints
]);
?>
