@extends('layouts.member')

@section('title', 'Pending KYC | Admin')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.index') }}" class="gmtd-btn" style="background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none;">
        <i class="fa fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

<div class="gmtd-pagehead">
    <h1><i class="fa fa-id-card"></i> Pending KYC Requests</h1>
    <p>Verify member identification documents.</p>
</div>

@if($requests->isEmpty())
    <div class="gmtd-card" style="padding: 40px; text-align: center; color: #64748b;">
        <i class="fa fa-check-circle" style="font-size: 48px; color: #1fb1a8; margin-bottom: 15px; display: block;"></i>
        No pending requests. All members are verified.
    </div>
@else
    <div style="display: flex; flex-direction: column; gap: 15px;">
        @foreach($requests as $req)
            <div class="gmtd-card" style="padding: 20px; border: 1px solid var(--kwsp-border); display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center;">
                <div style="flex: 1; min-width: 250px;">
                    <h3 style="color: #00458C; margin: 0; font-weight: 800;">{{ $req->user->name }}</h3>
                    <p style="color: #64748b; margin: 5px 0; font-size: 13px;">{{ $req->user->email }} | {{ $req->user->phone }}</p>
                    <div style="display: flex; gap: 12px; margin-top:15px;">
                        <a href="{{ asset('storage/'.$req->id_front_path) }}" target="_blank" class="gmtd-btn" style="padding: 8px 14px; font-size: 11px;">
                            <i class="fa fa-image"></i> IC Front
                        </a>
                        <a href="{{ asset('storage/'.$req->id_back_path) }}" target="_blank" class="gmtd-btn" style="padding: 8px 14px; font-size: 11px;">
                            <i class="fa fa-image"></i> IC Back
                        </a>
                        <a href="{{ asset('storage/'.$req->selfie_path) }}" target="_blank" class="gmtd-btn" style="padding: 8px 14px; font-size: 11px;">
                            <i class="fa fa-user"></i> Selfie
                        </a>
                    </div>
                </div>
                <div class="gmtd-actions">
                    <form action="{{ route('admin.kyc.approve', $req->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="gmtd-btn gmtd-btn--primary" style="background: #28a745 !important; border: none; box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);">Approve</button>
                    </form>
                    <form action="{{ route('admin.kyc.reject', $req->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="gmtd-btn" style="color: #ff5c7a; border-color: #ff5c7a;">Reject</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
