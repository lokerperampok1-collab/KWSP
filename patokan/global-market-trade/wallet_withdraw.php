<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/auth/_csrf.php";
require_once __DIR__ . "/wallet/_wallet.php";

require_login();

$BASE = "";

$CUR = function_exists('currency_display') ? currency_display() : 'RM';
$CURCODE = function_exists('user_currency_code') ? user_currency_code() : 'MYR';

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) {
  header("Location: {$BASE}/login.php");
  exit;
}

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
$_SESSION["is_admin"] = $isAdmin;


$csrf = csrf_token();

$balance = function_exists('wallet_balance') ? wallet_balance($pdo, $userId, $CUR) : "0.00";
$stmt = $pdo->prepare("SELECT 
  COALESCE(SUM(CASE WHEN type='withdraw' AND status='approved' THEN amount END),0) AS total_ok,
  COALESCE(SUM(CASE WHEN type='withdraw' AND status='pending' THEN amount END),0) AS total_pending
FROM wallet_transactions WHERE user_id=?");
$stmt->execute([$userId]);
$st = $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total_ok"=>0,"total_pending"=>0];
$totalOk = number_format((float)$st["total_ok"], 2);
$totalPending = number_format((float)$st["total_pending"], 2);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Withdraw | Global Market Trade</title>
<link rel="SHORTCUT ICON" href="<?= $BASE ?>/images/banner/favicon_632c647e32030662c66da4f1f5c0abbe.png">

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
      <h1>Withdraw</h1>
      <p>Request a withdrawal to your saved bank account (from Profile).</p>
    </div>

    <div class="gmtd-stats">
      <div class="gmtd-stat">
        <div class="k">Available Balance</div>
        <div class="v"><?= e($CUR) ?> <?= e($balance) ?></div>
        <div class="s"><?= e($CURCODE) ?></div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Total Withdraw</div>
        <div class="v"><?= e($CUR) ?> <?= e($totalOk) ?></div>
        <div class="s">Approved</div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Pending</div>
        <div class="v"><?= e($CUR) ?> <?= e($totalPending) ?></div>
        <div class="s">Processing</div>
      </div>
    </div>

    <section class="gmtd-card" id="withdrawUnverified" style="display:none" aria-label="KYC required">
      <div class="gmtd-card__inner">
        <div class="gmtd-card__title"><i class="fa fa-shield"></i> Verification required</div>
        <p class="gmtd-help">Please complete KYC to enable withdrawals.</p>
        <div class="gmtd-actions">
          <a class="gmtd-btn gmtd-btn--primary" href="<?= $BASE ?>/kyc.php"><i class="fa fa-id-card"></i> Go to KYC</a>
          <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/profile.php"><i class="fa fa-user"></i> Update Profile</a>
        </div>
      </div>
    </section>

    <section class="gmtd-card" id="withdrawVerified" style="display:none" aria-label="Withdraw form">
      <div class="gmtd-card__inner">
        <div class="gmtd-card__title">Withdrawal Request</div>

        <div class="gmtd-form">
          <div class="gmtd-row">
            <div>
              <label class="gmtd-label">Amount (<?= e($CUR) ?>)</label>
              <input id="withdrawAmount" class="gmtd-input" type="number" min="0" step="0.01" placeholder="e.g. 50">
            </div>
            <div>
              <label class="gmtd-label">Note</label>
              <input id="withdrawNote" class="gmtd-input" type="text" placeholder="Withdrawal">
            </div>
          </div>

          <div class="gmtd-row">
            <div>
              <label class="gmtd-label">Bank</label>
              <select id="withdrawBank" class="gmtd-select">
                <option value="">Loading...</option>
              </select>
              <p class="gmtd-help">Bank is taken from your Profile and cannot be edited here.</p>
            </div>
            <div>
              <label class="gmtd-label">Account Number</label>
              <select id="withdrawAcc" class="gmtd-select">
                <option value="">Loading...</option>
              </select>
            </div>
          </div>

          <div class="gmtd-actions">
            <button id="withdrawBtn" class="gmtd-btn gmtd-btn--primary" type="button">
              <i class="fa fa-arrow-circle-down"></i> Request Withdraw
            </button>
            <button class="gmtd-btn gmtd-btn--ghost" type="button" onclick="loadWithdrawHistory()">
              <i class="fa fa-refresh"></i> Refresh History
            </button>
          </div>
          <p class="gmtd-help">Withdrawal will reduce available balance immediately while pending (hold).</p>
        </div>
      </div>
    </section>

    <div class="gmtd-divider"></div>

    <section class="gmtd-card" aria-label="Withdraw history">
      <div class="gmtd-card__inner">
        <div class="gmtd-card__title">Withdrawal History</div>
        <div class="gmtd-tablewrap">
          <table class="gmtd-table" id="withdrawTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Fee</th>
                <th>Received</th>
                <th>Note</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="6" class="gmtd-td-muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </div>

  <nav class="gmtd-nav" aria-label="Bottom navigation">
    <div class="gmtd-nav__wrap">
      <a href="<?= $BASE ?>/wallet_deposit.php"><i class="fa fa-credit-card"></i><span>Deposit</span></a>
      <a href="<?= $BASE ?>/investment.php"><i class="fa fa-line-chart"></i><span>My Invest</span></a>
      <a href="<?= $BASE ?>/dashboard.php"><i class="fa fa-home"></i><span>Home</span></a>
      <a class="active" href="<?= $BASE ?>/wallet_withdraw.php"><i class="fa fa-arrow-circle-down"></i><span>Withdraw</span></a>
      <a href="<?= $BASE ?>/wallet_transfer.php"><i class="fa fa-exchange"></i><span>Transfer</span></a>
    </div>
  </nav>

<script>
  function confirmLogout(){
    if (typeof swal === "function") {
      swal({
        title: "Logout?",
        text: "Are you sure you want to logout?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, logout",
        cancelButtonText: "Cancel"
      }, function(ok){
        if (ok) window.location.href = "<?= $BASE ?>/logout.php";
      });
    } else {
      if (confirm("Logout?")) window.location.href = "<?= $BASE ?>/logout.php";
    }
  }

  (function(){
    var btn = document.getElementById('userMenuBtn');
    var menu = document.getElementById('userMenu');
    if(!btn || !menu) return;
    function close(){ menu.style.display='none'; btn.setAttribute('aria-expanded','false'); }
    btn.addEventListener('click', function(e){
      e.preventDefault();
      var open = (menu.style.display === 'block');
      menu.style.display = open ? 'none' : 'block';
      btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
    document.addEventListener('click', function(e){
      if(!menu.contains(e.target) && !btn.contains(e.target)) close();
    });
  })();
</script>


  <script>
  var csrf = <?= json_encode($csrf) ?>;

  function esc(s){ return String(s == null ? "" : s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); }); }
  function badge(status){
    var s = String(status || '').toLowerCase();
    if(s === 'approved') return '<span class="gmtd-badge gmtd-badge--ok">Approved</span>';
    if(s === 'rejected') return '<span class="gmtd-badge gmtd-badge--bad">Rejected</span>';
    return '<span class="gmtd-badge gmtd-badge--pending">Pending</span>';
  }

  var unv = document.getElementById('withdrawUnverified');
  var ver = document.getElementById('withdrawVerified');

  async function gate(){
    try{
      var r = await fetch('api/me.php', {credentials:'same-origin'});
      var j = await r.json();
      var ok = !!(j && (j.verified === true || (j.kyc_status && j.kyc_status === 'approved')));
      if(ok){
        ver.style.display='block'; unv.style.display='none';

        var bSel = document.getElementById('withdrawBank');
        var aSel = document.getElementById('withdrawAcc');

        var b = (j.bank_name && String(j.bank_name).trim()) ? String(j.bank_name).trim() : 'Please update bank in Profile';
        var acc = (j.bank_account && String(j.bank_account).trim()) ? String(j.bank_account).trim() : 'Please update account in Profile';

        bSel.innerHTML = '<option value="' + esc(b) + '">' + esc(b) + '</option>';
        aSel.innerHTML = '<option value="' + esc(acc) + '">' + esc(acc) + '</option>';
      } else {
        ver.style.display='none'; unv.style.display='block';
      }
    }catch(e){
      ver.style.display='none'; unv.style.display='block';
    }
  }

  var btn = document.getElementById('withdrawBtn');
  if(btn){
    btn.addEventListener('click', async function(){
      var amount = (document.getElementById('withdrawAmount').value || '').trim();
      var bankName = document.getElementById('withdrawBank').value || '';
      var bankAcc = document.getElementById('withdrawAcc').value || '';
      var note = document.getElementById('withdrawNote').value || 'Withdraw';

      if(!amount){
        if(typeof swal === "function") swal("Oops","Please enter amount","warning");
        else alert("Please enter amount");
        return;
      }
      if(!bankName || bankName.indexOf('Please update') !== -1){
        alert('Silakan update Bank di Profile dulu.');
        window.location.href = 'profile.php';
        return;
      }
      if(!bankAcc || bankAcc.indexOf('Please update') !== -1){
        alert('Silakan update Nomor Rekening di Profile dulu.');
        window.location.href = 'profile.php';
        return;
      }

      try{
        var fd = new URLSearchParams();
        fd.set('amount', amount);
        fd.set('bank_name', bankName);
        fd.set('bank_account', bankAcc);
        fd.set('pay', 'Bank: ' + bankName + ' | Acc: ' + bankAcc);
        fd.set('note', note);
        fd.set('csrf', csrf);

        var r = await fetch('api/wallet_withdraw_request.php', {
          method:'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: fd.toString(),
          credentials:'same-origin'
        });
        var ct = r.headers.get('content-type') || '';
        var j = ct.indexOf('application/json') !== -1 ? await r.json() : {ok:false, error: await r.text()};
        if(!r.ok || !j.ok) throw new Error(j.error || 'Withdraw failed');

        if(typeof swal === "function") swal("Success","Withdraw request submitted","success");
        else alert("Withdraw request submitted");

        document.getElementById('withdrawAmount').value='';
        document.getElementById('withdrawNote').value='';
        loadWithdrawHistory();
      }catch(e){
        if(typeof swal === "function") swal("Error", String(e.message||e), "error");
        else alert(String(e.message||e));
      }
    });
  }

  async function loadWithdrawHistory(){
    try{
      var res = await fetch("<?php echo $BASE; ?>/api/wallet/transactions.php?limit=120", {credentials:"include"});
      var data = await res.json();
      if(!res.ok) throw new Error((data && data.error) ? data.error : "Server error");

      var rows = (data.transactions || []).filter(function(r){
        return String((r.type||"")).toLowerCase() === "withdraw";
      });

      var tbody = document.querySelector("#withdrawTable tbody");
      if(!tbody) return;

      if(rows.length === 0){
        tbody.innerHTML = '<tr><td colspan="6" class="gmtd-td-muted">No withdrawal history yet.</td></tr>';
        return;
      }

      tbody.innerHTML = rows.map(function(r){
        var date = esc(r.created_at || "");
        var amount = esc(r.amount || "");
        var note = esc(r.note || "");
        var status = esc(r.status || "");
        return '<tr>' +
          '<td>' + date + '</td>' +
          '<td>' + amount + '</td>' +
          '<td class="gmtd-td-muted">-</td>' +
          '<td class="gmtd-td-muted">-</td>' +
          '<td class="gmtd-td-muted">' + (note ? note : '-') + '</td>' +
          '<td>' + badge(status) + '</td>' +
        '</tr>';
      }).join("");
    }catch(err){
      var tbody2 = document.querySelector("#withdrawTable tbody");
      if(tbody2) tbody2.innerHTML = '<tr><td colspan="6" class="gmtd-td-muted">Failed to load history.</td></tr>';
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    gate();
    loadWithdrawHistory();
  });
  </script>
</body>
</html>
