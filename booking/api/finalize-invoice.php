<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
	// Auth (front_desk or manager), allow API key fallback
	if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
		$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_API_KEY'] ?? null;
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

	$raw_id = $_GET['reservation_id'] ?? $_POST['reservation_id'] ?? null;
	if (!$raw_id) {
		throw new Exception('Reservation ID is required');
	}

	// Resolve reservation id if reservation number passed
	$reservation_id = is_numeric($raw_id) ? (int)$raw_id : 0;
	if ($reservation_id === 0) {
		$lookup = $pdo->prepare("SELECT id FROM reservations WHERE reservation_number = ? LIMIT 1");
		$lookup->execute([$raw_id]);
		$row = $lookup->fetch();
		if ($row && isset($row['id'])) {
			$reservation_id = (int)$row['id'];
		}
	}
	if ($reservation_id === 0) {
		throw new Exception('Reservation not found');
	}

	// Reuse get-billing-summary compute by calling the function directly
	require_once __DIR__ . '/get-billing-summary.php';
	$summary = getBillingSummary($reservation_id);
	if (!$summary) {
		throw new Exception('Unable to compute billing summary');
	}

	// Upsert into billing (get-billing-summary already attempts this, but ensure persistence)
	try {
		$find = $pdo->prepare("SELECT id FROM billing WHERE reservation_id = ?");
		$find->execute([$reservation_id]);
		$bill = $find->fetch();
		if ($bill) {
			$upd = $pdo->prepare("UPDATE billing SET room_charges=?, additional_charges=?, tax_amount=?, total_amount=?, payment_status=COALESCE(payment_status,'pending') WHERE id=?");
			$upd->execute([
				$summary['room_charges'],
				$summary['additional_charges'],
				$summary['tax_amount'],
				$summary['total_amount'],
				$bill['id']
			]);
			$summary['id'] = $bill['id'];
		} else {
			$ins = $pdo->prepare("INSERT INTO billing (reservation_id, guest_id, room_charges, additional_charges, tax_amount, total_amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
			$ins->execute([
				$reservation_id,
				$summary['guest_id'] ?? null,
				$summary['room_charges'],
				$summary['additional_charges'],
				$summary['tax_amount'],
				$summary['total_amount']
			]);
			$summary['id'] = $pdo->lastInsertId();
		}
	} catch (PDOException $e) {
		// ignore persistence failure if compute succeeded
		error_log('Finalize invoice upsert warning: ' . $e->getMessage());
	}

	// Also ensure a formal invoice exists in `bills` with `bill_items`
	try {
		// Ensure bill row
		$selectBill = $pdo->prepare("SELECT id, bill_number FROM bills WHERE reservation_id = ? LIMIT 1");
		$selectBill->execute([$reservation_id]);
		$billRow = $selectBill->fetch();
		if ($billRow && isset($billRow['id'])) {
			$billId = (int)$billRow['id'];
			$updBill = $pdo->prepare("UPDATE bills SET bill_date = CURDATE(), due_date = CURDATE(), subtotal = ?, tax_amount = ?, discount_amount = ?, total_amount = ?, status = 'pending', updated_at = NOW() WHERE id = ?");
			$updBill->execute([
				(float)$summary['room_charges'] + (float)$summary['additional_charges'],
				(float)$summary['tax_amount'],
				(float)($summary['discounts'] ?? 0),
				(float)$summary['total_amount'],
				$billId
			]);
		} else {
			$billNumber = 'INV' . date('Ymd') . sprintf('%04d', (int)$reservation_id);
			$insBill = $pdo->prepare("INSERT INTO bills (bill_number, reservation_id, bill_date, due_date, subtotal, tax_amount, discount_amount, total_amount, status, notes, created_by, created_at, updated_at) VALUES (?, ?, CURDATE(), CURDATE(), ?, ?, ?, ?, 'pending', NULL, ?, NOW(), NOW())");
			$insBill->execute([
				$billNumber,
				$reservation_id,
				(float)$summary['room_charges'] + (float)$summary['additional_charges'],
				(float)$summary['tax_amount'],
				(float)($summary['discounts'] ?? 0),
				(float)$summary['total_amount'],
				$_SESSION['user_id'] ?? null
			]);
			$billId = (int)$pdo->lastInsertId();
		}

		// Rebuild bill_items
		try {
			$pdo->prepare("DELETE FROM bill_items WHERE bill_id = ?")->execute([$billId]);
		} catch (PDOException $e) {}

		// 1) Room charge line item
		$descRoom = 'Room charges (' . (int)($summary['nights'] ?? 1) . ' nights @ ' . number_format((float)$summary['room_rate'], 2) . ')';
		$insItem = $pdo->prepare("INSERT INTO bill_items (bill_id, description, quantity, unit_price, total_amount) VALUES (?, ?, ?, ?, ?)");
		$insItem->execute([$billId, $descRoom, (float)($summary['nights'] ?? 1), (float)$summary['room_rate'], (float)$summary['room_charges']]);

		// 2) Service charges as individual items (group by service name if available)
		try {
			$q = $pdo->prepare("SELECT COALESCE(s.name, rs.service_name) AS name, SUM(COALESCE(sc.quantity, rs.quantity, 1)) AS qty, 
										SUM(COALESCE(sc.total_price, rs.amount * rs.quantity, 0)) AS total,
										CASE WHEN SUM(COALESCE(sc.quantity, rs.quantity, 1)) > 0 THEN 
											SUM(COALESCE(sc.total_price, rs.amount * rs.quantity, 0)) / SUM(COALESCE(sc.quantity, rs.quantity, 1))
										ELSE 0 END AS unit
							FROM reservations r
							LEFT JOIN service_charges sc ON sc.reservation_id = r.id
							LEFT JOIN additional_services s ON sc.service_id = s.id
							LEFT JOIN reservation_services rs ON rs.reservation_id = r.id
							WHERE r.id = ?
							GROUP BY COALESCE(s.name, rs.service_name)");
			$q->execute([$reservation_id]);
			$rows = $q->fetchAll();
			foreach ($rows as $r) {
				if (!$r || !$r['name']) { continue; }
				$insItem->execute([$billId, (string)$r['name'], (float)($r['qty'] ?? 1), (float)($r['unit'] ?? 0), (float)($r['total'] ?? 0)]);
			}
		} catch (PDOException $e) {
			// ignore if tables missing; room and tax still present
		}

		// 3) Tax line item
		if ((float)$summary['tax_amount'] > 0) {
			$insItem->execute([$billId, 'Tax (10%)', 1, (float)$summary['tax_amount'], (float)$summary['tax_amount']]);
		}
	} catch (PDOException $e) {
		error_log('Bills/bill_items upsert warning: ' . $e->getMessage());
	}

	echo json_encode(['success' => true, 'billing' => $summary]);
} catch (Exception $e) {
	error_log('Finalize invoice error: ' . $e->getMessage());
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


