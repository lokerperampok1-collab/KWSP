<?php
// Member Header (shared)
$BASE = $BASE ?? '';
$CUR = function_exists('currency_display') ? currency_display() : 'RM';
?>
<header class="main-header">
  <a href="<?= $BASE ?>/dashboard.php" class="logo">
    <span class="logo-mini"><b>GM</b>T</span>
    <span class="logo-lg"><b>Global</b>Market</span>
  </a>

  <nav class="navbar navbar-static-top" role="navigation">
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>

    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">

        <!-- Balance -->
        <li class="dropdown balance-pill">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <span class="badge badge-balance"><span id="curSymTop"><?= e($CUR) ?></span> <span id="topBalance">0.00</span></span>
            <span class="badge badge-live">Real</span>
          </a>
        </li>

        <!-- Notifications -->
        <li class="dropdown notifications-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-bell-o"></i>
            <span id="gmtNotifDot" class="label label-danger" style="display:none;">!</span>
          </a>
          <ul class="dropdown-menu">
            <li class="header">Notifications</li>
            <li>
              <ul class="menu" id="gmtNotifList">
                <li><a href="#"><i class="fa fa-bell text-aqua"></i> No new notifications</a></li>
              </ul>
            </li>
            <li class="footer"><a href="">View all</a></li>
          </ul>
        </li>

        <!-- User -->
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img id="topUserAvatar" src="<?= $BASE ?>/images/no_image.png" class="user-image" alt="User Image">
            <span class="hidden-xs" id="topUserName">User</span>
          </a>
          <ul class="dropdown-menu">
            <li class="user-header">
              <img id="gmtUserAvatar" src="<?= $BASE ?>/images/no_image.png" class="img-circle" alt="User Image">
              <p>
                <span id="gmtUserName">User</span>
                <small>@<span id="topUserUsername">user</span></small>
              </p>
            </li>
            <li class="user-footer gmt-user-footer">
  <a class="gmt-user-item" href="<?= $BASE ?>/profile.php">
    <i class="fa fa-user"></i>
    <span>My Profile</span>
  </a>

  <a class="gmt-user-item danger" href="<?= $BASE ?>/logout.php" onclick="confirmLogout();return false;">
    <i class="fa fa-sign-out"></i>
    <span>Logout</span>
  </a>
</li>

          </ul>
        </li>

      </ul>
    </div>
  </nav>
</header>

<script>
  window.GMT_BASE = "<?= $BASE ?>";
</script>
<script src="<?= $BASE ?>/user/js/gmt_ui.js"></script>
