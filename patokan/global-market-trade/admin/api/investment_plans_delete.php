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
  if ($id <= 0) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Invalid id']); exit; }

  // Soft delete: set status=0 (safer if already used)
  $stmt = $pdo->prepare("UPDATE investment_plans SET status=0 WHERE id=?");
  $stmt->execute([$id]);

  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
