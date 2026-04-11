@extends('layouts.member')

@section('title', 'Pending Transactions | Admin')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-exchange"></i> Pending Transactions</h1>
    <p>Review and approve financial requests.</p>
</div>

@if($transactions->isEmpty())
    <div class="gmtd-card" style="padding: 40px; text-align: center; color: #64748b;">
        <i class="fa fa-check-circle" style="font-size: 48px; color: #1fb1a8; margin-bottom: 15px; display: block;"></i>
        No pending transactions. All clear!
    </div>
@else
    <div style="display: flex; flex-direction: column; gap: 15px;">
        @foreach($transactions as $tx)
            <div class="gmtd-card" style="padding: 20px; border: 1px solid var(--kwsp-border); display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center;">
                <div style="flex: 1; min-width: 250px;">
                    <h3 style="color: #00458C; margin: 0; font-weight: 800;">{{ $tx->user->name }}</h3>
                    <p style="color: #64748b; margin: 5px 0; font-size: 13px;">{{ $tx->user->email }}</p>
                    <div style="margin-top: 10px;">
                        <span class="gmtd-badge" style="background: {{ $tx->type === 'deposit' ? 'rgba(31, 225, 168, 0.1)' : 'rgba(255, 92, 122, 0.1)' }}; color: {{ $tx->type === 'deposit' ? '#1fb1a8' : '#ff5c7a' }}; border-color: {{ $tx->type === 'deposit' ? 'rgba(31, 225, 168, 0.2)' : 'rgba(255, 92, 122, 0.2)' }};">
                            {{ strtoupper($tx->type) }}
                        </span>
                        <span style="font-weight: 800; font-size: 18px; color: #1455B7; margin-left: 10px;">RM {{ number_format($tx->amount, 2) }}</span>
                    </div>
                    @if($tx->note)
                        <div style="margin-top: 8px; font-size: 11px; color: #64748b; font-style: italic;">
                            Note: {{ $tx->note }}
                        </div>
                    @endif
                </div>
                <div class="gmtd-actions">
                    <form action="{{ route('admin.wallet.approve', $tx->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="gmtd-btn gmtd-btn--primary" style="background: #28a745 !important; border: none; box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);">Approve</button>
                    </form>
                    <form action="{{ route('admin.wallet.reject', $tx->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="gmtd-btn" style="color: #ff5c7a; border-color: #ff5c7a;">Reject</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
