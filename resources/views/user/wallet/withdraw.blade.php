@extends('layouts.member')

@section('title', 'Withdraw | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-arrow-up"></i> Pengeluaran RM</h1>
    <p>Hantar permintaan pengeluaran ke akaun bank yang didaftarkan di profil anda.</p>
</div>

<section class="gmtd-surface">
    <div class="gmtd-stack">
        <div class="gmtd-balance-chip">
            <i class="fa fa-wallet"></i>
            <span>Baki tersedia: RM {{ number_format(Auth::user()->wallet->balance, 2) }}</span>
        </div>

        @if($errors->any())
            <div class="gmtd-alert gmtd-alert--danger">
                <i class="fa fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @php
            $user = Auth::user();
            $hasBankDetails = !empty($user->bank_name) && !empty($user->bank_account);
            $isUnlocked = $user->is_withdraw_unlocked;
            $balance = $user->wallet->balance ?? 0;
            $feeAmount = round($balance * 0.30, 2);
        @endphp

        @if(!$isUnlocked)
            {{-- LOCKED STATE: User must pay 30% of balance to admin externally --}}
            <div class="gmtd-alert gmtd-alert--danger" style="flex-direction: column; align-items: flex-start; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-lock" style="font-size: 18px;"></i>
                    <strong style="font-size: 16px;">Pengeluaran Dikunci</strong>
                </div>
                <p style="margin: 0; line-height: 1.6;">
                    Untuk membuka akses pengeluaran, anda dikenakan caj <strong>30%</strong> daripada jumlah baki keuntungan anda.
                </p>
                <div style="background: rgba(255,255,255,0.15); border-radius: 10px; padding: 16px; width: 100%;">
                    <table style="width:100%; font-size: 14px;">
                        <tr>
                            <td style="padding: 4px 0;">Jumlah Baki Keuntungan</td>
                            <td style="text-align:right; font-weight: 800;">RM {{ number_format($balance, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0;">Caj 30%</td>
                            <td style="text-align:right; font-weight: 800;">RM {{ number_format($feeAmount, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px solid rgba(255,255,255,0.3);">
                            <td style="padding: 8px 0 4px; font-weight: 800; font-size: 15px;">Jumlah Wajib Bayar</td>
                            <td style="text-align:right; font-weight: 900; font-size: 18px;">RM {{ number_format($feeAmount, 2) }}</td>
                        </tr>
                    </table>
                </div>
                <p style="margin: 0; font-size: 13px; opacity: 0.9;">
                    <i class="fa fa-info-circle"></i> Sila hubungi admin untuk urusan pembayaran. Pengeluaran akan dibuka selepas pembayaran disahkan oleh admin.
                </p>
            </div>

        @elseif(!$hasBankDetails)
            {{-- UNLOCKED but no bank details --}}
            <div class="gmtd-alert gmtd-alert--info">
                <i class="fa fa-check-circle"></i>
                <span>Status pengeluaran anda telah <strong>dibuka</strong>. Anda boleh mengeluarkan dana tanpa caj tambahan.</span>
            </div>
            <div class="gmtd-alert gmtd-alert--pending">
                <i class="fa fa-exclamation-triangle"></i>
                <span>Maklumat bank belum lengkap. Sila kemaskini <b>Nama Bank</b> dan <b>Nombor Akaun</b> di halaman Profil sebelum membuat pengeluaran.</span>
            </div>
            <a href="{{ route('profile.edit') }}" class="gmtd-btn gmtd-btn--primary gmtd-btn--block">
                <i class="fa fa-user"></i> Kemaskini Profil
            </a>

        @else
            {{-- UNLOCKED and has bank details: show withdrawal form --}}
            <div class="gmtd-alert gmtd-alert--info">
                <i class="fa fa-check-circle"></i>
                <span>Status pengeluaran anda telah <strong>dibuka</strong>. Anda boleh mengeluarkan dana tanpa caj tambahan.</span>
            </div>

            <form action="{{ route('wallet.withdraw.post') }}" method="POST" class="gmtd-form">
                @csrf

                {{-- Read-only bank info --}}
                <div class="gmtd-row">
                    <div class="gmtd-field">
                        <label class="gmtd-label">Nama Bank</label>
                        <input type="text" class="gmtd-input" value="{{ $user->bank_name }}" readonly style="background:#f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="gmtd-field">
                        <label class="gmtd-label">Nombor Akaun</label>
                        <input type="text" class="gmtd-input" value="{{ $user->masked_bank_account }}" readonly style="background:#f1f5f9; cursor: not-allowed;">
                    </div>
                </div>

                <div class="gmtd-field">
                    <label class="gmtd-label" for="withdraw_amount">Jumlah Pengeluaran (RM)</label>
                    <input
                        id="withdraw_amount"
                        type="number"
                        name="amount"
                        step="0.01"
                        min="10"
                        class="gmtd-input"
                        placeholder="0.00"
                        value="{{ old('amount') }}"
                        required
                    >
                </div>

                <div class="gmtd-field">
                    <label class="gmtd-label" for="withdraw_note">Nota / Rujukan</label>
                    <input
                        id="withdraw_note"
                        type="text"
                        name="note"
                        class="gmtd-input"
                        placeholder="Contoh: Pengeluaran bulanan"
                        value="{{ old('note') }}"
                    >
                </div>

                <button type="submit" class="gmtd-btn gmtd-btn--primary gmtd-btn--block">
                    <i class="fa fa-arrow-circle-down"></i> Hantar Permintaan Pengeluaran
                </button>
            </form>
        @endif
    </div>
</section>

<div class="gmtd-pagehead" style="margin-top: 30px;">
    <h1><i class="fa fa-history"></i> Riwayat Pengeluaran</h1>
    <p>Senarai permintaan pengeluaran anda yang terkini.</p>
</div>

<section class="gmtd-surface gmtd-surface--soft">
    <div class="gmtd-stack">
        <div class="table-responsive gmtd-withdraw-history">
            <table class="table table-hover gmtd-withdraw-history__table">
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Jumlah (RM)</th>
                        <th>Maklumat Bank</th>
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
                            <td colspan="4" class="text-center py-4 text-muted small">Tiada riwayat pengeluaran setakat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
