<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/schema.php';

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
  exit;
}

try {
  ensure_investment_schema($pdo);

  $stmt = $pdo->query("
    SELECT id,name,description,min_amount,max_amount,roi_daily_percent,duration_days,status,sort_order
    FROM investment_plans
    WHERE status=1
    ORDER BY sort_order ASC, id ASC
  ");
  $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok'=>true,'plans'=>$plans]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
