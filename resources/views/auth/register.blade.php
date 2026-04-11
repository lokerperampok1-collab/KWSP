<x-guest-layout>
    @section('title', 'Register | KWSP Malaysia')

    <section class="gmt-auth__card" aria-label="Register">

        <div class="gmt-auth__left">
            <a class="gmt-auth__brand" href="/" aria-label="Back to home">
                <img src="{{ asset('myasset/image/main_logo.png') }}" alt="KWSP EPF">
            </a>

            <div class="gmt-auth__kicker"><span class="gmt-dot"></span><span style="color:var(--a-muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;">Sertai</span></div>
            <h1 class="gmt-auth__title">Mulakan Pelaburan.</h1>
            <p class="gmt-auth__sub">Sertai komuniti pelabur kami yang berkembang pesat dan mula bina masa depan anda hari ini.</p>


        </div>

        <div class="gmt-auth__right">
            <form class="gmt-form" method="POST" action="{{ route('register') }}" autocomplete="off">
                @csrf

                <!-- Name -->
                <div class="gmt-field">
                    <label class="gmt-label" for="name">Nama Penuh</label>
                    <input id="name" class="gmt-input" type="text" name="name" :value="old('name')" placeholder="Nama Penuh Anda" required autofocus autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="gmt-field">
                    <label class="gmt-label" for="email">E-mel</label>
                    <input id="email" class="gmt-input" type="email" name="email" :value="old('email')" placeholder="anda@email.com" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Phone -->
                <div class="gmt-field">
                    <label class="gmt-label" for="phone">No. Telefon</label>
                    <input id="phone" class="gmt-input" type="text" name="phone" :value="old('phone')" placeholder="+60 123456789" required>
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <!-- Country -->
                <div class="gmt-field">
                    <label class="gmt-label" for="country_name">Negara</label>
                    <input id="country_name" class="gmt-input" type="text" name="country_name" value="Malaysia" readonly style="background-color: #f3f4f6; cursor: pointer;">
                    <x-input-error :messages="$errors->get('country_name')" class="mt-2" />
                </div>

                <!-- Currency -->
                <div class="gmt-field">
                    <label class="gmt-label" for="currency_code">Mata Wang</label>
                    <input id="currency_code" class="gmt-input" type="text" name="currency_code" value="MYR/RM" readonly style="background-color: #f3f4f6; cursor: pointer;">
                    <x-input-error :messages="$errors->get('currency_code')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="gmt-field">
                    <label class="gmt-label" for="password">Kata Laluan</label>
                    <input id="password" class="gmt-input" type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="gmt-field">
                    <label class="gmt-label" for="password_confirmation">Sahkan Kata Laluan</label>
                    <input id="password_confirmation" class="gmt-input" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div style="margin-top:14px;">
                    <button class="gmt-cta" type="submit">Daftar Sekarang</button>
                </div>

                <p class="gmt-help">Sudah mempunyai akaun? <a href="{{ route('login') }}">Log masuk</a></p>
            </form>
        </div>

    </section>
</x-guest-layout>
