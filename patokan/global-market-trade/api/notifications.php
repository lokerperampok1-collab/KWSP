<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";

require_login();
header("Content-Type: application/json; charset=UTF-8");

$userId = (int)($_SESSION["user_id"] ?? 0);
ensure_notifications_schema($pdo);

$limit = (int)($_GET["limit"] ?? 10);
if ($limit <= 0 || $limit > 50) $limit = 10;

$stmt = $pdo->prepare("
  SELECT id, title, body, link, is_read, created_at
  FROM notifications
  WHERE user_id = ?
  ORDER BY id DESC
  LIMIT $limit
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$stmt2 = $pdo->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0");
$stmt2->execute([$userId]);
$unread = (int)(($stmt2->fetch(PDO::FETCH_ASSOC)["c"] ?? 0));

echo json_encode([
  "ok" => true,
  "unread" => $unread,
  "items" => $items
]);
