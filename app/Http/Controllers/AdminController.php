<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'pending_kyc' => KycRequest::where('status', 'pending')->count(),
            'pending_tx' => WalletTransaction::where('status', 'pending')->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }

    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function kyc()
    {
        $requests = KycRequest::with('user')->where('status', 'pending')->latest()->get();
        return view('admin.kyc', compact('requests'));
    }

    public function approveKyc($id)
    {
        $req = KycRequest::findOrFail($id);
        $req->update(['status' => 'approved']);
        $req->user->update(['status_kyc' => 'approved']);
        return back()->with('ok', 'KYC Approved');
    }

    public function rejectKyc($id)
    {
        $req = KycRequest::findOrFail($id);
        $req->update(['status' => 'rejected']);
        $req->user->update(['status_kyc' => 'rejected']);
        return back()->with('ok', 'KYC Rejected');
    }

    public function wallet()
    {
        $transactions = WalletTransaction::with('user')->where('status', 'pending')->latest()->get();
        return view('admin.wallet', compact('transactions'));
    }

    public function approveTx($id)
    {
        $tx = WalletTransaction::findOrFail($id);
        if ($tx->status !== 'pending') return back();

        $tx->update(['status' => 'approved']);
        
        $user = $tx->user;
        if ($tx->type === 'deposit' || $tx->type === 'profit') {
            $user->wallet->increment('balance', $tx->amount);
        } elseif ($tx->type === 'withdraw') {
            // Deduct withdrawal amount only (30% fee was paid externally)
            $user->wallet->decrement('balance', $tx->amount);
        }

        return back()->with('ok', 'Transaction Approved');
    }

    public function rejectTx($id)
    {
        $tx = WalletTransaction::findOrFail($id);
        $tx->update(['status' => 'rejected']);
        return back()->with('ok', 'Transaction Rejected');
    }

    public function impersonate($id)
    {
        // Only admin can impersonate
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        
        // Store original admin ID
        session(['impersonate' => auth()->id()]);
        
        auth()->login($user);
        
        return redirect()->route('dashboard')->with('ok', "Now impersonating {$user->name}");
    }

    public function adjustBalance(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $amount = $request->input('amount');
        
        if ($amount > 0) {
            $user->wallet->increment('balance', $amount);
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'approved',
                'description' => 'Admin adjustment (Credit)'
            ]);
        } else {
            $user->wallet->decrement('balance', abs($amount));
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => abs($amount),
                'type' => 'withdraw',
                'status' => 'approved',
                'description' => 'Admin adjustment (Debit)'
            ]);
        }
        
        return back()->with('ok', 'Balance adjusted successfully');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $password = $request->input('password', '12345678');
        $user->update(['password' => bcrypt($password)]);
        return back()->with('ok', "Password reset to: {$password}");
    }

    public function leaveImpersonate()
    {
        if (session()->has('impersonate')) {
            $adminId = session()->pull('impersonate');
            $admin = User::findOrFail($adminId);
            auth()->login($admin);
            return redirect()->route('admin.users')->with('ok', 'Reverted to admin');
        }
        return redirect('/');
    }

    public function toggleWithdraw($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_withdraw_unlocked' => !$user->is_withdraw_unlocked,
        ]);

        $status = $user->is_withdraw_unlocked ? 'dibuka' : 'dikunci';
        return back()->with('ok', "Pengeluaran untuk {$user->name} telah {$status}.");
    }
}
