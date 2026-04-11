<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../auth/admin_guard.php";
require_once __DIR__ . "/../../auth/_csrf.php";
require_once __DIR__ . "/../../config/schema.php";

header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors','0');

csrf_check();
ensure_kyc_schema($pdo);

$action = isset($_POST["action"]) ? (string)$_POST["action"] : "";
$kycId = isset($_POST["kyc_id"]) ? (int)$_POST["kyc_id"] : 0;
$note = isset($_POST["note"]) ? trim((string)$_POST["note"]) : null;

if (!in_array($action, ["approve","reject","set_pending"], true)) {
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>"Invalid action"]);
  exit;
}
if ($kycId <= 0) {
  http_response_code(422);
  echo json_encode(["ok"=>false, "error"=>"Missing kyc_id"]);
  exit;
}

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
    SELECT k.id, k.user_id, k.status, u.email
    FROM kyc_requests k
    JOIN users u ON u.id = k.user_id
    WHERE k.id = ?
    LIMIT 1
  ");
  $stmt->execute([$kycId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    throw new RuntimeException("KYC request not found");
  }

  $newStatus = "pending";
  if ($action === "approve") $newStatus = "approved";
  if ($action === "reject")  $newStatus = "rejected";
  if ($action === "set_pending") $newStatus = "pending";

  if ($newStatus === "rejected" && (!$note || $note === "")) {
    throw new RuntimeException("Reject requires a note/reason");
  }

  $stmt = $pdo->prepare("UPDATE kyc_requests SET status=?, note=? WHERE id=?");
  $stmt->execute([$newStatus, $note, $kycId]);

  // Best-effort: also sync users table flags if columns exist (for legacy gates)
  try {
    $pdo->prepare("UPDATE users u JOIN kyc_requests k ON k.user_id=u.id SET u.kyc_status=? WHERE k.id=?")->execute([$newStatus, $kycId]);
  } catch (Throwable $e) {}
  try {
    $v = ($newStatus === 'approved') ? 1 : 0;
    $pdo->prepare("UPDATE users u JOIN kyc_requests k ON k.user_id=u.id SET u.verified=? WHERE k.id=?")->execute([$v, $kycId]);
  } catch (Throwable $e) {}
  try {
    $v = ($newStatus === 'approved') ? 1 : 0;
    $pdo->prepare("UPDATE users u JOIN kyc_requests k ON k.user_id=u.id SET u.is_verified=? WHERE k.id=?")->execute([$v, $kycId]);
  } catch (Throwable $e) {}


  $pdo->commit();

  // notify user (best-effort)
  $userId = (int)$row["user_id"];
  try {
    if ($newStatus === "approved") {
      notify_user($pdo, $userId, "KYC approved", "Your verification has been approved.", "/kyc.php");
    } elseif ($newStatus === "rejected") {
      notify_user($pdo, $userId, "KYC rejected", "Your verification was rejected. Reason: " . (string)$note, "/kyc.php");
    } else {
      notify_user($pdo, $userId, "KYC status updated", "Your verification status is now: {$newStatus}.", "/kyc.php");
    }
  } catch (Throwable $e) {}

  echo json_encode(["ok"=>true, "status"=>$newStatus]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()]);
}
