<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
	// Require manager role; allow API key via header or query param
	if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['manager'])) {
		$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? ($_GET['api_key'] ?? $_GET['apikey'] ?? null);
		if ($apiKey && $apiKey === 'pms_users_api_2024') {
			$_SESSION['user_id'] = 1073;
			$_SESSION['user_role'] = 'manager';
			$_SESSION['name'] = 'API User';
		} else {
			http_response_code(401);
			echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
			exit();
		}
	}

	$confirm = $_POST['confirm'] ?? $_GET['confirm'] ?? '';
	if (strtolower($confirm) !== 'yes') {
		throw new Exception('Confirmation required. Pass confirm=yes to proceed.');
	}

	$pdo->beginTransaction();

	// 1) Purge billing/invoice/payment and service charge data
	try { $pdo->exec('SET FOREIGN_KEY_CHECKS=0'); } catch (Throwable $e) {}
	$tables = ['payments','bill_items','bills','billing','service_charges','reservation_services','discounts'];
	$deleted = [];
	foreach ($tables as $t) {
		try { $pdo->exec("DELETE FROM `{$t}`"); $deleted[] = $t; } catch (PDOException $e) { /* table may not exist */ }
	}
	try { $pdo->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (Throwable $e) {}

	// 2) Seed realistic additional services if missing
	$defaults = [
		['Room Service - Breakfast', 'Continental breakfast served in-room', 25.00, 'food_beverage'],
		['Room Service - Dinner', 'Chef special dinner', 45.00, 'food_beverage'],
		['Laundry Service', 'Wash and fold', 15.00, 'laundry'],
		['Minibar Items', 'Assorted minibar consumption', 12.50, 'food_beverage'],
		['Concierge Service', 'Errand/arrangement service', 20.00, 'other'],
		['Housekeeping Extra', 'Extra housekeeping/turn-down', 10.00, 'other'],
		['Airport Transfer', 'One-way shuttle', 40.00, 'transportation'],
		['Spa - Massage', '60-minute massage', 60.00, 'spa']
	];
	$created = 0; $existing = 0;
	foreach ($defaults as $svc) {
		list($name, $desc, $price, $category) = $svc;
		try {
			$find = $pdo->prepare('SELECT id FROM additional_services WHERE name = ? LIMIT 1');
			$find->execute([$name]);
			$row = $find->fetch();
			if ($row && isset($row['id'])) { $existing++; continue; }
			$ins = $pdo->prepare('INSERT INTO additional_services (name, description, price, category, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())');
			$ins->execute([$name, $desc, $price, $category]);
			$created++;
		} catch (PDOException $e) {
			// if additional_services missing, skip seeding silently
		}
	}

	$pdo->commit();

	echo json_encode([
		'success' => true,
		'message' => 'Reset complete: billing/payment/charges purged; services seeded.',
		'deleted_tables' => $deleted,
		'services_created' => $created,
		'services_existing' => $existing
	]);
} catch (Exception $e) {
	if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
	error_log('Reset billing/services error: ' . $e->getMessage());
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
