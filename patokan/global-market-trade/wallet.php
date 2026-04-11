<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";

require_login();

$BASE = "";

$CUR = function_exists('currency_display') ? currency_display() : 'RM';
$CURCODE = function_exists('user_currency_code') ? user_currency_code() : 'MYR'; // sesuaikan jika folder project beda

$uid = (int)($_SESSION["user_id"] ?? 0);
if ($uid <= 0) {
  header("Location: {$BASE}/login.php");
  exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

  <title>Wallet | Global Market Trade</title>

  <!-- Bootstrap 4.0-->
  <link rel="stylesheet" href="assets/vendor_components/bootstrap/dist/css/bootstrap.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.css">

  <!-- Bootstrap-extend -->
  <link rel="stylesheet" href="user/css/bootstrap-extend.css">
  <!-- theme style -->
  <link rel="stylesheet" href="user/css/master_style.css">
    <link rel="stylesheet" href="user/css/custom_ui.css">
  <!-- skins -->
  <link rel="stylesheet" href="user/css/skins/_all-skins.css">

  <!-- SweetAlert (optional) -->
  <script src="user/js/sweetalert-dev.js"></script>
  <link rel="stylesheet" href="user/css/sweetalert.css">

  <style>
    .wallet-hero{margin-bottom:20px;}
    .table td,.table th{vertical-align:middle;}
    .badge-status{font-size:12px;}

    /* DataTables buttons (biar mirip screenshot) */
    .dt-buttons .dt-button{
      background:#2b79ff;
      color:#fff;
      border:0;
      border-radius:4px;
      padding:6px 14px;
      margin-right:6px;
    }
    .dt-buttons .dt-button:hover{ filter:brightness(0.9); }
    .dataTables_filter input{
      background:#fff;
      border-radius:4px;
      border:1px solid #666;
      padding:4px 8px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      padding:0.2em 0.8em;
      margin-left:4px;
      border-radius:4px;
    }
  </style>

  <!-- DataTables (JS ada di project; CSS kita style minimal via custom di atas) -->
  <script defer src="assets/vendor_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script defer src="assets/vendor_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <script defer src="assets/vendor_plugins/DataTables-1.10.15/extensions/Buttons/js/dataTables.buttons.min.js"></script>
  <script defer src="assets/vendor_plugins/DataTables-1.10.15/extensions/Buttons/js/buttons.html5.min.js"></script>
  <script defer src="assets/vendor_plugins/DataTables-1.10.15/extensions/Buttons/js/buttons.print.min.js"></script>
  <script defer src="assets/vendor_plugins/DataTables-1.10.15/ex-js/jszip.min.js"></script>
  <script defer src="assets/vendor_plugins/DataTables-1.10.15/ex-js/pdfmake.min.js"></script>
  <script defer src="assets/vendor_plugins/DataTables-1.10.15/ex-js/vfs_fonts.js"></script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php
    $activePage = basename($_SERVER['PHP_SELF'] ?? '');
    include __DIR__ . '/partials/member_header.php';
    include __DIR__ . '/partials/member_sidebar.php';
  ?>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header wallet-hero">
      <h1><?= e($CUR) ?> Balance</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="breadcrumb-item active"><?= e($CUR) ?> Balance</li>
      </ol>
    </section>

    <section class="content">

      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Balance [<?= e($CUR) ?> <span id="balance">0</span>]</h4>
          <p class="text-muted mb-0" id="balanceMsg" style="margin-top:6px;">Loading...</p>
        </div>

        <div class="box-body">
          <div class="table-responsive">
            <table id="walletTable" class="table table-bordered table-hover table-dark" style="width:100%">
              <thead>
                <tr>
                  <th>NO</th>
                  <th>DATE</th>
                  <th>TYPE</th>
                  <th>AMOUNT</th>
                  <th>NOTE</th>
                  <th>STATUS</th>
                </tr>
              </thead>
              <tbody id="txBody">
                <tr><td colspan="6" class="text-muted">Loading...</td></tr>
              </tbody>
            </table>
          </div>
          <div id="txMsg" class="text-muted"></div>
        </div>
      </div>

    </section>
  </div>

  <footer class="main-footer">
    Copyright &copy; <?php echo date("Y"); ?> Global Market Trade, All Rights Reserved.
  </footer>

  <div class="control-sidebar-bg"></div>
</div>

<!-- JS vendor -->
<script src="assets/vendor_components/jquery/dist/jquery.js"></script>
<script src="assets/vendor_components/popper/dist/popper.min.js"></script>
<script src="assets/vendor_components/bootstrap/dist/js/bootstrap.js"></script>
<script src="assets/vendor_components/jquery-slimscroll/jquery.slimscroll.js"></script>
<script src="assets/vendor_components/fastclick/lib/fastclick.js"></script>

<!-- DataTables + Buttons (untuk Copy/CSV/Excel/PDF/Print) -->
<script src="assets/vendor_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/vendor_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="assets/vendor_plugins/DataTables-1.10.15/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/vendor_plugins/DataTables-1.10.15/extensions/Buttons/js/buttons.html5.min.js"></script>
<script src="assets/vendor_plugins/DataTables-1.10.15/extensions/Buttons/js/buttons.print.min.js"></script>
<script src="assets/vendor_plugins/DataTables-1.10.15/ex-js/jszip.min.js"></script>
<script src="assets/vendor_plugins/DataTables-1.10.15/ex-js/pdfmake.min.js"></script>
<script src="assets/vendor_plugins/DataTables-1.10.15/ex-js/vfs_fonts.js"></script>

<!-- Template JS -->
<script src="user/js/template.js"></script>

<script>
function confirmLogout(){
  if (typeof swal === "function") {
    swal({
      title: "Logout?",
      text: "Are you sure want to logout?",
      type: "info",
      showCancelButton: true,
      confirmButtonColor: "#AEDEF4",
      confirmButtonText: "OK, Logout",
      cancelButtonText: "Cancel",
      closeOnConfirm: true
    }, function(isConfirm){
      if (isConfirm) window.location.href = "<?php echo $BASE; ?>/logout.php";
    });
  } else {
    if (confirm("Logout?")) window.location.href = "<?php echo $BASE; ?>/logout.php";
  }
}

function esc(s){ return String(s ?? "").replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

async function loadBalance(){
  try{
    const res = await fetch("<?php echo $BASE; ?>/api/balance.php", {credentials:"include"});
    const data = await res.json();
    if(!res.ok) throw new Error(data?.error || "Server error");
    document.getElementById("balance").textContent = data.balance_rm ?? 0;
    document.getElementById("topBalance").textContent = data.balance_rm ?? 0;
    document.getElementById("balanceMsg").textContent = "Wallet ready.";
  }catch(e){
    document.getElementById("balanceMsg").textContent = "Error: " + (e?.message || "Server error");
  }
}

let dtInited = false;

function initDT(){
  if(dtInited) return;
  if(!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
  dtInited = true;
  jQuery('#walletTable').DataTable({
    dom: 'Bfrtip',
    buttons: ['copy','csv','excel','pdf','print'],
    order: []
  });
}

async function loadTx(){
  const tbody = document.getElementById("txBody");
  const msg = document.getElementById("txMsg");
  try{
    const res = await fetch("<?php echo $BASE; ?>/api/wallet/transactions.php?limit=20", {credentials:"include"});
    const data = await res.json();
    if(!res.ok) throw new Error(data?.error || "Server error");

    const rows = data.transactions || [];
    if(rows.length === 0){
      tbody.innerHTML = `<tr><td colspan="6" class="text-muted">No data available in table</td></tr>`;
      msg.textContent = "";
      initDT();
      return;
    }

    tbody.innerHTML = rows.map(r => {
  const status = (r.status || "posted").toLowerCase();
  const badge =
    status === "pending"  ? "bg-warning" :
    (status === "rejected" || status === "void" || status === "failed") ? "bg-danger" :
    "bg-success";

  return `<tr>
    <td>${esc(r.id ?? "-")}</td>
    <td>${esc(r.created_at || "")}</td>
    <td><span class="badge bg-primary">${esc(r.type || "")}</span></td>
    <td><?= $CUR ?> ${esc(r.amount ?? 0)}</td>
    <td>${esc(r.note || "-")}</td>
    <td><span class="badge ${badge} badge-status">${esc(status)}</span></td>
  </tr>`;
}).join("");
    msg.textContent = "";
    // init setelah tbody terisi
    initDT();
  }catch(e){
    tbody.innerHTML = `<tr><td colspan="6" class="text-danger">Failed to load transactions.</td></tr>`;
    msg.textContent = (e?.message || "Server error");
    initDT();
  }
}

loadBalance();
loadTx();
setInterval(loadBalance, 15000);
</script>

</body>
</html>
