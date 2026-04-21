@extends('layouts.member')

@section('title', 'Pelaburan Aktif | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-briefcase"></i> Pelaburan Aktif</h1>
    <p>Lihat status dan kematangan pelaburan anda yang sedang berjalan.</p>
</div>

<section class="gmtd-active-investments">
    @foreach($activeInvestments as $investment)
        <div class="gmtd-card" style="padding: 32px; border-radius: 24px; position: relative; overflow: hidden; border: 1px solid #e2e8f0; background: white; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
            <!-- Status Badge -->
            <div style="position: absolute; top: 24px; right: 24px;">
                <span class="gmtd-badge gmtd-badge--ok" style="padding: 8px 16px; border-radius: 100px; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fa fa-refresh fa-spin" style="margin-right: 8px;"></i> Sedang Aktif
                </span>
            </div>

            <div class="gmtd-row" style="margin-bottom: 32px;">
                <div style="flex: 1;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px;">Pakej Dipilih</div>
                    <h2 style="font-size: 28px; font-weight: 800; color: #00458C; margin: 0;">{{ $investment->plan_name }}</h2>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; margin-bottom: 32px; padding: 24px; background: #f8fafc; border-radius: 20px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Modal Pelaburan</div>
                    <div style="font-size: 22px; font-weight: 800; color: #00458C;">RM {{ number_format($investment->amount, 2) }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Sasaran Pulangan</div>
                    <div style="font-size: 22px; font-weight: 800; color: #166534;">RM {{ number_format($investment->target_return, 2) }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Tarikh Kematangan</div>
                    <div style="font-size: 22px; font-weight: 800; color: #0f172a;">{{ \Carbon\Carbon::parse($investment->end_at)->format('d M Y, h:i A') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 24px; color: #64748b; font-size: 13px; font-weight: 600;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-calendar-check-o" style="color: #00458C;"></i>
                    Mula: {{ \Carbon\Carbon::parse($investment->start_at)->format('d M Y, h:i A') }}
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-hourglass-half" style="color: #00458C;"></i>
                    Tempoh: 3-6 Jam
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-info-circle" style="color: #00458C;"></i>
                    Status: Menunggu Kematangan
                </div>
            </div>

            <!-- Simulated Progress Bar (Visual Only) -->
            <div style="margin-top: 32px; height: 6px; background: #e2e8f0; border-radius: 100px; overflow: hidden;">
                <div style="height: 100%; width: 35%; background: linear-gradient(90deg, #00458C, #1455B7); border-radius: 100px;"></div>
            </div>
            <div style="margin-top: 12px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: right;">
                Proses Pelaburan Berjalan...
            </div>
        </div>
    @endforeach

    <div style="text-align: center; margin-top: 40px;">
        <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">Anda mempunyai pelaburan yang sedang aktif. Anda hanya boleh melanggan pakej baharu selepas pelaburan semasa tamat.</p>
        <a href="{{ route('dashboard') }}" class="gmtd-btn" style="padding: 12px 32px; border-radius: 12px; font-weight: 700;">Kembali ke Dashboard</a>
    </div>
</section>
@endsection
