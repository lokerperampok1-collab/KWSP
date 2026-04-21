@extends('layouts.member')

@section('title', 'Deposit | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-arrow-down"></i> Deposit RM</h1>
    <p>Masukkan jumlah deposit dan rujukan pembayaran untuk diproses oleh pasukan kami.</p>
</div>

<section class="gmtd-surface gmtd-surface--soft">
    <div class="gmtd-stack">
        @if(session('ok'))
            <div class="gmtd-alert gmtd-alert--success">
                <i class="fa fa-check-circle"></i>
                <span>{{ session('ok') }}</span>
            </div>
        @endif

        <form action="{{ route('wallet.deposit.post') }}" method="POST" class="gmtd-form">
            @csrf

            <div class="gmtd-field">
                <label class="gmtd-label" for="deposit_amount">Jumlah (RM)</label>
                <input
                    id="deposit_amount"
                    type="number"
                    name="amount"
                    step="0.01"
                    class="gmtd-input"
                    placeholder="0.00"
                    value="{{ old('amount') }}"
                    required
                >
                @error('amount')
                    <span class="mt-2">{{ $message }}</span>
                @enderror
            </div>

            <div class="gmtd-field">
                <label class="gmtd-label" for="deposit_note">Nota / Rujukan</label>
                <input
                    id="deposit_note"
                    type="text"
                    name="note"
                    class="gmtd-input"
                    placeholder="Contoh: Transfer Bank"
                    value="{{ old('note') }}"
                >
                <p class="gmtd-note">Tambah maklumat rujukan supaya semakan pembayaran lebih cepat.</p>
            </div>

            <button type="submit" class="gmtd-btn gmtd-btn--primary gmtd-btn--block">
                Hantar Permintaan Deposit
            </button>
        </form>
    </div>
</section>

<div class="gmtd-pagehead" style="margin-top: 30px;">
    <h1><i class="fa fa-history"></i> Riwayat Deposit</h1>
    <p>Senarai permintaan deposit anda yang terkini.</p>
</div>

<section class="gmtd-surface gmtd-surface--soft">
    <div class="gmtd-stack">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Jumlah (RM)</th>
                        <th>Nota</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="text-nowrap">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                            <td class="font-bold">RM {{ number_format($tx->amount, 2) }}</td>
                            <td class="text-sm">{{ $tx->note ?? '-' }}</td>
                            <td class="text-center">
                                @if($tx->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($tx->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">Tiada riwayat deposit setakat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
