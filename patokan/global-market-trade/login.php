<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . "/auth/_csrf.php";

if (isset($_SESSION["user_id"])) {
  header("Location: dashboard.php");
  exit;
}

$err = (string)($_GET["err"] ?? "");
$ok  = (string)($_GET["ok"] ?? "");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Global Market Trade</title>

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
    <section class="gmt-auth__card" aria-label="Login">

      <div class="gmt-auth__left">
        <a class="gmt-auth__brand" href="index.php" aria-label="Back to home">
          <img src="myasset/image/logo.png" alt="Global Market Trade">
          <div>
            <b>Global Market Trade</b>
            <span>Secure member access</span>
          </div>
        </a>

        <div class="gmt-auth__kicker"><span class="gmt-dot"></span><span style="color:var(--a-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;">Account</span></div>
        <h1 class="gmt-auth__title">Welcome back.</h1>
        <p class="gmt-auth__sub">Log in to access and review your account overview, activity, and latest market updates securely.</p>

        <div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;">
          <a class="gmt-tab" href="index.php"><i class="fa fa-home" style="margin-right:8px"></i>Home</a>
          <a class="gmt-tab" href="signup.php"><i class="fa fa-user-plus" style="margin-right:8px"></i>Register</a>
        </div>
      </div>

      <div class="gmt-auth__right">

        <?php if ($err !== ""): ?>
          <div class="gmt-alert err"><i class="fa fa-exclamation-triangle" style="margin-right:8px"></i><?php echo htmlspecialchars($err); ?></div>
        <?php elseif ($ok !== ""): ?>
          <div class="gmt-alert ok"><i class="fa fa-check" style="margin-right:8px"></i><?php echo htmlspecialchars($ok); ?></div>
        <?php endif; ?>

        <form class="gmt-form" action="auth/login_post.php" method="POST" autocomplete="off">
          <?php echo csrf_input(); ?>

          <div class="gmt-field">
            <label class="gmt-label" for="email">Email</label>
            <input id="email" class="gmt-input" type="email" name="email" placeholder="you@email.com" required>
          </div>

          <div class="gmt-field">
            <label class="gmt-label" for="password">Password</label>
            <div class="gmt-passwrap">
              <input id="password" class="gmt-input" type="password" name="password" placeholder="••••••••" required>
              <button type="button" class="gmt-eye" id="togglePass" aria-label="Toggle password">
                <i class="fa fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="gmt-row">
            <label class="gmt-check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
            <a class="gmt-help" href="forgotpass.php">Forgot password?</a>
          </div>

          <div style="margin-top:14px;">
            <button class="gmt-cta" type="submit">Log in</button>
          </div>

          <p class="gmt-help">Don’t have an account? <a href="signup.php">Create one</a></p>
        </form>
      </div>

    </section>
  </main>

  <script>
    (function(){
      var btn = document.getElementById('togglePass');
      var input = document.getElementById('password');
      if(!btn || !input) return;
      btn.addEventListener('click', function(){
        var isPwd = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPwd ? 'text' : 'password');
        btn.innerHTML = isPwd ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
      });
    })();
  </script>
</body>
</html>
