@extends('layouts.member')

@section('title', 'Transfer | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-exchange"></i> Pindahan Dana</h1>
    <p>Pindahkan dana ke akaun pengguna lain dalam platform menggunakan alamat emel mereka.</p>
</div>

<section class="gmtd-surface">
    <div class="gmtd-stack">
        <div class="gmtd-balance-chip">
            <i class="fa fa-wallet"></i>
            <span>Baki tersedia: RM {{ number_format(Auth::user()->wallet->balance, 2) }}</span>
        </div>

        {{-- Fee info banner --}}
        <div class="gmtd-alert gmtd-alert--info">
            <i class="fa fa-info-circle"></i>
            <span>Caj perkhidmatan sebanyak <b>30%</b> daripada jumlah pindahan akan dikenakan. Contoh: pindahan RM 100 → jumlah tolakan RM 130.</span>
        </div>

        @if(session('ok'))
            <div class="gmtd-alert gmtd-alert--success">
                <i class="fa fa-check-circle"></i>
                <span>{{ session('ok') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="gmtd-alert gmtd-alert--danger">
                <i class="fa fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('wallet.transfer.post') }}" method="POST" class="gmtd-form">
            @csrf

            <div class="gmtd-field">
                <label class="gmtd-label" for="transfer_email">Emel Penerima</label>
                <input
                    id="transfer_email"
                    type="email"
                    name="email"
                    class="gmtd-input"
                    placeholder="contoh@email.com"
                    value="{{ old('email') }}"
                    required
                >
                <p class="gmtd-note">Masukkan emel pengguna berdaftar yang ingin menerima dana.</p>
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="transfer_amount">Jumlah Pindahan (RM)</label>
                <input
                    id="transfer_amount"
                    type="number"
                    name="amount"
                    step="0.01"
                    min="1"
                    class="gmtd-input"
                    placeholder="0.00"
                    value="{{ old('amount') }}"
                    required
                >
                <p class="gmtd-note" id="feeCalc" style="color: var(--kwsp-blue-deep); font-weight: 700;"></p>
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="transfer_note">Nota / Rujukan</label>
                <input
                    id="transfer_note"
                    type="text"
                    name="note"
                    class="gmtd-input"
                    placeholder="Contoh: Bayaran hutang"
                    value="{{ old('note') }}"
                >
            </div>

            <button type="submit" class="gmtd-btn gmtd-btn--primary gmtd-btn--block">
                <i class="fa fa-paper-plane"></i> Hantar Pindahan
            </button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
    var amountInput = document.getElementById('transfer_amount');
    var feeCalc = document.getElementById('feeCalc');
    if(!amountInput || !feeCalc) return;

    function update(){
        var val = parseFloat(amountInput.value);
        if(isNaN(val) || val <= 0){
            feeCalc.textContent = '';
            return;
        }
        var fee = val * 0.30;
        var total = val + fee;
        feeCalc.textContent = 'Caj 30%: RM ' + fee.toFixed(2) + '  •  Jumlah tolakan: RM ' + total.toFixed(2);
    }

    amountInput.addEventListener('input', update);
    update();
})();
</script>
@endpush
