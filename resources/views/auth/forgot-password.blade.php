<x-guest-layout>
    @section('title', 'Lupa Kata Laluan | KWSP Malaysia')

    <section class="gmt-auth__card" aria-label="Forgot Password">

        <div class="gmt-auth__left">
            <a class="gmt-auth__brand" href="/" aria-label="Back to home">
                <img src="{{ asset('myasset/image/main_logo.png') }}" alt="KWSP EPF">
            </a>

            <div class="gmt-auth__kicker">
                <span class="gmt-dot"></span>
                <span style="color:var(--a-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;">Pemulihan</span>
            </div>
            <h1 class="gmt-auth__title">Lupa Kata Laluan?</h1>
            <p class="gmt-auth__sub">Tidak mengapa. Sila masukkan e-mel anda dan kami akan menghantar pautan untuk menetapkan semula kata laluan anda.</p>
        </div>

        <div class="gmt-auth__right">

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form class="gmt-form" method="POST" action="{{ route('password.email') }}" autocomplete="off">
                @csrf

                <div class="gmt-field">
                    <label class="gmt-label" for="email">E-mel</label>
                    <input id="email" class="gmt-input" type="email" name="email" :value="old('email')" placeholder="anda@email.com" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div style="margin-top:14px;">
                    <button class="gmt-cta" type="submit">Hantar Pautan</button>
                </div>

                <p class="gmt-help">Ingat kata laluan anda? <a href="{{ route('login') }}">Log masuk</a></p>
            </form>
        </div>

    </section>
</x-guest-layout>
