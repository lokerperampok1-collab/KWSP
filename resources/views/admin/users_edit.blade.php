@extends('layouts.member')

@section('title', 'Edit Profil Pengguna | Admin')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.users') }}" class="gmtd-btn" style="background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none;">
        <i class="fa fa-arrow-left"></i> Kembali ke Senarai
    </a>
</div>

<div class="gmtd-pagehead">
    <h1><i class="fa fa-user-edit"></i> Edit Profil Pengguna</h1>
    <p>Kemaskini maklumat terperinci untuk <b>{{ $user->name }}</b>.</p>
</div>

<section class="gmtd-surface" style="max-width: 800px;">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="gmtd-stack">
        @csrf
        
        <div class="gmtd-row" style="grid-template-columns: 1fr 1fr; display: grid; gap: 20px;">
            <div class="gmtd-field">
                <label class="gmtd-label">Nama Penuh</label>
                <input type="text" name="name" class="gmtd-input" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Alamat Emel</label>
                <input type="email" name="email" class="gmtd-input" value="{{ old('email', $user->email) }}" required>
            </div>
        </div>

        <div class="gmtd-row" style="grid-template-columns: 1fr 1fr; display: grid; gap: 20px; margin-top: 20px;">
            <div class="gmtd-field">
                <label class="gmtd-label">Nombor Telefon</label>
                <input type="text" name="phone" class="gmtd-input" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Role Akaun</label>
                <select name="role" class="gmtd-input">
                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User (Member)</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin (Pengurus)</option>
                </select>
            </div>
        </div>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e2e8f0;">
        <h3 style="font-size: 16px; font-weight: 800; color: #00458C; margin-bottom: 20px;">Informasi Perbankan</h3>

        <div class="gmtd-row" style="grid-template-columns: 1fr 1fr; display: grid; gap: 20px;">
            <div class="gmtd-field">
                <label class="gmtd-label">Nama Bank</label>
                <input type="text" name="bank_name" class="gmtd-input" value="{{ old('bank_name', $user->bank_name) }}">
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Nombor Akaun</label>
                <input type="text" name="bank_account" class="gmtd-input" value="{{ old('bank_account', $user->bank_account) }}">
            </div>
        </div>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e2e8f0;">
        <h3 style="font-size: 16px; font-weight: 800; color: #00458C; margin-bottom: 20px;">Status & Keselamatan</h3>

        <div class="gmtd-row" style="grid-template-columns: 1fr 1fr; display: grid; gap: 20px;">
            <div class="gmtd-field">
                <label class="gmtd-label">Status KYC</label>
                <select name="status_kyc" class="gmtd-input">
                    <option value="unsubmitted" {{ $user->status_kyc == 'unsubmitted' ? 'selected' : '' }}>Belum Dihantar</option>
                    <option value="pending" {{ $user->status_kyc == 'pending' ? 'selected' : '' }}>Menunggu Pengesahan</option>
                    <option value="approved" {{ $user->status_kyc == 'approved' ? 'selected' : '' }}>Disahkan (Approved)</option>
                    <option value="rejected" {{ $user->status_kyc == 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                </select>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Akses Pengeluaran</label>
                <select name="is_withdraw_unlocked" class="gmtd-input">
                    <option value="0" {{ !$user->is_withdraw_unlocked ? 'selected' : '' }}>Dikunci (Locked)</option>
                    <option value="1" {{ $user->is_withdraw_unlocked ? 'selected' : '' }}>Dibuka (Unlocked)</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 40px; display: flex; gap: 12px;">
            <button type="submit" class="gmtd-btn gmtd-btn--primary" style="flex: 1; height: 50px; font-weight: 800;">
                <i class="fa fa-save"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.users') }}" class="gmtd-btn" style="padding: 12px 24px; border-color: #e2e8f0; color: #64748b;">Batal</a>
        </div>
    </form>
</section>
@endsection
