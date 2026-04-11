<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/schema.php";
require_once __DIR__ . "/auth/_csrf.php";

require_login();
header("Content-Type: text/html; charset=UTF-8");

$BASE = ""; // sesuaikan jika folder project beda

$uid = (int)($_SESSION["user_id"] ?? 0);
if ($uid <= 0) {
  header("Location: {$BASE}/login.php");
  exit;
}

// Ensure profile columns (safe to call repeatedly)
try { ensure_user_profile_schema($pdo); } catch (Throwable $e) { /* ignore */ }
// Add avatar column if missing (best-effort)
try { $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL"); } catch (Throwable $e) { /* ignore */ }

$tab = strtolower((string)($_GET["tab"] ?? "profile"));
if (!in_array($tab, ["profile","photo","security"], true)) $tab = "profile";

$okMsg  = "";
$errMsg = "";

// Load user
$stmt = $pdo->prepare("
  SELECT id, full_name, email, phone, country_name, country_code,
         currency_code, currency_symbol,
         bank_name, bank_account, bank_locked_at,
         avatar_path, password_hash, is_admin, created_at
  FROM users
  WHERE id = ?
  LIMIT 1
");
$stmt->execute([$uid]);
$me = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$fullName = (string)($me["full_name"] ?? "Member");
$isAdmin  = (int)($me["is_admin"] ?? 0);

$bankLocked = !empty($me["bank_locked_at"]) || (!empty($me["bank_name"]) && !empty($me["bank_account"]));

if (!function_exists('e')) {
  function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

function save_avatar_upload(int $userId, string $field = "avatar"): ?string {
  if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) return null;
  $f = $_FILES[$field];
  if (($f["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;

  $max = 2 * 1024 * 1024; // 2MB
  if (($f["size"] ?? 0) > $max) throw new RuntimeException("Max file size is 2MB.");

  $tmp = (string)($f["tmp_name"] ?? "");
  if ($tmp === "" || !is_uploaded_file($tmp)) throw new RuntimeException("Invalid upload.");

  $ext = strtolower(pathinfo((string)($f["name"] ?? ""), PATHINFO_EXTENSION));
  $allow = ["jpg","jpeg","png","webp"];
  if (!in_array($ext, $allow, true)) throw new RuntimeException("Only JPG, PNG, or WEBP allowed.");

  $dir = __DIR__ . "/uploads/avatar";
  if (!is_dir($dir)) {
    if (!mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException("Cannot create upload dir.");
  }

  $fname = "u{$userId}_" . bin2hex(random_bytes(8)) . "." . ($ext === "jpeg" ? "jpg" : $ext);
  $destAbs = $dir . "/" . $fname;

  if (!move_uploaded_file($tmp, $destAbs)) throw new RuntimeException("Upload failed.");

  return "uploads/avatar/" . $fname;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
  csrf_check();

  $action = (string)($_POST["action"] ?? "");
  if ($action === "") $action = "profile"; // backward-compat

  try {
    if ($action === "profile") {
      $phone = trim((string)($_POST["phone"] ?? ""));
      $bank  = trim((string)($_POST["bank_name"] ?? ""));
      $acc   = trim((string)($_POST["bank_account"] ?? ""));

      if ($phone !== "" && strlen($phone) > 40) {
        throw new RuntimeException("Phone number too long.");
      }

      if (!$bankLocked) {
        if (($bank !== "" && $acc === "") || ($bank === "" && $acc !== "")) {
          throw new RuntimeException("Please fill both Bank and Account Number.");
        }
        if ($acc !== "" && !preg_match('/^[0-9A-Za-z\-\s\.]{4,64}$/', $acc)) {
          throw new RuntimeException("Invalid account number format.");
        }
        if ($bank !== "" && strlen($bank) > 80) {
          throw new RuntimeException("Bank name too long.");
        }
      }

      $pdo->prepare("UPDATE users SET phone=? WHERE id=?")->execute([$phone, $uid]);

      if (!$bankLocked) {
        if ($bank !== "" && $acc !== "") {
          $pdo->prepare("UPDATE users SET bank_name=?, bank_account=?, bank_locked_at=NOW() WHERE id=?")
              ->execute([$bank, $acc, $uid]);
          $bankLocked = true;
        }
      }

      $okMsg = "Profile updated.";
      $tab = "profile";
    }

    if ($action === "avatar") {
      $path = save_avatar_upload($uid, "avatar");
      if (!$path) throw new RuntimeException("Please choose an image file.");

      $old = (string)($me["avatar_path"] ?? "");
      if ($old && strpos($old, "uploads/avatar/") === 0) {
        $oldAbs = __DIR__ . "/" . $old;
        if (is_file($oldAbs)) { @unlink($oldAbs); }
      }

      $pdo->prepare("UPDATE users SET avatar_path=? WHERE id=?")->execute([$path, $uid]);
      $okMsg = "Profile picture updated.";
      $tab = "photo";
    }

    if ($action === "password") {
      $cur = (string)($_POST["current_password"] ?? "");
      $new = (string)($_POST["new_password"] ?? "");
      $con = (string)($_POST["confirm_password"] ?? "");

      if ($cur === "" || $new === "" || $con === "") {
        throw new RuntimeException("Please fill all password fields.");
      }
      if ($new !== $con) throw new RuntimeException("New password confirmation does not match.");
      if (strlen($new) < 6) throw new RuntimeException("New password must be at least 6 characters.");

      $hash = (string)($me["password_hash"] ?? "");
      if ($hash === "" || !password_verify($cur, $hash)) {
        throw new RuntimeException("Current password is incorrect.");
      }

      $newHash = password_hash($new, PASSWORD_DEFAULT);
      $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$newHash, $uid]);

      $okMsg = "Password updated.";
      $tab = "security";
    }

    // Reload user
    $stmt->execute([$uid]);
    $me = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  } catch (Throwable $e) {
    $errMsg = $e->getMessage();
  }
}

$avatarPath = (string)($me["avatar_path"] ?? "");
$avatarUrl  = ($avatarPath !== "") ? ($BASE . "/" . ltrim($avatarPath, "/")) : "";
$phoneVal   = (string)($me["phone"] ?? "");
$bankVal    = (string)($me["bank_name"] ?? "");
$accVal     = (string)($me["bank_account"] ?? "");
$country    = (string)($me["country_name"] ?? "");
$email      = (string)($me["email"] ?? "");
$joined     = (string)($me["created_at"] ?? "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Profile | Global Market Trade</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= $BASE ?>/assets/vendor_components/font-awesome/css/font-awesome.css">
  <script src="<?= $BASE ?>/user/js/sweetalert-dev.js"></script>
  <link rel="stylesheet" href="<?= $BASE ?>/user/css/sweetalert.css">

  <link rel="stylesheet" href="<?= $BASE ?>/user/css/gmtd_member_v2.css">
</head>
<body>
  <div class="gmtd-app">

    <header class="gmtd-top">
      <a class="gmtd-brand" href="<?= $BASE ?>/dashboard.php">
        <img src="<?= $BASE ?>/myasset/image/logo.png" alt="Global Market Trade">
        <div>
          <b>Global Market Trade</b>
          <span>Trust your investments</span>
        </div>
      </a>

      <div class="gmtd-user">
        <button class="gmtd-userbtn" id="userMenuBtn" type="button" aria-haspopup="true" aria-expanded="false">
          <span class="gmtd-username"><?= e($fullName) ?></span>
          <i class="fa fa-chevron-down"></i>
        </button>
        <div class="gmtd-menu" id="userMenu" role="menu" aria-label="User menu">
          <a href="<?= $BASE ?>/profile.php"><i class="fa fa-user"></i> Profile</a>
          <a href="<?= $BASE ?>/kyc.php"><i class="fa fa-id-card"></i> KYC</a>
          <?php if ($isAdmin === 1): ?>
            <a href="<?= $BASE ?>/admin.php"><i class="fa fa-lock"></i> Admin</a>
          <?php endif; ?>
          <button type="button" onclick="confirmLogout()"><i class="fa fa-sign-out"></i> Logout</button>
        </div>
      </div>
    </header>

    <div class="gmtd-pagehead">
      <h1>Profile</h1>
      <p>Update profile, profile picture, and security.</p>
    </div>

    <div class="gmtd-stats">
      <div class="gmtd-stat">
        <div class="k">Account</div>
        <div class="v"><?= e($fullName) ?></div>
        <div class="s"><?= e($email) ?></div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Country</div>
        <div class="v"><?= e($country ?: "-") ?></div>
        <div class="s">Joined <?= e($joined ?: "-") ?></div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Phone</div>
        <div class="v"><?= e($phoneVal ?: "-") ?></div>
        <div class="s">Editable</div>
      </div>
    </div>

    <?php if ($okMsg !== ""): ?>
      <div style="margin:10px 0 14px;">
        <span class="gmtd-badge gmtd-badge--ok"><i class="fa fa-check"></i> <?= e($okMsg) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($errMsg !== ""): ?>
      <div style="margin:10px 0 14px;">
        <span class="gmtd-badge gmtd-badge--bad"><i class="fa fa-warning"></i> <?= e($errMsg) ?></span>
      </div>
    <?php endif; ?>

    <div class="gmtd-tabs" role="tablist" aria-label="Profile tabs">
      <a class="gmtd-tab <?= $tab==='profile'?'active':'' ?>" href="<?= $BASE ?>/profile.php?tab=profile">Profile</a>
      <a class="gmtd-tab <?= $tab==='photo'?'active':'' ?>" href="<?= $BASE ?>/profile.php?tab=photo">Profile Picture</a>
      <a class="gmtd-tab <?= $tab==='security'?'active':'' ?>" href="<?= $BASE ?>/profile.php?tab=security">Security</a>
    </div>

    <!-- TAB: PROFILE -->
    <section class="gmtd-tabpane <?= $tab==='profile'?'active':'' ?>" id="tab-profile">
      <section class="gmtd-card" aria-label="Profile form">
        <div class="gmtd-card__inner">
          <div class="gmtd-kicker">Personal</div>
          <div class="gmtd-card__title">Profile Information</div>

          <form class="gmtd-form" method="post" action="<?= $BASE ?>/profile.php?tab=profile">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="profile">

            <div class="gmtd-row">
              <div>
                <label class="gmtd-label">Full Name</label>
                <input class="gmtd-input" type="text" value="<?= e((string)($me['full_name'] ?? '')) ?>" readonly>
              </div>
              <div>
                <label class="gmtd-label">Email</label>
                <input class="gmtd-input" type="text" value="<?= e($email) ?>" readonly>
              </div>
            </div>

            <div class="gmtd-row" style="margin-top:12px;">
              <div>
                <label class="gmtd-label">Phone</label>
                <input class="gmtd-input" name="phone" type="text" placeholder="your phone number" value="<?= e($phoneVal) ?>">
              </div>
              <div>
                <label class="gmtd-label">Country</label>
                <input class="gmtd-input" type="text" value="<?= e($country) ?>" readonly>
              </div>
            </div>

            <div class="gmtd-divider"></div>

            <div class="gmtd-kicker">Wallet</div>
            <div class="gmtd-card__title" style="margin-top:4px;">Bank Details</div>

            <div class="gmtd-row" style="margin-top:10px;">
              <div>
                <label class="gmtd-label">Bank</label>
                <input class="gmtd-input" name="bank_name" type="text" placeholder="your bank name" value="<?= e($bankVal) ?>" <?= $bankLocked ? 'readonly' : '' ?>>
                <?php if ($bankLocked): ?><div class="gmtd-help" style="margin-top:8px;"></div><?php endif; ?>
              </div>
              <div>
                <label class="gmtd-label">Account Number</label>
                <input class="gmtd-input" name="bank_account" type="text" placeholder="your account number/IBAN" value="<?= e($accVal) ?>" <?= $bankLocked ? 'readonly' : '' ?>>
              </div>
            </div>

            <div class="gmtd-actions" style="margin-top:14px;">
              <button class="gmtd-btn gmtd-btn--primary" type="submit"><i class="fa fa-save"></i> Save</button>
              <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/kyc.php"><i class="fa fa-id-card"></i> KYC</a>
            </div>

            <div class="gmtd-help">Please enter your bank details correctly.</div>
          </form>
        </div>
      </section>
    </section>

    <!-- TAB: PHOTO -->
    <section class="gmtd-tabpane <?= $tab==='photo'?'active':'' ?>" id="tab-photo">
      <section class="gmtd-card" aria-label="Profile picture">
        <div class="gmtd-card__inner">
          <div class="gmtd-kicker">Avatar</div>
          <div class="gmtd-card__title">Profile Picture</div>

          <form class="gmtd-form" method="post" action="<?= $BASE ?>/profile.php?tab=photo" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="avatar">

            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
              <div class="gmtd-avatar">
                <?php if ($avatarUrl !== ""): ?>
                  <img src="<?= e($avatarUrl) ?>" alt="Avatar">
                <?php else: ?>
                  <i class="fa fa-user"></i>
                <?php endif; ?>
              </div>

              <div style="flex:1;min-width:240px;">
                <label class="gmtd-label">Choose Image</label>
                <input class="gmtd-input" type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
                <div class="gmtd-help">Allowed: JPG/PNG/WEBP. Max 2MB.</div>
              </div>
            </div>

            <div class="gmtd-actions" style="margin-top:14px;">
              <button class="gmtd-btn gmtd-btn--primary" type="submit"><i class="fa fa-upload"></i> Upload</button>
            </div>
          </form>
        </div>
      </section>
    </section>

    <!-- TAB: SECURITY -->
    <section class="gmtd-tabpane <?= $tab==='security'?'active':'' ?>" id="tab-security">
      <section class="gmtd-card" aria-label="Security">
        <div class="gmtd-card__inner">
          <div class="gmtd-kicker">Password</div>
          <div class="gmtd-card__title">Change Password</div>

          <form class="gmtd-form" method="post" action="<?= $BASE ?>/profile.php?tab=security">
            <?= csrf_input(); ?>
            <input type="hidden" name="action" value="password">

            <div class="gmtd-row">
              <div>
                <label class="gmtd-label">Current Password</label>
                <input class="gmtd-input" type="password" name="current_password" autocomplete="current-password">
              </div>
              <div>
                <label class="gmtd-label">New Password</label>
                <input class="gmtd-input" type="password" name="new_password" autocomplete="new-password">
              </div>
            </div>

            <div class="gmtd-row" style="margin-top:12px;">
              <div>
                <label class="gmtd-label">Confirm New Password</label>
                <input class="gmtd-input" type="password" name="confirm_password" autocomplete="new-password">
              </div>
              <div>
                <label class="gmtd-label">Tips</label>
                <input class="gmtd-input" type="text" value="Use a strong password." readonly>
              </div>
            </div>

            <div class="gmtd-actions" style="margin-top:14px;">
              <button class="gmtd-btn gmtd-btn--primary" type="submit"><i class="fa fa-refresh"></i> Update Password</button>
            </div>

            <div class="gmtd-help">After changing password, you stay logged in on this device.</div>
          </form>
        </div>
      </section>
    </section>

  </div>

  <nav class="gmtd-nav" aria-label="Bottom navigation">
    <div class="gmtd-nav__wrap">
      <a href="<?= $BASE ?>/wallet_deposit.php"><i class="fa fa-credit-card"></i><span>Deposit</span></a>
      <a href="<?= $BASE ?>/investment.php"><i class="fa fa-line-chart"></i><span>My Invest</span></a>
      <a href="<?= $BASE ?>/dashboard.php"><i class="fa fa-home"></i><span>Home</span></a>
      <a href="<?= $BASE ?>/wallet_withdraw.php"><i class="fa fa-arrow-circle-down"></i><span>Withdraw</span></a>
      <a href="<?= $BASE ?>/wallet_transfer.php"><i class="fa fa-exchange"></i><span>Transfer</span></a>
    </div>
  </nav>

  <script>
    function confirmLogout(){
      if (!window.swal) { window.location.href = "<?= $BASE ?>/logout.php"; return; }
      swal({
        title: "Logout?",
        text: "You will be signed out.",
        type: "info",
        showCancelButton: true,
        confirmButtonColor: "#2ea7ff",
        confirmButtonText: "Yes, logout",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
      }, function(ok){
        if (ok) window.location.href = "<?= $BASE ?>/logout.php";
      });
    }

    (function(){
      var btn = document.getElementById('userMenuBtn');
      var menu = document.getElementById('userMenu');
      if(!btn || !menu) return;
      function close(){ menu.style.display = 'none'; btn.setAttribute('aria-expanded','false'); }
      btn.addEventListener('click', function(){
        var open = menu.style.display === 'block';
        menu.style.display = open ? 'none' : 'block';
        btn.setAttribute('aria-expanded', open ? 'false' : 'true');
      });
      document.addEventListener('click', function(e){
        if(!menu.contains(e.target) && !btn.contains(e.target)) close();
      });
    })();
  </script>
</body>
</html>
