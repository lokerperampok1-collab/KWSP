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

$stmt = $pdo->prepare("SELECT 
  COALESCE(SUM(CASE WHEN type='investment' AND status='approved' THEN amount END),0) AS total_invest,
  COALESCE(COUNT(CASE WHEN type='investment' AND status='approved' THEN 1 END),0) AS invest_count
FROM wallet_transactions WHERE user_id=?");
$stmt->execute([$userId]);
$st = $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total_invest"=>0,"invest_count"=>0];
$totalInvest = number_format((float)$st["total_invest"], 2);
$investCount = (int)$st["invest_count"];

$balance = function_exists('wallet_balance') ? wallet_balance($pdo, $userId, $CUR) : "0.00";

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>My Invest | Global Market Trade</title>
<link rel="SHORTCUT ICON" href="<?= $BASE ?>/images/banner/favicon_632c647e32030662c66da4f1f5c0abbe.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= $BASE ?>/assets/vendor_components/font-awesome/css/font-awesome.css">

<script src="<?= $BASE ?>/user/js/sweetalert-dev.js"></script>
<link rel="stylesheet" href="<?= $BASE ?>/user/css/sweetalert.css">

<link rel="stylesheet" href="<?= $BASE ?>/user/css/gmtd_member_v2.css">

  <script src="https://s3.tradingview.com/tv.js"></script>
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
      <h1>My Invest</h1>
      <p>Choose a plan, invest, and track your investment history.</p>
    </div>

    <div class="gmtd-stats">
      <div class="gmtd-stat">
        <div class="k">Available Balance</div>
        <div class="v"><?= e($CUR) ?> <?= e($balance) ?></div>
        <div class="s"><?= e($CURCODE) ?></div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Total Invested</div>
        <div class="v"><?= e($CUR) ?> <?= e($totalInvest) ?></div>
        <div class="s">Approved</div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Investments</div>
        <div class="v"><?= e((string)$investCount) ?></div>
        <div class="s">Approved count</div>
      </div>
    </div>

    <section class="gmtd-card" aria-label="Market chart">
      <div class="gmtd-card__inner" style="padding:0">
        <div style="padding:16px 16px 0">
          <div class="gmtd-card__title">Market Chart</div>
        </div>
        <div class="tradingview-widget-container">
          <div id="tv_invest_chart" style="height:360px; width:100%;"></div>
        </div>
      </div>
    </section>

    <div class="gmtd-divider"></div>

    <section class="gmtd-card" aria-label="Plans">
      <div class="gmtd-card__inner">
        <div class="gmtd-card__title">Available Plans</div>
        <div id="plansRow" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px"></div>
        <p id="plansEmpty" class="gmtd-help" style="display:none;margin-top:12px">No plans available.</p>
      </div>
    </section>

    <div class="gmtd-divider"></div>

    <section class="gmtd-card" aria-label="Investment history">
      <div class="gmtd-card__inner">
        <div class="gmtd-card__title">Investment History</div>
        <div class="gmtd-tablewrap">
          <table class="gmtd-table" id="investTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Duration</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="5" class="gmtd-td-muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </div>

  <nav class="gmtd-nav" aria-label="Bottom navigation">
    <div class="gmtd-nav__wrap">
      <a href="<?= $BASE ?>/wallet_deposit.php"><i class="fa fa-credit-card"></i><span>Deposit</span></a>
      <a class="active" href="<?= $BASE ?>/investment.php"><i class="fa fa-line-chart"></i><span>My Invest</span></a>
      <a href="<?= $BASE ?>/dashboard.php"><i class="fa fa-home"></i><span>Home</span></a>
      <a href="<?= $BASE ?>/wallet_withdraw.php"><i class="fa fa-arrow-circle-down"></i><span>Withdraw</span></a>
      <a href="<?= $BASE ?>/wallet_transfer.php"><i class="fa fa-exchange"></i><span>Transfer</span></a>
    </div>
  </nav>

<div class="gmtd-modal" id="investModal" aria-hidden="true">
  <div class="gmtd-modal__panel" role="dialog" aria-modal="true" aria-label="Invest modal">
    <div class="gmtd-modal__head">
      <b>Invest</b>
      <button class="gmtd-modal__close" type="button" onclick="closeInvestModal()"><i class="fa fa-times"></i></button>
    </div>
    <div class="gmtd-modal__body">
      <div class="gmtd-form">
        <input type="hidden" id="invPlanId" value="">
        <div>
          <label class="gmtd-label">Plan</label>
          <input class="gmtd-input" id="invPlanName" type="text" value="" readonly>
        </div>
        <div>
          <label class="gmtd-label">Amount (<?= e($CUR) ?>)</label>
          <input class="gmtd-input" id="invAmount" type="number" min="0" step="0.01" placeholder="e.g. 100">
        </div>
        <div class="gmtd-actions">
          <button class="gmtd-btn gmtd-btn--primary" type="button" onclick="submitInvest()"><i class="fa fa-check"></i> Confirm</button>
          <button class="gmtd-btn gmtd-btn--ghost" type="button" onclick="closeInvestModal()"><i class="fa fa-times"></i> Cancel</button>
        </div>
        <p class="gmtd-help">Your investment will appear in history after submission.</p>
      </div>
    </div>
  </div>
</div>

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
  var GMT_CSRF = <?= json_encode($csrf) ?>;

  function esc(s){ return String(s == null ? "" : s).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); }); }
  function badge(status){
    var s = String(status || '').toLowerCase();
    if(s === 'approved') return '<span class="gmtd-badge gmtd-badge--ok">Approved</span>';
    if(s === 'rejected') return '<span class="gmtd-badge gmtd-badge--bad">Rejected</span>';
    return '<span class="gmtd-badge gmtd-badge--pending">Pending</span>';
  }

  function openInvestModal(planId, planName){
    document.getElementById('invPlanId').value = planId || '';
    document.getElementById('invPlanName').value = planName || '';
    document.getElementById('invAmount').value = '';
    var m = document.getElementById('investModal');
    m.classList.add('is-open');
    m.setAttribute('aria-hidden','false');
  }
  function closeInvestModal(){
    var m = document.getElementById('investModal');
    m.classList.remove('is-open');
    m.setAttribute('aria-hidden','true');
  }
  window.closeInvestModal = closeInvestModal;

  function planCard(p){
    var name = esc(p.name || 'Plan');
    var min = esc(p.min_amount || '-');
    var max = esc(p.max_amount || '-');
    var dur = esc(p.duration_days ? (p.duration_days + ' days') : (p.duration || '-'));
    var roi = p.roi_percent ? esc(p.roi_percent + '%') : (p.profit ? esc(p.profit) : '');

    var html = '';
    html += '<div class="gmtd-tile" style="padding:16px">';
    html +=   '<div style="font-weight:900;letter-spacing:.10em;text-transform:uppercase;font-size:11px;color:var(--d-muted)">Plan</div>';
    html +=   '<div style="margin-top:6px;font-weight:900;font-size:16px">' + name + '</div>';
    html +=   '<div style="margin-top:10px;display:grid;grid-template-columns:1fr 1fr;gap:10px">';
    html +=     '<div class="gmtd-stat" style="padding:10px;border-radius:16px;box-shadow:none">';
    html +=       '<div class="k">Min</div><div class="v" style="font-size:14px"><?= e($CUR) ?> ' + min + '</div>';
    html +=     '</div>';
    html +=     '<div class="gmtd-stat" style="padding:10px;border-radius:16px;box-shadow:none">';
    html +=       '<div class="k">Max</div><div class="v" style="font-size:14px"><?= e($CUR) ?> ' + max + '</div>';
    html +=     '</div>';
    html +=   '</div>';
    html +=   '<div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap">';
    html +=     '<span class="gmtd-badge" title="Duration"><i class="fa fa-clock-o"></i> ' + dur + '</span>';
    if(roi){ html += '<span class="gmtd-badge" title="ROI"><i class="fa fa-line-chart"></i> ' + roi + '</span>'; }
    html +=   '</div>';
    html +=   '<div class="gmtd-actions" style="margin-top:12px">';
    html +=     '<button class="gmtd-btn gmtd-btn--primary" type="button" onclick="openInvestModal(\'' + esc(p.id) + '\', \'' + name.replace(/'/g, "\\'") + '\')">';
    html +=       '<i class="fa fa-plus"></i> Invest';
    html +=     '</button>';
    html +=   '</div>';
    html += '</div>';
    return html;
  }

  async function loadPlans(){
    var row = document.getElementById('plansRow');
    var empty = document.getElementById('plansEmpty');
    try{
      var res = await fetch('api/investment_plans.php', {credentials:'same-origin'});
      var j = await res.json();
      if(!res.ok || !j.ok) throw new Error(j.error || 'Failed to load plans');
      var plans = j.plans || [];
      if(plans.length === 0){
        row.innerHTML = '';
        empty.style.display = 'block';
        return;
      }
      empty.style.display = 'none';
      row.innerHTML = plans.map(planCard).join('');
    }catch(e){
      row.innerHTML = '<div class="gmtd-td-muted" style="font-weight:800;padding:10px 0">Failed to load plans.</div>';
    }
  }

  async function loadHistory(){
    var tbody = document.querySelector('#investTable tbody');
    try{
      var res = await fetch('api/investment_my.php', {credentials:'same-origin'});
      var j = await res.json();
      if(!res.ok || !j.ok) throw new Error(j.error || 'Failed');
      var rows = j.investments || [];
      if(rows.length === 0){
        tbody.innerHTML = '<tr><td colspan="5" class="gmtd-td-muted">No investment history yet.</td></tr>';
        return;
      }
      tbody.innerHTML = rows.map(function(r){
        var date = esc(r.created_at || '');
        var plan = esc(r.plan_name || '');
        var amount = '<?= e($CUR) ?> ' + esc(r.amount || '');
        var dur = esc(r.duration_days ? (r.duration_days + ' days') : (r.duration || '-'));
        var st = badge(r.status || '');
        return '<tr>' +
          '<td>' + date + '</td>' +
          '<td>' + plan + '</td>' +
          '<td>' + amount + '</td>' +
          '<td class="gmtd-td-muted">' + (dur ? dur : '-') + '</td>' +
          '<td>' + st + '</td>' +
        '</tr>';
      }).join('');
    }catch(e){
      tbody.innerHTML = '<tr><td colspan="5" class="gmtd-td-muted">Failed to load history.</td></tr>';
    }
  }

  async function submitInvest(){
    var planId = document.getElementById('invPlanId').value;
    var amount = (document.getElementById('invAmount').value || '').trim();

    if(!planId){
      if(typeof swal === "function") swal("Oops","Plan not selected","warning");
      else alert("Plan not selected");
      return;
    }
    if(!amount){
      if(typeof swal === "function") swal("Oops","Please enter amount","warning");
      else alert("Please enter amount");
      return;
    }

    try{
      var form = new FormData();
      form.append('csrf', GMT_CSRF);
      form.append('plan_id', planId);
      form.append('amount', amount);

      var res = await fetch('api/investment_buy.php', {method:'POST', body: form, credentials:'same-origin'});
      var ct = res.headers.get('content-type') || '';
      var j = ct.indexOf('application/json') !== -1 ? await res.json() : {ok:false, error: await res.text()};
      if(!res.ok || !j.ok) throw new Error(j.error || 'Failed to invest');

      closeInvestModal();
      if(typeof swal === "function") swal("Success","Investment submitted","success");
      else alert("Investment submitted");
      loadHistory();
    }catch(e){
      if(typeof swal === "function") swal("Error", String(e.message||e), "error");
      else alert(String(e.message||e));
    }
  }
  window.submitInvest = submitInvest;
  window.openInvestModal = openInvestModal;

  function initTV(){
    if (!window.TradingView) return;
    new TradingView.widget({
      autosize: true,
      symbol: "FX:USDJPY",
      interval: "60",
      timezone: "Etc/UTC",
      theme: "dark",
      style: "1",
      locale: "en",
      toolbar_bg: "rgba(8,20,40,0.0)",
      enable_publishing: false,
      hide_top_toolbar: false,
      save_image: false,
      container_id: "tv_invest_chart"
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    var plansRow = document.getElementById('plansRow');
    function gridCols(){
      if(window.innerWidth < 560) plansRow.style.gridTemplateColumns = '1fr';
      else if(window.innerWidth < 980) plansRow.style.gridTemplateColumns = '1fr 1fr';
      else plansRow.style.gridTemplateColumns = 'repeat(3, 1fr)';
    }
    window.addEventListener('resize', gridCols);
    gridCols();

    initTV();
    loadPlans();
    loadHistory();

    var modal = document.getElementById('investModal');
    modal.addEventListener('click', function(e){
      if(e.target === modal) closeInvestModal();
    });
  });
</script>

</body>
</html>
