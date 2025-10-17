<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

try {
  $input = json_decode(file_get_contents('php://input'), true);
  $id = (int)($input['scenario_id'] ?? 0);
  $response = trim($input['response'] ?? '');
  if (!$id) throw new Exception('Invalid scenario');
  $res = submitCustomerServiceAttempt($id, $response, $_SESSION['user_id']);
  echo json_encode($res);
} catch (Exception $e) {
  error_log('submit-customer-service: '.$e->getMessage());
  echo json_encode(['success'=>false,'message'=>'Failed to submit']);
}
?>

