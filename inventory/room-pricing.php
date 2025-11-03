<?php
require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

// Require login and manager role
if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit();
}

$user_role = $_SESSION['user_role'] ?? '';
if ($user_role !== 'manager') {
	header('Location: index.php');
	exit();
}

$message = '';
$message_type = 'success';

// Handle rate update (per type)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$room_type_post = isset($_POST['room_type']) ? trim($_POST['room_type']) : '';
	$new_rate = isset($_POST['new_rate']) ? trim($_POST['new_rate']) : '';

	if ($room_type_post === '' || $new_rate === '') {
		$message = 'Invalid input.';
		$message_type = 'error';
	} else {
		// Validate decimal(10,2)
		if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $new_rate)) {
			$message = 'Rate must be a valid amount (up to 2 decimals).';
			$message_type = 'error';
		} else {
			try {
				$pdo->beginTransaction();
				$update = $pdo->prepare("UPDATE rooms SET rate = :rate, updated_at = NOW() WHERE LOWER(TRIM(room_type)) = LOWER(TRIM(:room_type))");
				$update->execute([
					':rate' => $new_rate,
					':room_type' => $room_type_post
				]);

				// Optional: log activity if table exists
				try {
					$log = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
					$log->execute([
						(int)($_SESSION['user_id'] ?? 0),
						'update_room_type_rate',
						'Updated room type rate (' . $room_type_post . ') to ' . $new_rate,
						$_SERVER['REMOTE_ADDR'] ?? 'unknown'
					]);
				} catch (Exception $e) {
					// Non-fatal if activity_log not present
				}

				$pdo->commit();
				$message = 'Room type rate updated successfully.';
				$message_type = 'success';
			} catch (Exception $e) {
				if ($pdo->inTransaction()) {
					$pdo->rollBack();
				}
				error_log('Room pricing update failed: ' . $e->getMessage());
				$message = 'Failed to update rate. Please try again.';
				$message_type = 'error';
			}
		}
	}
}

// Fetch summary by room type
$room_types = [];
try {
	$stmt = $pdo->query("SELECT room_type, COUNT(*) AS num_rooms, MIN(rate) AS min_rate, MAX(rate) AS max_rate FROM rooms GROUP BY room_type ORDER BY room_type ASC");
	$room_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	error_log('Failed to fetch room types: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Room Pricing - Inventory</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
	<?php include __DIR__ . '/includes/inventory-header.php'; ?>
	<?php include __DIR__ . '/includes/sidebar-inventory.php'; ?>

	<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300 overflow-y-auto max-h-screen">
		<div class="flex items-center justify-between mb-6">
			<h2 class="text-2xl font-semibold text-gray-800">
				<i class="fas fa-tags mr-2 text-green-600"></i>
				Room Pricing
			</h2>
		</div>

		<?php if ($message): ?>
			<div class="mb-4 p-4 rounded <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
				<?php echo htmlspecialchars($message); ?>
			</div>
		<?php endif; ?>

		<div class="bg-white rounded-lg shadow">
			<div class="p-4 border-b">
				<p class="text-sm text-gray-600">Update nightly rates by room type. Changes apply immediately to all rooms of that type.</p>
			</div>
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200">
					<thead class="bg-gray-50">
						<tr>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rooms</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Rates</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Set New Rate</th>
							<th class="px-4 py-3"></th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						<?php foreach ($room_types as $typeRow): ?>
							<?php
								$min = (float)$typeRow['min_rate'];
								$max = (float)$typeRow['max_rate'];
								$currentLabel = $min === $max
									? '₱' . number_format($min, 2)
									: '₱' . number_format($min, 2) . ' – ₱' . number_format($max, 2);
							?>
							<tr>
								<td class="px-4 py-3 font-medium text-gray-900 capitalize"><?php echo htmlspecialchars($typeRow['room_type']); ?></td>
								<td class="px-4 py-3 text-gray-700"><?php echo (int)$typeRow['num_rooms']; ?></td>
								<td class="px-4 py-3 text-gray-700"><?php echo $currentLabel; ?></td>
								<td class="px-4 py-3">
									<form method="post" class="flex items-center space-x-2">
										<input type="hidden" name="room_type" value="<?php echo htmlspecialchars($typeRow['room_type']); ?>">
										<div class="relative">
											<span class="absolute left-2 top-1.5 text-gray-500">₱</span>
											<input name="new_rate" value="<?php echo htmlspecialchars(number_format((float)$typeRow['max_rate'], 2, '.', '')); ?>" class="pl-5 border rounded px-2 py-1 w-28 focus:outline-none focus:ring-2 focus:ring-green-500" required>
										</div>
										<button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">Update</button>
									</form>
								</td>
								<td class="px-4 py-3 text-right"></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</main>
</body>
</html>


