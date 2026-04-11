<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/auth/_csrf.php";

require_login();

$BASE = "";

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
$isAdmin   = (int)($u["is_admin"] ?? 0);
$_SESSION["is_admin"] = $isAdmin;

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>KYC | Global Market Trade</title>
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
      <h1>KYC Verification</h1>
      <p>Upload your documents to unlock Deposit, Withdraw, and Transfer.</p>
    </div>

    <div class="gmtd-stats">
      <div class="gmtd-stat">
        <div class="k">Status</div>
        <div class="v" id="kycStatusLabel">Loading...</div>
        <div class="s" id="kycStatusMeta">Checking your verification</div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Requirement</div>
        <div class="v">ID + Selfie</div>
        <div class="s">Front ID required</div>
      </div>
      <div class="gmtd-stat">
        <div class="k">Max file size</div>
        <div class="v">5 MB</div>
        <div class="s">JPG/PNG/WEBP/PDF</div>
      </div>
    </div>

    <div class="gmtd-row">
      <!-- Status card -->
      <section class="gmtd-card" aria-label="KYC status">
        <div class="gmtd-card__inner">
          <div class="gmtd-card__title"><i class="fa fa-id-card"></i> Verification status</div>

          <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
            <div style="width:54px;height:54px;border-radius:18px;display:grid;place-items:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.10);">
              <i id="kycIcon" class="fa fa-id-card" style="font-size:22px;"></i>
            </div>
            <div>
              <div style="font-weight:1000;letter-spacing:.02em;font-size:18px;" id="kycCardTitle">UNVERIFIED</div>
              <div style="margin-top:6px;" id="kycCardBadgeWrap">
                <span class="gmtd-badge gmtd-badge--pending" id="kycBadge">Loading</span>
              </div>
            </div>
          </div>

          <div class="gmtd-divider"></div>
          <p class="gmtd-help" id="kycHelp" style="margin:0;">Checking your KYC status...</p>
          <p class="gmtd-help" id="kycNote" style="margin:10px 0 0; display:none;"></p>

          <div class="gmtd-actions" style="margin-top:12px;">
            <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/profile.php"><i class="fa fa-user"></i> Open Profile</a>
            <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/dashboard.php"><i class="fa fa-home"></i> Back Home</a>
          </div>
        </div>
      </section>

      <!-- Submit card -->
      <section class="gmtd-card" aria-label="Submit KYC documents">
        <div class="gmtd-card__inner">
          <div class="gmtd-card__title">Submit Documents</div>

          <form id="kycForm" class="gmtd-form" enctype="multipart/form-data">
            <?= csrf_input(); ?>

            <div>
              <label class="gmtd-label">Upload ID (Front) <span style="color:#ffcc66">*</span></label>
              <input class="gmtd-input" type="file" name="id_front" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
            </div>

            <div>
              <label class="gmtd-label">Upload ID (Back) <span style="opacity:.7">(optional)</span></label>
              <input class="gmtd-input" type="file" name="id_back" accept=".jpg,.jpeg,.png,.webp,.pdf">
            </div>

            <div>
              <label class="gmtd-label">Upload Selfie <span style="color:#ffcc66">*</span></label>
              <input class="gmtd-input" type="file" name="selfie" accept=".jpg,.jpeg,.png,.webp" required>
            </div>

            <div class="gmtd-actions">
              <button class="gmtd-btn gmtd-btn--primary" type="submit" id="kycSubmitBtn"><i class="fa fa-upload"></i> Submit KYC</button>
              <button class="gmtd-btn gmtd-btn--ghost" type="button" onclick="loadKycStatus()"><i class="fa fa-refresh"></i> Refresh</button>
            </div>

            <p class="gmtd-help">After submit, status will be <b>pending</b> until reviewed by admin.</p>
          </form>

        </div>
      </section>
    </div>

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

  function setBadge(status){
    var b = document.getElementById('kycBadge');
    if(!b) return;
    var s = String(status || '').toLowerCase();
    b.className = 'gmtd-badge ' + (s === 'approved' ? 'gmtd-badge--ok' : (s === 'rejected' ? 'gmtd-badge--bad' : 'gmtd-badge--pending'));
    b.textContent = (s === 'approved') ? 'Approved' : (s === 'rejected' ? 'Rejected' : (s === 'pending' ? 'Pending' : 'Not submitted'));
  }

  function setCard(status, note){
    var s = String(status || '').toLowerCase();
    var title = document.getElementById('kycCardTitle');
    var help  = document.getElementById('kycHelp');
    var noteEl= document.getElementById('kycNote');
    var lbl   = document.getElementById('kycStatusLabel');
    var meta  = document.getElementById('kycStatusMeta');

    setBadge(s);

    if(title){
      title.textContent = (s === 'approved') ? 'VERIFIED' : 'UNVERIFIED';
    }

    if(lbl){
      lbl.textContent = (s === 'approved') ? 'VERIFIED' : (s === 'pending' ? 'PENDING' : (s === 'rejected' ? 'REJECTED' : 'NOT SUBMITTED'));
    }

    if(meta){
      meta.textContent = (s === 'approved') ? 'All wallet features are unlocked.' :
                        (s === 'pending')  ? 'Your documents are under review.' :
                        (s === 'rejected') ? 'Please re-submit correct documents.' :
                                             'Submit your documents to unlock features.';
    }

    if(help){
      help.textContent = (s === 'approved') ? 'Your account is verified. You can use Deposit, Withdraw & Transfer.' :
                         (s === 'pending')  ? 'Your KYC is pending. Please wait for approval.' :
                         (s === 'rejected') ? 'Your KYC was rejected. Please upload clearer/valid documents.' :
                                              'You have not submitted KYC yet. Please upload ID Front and Selfie.';
    }

    if(noteEl){
      if(note){
        noteEl.style.display = 'block';
        noteEl.textContent = 'Note from admin: ' + note;
      } else {
        noteEl.style.display = 'none';
        noteEl.textContent = '';
      }
    }
  }

  async function loadKycStatus(){
    try{
      var r = await fetch('api/kyc_status.php', {credentials:'same-origin'});
      var j = await r.json();
      if(!j || !j.ok) return;
      setCard(j.status, j.note);
    }catch(e){
      setCard('none', null);
    }
  }
  window.loadKycStatus = loadKycStatus;

  (function(){
    var form = document.getElementById('kycForm');
    if(!form) return;

    form.addEventListener('submit', async function(ev){
      ev.preventDefault();
      var btn = document.getElementById('kycSubmitBtn');
      if(btn) btn.disabled = true;

      try{
        var fd = new FormData(form);
        var r = await fetch('api/kyc_submit.php', {method:'POST', body: fd, credentials:'same-origin'});
        var ct = r.headers.get('content-type') || '';
        var j;
        if(ct.indexOf('application/json') !== -1){
          j = await r.json();
        } else {
          var t = await r.text();
          try{ j = JSON.parse(t); }catch(e){ j = {ok:false, error:t}; }
        }

        if(!r.ok || !j.ok){
          if(typeof swal === 'function') swal('Error', (j && j.error) ? j.error : 'Failed to submit', 'error');
          else alert((j && j.error) ? j.error : 'Failed to submit');
          return;
        }

        if(typeof swal === 'function') swal('Success', j.message || 'KYC submitted (pending approval).', 'success');
        else alert(j.message || 'KYC submitted');

        form.reset();
        loadKycStatus();
      }catch(e){
        if(typeof swal === 'function') swal('Error', String(e.message || e), 'error');
        else alert(String(e.message || e));
      } finally {
        if(btn) btn.disabled = false;
      }
    });
  })();

  document.addEventListener('DOMContentLoaded', function(){
    loadKycStatus();
  });
</script>

</body>
</html>
