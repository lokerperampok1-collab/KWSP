@extends('layouts.member')

@section('title', 'KYC Verification | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-id-card"></i> Pengesahan Identiti (KYC)</h1>
    <p>Muat naik dokumen yang jelas untuk mempercepat proses semakan dan membuka akses pelaburan penuh.</p>
</div>

<section class="gmtd-surface">
    @if($kyc && $kyc->status === 'approved')
        <div class="gmtd-alert gmtd-alert--success">
            <i class="fa fa-check-circle"></i>
            <span>Akaun anda telah disahkan. Anda boleh meneruskan aktiviti pelaburan tanpa halangan.</span>
        </div>
    @elseif($kyc && $kyc->status === 'pending')
        <div class="gmtd-alert gmtd-alert--pending">
            <i class="fa fa-clock-o"></i>
            <span>Dokumen anda sedang disemak. Kami akan maklumkan sebaik sahaja proses selesai.</span>
        </div>
    @else
        <form action="{{ route('kyc.store') }}" method="POST" enctype="multipart/form-data" class="gmtd-form">
            @csrf

            <p class="gmtd-note">Pastikan setiap fail terang, tidak kabur, dan memaparkan keseluruhan dokumen.</p>

            <div class="gmtd-field">
                <label class="gmtd-label" for="id_front">Kad Pengenalan (Depan)</label>
                <div class="gmtd-upload" data-upload>
                    <input id="id_front" type="file" name="id_front" class="gmtd-upload__input" required data-upload-input>
                    <label class="gmtd-upload__card" for="id_front">
                        <span class="gmtd-upload__button">Pilih Fail</span>
                        <span class="gmtd-upload__meta">
                            <span class="gmtd-upload__filename" data-upload-name>Belum ada fail dipilih</span>
                            <span class="gmtd-upload__hint">Format jelas dan penuh muka hadapan IC</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="id_back">Kad Pengenalan (Belakang)</label>
                <div class="gmtd-upload" data-upload>
                    <input id="id_back" type="file" name="id_back" class="gmtd-upload__input" required data-upload-input>
                    <label class="gmtd-upload__card" for="id_back">
                        <span class="gmtd-upload__button">Pilih Fail</span>
                        <span class="gmtd-upload__meta">
                            <span class="gmtd-upload__filename" data-upload-name>Belum ada fail dipilih</span>
                            <span class="gmtd-upload__hint">Pastikan teks dan nombor IC boleh dibaca</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="selfie">Selfie dengan IC</label>
                <div class="gmtd-upload" data-upload>
                    <input id="selfie" type="file" name="selfie" class="gmtd-upload__input" required data-upload-input>
                    <label class="gmtd-upload__card" for="selfie">
                        <span class="gmtd-upload__button">Pilih Fail</span>
                        <span class="gmtd-upload__meta">
                            <span class="gmtd-upload__filename" data-upload-name>Belum ada fail dipilih</span>
                            <span class="gmtd-upload__hint">Wajah dan IC perlu kelihatan terang dalam satu gambar</span>
                        </span>
                    </label>
                </div>
            </div>

            <button type="submit" class="gmtd-btn gmtd-btn--primary gmtd-btn--block">
                Muat Naik Dokumen
            </button>
        </form>
    @endif
</section>
@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-upload]').forEach(function (root) {
            var input = root.querySelector('[data-upload-input]');
            var name = root.querySelector('[data-upload-name]');
            if (!input || !name) return;

            input.addEventListener('change', function () {
                var fileName = input.files && input.files.length ? input.files[0].name : 'Belum ada fail dipilih';
                name.textContent = fileName;
                root.classList.toggle('has-file', !!(input.files && input.files.length));
            });
        });
    })();
</script>
@endpush
