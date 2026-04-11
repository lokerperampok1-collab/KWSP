<?php
$BASE = $BASE ?? '';
$activePage = $activePage ?? basename($_SERVER['PHP_SELF'] ?? '');
function active($file, $activePage) { return $file === $activePage ? 'active' : ''; }
?>
<aside class="main-sidebar">
  <section class="sidebar">

    <div class="user-panel" style="margin-top:6px;">
      <div class="pull-left image">
        <img id="gmtSidebarAvatar" src="<?= $BASE ?>/images/no_image.png" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p id="gmtSidebarName">Welcome User</p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MAIN MENU</li>

      <li class="<?= active('dashboard.php', $activePage) ?>">
        <a href="<?= $BASE ?>/dashboard.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
      </li>

      <li class="treeview <?= in_array($activePage, ['trade.php','trade_history.php','trade_profits.php'], true) ? 'active' : '' ?>">
        <a href="#"><i class="fa fa-line-chart"></i> <span>Trade</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li class="<?= active('trade.php', $activePage) ?>"><a href="<?= $BASE ?>/trade.php"><i class="fa fa-circle-o"></i> New Trade</a></li>
          <li class="<?= active('trade_history.php', $activePage) ?>"><a href="<?= $BASE ?>/trade_history.php"><i class="fa fa-circle-o"></i> Trade History</a></li>
          <li class="<?= active('trade_profits.php', $activePage) ?>"><a href="<?= $BASE ?>/trade_profits.php"><i class="fa fa-circle-o"></i> Trade Profits</a></li>
        </ul>
      </li>

      <li class="treeview <?= in_array($activePage, ['wallet.php','wallet_deposit.php','wallet_withdraw.php','wallet_transfer.php','wallet_virtual.php'], true) ? 'active' : '' ?>">
        <a href="#"><i class="fa fa-wallet"></i> <span>Wallet</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li class="<?= active('wallet.php', $activePage) ?>"><a href="<?= $BASE ?>/wallet.php"><i class="fa fa-circle-o"></i> Overview</a></li>
          <li class="<?= active('wallet_deposit.php', $activePage) ?>"><a href="<?= $BASE ?>/wallet_deposit.php"><i class="fa fa-circle-o"></i> Deposit</a></li>
          <li class="<?= active('wallet_withdraw.php', $activePage) ?>"><a href="<?= $BASE ?>/wallet_withdraw.php"><i class="fa fa-circle-o"></i> Withdraw</a></li>
          <li class="<?= active('wallet_transfer.php', $activePage) ?>"><a href="<?= $BASE ?>/wallet_transfer.php"><i class="fa fa-circle-o"></i> Transfer</a></li>
          <li class="<?= active('wallet_virtual.php', $activePage) ?>"><a href="<?= $BASE ?>/wallet_virtual.php"><i class="fa fa-circle-o"></i> Virtual Wallet</a></li>
        </ul>
      </li>

      <li class="<?= active('investment.php', $activePage) ?>">
        <a href="<?= $BASE ?>/investment.php"><i class="fa fa-briefcase"></i> <span>Investment</span></a>
      </li>

      <li class="<?= active('profits.php', $activePage) ?>">
        <a href="<?= $BASE ?>/profits.php"><i class="fa fa-money"></i> <span>Profits</span></a>
      </li>

      <li class="<?= active('referrals.php', $activePage) ?>">
        <a href="<?= $BASE ?>/referrals.php"><i class="fa fa-users"></i> <span>Referrals</span></a>
      </li>

      <li class="<?= active('kyc.php', $activePage) ?>">
        <a href="<?= $BASE ?>/kyc.php"><i class="fa fa-id-card"></i> <span>KYC</span></a>
      </li>

      <li class="treeview <?= in_array($activePage, ['profile.php','profile_image.php','security.php'], true) ? 'active' : '' ?>">
        <a href="#"><i class="fa fa-user"></i> <span>Account</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li class="<?= active('profile.php', $activePage) ?>"><a href="<?= $BASE ?>/profile.php"><i class="fa fa-circle-o"></i> Profile</a></li>
          <li class="<?= active('profile_image.php', $activePage) ?>"><a href="<?= $BASE ?>/profile_image.php"><i class="fa fa-circle-o"></i> Profile Image</a></li>
          <li class="<?= active('security.php', $activePage) ?>"><a href="<?= $BASE ?>/security.php"><i class="fa fa-circle-o"></i> Security</a></li>
        </ul>
      </li>

      <li class="<?= active('news.php', $activePage) ?>">
        <a href="<?= $BASE ?>/news.php"><i class="fa fa-newspaper-o"></i> <span>News</span></a>
      </li>

      <li class="<?= active('faq.php', $activePage) ?>">
        <a href="<?= $BASE ?>/faq.php"><i class="fa fa-question-circle"></i> <span>FAQ</span></a>
      </li>

      <li class="<?= active('testimoni.php', $activePage) ?>">
        <a href="<?= $BASE ?>/testimoni.php"><i class="fa fa-star"></i> <span>Testimonials</span></a>
      </li>

      <li id="gmtAdminLink" style="display:none;" class="<?= active('admin.php', $activePage) ?>">
        <a href="<?= $BASE ?>/admin.php"><i class="fa fa-lock"></i> <span>Admin</span></a>
      </li>

      <li>
        <a href="<?= $BASE ?>/auth/logout.php" onclick="confirmLogout();return false;"><i class="fa fa-sign-out"></i> <span>Logout</span></a>
      </li>
    </ul>
  </section>
</aside>
