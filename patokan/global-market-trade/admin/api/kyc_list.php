<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../auth/admin_guard.php";
require_once __DIR__ . "/../../config/schema.php";

header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors','0');

ensure_kyc_schema($pdo);

$status = isset($_GET["status"]) ? (string)$_GET["status"] : "pending"; // pending|approved|rejected|all
$q = isset($_GET["q"]) ? trim((string)$_GET["q"]) : "";

$allowed = ["pending","approved","rejected","all"];
if (!in_array($status, $allowed, true)) $status = "pending";

$params = [];
$where = "1=1";

if ($status !== "all") {
  $where .= " AND k.status = ?";
  $params[] = $status;
}

if ($q !== "") {
  $where .= " AND (u.email LIKE ? OR u.full_name LIKE ?)";
  $params[] = "%{$q}%";
  $params[] = "%{$q}%";
}

$sql = "
  SELECT
    k.id,
    k.user_id,
    u.full_name,
    u.email,
    k.status,
    k.note,
    k.id_front_path,
    k.id_back_path,
    k.selfie_path,
    k.created_at,
    k.updated_at
  FROM kyc_requests k
  JOIN users u ON u.id = k.user_id
  WHERE {$where}
  ORDER BY
    CASE k.status
      WHEN 'pending' THEN 0
      WHEN 'rejected' THEN 1
      WHEN 'approved' THEN 2
      ELSE 3
    END,
    k.updated_at DESC
  LIMIT 500
";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(["ok"=>true, "items"=>$rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()]);
}
