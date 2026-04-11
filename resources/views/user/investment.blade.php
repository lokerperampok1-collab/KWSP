@extends('layouts.member')

@section('title', 'Investment Plans | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-line-chart"></i> Pelan Pelaburan</h1>
    <p>Pilih pelan yang sesuai dengan modal anda dan teruskan pelaburan dalam beberapa langkah sahaja.</p>
</div>

<section class="gmtd-plan-list">
    @forelse($plans as $plan)
        <article class="gmtd-plan">
            <div>
                <h2 class="gmtd-plan__title">{{ $plan->name }}</h2>
                <p class="gmtd-plan__lead">ROI {{ rtrim(rtrim(number_format($plan->roi_daily_percent, 2), '0'), '.') }}% / hari</p>
                <div class="gmtd-plan__meta">
                    <span><i class="fa fa-clock-o"></i> {{ $plan->duration_days }} hari</span>
                    <span><i class="fa fa-money"></i> Min RM {{ number_format($plan->min_amount, 2) }}</span>
                    @if(!is_null($plan->max_amount))
                        <span><i class="fa fa-credit-card"></i> Maks RM {{ number_format($plan->max_amount, 2) }}</span>
                    @endif
                </div>
                @if($plan->description)
                    <p class="gmtd-note" style="margin-top: 14px !important;">{{ $plan->description }}</p>
                @endif
            </div>

            <div class="gmtd-plan__action">
                <form action="{{ route('investment.invest') }}" method="POST" class="gmtd-inline-form">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <label class="gmtd-label" for="amount_{{ $plan->id }}">Jumlah Pelaburan</label>
                    <input
                        id="amount_{{ $plan->id }}"
                        type="number"
                        name="amount"
                        value="{{ old('plan_id') == $plan->id ? old('amount') : $plan->min_amount }}"
                        min="{{ $plan->min_amount }}"
                        @if(!is_null($plan->max_amount)) max="{{ $plan->max_amount }}" @endif
                        step="0.01"
                        class="gmtd-input"
                        required
                    >
                    <button type="submit" class="gmtd-btn gmtd-btn--primary gmtd-btn--block">Labur Sekarang</button>
                </form>
            </div>
        </article>
    @empty
        <section class="gmtd-surface">
            <p class="gmtd-note">Tiada pelan pelaburan tersedia buat masa ini.</p>
        </section>
    @endforelse
</section>
@endsection
