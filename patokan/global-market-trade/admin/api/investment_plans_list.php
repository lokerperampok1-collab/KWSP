<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/schema.php';

require_admin();

try {
  ensure_investment_schema($pdo);
  $rows = $pdo->query("SELECT * FROM investment_plans ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['ok'=>true,'plans'=>$rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
