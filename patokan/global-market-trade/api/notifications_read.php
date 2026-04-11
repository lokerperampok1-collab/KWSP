<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";
require_once __DIR__ . "/../auth/_csrf.php";

require_login();
csrf_check();
header("Content-Type: application/json; charset=UTF-8");

$userId = (int)($_SESSION["user_id"] ?? 0);
ensure_notifications_schema($pdo);

$id = (int)($_POST["id"] ?? 0);

if ($id > 0) {
  $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
  $stmt->execute([$id, $userId]);
} else {
  // mark all read
  $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
  $stmt->execute([$userId]);
}

echo json_encode(["ok"=>true]);
