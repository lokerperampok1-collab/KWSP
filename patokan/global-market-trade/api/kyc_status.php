<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/schema.php";

require_login();
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors','0');

$userId = (int)($_SESSION["user_id"] ?? 0);
ensure_kyc_schema($pdo);

$stmt = $pdo->prepare("SELECT status, note, updated_at, created_at, id_front_path, id_back_path, selfie_path FROM kyc_requests WHERE user_id=? ORDER BY updated_at DESC, id DESC LIMIT 1");
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  echo json_encode(["ok"=>true, "status"=>"none"]);
  exit;
}

echo json_encode([
  "ok"=>true,
  "status" => (string)$row["status"],
  "note" => $row["note"],
  "updated_at" => $row["updated_at"],
  "created_at" => $row["created_at"],
]);
