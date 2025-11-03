<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
	// Require manager role; allow API key fallback (header or query param for local testing)
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
		throw new Exception("Confirmation required. Pass confirm=yes to proceed.");
	}

	$pdo->beginTransaction();

	// Disable foreign key checks to simplify truncation across varying schemas
	try { $pdo->exec('SET FOREIGN_KEY_CHECKS=0'); } catch (Throwable $e) {}

	$tables = [
		// Payment/invoice tables (both legacy and current variants)
		'payments', 'bill_items', 'bills', 'billing',
		// Service charges variants
		'service_charges', 'reservation_services',
		// Optional discounts tied to reservations/bills
		'discounts'
	];

	$deleted = [];
	foreach ($tables as $table) {
		try {
			$pdo->exec("DELETE FROM `{$table}`");
			$deleted[] = $table;
		} catch (PDOException $e) {
			// Table might not exist in this install — skip silently
		}
	}

	// Re-enable foreign key checks
	try { $pdo->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (Throwable $e) {}

	$pdo->commit();

	echo json_encode([
		'success' => true,
		'message' => 'Invoice, payment, and service charge data purged successfully',
		'deleted_tables' => $deleted
	]);
} catch (Exception $e) {
	if ($pdo && $pdo->inTransaction()) {
		$pdo->rollBack();
	}
	error_log('Purge billing data error: ' . $e->getMessage());
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
