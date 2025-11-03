<?php
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
/**
 * Process Check-in API
 * Handles guest check-in process
 */

session_start();
ob_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and has front desk access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['front_desk', 'manager'])) {
	ob_clean();
	echo json_encode([
		'success' => false,
		'message' => 'Unauthorized access'
	]);
	exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	ob_clean();
	echo json_encode([
		'success' => false,
		'message' => 'Method not allowed'
	]);
	exit();
}

try {
	// Get JSON input
	$input = json_decode(file_get_contents('php://input'), true);
	
	if (!$input) {
		throw new Exception('Invalid input data');
	}
	
	// Validate required fields
	$reservation_id = $input['reservation_id'] ?? null;
	$room_key_issued = $input['room_key_issued'] ?? null;
	$welcome_amenities = $input['welcome_amenities'] ?? null;
	$special_instructions = $input['special_instructions'] ?? '';
	
	if (!$reservation_id) {
		throw new Exception('Reservation ID is required');
	}
	
	if ($room_key_issued === null) {
		throw new Exception('Room key status is required');
	}
	
	if ($welcome_amenities === null) {
		throw new Exception('Welcome amenities status is required');
	}
	
	// Get reservation details
	$reservation = getReservationDetails($reservation_id);
	if (!$reservation) {
		throw new Exception('Reservation not found');
	}
	
	// Check if reservation is already checked in
	if ($reservation['status'] === 'checked_in') {
		throw new Exception('Guest is already checked in');
	}
	
	// Normalize enum values expected by DB ('yes'/'no')
	$normalizeYesNo = function($val) {
		if (is_bool($val)) return $val ? 'yes' : 'no';
		$s = strtolower(trim((string)$val));
		return in_array($s, ['1','yes','y','true','on'], true) ? 'yes' : 'no';
	};
	$room_key_issued = $normalizeYesNo($room_key_issued);
	$welcome_amenities = $normalizeYesNo($welcome_amenities);

	// Start transaction
	$pdo->beginTransaction();
	
	try {
		// Update reservation status
		$stmt = $pdo->prepare("
			UPDATE reservations 
			SET status = 'checked_in',
				checked_in_at = NOW(),
				checked_in_by = ?
			WHERE id = ?
		");
		$stmt->execute([$_SESSION['user_id'], $reservation_id]);
		
		// Update room status
		$stmt = $pdo->prepare("
			UPDATE rooms 
			SET status = 'occupied'
			WHERE id = ?
		");
		$stmt->execute([$reservation['room_id']]);
		
		// Insert check-in record (robust to non-AI id columns)
		try {
			$stmt = $pdo->prepare("
				INSERT INTO check_in_records (
					reservation_id,
					room_key_issued,
					welcome_amenities_provided,
					special_instructions,
					checked_in_by,
					checked_in_at
				) VALUES (?, ?, ?, ?, ?, NOW())
			");
			$stmt->execute([
				$reservation_id,
				$room_key_issued,
				$welcome_amenities,
				$special_instructions,
				$_SESSION['user_id']
			]);
		} catch (PDOException $e) {
			// If the table doesn't have AUTO_INCREMENT for id, compute next id and insert explicitly
			if (strpos($e->getMessage(), "doesn't have a default value") !== false) {
				$nextId = (int)$pdo->query("SELECT COALESCE(MAX(id)+1,1) AS next_id FROM check_in_records FOR UPDATE")->fetch()['next_id'];
				$stmt = $pdo->prepare("
					INSERT INTO check_in_records (
						id,
						reservation_id,
						room_key_issued,
						welcome_amenities_provided,
						special_instructions,
						checked_in_by,
						checked_in_at
					) VALUES (?, ?, ?, ?, ?, ?, NOW())
				");
				$stmt->execute([
					$nextId,
					$reservation_id,
					$room_key_issued,
					$welcome_amenities,
					$special_instructions,
					$_SESSION['user_id']
				]);
			} else {
				throw $e;
			}
		}

		// Ensure a billing row/invoice exists right after check-in
		try {
			ob_start();
			require_once __DIR__ . '/get-billing-summary.php';
			getBillingSummary((int)$reservation_id); // also upserts into billing
			ob_end_clean();
		} catch (Throwable $ignore) {}
		
		// Commit transaction
		$pdo->commit();
		
		ob_clean();
		echo json_encode([
			'success' => true,
			'message' => 'Check-in completed successfully',
			'reservation_id' => $reservation_id
		]);
		exit();
		
	} catch (Exception $e) {
		$pdo->rollBack();
		throw $e;
	}
	
} catch (Exception $e) {
	error_log("Error processing check-in: " . $e->getMessage());
	ob_clean();
	echo json_encode([
		'success' => false,
		'message' => $e->getMessage()
	]);
	exit();
}
?>
