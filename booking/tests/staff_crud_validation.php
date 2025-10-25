<?php
try {
    require __DIR__ . '/../includes/booking-paths.php';
    booking_initialize_paths();
    require __DIR__ . '/../../includes/functions.php';

    $result = [
        'before_count' => count(getUsers()),
        'steps' => []
    ];

    $username = 'cli_staff_' . time();
    $create = createUser([
        'name' => 'CLI Staff Tester',
        'username' => $username,
        'password' => 'TempPass123!',
        'email' => $username . '@example.com',
        'role' => 'front_desk'
    ]);
    $result['steps']['create'] = $create;

    $userId = $create['user_id'] ?? null;
    $result['steps']['after_create'] = getUsers('', '', $username);

    if ($userId) {
        $update = updateUser((int) $userId, [
            'role' => 'housekeeping',
            'is_active' => 0
        ]);
        $result['steps']['update'] = $update;
        $result['steps']['after_update'] = getUsers('', '', $username);

        $delete = deleteUser((int) $userId, 1);
        $result['steps']['delete'] = $delete;
        $result['steps']['after_delete'] = getUsers('', '', $username);
    }

    echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Validation error: ' . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, $e->getTraceAsString() . PHP_EOL);
    exit(1);
}
