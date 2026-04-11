<section class="gmtd-stack" style="gap: 15px;">
    <header>
        <h2 class="gmtd-title" style="color: #991B1B;">
            <i class="fa fa-exclamation-triangle"></i> {{ __('Padam Akaun') }}
        </h2>

        <p class="gmtd-note">
            {{ __('Setelah akaun anda dipadamkan, semua sumber dan datanya akan dipadamkan secara kekal. Sebelum memadamkan akaun anda, sila muat turun sebarang data atau maklumat yang anda ingin simpan.') }}
        </p>
    </header>

    <div>
        <button
            class="gmtd-btn"
            style="background-color: #991B1B; color: white;"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >
            <i class="fa fa-trash"></i> {{ __('Padam Akaun') }}
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 gmtd-form" style="padding: 24px;">
            @csrf
            @method('delete')

            <h2 class="gmtd-title" style="color: #991B1B;">
                {{ __('Adakah anda pasti mahu memadamkan akaun anda?') }}
            </h2>

            <p class="gmtd-note">
                {{ __('Setelah akaun anda dipadamkan, semua sumber dan datanya akan dipadamkan secara kekal. Sila masukkan kata laluan anda untuk mengesahkan bahawa anda mahu memadamkan akaun anda secara kekal.') }}
            </p>

            <div class="gmtd-field" style="margin-top: 20px;">
                <label class="gmtd-label sr-only" for="password">{{ __('Kata Laluan') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="gmtd-input"
                    placeholder="{{ __('Kata Laluan Anda') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="gmtd-btn" style="background-color: #f3f4f6; color: #374151;" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </button>

                <button type="submit" class="gmtd-btn" style="background-color: #991B1B; color: white;">
                    {{ __('Padam Akaun') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
