<?php
declare(strict_types=1);
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | Global Market Trade</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="myasset/css/auth_v2.css">

  <style>
    body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
  </style>
</head>
<body>
  <main class="gmt-auth">
    <section class="gmt-auth__card" aria-label="Forgot password">

      <div class="gmt-auth__left">
        <a class="gmt-auth__brand" href="index.php" aria-label="Back to home">
          <img src="myasset/image/logo.png" alt="Global Market Trade">
          <div>
            <b>Global Market Trade</b>
            <span>Password assistance</span>
          </div>
        </a>

        <div class="gmt-auth__kicker"><span class="gmt-dot"></span><span style="color:var(--a-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;">Help</span></div>
        <h1 class="gmt-auth__title">Forgot your password?</h1>
        <p class="gmt-auth__sub">
          Demi keamanan akun, reset password saat ini ditangani oleh admin.
          Silakan hubungi Admin via Telegram untuk request perubahan password.
        </p>
      </div>

      <div class="gmt-auth__right">
        <div class="gmt-tabs" role="tablist" aria-label="Auth navigation">
          <a class="gmt-tab" href="login.php">Login</a>
          <a class="gmt-tab" href="signup.php">Register</a>
          <a class="gmt-tab active" href="forgotpass.php">Forgot</a>
        </div>

        <div class="gmt-alert" style="margin-top:0">
          <i class="fa fa-info-circle" style="margin-right:8px"></i>
          Klik tombol di bawah untuk menghubungi support.
        </div>

        <div style="display:grid;gap:12px;margin-top:14px">
          <a class="gmt-cta" href="https://t.me/GlobalMarketTradeSupport" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;">
            <i class="fa fa-telegram" style="margin-right:10px"></i> Contact Admin (Telegram)
          </a>
          <a class="gmt-tab" href="login.php" style="justify-content:center;height:46px;border-radius:14px;">
            <i class="fa fa-arrow-left" style="margin-right:10px"></i> Back to Login
          </a>
        </div>

        <p class="gmt-help">Butuh bantuan cepat? Pastikan sertakan email akun saat chat admin.</p>
      </div>

    </section>
  </main>
</body>
</html>
