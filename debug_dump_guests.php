<?php
require __DIR__ . '/booking/config/database.php';

$stmt = $pdo->query('SELECT id, first_name, last_name FROM guests ORDER BY id DESC LIMIT 20');
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_export($result);
