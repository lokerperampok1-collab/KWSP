@extends('layouts.member')

@section('title', 'Manage Users | Admin')

@section('content')
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

<!-- Scripts for Admin Actions -->
<script>
function adjustBalance(userId, name, current) {
    let amount = prompt("Adjust balance for " + name + " (Current: RM" + current + "). Enter positive for add, negative for sub:", "0");
    if (amount !== null && !isNaN(amount) && amount != 0) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.index') }}/users/" + userId + "/balance";
        form.innerHTML = `
            @csrf
            <input type="hidden" name="amount" value="${amount}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function resetPassword(userId, name) {
    let password = prompt("Reset password for " + name + ":", "12345678");
    if (password !== null && password.length >= 8) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.index') }}/users/" + userId + "/reset-password";
        form.innerHTML = `
            @csrf
            <input type="hidden" name="password" value="${password}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
