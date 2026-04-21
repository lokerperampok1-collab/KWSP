@extends('layouts.member')

@section('title', 'Manage Users | Admin')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.index') }}" class="gmtd-btn" style="background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none;">
        <i class="fa fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

<div class="gmtd-pagehead">
    <h1><i class="fa fa-users"></i> Member Management</h1>
    <p>View and manage all members of the platform.</p>
</div>

<div class="gmtd-tablewrap">
    <table class="gmtd-table">
        <thead>
            <tr>
                <th>Member</th>
                <th>Contact</th>
                <th>Wallet Balance</th>
                <th>KYC Status</th>
                <th>Account</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>
                    <div style="font-weight: 800; color: #00458C;">{{ $user->name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Member since {{ $user->created_at->format('M Y') }}</div>
                </td>
                <td>
                    <div style="font-weight: 700;">{{ $user->email }}</div>
                    <div style="font-size: 11px; color: #64748b;">{{ $user->phone }}</div>
                </td>
                <td>
                    <div style="font-weight: 800; color: #1455B7;">RM {{ number_format($user->wallet->balance ?? 0, 2) }}</div>
                </td>
                <td>
                    @if($user->status_kyc === 'approved')
                        <span class="gmtd-badge gmtd-badge--ok">Approved</span>
                    @elseif($user->status_kyc === 'pending')
                        <span class="gmtd-badge gmtd-badge--pending">Pending</span>
                    @else
                        <span class="gmtd-badge">Not Submitted</span>
                    @endif
                </td>
                <td>
                    <span class="gmtd-badge">{{ strtoupper($user->role) }}</span>
                </td>
                <td>
                    <div class="gmtd-actions" style="gap: 5px;">
                        <a href="{{ route('admin.users.impersonate', $user->id) }}" class="gmtd-btn" style="padding: 6px 10px; font-size: 11px; background: #eef6ff; color: #00458C; border-color: #d0e4ff;">
                            <i class="fa fa-user-secret"></i> Log In
                        </a>
                        <button class="gmtd-btn" style="padding: 6px 10px; font-size: 11px;" onclick="adjustBalance({{ $user->id }}, '{{ $user->name }}', {{ $user->wallet->balance ?? 0 }})">
                            <i class="fa fa-plus-minus"></i> Balance
                        </button>
                        <button class="gmtd-btn" style="padding: 6px 10px; font-size: 11px; border-color: #64748b; color: #64748b;" onclick="resetPassword({{ $user->id }}, '{{ $user->name }}')">
                            <i class="fa fa-key"></i> Pass
                        </button>
                        <form action="{{ route('admin.users.toggle_withdraw', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @if($user->is_withdraw_unlocked)
                                <button type="submit" class="gmtd-btn" style="padding: 6px 10px; font-size: 11px; background: #dcfce7; color: #166534; border-color: #bbf7d0;">
                                    <i class="fa fa-unlock"></i> Kunci
                                </button>
                            @else
                                <button type="submit" class="gmtd-btn" style="padding: 6px 10px; font-size: 11px; background: #fef2f2; color: #991b1b; border-color: #fecaca;">
                                    <i class="fa fa-lock"></i> Buka
                                </button>
                            @endif
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $users->links() }}
</div>

<!-- Custom Admin Modals -->
<style>
    .admin-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);
        display: none; place-items: center; z-index: 9999;
        transition: all 0.3s ease;
    }
    .admin-modal-overlay.active { display: grid; }
    .admin-modal-card {
        background: white; border-radius: 20px; padding: 32px;
        width: 100%; max-width: 420px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        animation: modalScale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes modalScale {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .admin-modal-title {
        color: #0f172a; font-size: 20px; font-weight: 800; margin-bottom: 8px;
    }
    .admin-modal-sub {
        color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5;
    }
    .admin-modal-actions {
        margin-top: 28px; display: flex; justify-content: flex-end; gap: 12px;
    }
</style>

<!-- Balance Modal -->
<div id="balanceModal" class="admin-modal-overlay">
    <div class="admin-modal-card">
        <h3 class="admin-modal-title">Urus Baki Pengguna</h3>
        <p class="admin-modal-sub">
            Melaraskan baki untuk <span id="balanceUserName" style="font-weight:700; color:#1455B7;"></span>.<br>
            Baki semasa: <span id="balanceUserCurrent" style="font-weight:700;"></span>
        </p>

        <form id="balanceForm" method="POST" action="" class="gmt-form">
            @csrf
            <div class="gmt-field">
                <label class="gmt-label">Jumlah (Positif + / Negatif -)</label>
                <input type="number" step="0.01" name="amount" id="balanceAmountInput" class="gmt-input" placeholder="Contoh: 100.00 atau -50.00" required autofocus>
            </div>
            
            <div class="admin-modal-actions">
                <button type="button" class="gmtd-btn" style="border-color:#e2e8f0; color:#64748b;" onclick="closeModal('balanceModal')">Batal</button>
                <button type="submit" class="gmtd-btn gmtd-btn--primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="admin-modal-overlay">
    <div class="admin-modal-card">
        <h3 class="admin-modal-title">Set Semula Kata Laluan</h3>
        <p class="admin-modal-sub">Menukar kata laluan untuk <span id="passwordUserName" style="font-weight:700; color:#1455B7;"></span>.</p>

        <form id="passwordForm" method="POST" action="" class="gmt-form">
            @csrf
            <div class="gmt-field">
                <label class="gmt-label">Kata Laluan Baharu</label>
                <input type="text" name="password" id="passwordInput" class="gmt-input" value="12345678" required>
            </div>
            
            <div class="admin-modal-actions">
                <button type="button" class="gmtd-btn" style="border-color:#e2e8f0; color:#64748b;" onclick="closeModal('passwordModal')">Batal</button>
                <button type="submit" class="gmtd-btn gmtd-btn--primary">Kemaskini Kata Laluan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    const input = document.getElementById(id).querySelector('input:not([type="hidden"])');
    if(input) setTimeout(() => input.focus(), 100);
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function adjustBalance(userId, name, current) {
    document.getElementById('balanceUserName').innerText = name;
    document.getElementById('balanceUserCurrent').innerText = 'RM ' + parseFloat(current).toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('balanceForm').action = "{{ route('admin.index') }}/users/" + userId + "/balance";
    document.getElementById('balanceAmountInput').value = '';
    openModal('balanceModal');
}

function resetPassword(userId, name) {
    document.getElementById('passwordUserName').innerText = name;
    document.getElementById('passwordForm').action = "{{ route('admin.index') }}/users/" + userId + "/reset-password";
    document.getElementById('passwordInput').value = '12345678';
    openModal('passwordModal');
}

// Close modal on click outside
window.onclick = function(event) {
    if (event.target.classList.contains('admin-modal-overlay')) {
        event.target.classList.remove('active');
    }
}
</script>
@endsection
