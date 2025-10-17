<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

try {
  $input = json_decode(file_get_contents('php://input'), true);
  $scenario_id = (int)($input['scenario_id'] ?? 0);
  $answers = $input['answers'] ?? [];
  if (!$scenario_id) throw new Exception('Invalid scenario');
  $res = submitScenarioAttempt($scenario_id, $answers, $_SESSION['user_id']);
  echo json_encode($res);
} catch (Exception $e) {
  error_log('submit-scenario: '.$e->getMessage());
  echo json_encode(['success'=>false,'message'=>'Failed to submit']);
}
?>

