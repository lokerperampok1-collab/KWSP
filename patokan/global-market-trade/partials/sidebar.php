<?php
/** Shared sidebar */
$BASE = $BASE ?? "";
$page = $page ?? "";
$userId = (int)($_SESSION["user_id"] ?? 0);
$fullName = (string)($_SESSION["full_name"] ?? $_SESSION["name"] ?? "User");
$refCode = (string)($_SESSION["ref_code"] ?? ($_SESSION["ref"] ?? ($userId ? ("u".$userId) : "u0")));
?>
<aside class="main-sidebar">
  <!-- sidebar -->
  <section class="sidebar">

    <!-- Sidebar user panel (optional, kamu boleh keep yang sudah ada) -->
    <div class="user-panel">
      <div class="ulogo">
        <a href="<?= $BASE ?>/dashboard.php" style="color:#2ea7ff;">
          <span><b>Welcome </b><?= e($fullName) ?></span>
        </a>
      </div>
      <div class="image">
        <img src="images/no_image.png" style="width:69px; height:69px;" class="rounded-circle" alt="user"/>
      </div>
      <div class="info">
        <p style="line-height:150%; color:#999;">
          <?= e($fullName) ?><br>(<?= e($refCode ?? ("u".$userId)) ?>)
        </p>
      </div>
    </div>

    <!-- sidebar menu -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="nav-devider"></li>

      <!-- Dashboard -->
      <li class="<?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/dashboard.php">
          <i class="icon-home"></i> <span>Dashboard</span>
        </a>
      </li>

      <!-- Setting -->
      <li class="treeview <?= in_array(($page ?? ''), ['profile','ftprofile'], true) ? 'active' : '' ?>">
        <a href="#">
          <i class="fa fa-cog"></i>
          <span>Setting</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-right pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?= $BASE ?>/profile.php">Profil</a></li>
          <li><a href="<?= $BASE ?>/profile_image.php">Profile Image</a></li>
        </ul>
      </li>

      <!-- Security -->
      <li class="<?= ($page ?? '') === 'secure' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/security.php">
          <i class="fa fa-shield"></i> <span>Security</span>
        </a>
      </li>

      <!-- KYC -->
      <li class="<?= ($page ?? '') === 'kyc' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/kyc.php">
          <i class="fa fa-id-card"></i> <span>KYC</span>
        </a>
      </li>

      <!-- Investment -->
      <li class="<?= ($page ?? '') === 'investment' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/investment.php">
          <i class="mdi mdi-checkbox-marked-circle-outline"></i> <span>Investment</span>
        </a>
      </li>

      <!-- Trade -->
      <li class="treeview <?= in_array(($page ?? ''), ['trade','historytrade','profitrade'], true) ? 'active' : '' ?>">
        <a href="#">
          <i class="mdi mdi-chart-areaspline"></i>
          <span>Trade</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-right pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?= $BASE ?>/trade.php">Trade</a></li>
          <li><a href="<?= $BASE ?>/trade_history.php">History</a></li>
          <li><a href="<?= $BASE ?>/trade_profits.php">Profits</a></li>
        </ul>
      </li>

      <!-- Funds -->
      <li class="treeview <?= in_array(($page ?? ''), ['bonus','profits'], true) ? 'active' : '' ?>">
        <a href="#">
          <i class="mdi mdi-cash-100"></i>
          <span>Funds</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-right pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?= $BASE ?>/bonus.php">Bonus</a></li>
          <li><a href="<?= $BASE ?>/profits.php">Profits</a></li>
        </ul>
      </li>

      <!-- Wallet (kita fokus ini dulu nanti) -->
      <li class="treeview <?= in_array(($page ?? ''), ['walletfree','walletcash','wallet_deposit','wallet_transfer','wallet_withdraw'], true) ? 'active' : '' ?>">
        <a href="#">
          <i class="mdi mdi-wallet" title="USD"></i>
          <span>Wallet</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-right pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?= $BASE ?>/wallet_virtual.php">Virtual Balance</a></li>
          <li><a href="<?= $BASE ?>/wallet.php">Balance</a></li>

          <li><a href="<?= $BASE ?>/wallet_deposit.php">Deposit</a></li>
          <li><a href="<?= $BASE ?>/wallet_transfer.php">Transfer</a></li>
          <li><a href="<?= $BASE ?>/wallet_withdraw.php">Withdrawal</a></li>
        </ul>
      </li>

      <!-- Network (skip dulu, tapi struktur dulu) -->
      <li class="treeview <?= in_array(($page ?? ''), ['referrals','generation'], true) ? 'active' : '' ?>">
        <a href="#">
          <i class="fa fa-sitemap"></i>
          <span>Network</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-right pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?= $BASE ?>/referrals.php">Refferals</a></li>
          <li><a href="<?= $BASE ?>/generation.php">Generation</a></li>
        </ul>
      </li>

      <!-- Register -->
      <li class="<?= ($page ?? '') === 'register' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/register_user.php">
          <i class="fa fa-pencil-square-o"></i> <span>Register</span>
        </a>
      </li>

      <!-- FAQ -->
      <li class="<?= ($page ?? '') === 'faq' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/faq.php">
          <i class="mdi mdi-help-circle"></i> <span>FAQ</span>
        </a>
      </li>

      <!-- Latest News -->
      <li class="<?= ($page ?? '') === 'news' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/news.php">
          <i class="fa fa-newspaper-o"></i> <span>Latest News</span>
        </a>
      </li>

      <!-- Testimonial -->
      <li class="treeview <?= in_array(($page ?? ''), ['testimoni','testimonial_add'], true) ? 'active' : '' ?>">
        <a href="#">
          <i class="fa fa-comments"></i>
          <span>Testimonial</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-right pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="<?= $BASE ?>/testimoni.php">Testimonial</a></li>
          <li><a href="<?= $BASE ?>/testimonial_add.php">Add Testimonial</a></li>
        </ul>
      </li>

      <!-- Download -->
      <li class="<?= ($page ?? '') === 'download' ? 'active' : '' ?>">
        <a href="<?= $BASE ?>/download.php">
          <i class="fa fa-download"></i> <span>Download</span>
        </a>
      </li>

      <!-- Log Out -->
      <li>
        <a href="#" onclick="confirmLogout();return false;">
          <i class="fa fa-power-off"></i> <span>Log Out</span>
        </a>
      </li>

    </ul>
  </section>
