<section>
    <header>
        <h2 class="gmtd-title">
            <i class="fa fa-info-circle"></i> {{ __('Maklumat Profil') }}
        </h2>
        <p class="gmtd-note">
            {{ __("Kemaskini maklumat profil dan alamat e-mel akaun anda.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="gmtd-form" style="margin-top: 20px;">
        @csrf
        @method('patch')

        <div class="gmtd-field">
            <label class="gmtd-label" for="name">{{ __('Nama') }}</label>
            <input id="name" name="name" type="text" class="gmtd-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="gmtd-field">
            <label class="gmtd-label" for="email">{{ __('E-mel') }}</label>
            <input id="email" name="email" type="email" class="gmtd-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div style="margin-top: 10px;">
                    <p class="gmtd-note" style="color: var(--kwsp-blue-deep);">
                        {{ __('Alamat e-mel anda belum disahkan.') }}

                        <button form="send-verification" class="gmtd-btn gmtd-btn--link" style="padding: 0; font-size: inherit;">
                            {{ __('Klik di sini untuk menghantar semula e-mel pengesahan.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="gmtd-note" style="color: #059669; font-weight: 700;">
                            {{ __('Pautan pengesahan baru telah dihantar ke alamat e-mel anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="gmtd-field">
            <label class="gmtd-label" for="phone">{{ __('Telefon') }}</label>
            <input id="phone" name="phone" type="text" class="gmtd-input" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label" for="country_name">{{ __('Negara') }}</label>
                <input id="country_name" name="country_name" type="text" class="gmtd-input" value="{{ old('country_name', $user->country_name) }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                <x-input-error class="mt-2" :messages="$errors->get('country_name')" />
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="currency_code">{{ __('Mata Wang') }}</label>
                <input id="currency_code" name="currency_code" type="text" class="gmtd-input" value="{{ $user->currency_code }}/{{ $user->currency_symbol }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                <x-input-error class="mt-2" :messages="$errors->get('currency_code')" />
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label" for="bank_name">{{ __('Nama Bank') }}</label>
                <input id="bank_name" name="bank_name" type="text" class="gmtd-input" value="{{ old('bank_name', $user->bank_name) }}" placeholder="Maybank, CIMB, RHB, dll.">
                <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="bank_account">{{ __('Nombor Akaun Bank') }}</label>
                <input id="bank_account" name="bank_account" type="text" class="gmtd-input" value="{{ old('bank_account', $user->bank_account) }}" placeholder="1234567890">
                <x-input-error class="mt-2" :messages="$errors->get('bank_account')" />
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
            <button type="submit" class="gmtd-btn gmtd-btn--primary">
                {{ __('Simpan Perubahan') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="gmtd-note"
                    style="color: #059669; font-weight: 700; margin: 0;"
                >{{ __('Berjaya Disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
