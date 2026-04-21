@extends('layouts.member')

@section('title', 'Investment Plans | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-line-chart"></i> Pelan Pelaburan</h1>
    <p>Pilih kategori pelaburan yang sesuai dengan matlamat kewangan anda.</p>
</div>

<section class="gmtd-tiers">
    @foreach(['BASIC', 'GOLD', 'DIAMOND', 'VVIP'] as $tierName)
        @if(isset($tiers[$tierName]))
            <div class="gmtd-tier-section" style="margin-bottom: 40px;">
                <h3 class="gmtd-tier-label" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; color: #00458C; font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                    @php
                        $icon = match($tierName) {
                            'BASIC' => '📲',
                            'GOLD' => '📲',
                            'DIAMOND' => '📲',
                            'VVIP' => '📲',
                            default => '📲'
                        };
                    @endphp
                    <span>{{ $icon }} {{ $tierName }}</span>
                    <hr style="flex: 1; border: 0; border-top: 2px solid #e2e8f0;">
                </h3>

                <div class="gmtd-plan-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    @foreach($tiers[$tierName] as $plan)
                        <article class="gmtd-plan" style="background: white; border-radius: 20px; padding: 24px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div>
                                <h4 style="margin: 0; font-size: 14px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">{{ $plan->name }}</h4>
                                <div style="margin: 15px 0; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="font-size: 24px; font-weight: 800; color: #00458C;">RM {{ number_format($plan->price, 0) }}</div>
                                    <div style="font-size: 18px; color: #64748b;"><i class="fa fa-long-arrow-right"></i></div>
                                    <div style="font-size: 24px; font-weight: 800; color: #166534;">RM {{ number_format($plan->target_return, 0) }}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 15px; color: #64748b; font-size: 13px;">
                                    <span><i class="fa fa-calendar-o"></i> 3-6 Jam</span>
                                    <span><i class="fa fa-check-circle-o"></i> Pulangan Tetap</span>
                                </div>
                                @if($plan->description)
                                    <p style="margin-top: 15px; font-size: 12px; color: #94a3b8; line-height: 1.4;">{{ $plan->description }}</p>
                                @endif
                            </div>

                            <form action="{{ route('investment.invest') }}" method="POST" style="margin-top: 20px;">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="gmtd-btn gmtd-btn--primary" style="width: 100%; font-weight: 800; border-radius: 12px; height: 48px;">Labur Sekarang</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if($tiers->isEmpty())
        <section class="gmtd-surface" style="padding: 40px; text-align: center; background: white; border-radius: 20px;">
            <p style="color: #64748b;">Tiada pelan pelaburan tersedia buat masa ini.</p>
        </section>
    @endif
</section>
@endsection
