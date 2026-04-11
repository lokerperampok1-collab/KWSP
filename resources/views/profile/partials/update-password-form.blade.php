<section>
    <header>
        <h2 class="gmtd-title">
            <i class="fa fa-lock"></i> {{ __('Kemas Kini Kata Laluan') }}
        </h2>
        <p class="gmtd-note">
            {{ __('Pastikan akaun anda menggunakan kata laluan yang panjang dan rawak untuk kekal selamat.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="gmtd-form" style="margin-top: 20px;">
        @csrf
        @method('put')

        <div class="gmtd-field">
            <label class="gmtd-label" for="update_password_current_password">{{ __('Kata Laluan Semasa') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="gmtd-input" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="gmtd-field">
            <label class="gmtd-label" for="update_password_password">{{ __('Kata Laluan Baru') }}</label>
            <input id="update_password_password" name="password" type="password" class="gmtd-input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="gmtd-field">
            <label class="gmtd-label" for="update_password_password_confirmation">{{ __('Sahkan Kata Laluan') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="gmtd-input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
            <button type="submit" class="gmtd-btn gmtd-btn--primary">
                {{ __('Simpan Kata Laluan') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="gmtd-note"
                    style="color: #059669; font-weight: 700; margin: 0;"
                >{{ __('Berjaya Dikemas Kini.') }}</p>
            @endif
        </div>
    </form>
</section>
