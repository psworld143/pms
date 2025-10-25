<?php
require __DIR__ . '/booking/config/database.php';
require __DIR__ . '/booking/includes/functions.php';

$guestId = isset($argv[1]) ? (int)$argv[1] : 0;
if ($guestId <= 0) {
    fwrite(STDERR, "Usage: php debug_guest.php <guest_id>\n");
    exit(1);
}

$result = getGuestDetails($guestId);
var_dump($result);
