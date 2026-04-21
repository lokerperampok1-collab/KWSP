<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'KWSP Malaysia'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- font-awesome (local) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor_components/font-awesome/css/font-awesome.css') }}">

    <!-- SweetAlert -->
    <script src="{{ asset('user/js/sweetalert-dev.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('user/css/sweetalert.css') }}">

    <!-- Corporate Light Design System -->
    <link rel="stylesheet" href="{{ asset('css/design_tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('user/css/gmtd_member_v2.css') }}">
    <link rel="stylesheet" href="{{ asset('custom_ui.css') }}">

    <style>
        body { 
            font-family: 'Poppins', 'Plus Jakarta Sans', system-ui, sans-serif; 
            line-height: var(--leading-body);
            color: var(--text-main);
        }
        /* Accessible Focus Rings */
        :focus-visible {
            outline: 3px solid var(--kwsp-blue-light);
            outline-offset: 2px;
        }
    </style>

    @stack('styles')
</head>

<body class="corporate-light">
    @include('partials.animated-bg')
    <div class="gmtd-app">
        @if(session()->has('impersonate'))
        <div style="
            background: linear-gradient(135deg, #FEF3C7, #FDE68A);
            color: #92400E;
            padding: 12px 20px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            border-radius: 12px;
            margin-bottom: 12px;
            border: 1px solid #FCD34D;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: kwspSlideDown 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        ">
            <i class="fa fa-user-secret" style="font-size: 16px;"></i>
            <span>Anda sedang log masuk sebagai <b>{{ Auth::user()->name }}</b></span>
            <a href="{{ route('admin.leave_impersonate') }}" style="
                background: #92400E;
                color: white;
                padding: 5px 14px;
                border-radius: 50px;
                font-weight: 700;
                font-size: 11px;
                text-decoration: none;
                margin-left: 6px;
                transition: all 0.2s ease;
            ">Kembali ke Admin</a>
        </div>
        @endif

        <header class="gmtd-top">
            <a class="gmtd-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('myasset/image/main_logo.png') }}" alt="KWSP EPF">
            </a>

            <div class="gmtd-user">
                <button class="gmtd-userbtn" id="userMenuBtn" type="button" aria-haspopup="true" aria-expanded="false">
                    <span class="gmtd-username">{{ Auth::user()->name }}</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
                <div class="gmtd-menu" id="userMenu" role="menu" aria-label="User menu">
                    <a href="{{ route('profile.edit') }}"><i class="fa fa-user"></i> Profil</a>
                    <a href="{{ route('kyc.index') }}"><i class="fa fa-id-card"></i> KYC</a>
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.index') }}"><i class="fa fa-lock"></i> Admin</a>
                    @endif
                    <div style="height:1px;background:var(--border-color);margin:4px 0;"></div>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit">
                            <i class="fa fa-sign-out"></i> Log Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @if(session('ok'))
        <div style="
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 14px;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: kwspSlideDown 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        ">
            <i class="fa fa-check-circle"></i> {{ session('ok') }}
        </div>
        @endif

        @yield('content')

    </div>

    <nav class="gmtd-nav" aria-label="Bottom navigation">
        <div class="gmtd-nav__wrap">
            <a class="{{ request()->routeIs('wallet.deposit') ? 'active' : '' }}" href="{{ route('wallet.deposit') }}"><i class="fa fa-credit-card"></i><span>Deposit</span></a>
            <a class="{{ request()->routeIs('investment.index') ? 'active' : '' }}" href="{{ route('investment.index') }}"><i class="fa fa-line-chart"></i><span>Invest</span></a>
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fa fa-home"></i><span>Home</span></a>
            <a class="{{ request()->routeIs('wallet.withdraw') ? 'active' : '' }}" href="{{ route('wallet.withdraw') }}"><i class="fa fa-arrow-circle-down"></i><span>Withdraw</span></a>
            <a class="{{ request()->routeIs('wallet.transfer') ? 'active' : '' }}" href="{{ route('wallet.transfer') }}"><i class="fa fa-exchange"></i><span>Transfer</span></a>
        </div>
    </nav>

    <script>
        // User menu toggle with animation
        (function(){
            var btn = document.getElementById('userMenuBtn');
            var menu = document.getElementById('userMenu');
            if(!btn || !menu) return;
            function setOpen(open){
                menu.classList.toggle('is-open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
            function close(){
                setOpen(false);
            }
            btn.addEventListener('click', function(){
                var open = menu.classList.contains('is-open');
                setOpen(!open);
            });
            document.addEventListener('click', function(e){
                if(!menu.contains(e.target) && !btn.contains(e.target)) close();
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
