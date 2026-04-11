<x-guest-layout>
    @section('title', 'Login | KWSP Malaysia')

    <section class="gmt-auth__card" aria-label="Login">

        <div class="gmt-auth__left">
            <a class="gmt-auth__brand" href="/" aria-label="Back to home">
                <img src="{{ asset('myasset/image/main_logo.png') }}" alt="KWSP EPF">
            </a>

            <div class="gmt-auth__kicker"><span class="gmt-dot"></span><span style="color:var(--a-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;">Akaun</span></div>
            <h1 class="gmt-auth__title">Selamat kembali.</h1>
            <p class="gmt-auth__sub">Log masuk untuk mengakses dan melihat gambaran keseluruhan akaun, aktiviti, dan kemas kini pasaran terkini anda dengan selamat.</p>
        </div>

        <div class="gmt-auth__right">

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form class="gmt-form" method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf

                <div class="gmt-field">
                    <label class="gmt-label" for="email">E-mel</label>
                    <input id="email" class="gmt-input" type="email" name="email" :value="old('email')" placeholder="anda@email.com" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="gmt-field">
                    <label class="gmt-label" for="password">Kata Laluan</label>
                    <div class="gmt-passwrap">
                        <input id="password" class="gmt-input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="gmt-eye" id="togglePass" aria-label="Toggle password">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="gmt-row">
                    <label class="gmt-check"><input type="checkbox" name="remember" value="1"> Kekal log masuk</label>
                    @if (Route::has('password.request'))
                    <a class="gmt-help" href="{{ route('password.request') }}">Lupa kata laluan?</a>
                    @endif
                </div>

                <div style="margin-top:14px;">
                    <button class="gmt-cta" type="submit">Log masuk</button>
                </div>

                <p class="gmt-help">Belum mempunyai akaun? <a href="{{ route('register') }}">Cipta satu</a></p>
            </form>
        </div>

    </section>

    @push('scripts')
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
    @endpush
</x-guest-layout>
