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
$ref = (string)($_GET['reff'] ?? ($_GET['ref'] ?? ''));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | Global Market Trade</title>

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
    <section class="gmt-auth__card" aria-label="Register">

      <div class="gmt-auth__left">
        <a class="gmt-auth__brand" href="index.php" aria-label="Back to home">
          <img src="myasset/image/logo.png" alt="Global Market Trade">
          <div>
            <b>Global Market Trade</b>
            <span>Create your member account</span>
          </div>
        </a>
        
        <div class="gmt-auth__kicker"><span class="gmt-dot"></span><span style="color:var(--a-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;">Register</span></div>
        <h1 class="gmt-auth__title">Open a investment account</h1>
        <p class="gmt-auth__sub">Create your profile to access the platform, manage funds, and view market insights. Built with security and compliance in mind.</p>

        <div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;">
          <a class="gmt-tab" href="index.php"><i class="fa fa-home" style="margin-right:8px"></i>Home</a>
          <a class="gmt-tab" href="login.php"><i class="fa fa-sign-in" style="margin-right:8px"></i>Login</a>
        </div>
      </div>

      <div class="gmt-auth__right">

        <?php if ($err !== ""): ?>
          <div class="gmt-alert err"><i class="fa fa-exclamation-triangle" style="margin-right:8px"></i><?php echo htmlspecialchars($err); ?></div>
        <?php elseif ($ok !== ""): ?>
          <div class="gmt-alert ok"><i class="fa fa-check" style="margin-right:8px"></i><?php echo htmlspecialchars($ok); ?></div>
        <?php endif; ?>

        <form class="gmt-form" action="auth/register_post.php" method="POST" autocomplete="off">
          <?php echo csrf_input(); ?>
          <input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref); ?>" />

          <div class="gmt-field">
            <label class="gmt-label" for="full_name">Full name</label>
            <input id="full_name" class="gmt-input" type="text" name="full_name" placeholder="Your name" required>
          </div>

          <div class="gmt-field">
            <label class="gmt-label" for="email">Email</label>
            <input id="email" class="gmt-input" type="email" name="email" placeholder="you@email.com" required>
          </div>

          <div class="gmt-field">
            <label class="gmt-label" for="password">Password</label>
            <div class="gmt-passwrap">
              <input id="password" class="gmt-input" type="password" name="password" placeholder="Min 8 characters" minlength="8" required>
              <button type="button" class="gmt-eye" id="togglePass" aria-label="Toggle password">
                <i class="fa fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="gmt-field">
            <label class="gmt-label" for="country_code">Country</label>
            <select id="country_code" name="country_code" class="gmt-input" required>
              <option value="" selected disabled>Select your country</option>
            </select>
          </div>

          <div class="gmt-field">
            <label class="gmt-label" for="currency_display">Currency</label>
            <input id="currency_display" class="gmt-input" type="text" placeholder="Currency" readonly>
            <div class="gmt-help" id="country_help" style="margin-top:6px"></div>
          </div>

          <div class="gmt-row" style="margin-top:10px">
            <label class="gmt-check">
              <input id="agree" type="checkbox" name="agree" value="on" required>
              I agree to Terms and Conditions
            </label>
          </div>

          <div style="margin-top:14px;">
            <button class="gmt-cta" type="submit">Create account</button>
          </div>

          <p class="gmt-help">Already have an account? <a href="login.php">Sign in</a></p>
        </form>
      </div>

    </section>
  </main>

  <script>
    (function(){
      var btn = document.getElementById('togglePass');
      var input = document.getElementById('password');
      if(btn && input){
        btn.addEventListener('click', function(){
          var isPwd = input.getAttribute('type') === 'password';
          input.setAttribute('type', isPwd ? 'text' : 'password');
          btn.innerHTML = isPwd ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
        });
      }
    })();

    (function(){
      const sel = document.getElementById('country_code');
      const cur = document.getElementById('currency_display');
      const help = document.getElementById('country_help');
      if(!sel || !cur) return;

      function setCurrency(row){
        if(!row){ cur.value=''; return; }
        const code = (row.currency_code || '').toUpperCase();
        let sym  = (row.currency_symbol || '').trim();

        const symMap = {
          SAR: 'ر.س', AED: 'د.إ', QAR: 'ر.ق', KWD: 'د.ك', BHD: 'د.ب', OMR: 'ر.ع.', JOD: 'د.ا', ILS: '₪', TRY: '₺', IRR: '﷼', IQD: 'د.ع', EGP: 'E£',
          USD: '$', CAD: 'C$', MXN: '$', BRL: 'R$', ARS: '$', CLP: '$', COP: '$', PEN: 'S/',
          EUR: '€', GBP: '£', CHF: 'CHF', SEK: 'kr', NOK: 'kr', DKK: 'kr', PLN: 'zł', CZK: 'Kč', HUF: 'Ft', RON: 'lei', BGN: 'лв', RUB: '₽', UAH: '₴',
          JPY: '¥', CNY: '¥', HKD: 'HK$', SGD: 'S$', IDR: 'Rp', MYR: 'RM', THB: '฿', VND: '₫', KRW: '₩', INR: '₹', PKR: '₨', BDT: '৳', LKR: 'Rs', NPR: 'Rs', PHP: '₱', TWD: 'NT$', AUD: 'A$', NZD: 'NZ$',
          ZAR: 'R', NGN: '₦', KES: 'KSh', GHS: 'GH₵', MAD: 'د.م.', TND: 'د.ت',
          BTC: '₿', ETH: 'Ξ'
        };

        if (!sym || sym.toUpperCase() === code) sym = symMap[code] || '';
        cur.value = sym ? `${sym} ${code}` : code;
      }

      fetch('api/countries.php', {cache:'no-store'})
        .then(r => r.json())
        .then(j => {
          if(!j || !j.ok || !Array.isArray(j.data)) throw new Error('bad');
          const rows = j.data;
          const frag = document.createDocumentFragment();
          rows.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.country_code;
            opt.textContent = row.country_name;
            opt.dataset.currency_code = row.currency_code || '';
            opt.dataset.currency_symbol = row.currency_symbol || '';
            opt.dataset.currency_name = row.currency_name || '';
            frag.appendChild(opt);
          });
          sel.appendChild(frag);
        })
        .catch(() => {
          if(help) help.textContent = 'Countries list not available. Please import countries SQL.';
        });

      sel.addEventListener('change', function(){
        const opt = sel.options[sel.selectedIndex];
        setCurrency({
          currency_code: opt.dataset.currency_code,
          currency_symbol: opt.dataset.currency_symbol,
          currency_name: opt.dataset.currency_name,
        });
      });
    })();
  </script>
</body>
</html>
