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

  <title>Virtual Balance | Global Market Trade</title>

  <!-- Bootstrap 4.0-->
  <link rel="stylesheet" href="assets/vendor_components/bootstrap/dist/css/bootstrap.css">
  <!-- Bootstrap-extend -->
  <link rel="stylesheet" href="user/css/bootstrap-extend.css">
  <!-- theme style -->
  <link rel="stylesheet" href="user/css/master_style.css">
    <link rel="stylesheet" href="user/css/custom_ui.css">
  <!-- skins -->
  <link rel="stylesheet" href="user/css/skins/_all-skins.css">

  <style>
    .wallet-hero{margin-bottom:20px;}
    .table td,.table th{vertical-align:middle;}

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
  </style>
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
      <h1>Wallet</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="breadcrumb-item active">Virtual Balance</li>
      </ol>
    </section>

    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Virtual Balance [<?= e($CUR) ?> 50]</h4>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table id="virtualTable" class="table table-bordered table-hover table-dark" style="width:100%">
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
              <tbody>
                <!-- contoh row (biar UI sama seperti screenshot) -->
                <tr>
                  <td>AF4FMB4607LT</td>
                  <td>2026-01-14<br><small class="text-muted">12:26:11</small></td>
                  <td><span class="badge" style="background:#8b5cf6;">Credit</span></td>
                  <td><?= $CUR ?> 50</td>
                  <td>Free Register Balance</td>
                  <td><span class="badge bg-success">Done</span></td>
                </tr>
              </tbody>
            </table>
          </div>
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

<!-- DataTables + Buttons -->
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
$(function(){
  if ($('#virtualTable').length && $.fn.DataTable) {
    $('#virtualTable').DataTable({
    dom: 'Bfrtip',
    buttons: ['copy','csv','excel','pdf','print'],
    order: []
  });
});
  }
});
</script>

</body>
</html>
