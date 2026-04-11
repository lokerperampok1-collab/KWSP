<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/schema.php';
require_once __DIR__ . '/../../auth/_csrf.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}

try {
  csrf_check();
  ensure_investment_schema($pdo);

  $id = (int)($_POST['id'] ?? 0);
  $name = trim((string)($_POST['name'] ?? ''));
  $desc = trim((string)($_POST['description'] ?? ''));
  $min = trim((string)($_POST['min_amount'] ?? '0'));
  $max = trim((string)($_POST['max_amount'] ?? ''));
  $roi = trim((string)($_POST['roi_daily_percent'] ?? '0'));
  $dur = (int)($_POST['duration_days'] ?? 0);
  $status = (int)($_POST['status'] ?? 1);
  $sort = (int)($_POST['sort_order'] ?? 0);

  if ($name === '' || $dur <= 0) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Invalid input']);
    exit;
  }
  if (!preg_match('/^\d+(\.\d{1,2})?$/', $min)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Invalid min']); exit; }
  if ($max !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', $max)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Invalid max']); exit; }
  if (!preg_match('/^\d+(\.\d{1,4})?$/', $roi)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Invalid roi']); exit; }

  $maxVal = ($max === '') ? null : $max;

  if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE investment_plans SET name=?, description=?, min_amount=?, max_amount=?, roi_daily_percent=?, duration_days=?, status=?, sort_order=? WHERE id=?");
    $stmt->execute([$name, $desc !== '' ? $desc : null, $min, $maxVal, $roi, $dur, $status ? 1 : 0, $sort, $id]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO investment_plans (name, description, min_amount, max_amount, roi_daily_percent, duration_days, status, sort_order) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$name, $desc !== '' ? $desc : null, $min, $maxVal, $roi, $dur, $status ? 1 : 0, $sort]);
    $id = (int)$pdo->lastInsertId();
  }

  echo json_encode(['ok'=>true,'id'=>$id]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
