<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KWSP Malaysia - Platform pelaburan dipercayai untuk pulangan yang konsisten dan masa depan yang stabil.">
    <title>KWSP Malaysia - Pelaburan Pintar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{ asset('css/design_tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('myasset/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('custom_ui.css') }}">
</head>
<body class="corporate-light">
    <!-- Topbar -->
    <div class="topbar">
        <div class="_in_content_">
            <a href="/" class="brand">
                <img class="logo" src="{{ asset('myasset/image/main_logo.png') }}" alt="KWSP EPF">
            </a>
            <div class="top-actions">
                @auth
                    <a class="__tL" href="{{ route('dashboard') }}">DASHBOARD</a>
                @else
                    <a class="__tL __tL_alt" href="{{ route('login') }}">LOG MASUK</a>
                    <a class="__tL" href="{{ route('register') }}">DAFTAR AKAUN</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Hero -->
    <section class="main_header__">
        <div class="_in_content_ hero">
            <div class="hero-left">
                <div style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:50px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);font-size:12px;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:18px;backdrop-filter:blur(4px);">
                    <span style="width:8px;height:8px;border-radius:50%;background:#10B981;display:inline-block;animation: kwspPulse 2s ease-in-out infinite;"></span>
                    Platform Aktif &bull; Selamat &bull; Dipercayai
                </div>
                <h1 class="l_text" style="font-family: 'Plus Jakarta Sans', sans-serif;">PENAWAR<br>MASA DEPAN<br>KEWANGAN ANDA</h1>
                <p class="hero-lead">
                    Melabur dengan bijak bersama <b>KWSP EPF</b>. Platform pelaburan yang dipercayai untuk pulangan konsisten dan masa depan kewangan yang stabil.
                </p>
                <div class="hero-cta">
                    <a class="btn" href="{{ route('register') }}" style="background: #FFCC00; color: #00458C; border: none; font-weight: 700; border-radius: 50px; padding: 14px 32px; font-size: 15px; box-shadow: 0 10px 25px rgba(255,204,0,0.25);">
                        <i class="fa fa-rocket" style="margin-right:6px"></i> Mula Melabur
                    </a>
                    <a class="btn" href="#advantages" style="border: 1.5px solid rgba(255,255,255,0.3); border-radius: 50px; padding: 14px 28px; color: white; font-weight: 600; font-size: 14px; background: rgba(255,255,255,0.06); backdrop-filter: blur(4px);">
                        Ketahui Lebih Lanjut <i class="fa fa-arrow-right" style="margin-left:6px"></i>
                    </a>
                </div>

                <div style="display:flex;gap:28px;margin-top:36px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;">5,200+</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.55);font-weight:600;">Ahli Aktif</div>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;">RM 12M+</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.55);font-weight:600;">Jumlah Pelaburan</div>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:800;letter-spacing:-0.02em;">99.9%</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.55);font-weight:600;">Uptime</div>
                    </div>
                </div>
            </div>
            <div class="hero-right">
                <img class="r_img" src="{{ asset('myasset/image/pc_main.svg') }}" alt="KWSP Investment Platform">
            </div>
        </div>
    </section>

    <!-- Advantages -->
    <section id="advantages" style="padding: 80px 0;">
        <h2 class="_seg____header__" style="text-align:center; margin-bottom: 48px;">KELEBIHAN MELABUR<br>DENGAN KAMI</h2>
        <div class="_in_content_">
            <div class="cards">
                <div class="_seg____">
                    <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#EFF6FF,#DBEAFE);display:grid;place-items:center;margin:0 auto 16px;">
                        <i class="fa fa-bolt" style="font-size:24px;color:#2563EB;"></i>
                    </div>
                    <h3 class="_seg____htxt_">Ketelasan & Pantas</h3>
                    <div class="_txt___">Proses pelaburan dan pengeluaran yang efisien tanpa birokrasi yang rumit.</div>
                </div>
                <div class="_seg____">
                    <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#ECFDF5,#D1FAE5);display:grid;place-items:center;margin:0 auto 16px;">
                        <i class="fa fa-shield" style="font-size:24px;color:#059669;"></i>
                    </div>
                    <h3 class="_seg____htxt_">Keselamatan Terjamin</h3>
                    <div class="_txt___">Data dan dana anda dilindungi dengan teknologi enkripsi gred bank.</div>
                </div>
                <div class="_seg____">
                    <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#FEF3C7,#FDE68A);display:grid;place-items:center;margin:0 auto 16px;">
                        <i class="fa fa-line-chart" style="font-size:24px;color:#D97706;"></i>
                    </div>
                    <h3 class="_seg____htxt_">Analisis Pakar</h3>
                    <div class="_txt___">Akses kepada pelan pelaburan yang dioptimumkan oleh algoritma kecerdasan pasaran global.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section style="padding:60px 0;background:linear-gradient(135deg,#001D3D,#003566);text-align:center;">
        <div class="_in_content_">
            <h2 style="color:white;font-size:28px;font-weight:800;margin:0 0 12px;letter-spacing:-0.01em;">Sedia Untuk Bermula?</h2>
            <p style="color:rgba(255,255,255,0.65);font-size:15px;max-width:480px;margin:0 auto 28px;line-height:1.7;">Daftar akaun percuma hari ini dan mulakan perjalanan pelaburan anda bersama ribuan ahli lain.</p>
            <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:8px;background:#FFCC00;color:#00458C;padding:14px 32px;border-radius:50px;font-weight:700;font-size:15px;text-decoration:none;box-shadow:0 10px 25px rgba(255,204,0,0.2);transition:all 0.3s ease;">
                <i class="fa fa-user-plus"></i> Daftar Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="_in_content_ footer-grid">
            <div class="foot_side___">
                    <img src="{{ asset('myasset/image/main_logo.png') }}" style="height: 40px;" alt="KWSP EPF">
                <p style="margin-top: 16px; color: rgba(255,255,255,0.6); font-size: 14px; line-height: 1.7;">KWSP Malaysia komited untuk membantu ahli membina kekayaan melalui pelaburan pasaran global yang inovatif dan selamat.</p>
            </div>
            <div class="foot_side___">
                <h4 style="font-weight: 700; font-size: 13px; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom:16px;">PAUTAN PANTAS</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display:flex; flex-direction:column; gap:10px;">
                    <li><a href="/" style="color: rgba(255,255,255,0.65); text-decoration:none; font-size:14px; font-weight:500;">Utama</a></li>
                    <li><a href="{{ route('login') }}" style="color: rgba(255,255,255,0.65); text-decoration:none; font-size:14px; font-weight:500;">Log Masuk</a></li>
                    <li><a href="{{ route('register') }}" style="color: rgba(255,255,255,0.65); text-decoration:none; font-size:14px; font-weight:500;">Pendaftaran</a></li>
                </ul>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(255,255,255,0.08); margin-top: 40px; padding: 20px 0;">
            <div class="_in_content_">
                <p style="margin: 0; color: rgba(255,255,255,0.4); font-size: 13px; font-weight: 500;">&copy; {{ date('Y') }} KWSP Malaysia. Semua Hak Terpelihara.</p>
            </div>
        </div>
    </footer>

    <!-- Smooth scroll -->
    <script>
    document.querySelectorAll('a[href^="#"]').forEach(function(a){
        a.addEventListener('click', function(e){
            var t = document.querySelector(this.getAttribute('href'));
            if(t){ e.preventDefault(); t.scrollIntoView({behavior:'smooth', block:'start'}); }
        });
    });
    </script>
</body>
</html>
