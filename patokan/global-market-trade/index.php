<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en" oncontextmenu="return false">
<head>
  <meta charset="utf-8">
  <meta name="author" content="your-site.com"/>
  <meta name="description" content="Landing Page"/>
  <meta name="keywords" content="Trading, Crypto, Landing Page"/>
  <title>Global Market Trade</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700|Open+Sans:400,700" rel="stylesheet">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="myasset/css/style.css">

</head>
<body>
  <!-- Fake earning notification -->
  <div id="notification-1" class="notification">
    <div class="notification-block">
      <div class="notification-img"><i class="fa fa-btc" aria-hidden="true"></i></div>
      <div class="notification-text-block">
        <div class="notification-title">Earning</div>
        <div class="notification-text" id="notif-text">Someone just earned $250</div>
      </div>
    </div>
  </div>
  <!-- Top bar -->
  <div class="topbar">
    <div class="_in_content_">
      <a href="index.php" class="brand"><img class="logo" src="myasset/image/logo.png" alt="Logo"></a>
      <div class="top-actions">
        <?php if(!empty($_SESSION["user_id"])): ?>
          <a class="__tL __tL_alt" href="dashboard.php">DASHBOARD</a>
          <a class="__tL" href="logout.php">LOGOUT</a>
        <?php else: ?>
          <a class="__tL __tL_alt" href="login.php">LOGIN</a>
          <a class="__tL" href="signup.php">SIGN UP</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <!-- TradingView ticker tape -->
  <div class="tv-strip">
    <div class="tradingview-widget-container _in_content_">
      <div class="tradingview-widget-container__widget"></div>
      <script src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async>
{
  "symbols": [
    {"title":"S&P 500","proName":"OANDA:SPX500USD"},
    {"title":"Nasdaq 100","proName":"OANDA:NAS100USD"},
    {"title":"EUR/USD","proName":"FX_IDC:EURUSD"},
    {"title":"BTC/USD","proName":"BITSTAMP:BTCUSD"},
    {"title":"ETH/USD","proName":"BITSTAMP:ETHUSD"}
  ],
  "colorTheme":"dark",
  "isTransparent": true,
  "displayMode":"adaptive",
  "showSymbolLogo": true,
  "locale":"en"
}
</script>
    </div>
  </div>
  <!-- HERO -->
  <div class="main_header__">
    <div id="particles-js"></div>
    <div class="_in_content_ hero">
      <div class="hero-left">
        <h2 class="l_text">PROFITABLE<br>INVESTMENTS<br>IN BITCOIN</h2>
        <p class="hero-lead">
          Trade Smarter with <b>Global Market Trade</b> — Your Gateway to Consistent Profits in the Crypto Market
        </p>
        <div class="hero-cta">
          <a class="btn btn-accent" href="<?php echo !empty($_SESSION['user_id']) ? 'dashboard.php' : 'signup.php'; ?>">Get Started</a>
          <a class="btn btn-ghost" href="#advantages">See Advantages</a>
        </div>
        <div class="stats">
          <div class="cnt_icon_num">
            <i class="fa fa-users"></i>
            <div class="value" id="stat-online">14559</div>
            <p>Traders<br>Online</p>
          </div>
          <div class="cnt_icon_num">
            <i class="fa fa-id-card-o"></i>
            <div class="value" id="stat-registered">5213</div>
            <p>Total<br>Registered</p>
          </div>
        </div>
      </div>
      <div class="hero-right">
        <img class="r_img" src="myasset/image/pc_main.svg" alt="Mockup">
      </div>
    </div>
  </div>
  <!-- WHY CHOOSE US -->
  <h2 class="_seg____header__">WHY CHOOSE US?</h2>
  <div class="_in_content_">
    <div class="cards">
      <div class="_seg____ b_none_">
        <h1 class="_seg____htxt_" style="text-align:center;">SMART & FAST TRADING</h1>
        <div class="_txt___">Execute trades instantly with our optimized global trading system</div>
      </div>
      <div class="_seg____ b_none_">
        <h1 class="_seg____htxt_" style="text-align:center;">LOW FEES & HIGH RETURNS</h1>
        <div class="_txt___">Minimize fees and maximize your profit with our efficient trading platform</div>
      </div>
      <div class="_seg____ b_none_">
        <h1 class="_seg____htxt_" style="text-align:center;">FAST & SECURE WITHDRAWAL</h1>
        <div class="_txt___">Instant withdrawals with full transparency and security</div>
      </div>
    </div>
  </div>
  <!-- GET UPDATED -->
  <h2 class="_seg____header__" style="margin-top:50px;">GET UPDATED WITH CRYPTOs</h2>
  <div class="_in_content_">
    <div class="tradingview-widget-container" style="margin-top:10px;">
      <div class="tradingview-widget-container__widget"></div>
      <script type="application/json" src="https://s3.tradingview.com/external-embedding/embed-widget-tickers.js" async>
{
  "symbols": [
    {"title":"S&P 500","proName":"OANDA:SPX500USD"},
    {"title":"Nasdaq 100","proName":"OANDA:NAS100USD"},
    {"title":"EUR/USD","proName":"FX_IDC:EURUSD"},
    {"title":"BTC/USD","proName":"BITSTAMP:BTCUSD"},
    {"title":"ETH/USD","proName":"BITSTAMP:ETHUSD"}
  ],
  "colorTheme":"dark",
  "isTransparent": true,
  "locale":"en"
}
      </script>
    </div>
  </div>
  <!-- AWARDS -->
  <div class="awards-wrap">
    <h2 class="_seg____header__" style="margin-top:10px;">OUR AWARD PLATFORM</h2>
    <div class="_in_content_">
      <div class="cards award-row">
        <div class="_seg____ a_seg____ b_none_">
          <img class="award-img" src="myasset/image/award_1.svg" alt="Award">
          <div class="_side___ _b_side___">
            <h1 class="_seg____htxt_" style="color:#FFF;">Century International Quality Award</h1>
            <div class="_txt___">Contoh deskripsi award.</div>
          </div>
        </div>
        <div class="_seg____ a_seg____ b_none_">
          <img class="award-img" src="myasset/image/award_2.svg" alt="Award">
          <div class="_side___ _b_side___">
            <h1 class="_seg____htxt_" style="color:#FFF;">Most Innovative Platform</h1>
            <div class="_txt___">Contoh deskripsi award 2.</div>
          </div>
        </div>
        <div class="_seg____ a_seg____ b_none_">
          <img class="award-img" src="myasset/image/award_3.svg" alt="Award">
          <div class="_side___ _b_side___">
            <h1 class="_seg____htxt_" style="color:#FFF;">Most Reliable Broker</h1>
            <div class="_txt___">Contoh deskripsi award 3.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- ADVANTAGES -->
  <h2 class="_seg____header__" id="advantages" style="margin-top:50px;">OUR PLATFORM ADVANTAGES</h2>
  <div class="_in_content_">
    <div class="cards adv">
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/payment.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">Payment Options</h1>
          <div class="_txt___">Berbagai metode pembayaran / payout.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/security.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">Strong Security</h1>
          <div class="_txt___">Proteksi akun dan data.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/world.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">World Coverage</h1>
          <div class="_txt___">Dipakai oleh user berbagai negara.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/team.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">Experienced Team</h1>
          <div class="_txt___">Tim berpengalaman.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/report.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">Advanced Reporting</h1>
          <div class="_txt___">Laporan dan statistik.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/platform.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">Cross-Platform</h1>
          <div class="_txt___">Mobile, tablet, dan PC.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/support.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">24/7 Support</h1>
          <div class="_txt___">Support kapan saja.</div>
        </div>
      </div>
      <div class="_seg____ b_none_">
        <img class="_seg___img _simg_" src="myasset/image/exchange.svg" alt="">
        <div class="_side___">
          <h1 class="_seg____htxt_">Instant Exchange</h1>
          <div class="_txt___">Proses cepat (contoh).</div>
        </div>
      </div>
    </div>
    <div class="tradingview-widget-container" style="margin-top:18px;">
      <div class="tradingview-widget-container__widget"></div>
      <script type="application/json" src="https://s3.tradingview.com/external-embedding/embed-widget-tickers.js" async>
{
  "symbols": [
    {"title":"BTC/USD","proName":"BITSTAMP:BTCUSD"},
    {"title":"ETH/USD","proName":"BITSTAMP:ETHUSD"},
    {"title":"EUR/USD","proName":"FX_IDC:EURUSD"}
  ],
  "colorTheme":"dark",
  "isTransparent": true,
  "locale":"en"
}
      </script>
    </div>
  </div>
  <!-- TESTIMONIALS -->
  <h2 class="_seg____header__" style="margin-top:50px;">WHAT INVESTORS SAY</h2>
  <div class="_in_content_">
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
      </ol>
      <div class="carousel-inner">
        <div class="item active">
          <div class="img-box">RN</div>
          <p class="testimonial">It has been an amazing journey since I started using this platform.</p>
          <p class="overview"><b>RICHARD NOAH</b> Germany</p>
          <div class="star-rating">
            <ul class="list-inline">
              <li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li>
            </ul>
          </div>
        </div>
        <div class="item">
          <div class="img-box">HO</div>
          <p class="testimonial">Smooth experience and quick support response. Great UI layout.</p>
          <p class="overview"><b>HENRY OSCAR</b> Mexico</p>
          <div class="star-rating">
            <ul class="list-inline">
              <li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li>
            </ul>
          </div>
        </div>
        <div class="item">
          <div class="img-box">AM</div>
          <p class="testimonial">Clean landing page, good sections, and trading widgets are nice.</p>
          <p class="overview"><b>ANTONIO MORENO</b> Spain</p>
          <div class="star-rating">
            <ul class="list-inline">
              <li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star"></i></li><li><i class="fa fa-star-half-o"></i></li>
            </ul>
          </div>
        </div>
      </div>
      <a class="carousel-control left" href="#myCarousel" data-slide="prev"><i class="fa fa-angle-left"></i></a>
      <a class="carousel-control right" href="#myCarousel" data-slide="next"><i class="fa fa-angle-right"></i></a>
    </div>
  </div>
  <!-- Payment -->
  <div class="paybar">
    <div class="_in_content_">
      <h2 class="_seg____header__" style="margin-top:0;">SECURED PAYMENT METHOD</h2>
      <div class="pay-icons">
        <span class="pay-pill"><i class="fa fa-cc-visa"></i> VISA</span>
        <span class="pay-pill"><i class="fa fa-cc-mastercard"></i> MASTERCARD</span>
        <span class="pay-pill"><i class="fa fa-paypal"></i> PAYPAL</span>
        <span class="pay-pill"><i class="fa fa-bitcoin"></i> CRYPTO</span>
      </div>
    </div>
  </div>
  <!-- Footer -->
  <div class="footer" style="background-image:url('myasset/image/footer-bg.svg');">
    <div class="_in_content_ footer-grid">
      <div class="foot_side___">
        <h1 class="_seg____htxt_" style="color:#FFF;">Most innovative platform</h1>
        <div class="_txt___" style="color:grey;">Deskripsi singkat perusahaan kamu. (Ganti sesuai kebutuhan)</div>
      </div>
      <div class="foot_side___">
        <h1 class="_seg____htxt_" style="color:#FFF;">Quick Links</h1>
        <a class="_txt___ _fLink" href="index.php">Home</a>
        <a class="_txt___ _fLink" href="login.php">Login</a>
        <a class="_txt___ _fLink" href="signup.php">Register</a>
        <a class="_txt___ _fLink" href="#">Chat With an Expert</a>
      </div>
      <div class="foot_side___">
        <h1 class="_seg____htxt_" style="color:#FFF;">About Us</h1>
        <div class="_txt___" style="color:grey;">Info legal/perusahaan kamu (negara, alamat kantor, dsb).</div>
      </div>
      <div class="foot_side___">
        <h1 class="_seg____htxt_" style="color:#FFF;">Contact Us</h1>
        <p class="_txt___ _cLink">Email: admin@example.com</p>
      </div>
    </div>
  </div>
  <div class="copyright">
    <div class="_in_content_">
      <center>
        <span class="copyright-note">
          You are granted limited non-exclusive non-transferable rights to use the IP provided on this website for personal and non-commercial purposes.
        </span>
        <br>
        <span style="color:#FFF;">Copyright &copy; <span id="year"></span> Global Market Trade, All Rights Reserved.</span>
      </center>
    </div>
  </div>
  <!-- JS libs -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- particles.js from CDN, config local -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <script src="myasset/js/particles-config.js"></script>
  <!-- local page scripts -->
  <script src="myasset/js/j.js"></script>
</body>
</html>