@extends('layouts.member')

@section('title', 'Admin Dashboard | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-tachometer"></i> Panel Kawalan Admin</h1>
    <p>Pantau statistik platform dan permintaan yang belum selesai.</p>
</div>

<div class="gmtd-grid">
    <div class="gmtd-tile" style="cursor:pointer;" onclick="location.href='{{ route('admin.users') }}'">
        <div class="ic" style="background:#EFF6FF;color:#2563EB;border:1px solid #BFDBFE;">
            <i class="fa fa-users"></i>
        </div>
        <div class="lbl">Jumlah Pengguna</div>
        <div class="val">{{ number_format($stats['users']) }}</div>
        <div style="margin-top:14px;">
            <a href="{{ route('admin.users') }}" class="gmtd-btn gmtd-btn--primary" style="padding:7px 16px;font-size:12px;border-radius:8px;">
                <i class="fa fa-list"></i> Urus Senarai
            </a>
        </div>
    </div>

    <div class="gmtd-tile" style="cursor:pointer;" onclick="location.href='{{ route('admin.kyc') }}'">
        <div class="ic" style="background:{{ $stats['pending_kyc'] > 0 ? '#FEF2F2' : '#F1F5F9' }};color:{{ $stats['pending_kyc'] > 0 ? '#DC2626' : '#64748b' }};border:1px solid {{ $stats['pending_kyc'] > 0 ? '#FECACA' : '#E2E8F0' }};">
            <i class="fa fa-id-card"></i>
        </div>
        <div class="lbl">KYC Belum Selesai</div>
        <div class="val" style="color:{{ $stats['pending_kyc'] > 0 ? '#DC2626' : '' }};">
            {{ $stats['pending_kyc'] }}
            @if($stats['pending_kyc'] > 0)
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#DC2626;margin-left:6px;animation: kwspPulse 1.5s ease-in-out infinite;"></span>
            @endif
        </div>
        <div style="margin-top:14px;">
            <a href="{{ route('admin.kyc') }}" class="gmtd-btn" style="padding:7px 16px;font-size:12px;border-radius:8px;">
                <i class="fa fa-eye"></i> Semak
            </a>
        </div>
    </div>

    <div class="gmtd-tile" style="cursor:pointer;" onclick="location.href='{{ route('admin.wallet') }}'">
        <div class="ic" style="background:{{ $stats['pending_tx'] > 0 ? '#FFFBEB' : '#F1F5F9' }};color:{{ $stats['pending_tx'] > 0 ? '#D97706' : '#64748b' }};border:1px solid {{ $stats['pending_tx'] > 0 ? '#FDE68A' : '#E2E8F0' }};">
            <i class="fa fa-exchange"></i>
        </div>
        <div class="lbl">Transaksi Tertunda</div>
        <div class="val" style="color:{{ $stats['pending_tx'] > 0 ? '#D97706' : '' }};">
            {{ $stats['pending_tx'] }}
            @if($stats['pending_tx'] > 0)
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#D97706;margin-left:6px;animation: kwspPulse 1.5s ease-in-out infinite;"></span>
            @endif
        </div>
        <div style="margin-top:14px;">
            <a href="{{ route('admin.wallet') }}" class="gmtd-btn" style="padding:7px 16px;font-size:12px;border-radius:8px;">
                <i class="fa fa-check-circle"></i> Lulus Dana
            </a>
        </div>
    </div>

    <div class="gmtd-tile" style="cursor:pointer;" onclick="location.href='{{ route('admin.plans.index') }}'">
        <div class="ic" style="background:#F3E8FF;color:#7C3AED;border:1px solid #DDD6FE;">
            <i class="fa fa-cubes"></i>
        </div>
        <div class="lbl">Pelan Pelaburan</div>
        <div class="val" style="font-size:16px;">Konfigurasi ROI</div>
        <div style="margin-top:14px;">
            <a href="{{ route('admin.plans.index') }}" class="gmtd-btn" style="padding:7px 16px;font-size:12px;border-radius:8px;">
                <i class="fa fa-cog"></i> Urus Pelan
            </a>
        </div>
    </div>
</div>
@endsection
