<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../config/db.php";

require_admin();
header("Content-Type: application/json; charset=UTF-8");

// filter optional
$status = (string)($_GET["status"] ?? "all"); // pending/approved/rejected
$type   = (string)($_GET["type"] ?? "");         // deposit/withdraw
$limit  = (int)($_GET["limit"] ?? 100);
if ($limit <= 0 || $limit > 300) $limit = 100;

$allowedStatus = ["all","pending","approved","rejected","void"];
if (!in_array($status, $allowedStatus, true)) $status = "all";

$params = [":status" => $status];
$where  = "wt.status = :status";

if ($type !== "") {
  $allowedType = ["deposit","withdraw","transfer_in","transfer_out","adjust"];
  if (in_array($type, $allowedType, true)) {
    $where .= " AND wt.type = :type";
    $params[":type"] = $type;
  }
}

$sql = "
  SELECT
    wt.id, wt.user_id, u.full_name, u.email,
    wt.currency, wt.type, wt.status, wt.amount, wt.note,
    wt.created_at, wt.updated_at
  FROM wallet_transactions wt
  JOIN users u ON u.id = wt.user_id
  WHERE $where
  ORDER BY wt.id DESC
  LIMIT $limit
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode([
  "ok" => true,
  "items" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
