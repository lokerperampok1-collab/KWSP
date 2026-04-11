@extends('layouts.member')

@section('title', 'Dashboard | KWSP Malaysia')

@section('content')
    <section class="gmtd-card" aria-label="Balance and chart">
        <div class="gmtd-card__content">
            <div class="gmtd-card__summary">
                <div class="gmtd-balance">
                    <div class="gmtd-kicker">Jumlah Keuntungan</div>
                    <div class="gmtd-amount">
                        <span id="balVal">{{ number_format($wallet->balance, 2) }}</span>
                        <small class="curSym">{{ $wallet->currency }}</small>
                    </div>
                </div>

                <div class="gmtd-subrow">
                    <span class="gmtd-pill"><i class="fa fa-line-chart"></i> Untung: <span class="curSym">{{ $wallet->currency }}</span> <span id="profitVal">{{ number_format($totalProfit, 2) }}</span></span>
                    <span class="gmtd-pill"><i class="fa fa-calendar"></i> Sertai: {{ Auth::user()->created_at->format('d M Y') }}</span>
                </div>
            </div>

            <div class="gmtd-chart">
                <div class="gmtd-chart__inner" id="tvchart"></div>
            </div>
        </div>
    </section>

    <section class="gmtd-grid" aria-label="Quick stats">
        <div class="gmtd-tile t-withdraw">
            <div class="ic"><i class="fa fa-arrow-up"></i></div>
            <div class="lbl">Total pengeluaran</div>
            <div class="val"><span class="curSym">{{ $wallet->currency }}</span> {{ number_format($totalWithdraw, 2) }}</div>
        </div>

        <div class="gmtd-tile t-deposit">
            <div class="ic"><i class="fa fa-arrow-down"></i></div>
            <div class="lbl">Total deposit</div>
            <div class="val"><span class="curSym">{{ $wallet->currency }}</span> {{ number_format($totalDeposit, 2) }}</div>
        </div>

        <div class="gmtd-tile t-invest">
            <div class="ic"><i class="fa fa-briefcase"></i></div>
            <div class="lbl">Total pelaburan</div>
            <div class="val"><span class="curSym">{{ $wallet->currency }}</span> {{ number_format($totalInvest, 2) }}</div>
        </div>

        <div class="gmtd-tile t-current">
            <div class="ic"><i class="fa fa-bar-chart"></i></div>
            <div class="lbl">Pelaburan aktif</div>
            <div class="val"><span class="curSym">{{ $wallet->currency }}</span> {{ number_format($currentInvest, 2) }}</div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // TradingView chart (dark)
    (function(){
        var el = document.getElementById('tvchart');
        if(!el) return;

        var symbols = [
            "BINANCE:BTCUSDT","BINANCE:ETHUSDT","FX:EURUSD","FX:USDJPY",
            "TVC:GOLD","TVC:SILVER","TVC:USOIL"
        ];

        var sym = symbols[Math.floor(Math.random()*symbols.length)];

        var s = document.createElement('script');
        s.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
        s.async = true;
        s.innerHTML = JSON.stringify({
            autosize: true,
            symbol: sym,
            interval: "D",
            timezone: "Etc/UTC",
            theme: "light",
            style: "1",
            locale: "en",
            enable_publishing: false,
            hide_top_toolbar: false,
            hide_legend: true,
            allow_symbol_change: true,
            calendar: false,
            support_host: "https://www.tradingview.com",
            backgroundColor: "rgba(255,255,255,1)",
            gridColor: "rgba(0,0,0,0.06)"
        });
        el.appendChild(s);
    })();
</script>
@endpush
