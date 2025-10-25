<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if (!$id) throw new Exception('Invalid id');
  $item = getProblemDetails($id);
  if (!$item) throw new Exception('Not found');
  echo json_encode(['success'=>true,'item'=>$item]);
} catch (Exception $e) {
  error_log('get-problem-details: '.$e->getMessage());
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>

