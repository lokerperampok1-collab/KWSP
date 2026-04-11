<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/wallet/_wallet.php";

require_login();

/**
 * WAJIB: samain dengan folder project kamu di htdocs
 */
$BASE = "";

$CUR = function_exists('currency_display') ? currency_display() : 'RM';
$CURCODE = function_exists('user_currency_code') ? user_currency_code() : 'MYR';

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) {
  header("Location: {$BASE}/login.php");
  exit;
}

// Ambil data user sesuai schema db kamu (users: id, full_name, email, password_hash, created_at, is_admin)
$stmt = $pdo->prepare("SELECT id, full_name, email, is_admin, created_at FROM users WHERE id=? LIMIT 1");
$stmt->execute([$userId]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
  session_unset();
  session_destroy();
  header("Location: {$BASE}/login.php?err=" . urlencode("Session invalid. Please login again."));
  exit;
}

$fullName  = (string)$u["full_name"];
$email     = (string)$u["email"];
$isAdmin   = (int)($u["is_admin"] ?? 0);
$createdAt = (string)$u["created_at"];

// simpan admin flag ke session biar kepake di halaman lain
$_SESSION["is_admin"] = $isAdmin;

// referral code sederhana: sebelum @
$refCode = strtolower(trim(strtok($email, "@")));
if ($refCode === "") $refCode = "u" . $userId;

// referral url ikut host aktif (localhost / domain)
$scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$host   = $_SERVER["HTTP_HOST"] ?? "localhost";
$refUrl = $scheme . "://" . $host . "{$BASE}/?reff=" . rawurlencode($refCode);
$CUR = function_exists('wallet_currency_key') ? wallet_currency_key() : 'RM';

ensure_wallet($pdo, $userId, $CUR);
$balanceRM = wallet_balance($pdo, $userId, $CUR);
// ---------- Stats untuk dashboard ----------
// Referral/Bonus masih belum ada schema -> sementara 0 (bisa disambung nanti)
$totalRef    = 0;
$totalBonus  = 0;

// Profit, Deposit, Withdraw, Invest (approved)
$sum = function(string $type, array $statuses = ['approved']) use ($pdo, $userId, $CUR): float {
  $in = implode(',', array_fill(0, count($statuses), '?'));
  $sql = "SELECT COALESCE(SUM(amount),0) FROM wallet_transactions WHERE user_id=? AND currency=? AND type=? AND status IN ($in)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array_merge([$userId, $CUR, $type], $statuses));
  return (float)$stmt->fetchColumn();
};

// Profit shown on dashboard: ONLY profit entries (deposit is NOT profit)
// Admin top-up that should appear as profit must be recorded as type='profit'.
$totalProfit   = $sum('profit', ['approved']);
$totalDeposit  = $sum('deposit', ['approved']);
$totalWithdraw = $sum('withdraw', ['approved']);
$totalInvest   = $sum('investment', ['approved']);

// Current invest (active)
require_once __DIR__ . '/config/schema.php';
ensure_investment_schema($pdo);
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM user_investments WHERE user_id=? AND status='active'");
$stmt->execute([$userId]);
$currentInvest = (float)$stmt->fetchColumn();

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Dashboard | Global Market Trade</title>
  <link rel="SHORTCUT ICON" href="<?= $BASE ?>/images/banner/favicon_632c647e32030662c66da4f1f5c0abbe.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- font-awesome (local) -->
  <link rel="stylesheet" href="<?= $BASE ?>/assets/vendor_components/font-awesome/css/font-awesome.css">

  <!-- SweetAlert (untuk confirm logout + notif copy) -->
  <script src="<?= $BASE ?>/user/js/sweetalert-dev.js"></script>
  <link rel="stylesheet" href="<?= $BASE ?>/user/css/sweetalert.css">

  <!-- UI baru (mobile first, dark-blue) -->
  <link rel="stylesheet" href="<?= $BASE ?>/user/css/gmtd_member_v2.css">

  <style>
    body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
  </style>

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
  </script>
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

    <section class="gmtd-card" aria-label="Balance and chart">
      <div class="gmtd-balance">
        <div class="gmtd-kicker">Profit Balance</div>
        <div class="gmtd-amount">
          <span id="balVal"><?= e((string)$balanceRM) ?></span>
          <small class="curSym"><?= e($CUR) ?></small>
        </div>
        <div class="gmtd-subrow">
          <span class="gmtd-pill"><i class="fa fa-line-chart"></i> Profit: <span class="curSym"><?= e($CUR) ?></span> <span id="profitVal"><?= number_format($totalProfit, 2, '.', '') ?></span></span>
          <span class="gmtd-pill"><i class="fa fa-calendar"></i> Joined: <?= e($createdAt) ?></span>
        </div>
      </div>

      <div class="gmtd-chart">
        <div class="gmtd-chart__inner" id="tvchart"></div>
      </div>
    </section>

    <section class="gmtd-grid" aria-label="Quick stats">
      <div class="gmtd-tile t-withdraw">
        <div class="ic"><i class="fa fa-arrow-up"></i></div>
        <div class="lbl">Total withdraw</div>
        <div class="val"><span class="curSym"><?= e($CUR) ?></span> <?= number_format($totalWithdraw, 2, '.', '') ?></div>
      </div>

      <div class="gmtd-tile t-deposit">
        <div class="ic"><i class="fa fa-arrow-down"></i></div>
        <div class="lbl">Total deposit</div>
        <div class="val"><span class="curSym"><?= e($CUR) ?></span> <?= number_format($totalDeposit, 2, '.', '') ?></div>
      </div>

      <div class="gmtd-tile t-invest">
        <div class="ic"><i class="fa fa-briefcase"></i></div>
        <div class="lbl">Total invest</div>
        <div class="val"><span class="curSym"><?= e($CUR) ?></span> <?= number_format($totalInvest, 2, '.', '') ?></div>
      </div>

      <div class="gmtd-tile t-current">
        <div class="ic"><i class="fa fa-bar-chart"></i></div>
        <div class="lbl">Current invest</div>
        <div class="val"><span class="curSym"><?= e($CUR) ?></span> <?= number_format($currentInvest, 2, '.', '') ?></div>
      </div>
    </section>

  </div>

  <nav class="gmtd-nav" aria-label="Bottom navigation">
    <div class="gmtd-nav__wrap">
      <a href="<?= $BASE ?>/wallet_deposit.php"><i class="fa fa-credit-card"></i><span>Deposit</span></a>
      <a href="<?= $BASE ?>/investment.php"><i class="fa fa-line-chart"></i><span>My Invest</span></a>
      <a class="active" href="<?= $BASE ?>/dashboard.php"><i class="fa fa-home"></i><span>Home</span></a>
      <a href="<?= $BASE ?>/wallet_withdraw.php"><i class="fa fa-arrow-circle-down"></i><span>Withdraw</span></a>
      <a href="<?= $BASE ?>/wallet_transfer.php"><i class="fa fa-exchange"></i><span>Transfer</span></a>
    </div>
  </nav>

  <script>
    // User menu toggle
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

// TradingView chart (dark) - random each load
(function(){
  var el = document.getElementById('tvchart');
  if(!el) return;

  var symbols = [
    "BINANCE:BTCUSDT","BINANCE:ETHUSDT","FX:EURUSD","FX:USDJPY",
    "TVC:GOLD","TVC:SILVER","TVC:USOIL","NASDAQ:AAPL","NASDAQ:MSFT"
  ];

  var sym = symbols[Math.floor(Math.random()*symbols.length)];

  var s = document.createElement('script');
  s.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
  s.async = true;
  s.innerHTML = JSON.stringify({
    autosize: true,
    symbol: sym,
    interval: "D",
    timezone: "Etc/UTC",
    theme: "dark",
    style: "1",
    locale: "en",
    enable_publishing: false,
    hide_top_toolbar: false,
    hide_legend: true,
    allow_symbol_change: true,
    calendar: false,
    support_host: "https://www.tradingview.com",
    backgroundColor: "rgba(0,0,0,0)",
    gridColor: "rgba(255,255,255,0.06)"
  });
  el.appendChild(s);
})();

// Live balance refresh (tetap pakai endpoint existing)
    async function refreshBalance(){
      try{
        const bal = await fetch("<?= $BASE ?>/api/balance.php", {credentials:"include"}).then(r=>r.json());
        if(!bal || !bal.ok) return;
        var val = Number(bal.balance ?? bal.balance_rm ?? 0).toFixed(2);
        var b = document.getElementById('balVal');
        if(b) b.textContent = val;
        // Update profit pill if API provides it
        var p = document.getElementById('profitVal');
        if(p && (bal.profit !== undefined && bal.profit !== null)) {
          p.textContent = Number(bal.profit ?? 0).toFixed(2);
        }
        document.querySelectorAll('.curSym').forEach(function(x){ x.textContent = (bal.currency ?? "RM"); });
      }catch(e){/* ignore */}
    }
    refreshBalance();
    setInterval(refreshBalance, 5000);
  </script>
</body>
</html>
