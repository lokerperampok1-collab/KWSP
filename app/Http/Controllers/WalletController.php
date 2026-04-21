<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    private const FEE_RATE = 0.30; // 30%

    // ── Deposit ──
    public function deposit()
    {
        $transactions = Auth::user()->transactions()->where('type', 'deposit')->latest()->get();
        return view('user.wallet.deposit', compact('transactions'));
    }

    public function depositPost(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'note' => 'nullable|string|max:255',
        ]);

        Auth::user()->transactions()->create([
            'currency' => 'MYR',
            'type' => 'deposit',
            'status' => 'pending',
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        return redirect()->route('dashboard')->with('ok', 'Permintaan deposit telah dihantar dan sedang diproses.');
    }

    // ── Withdraw ──
    public function withdraw()
    {
        $transactions = Auth::user()->transactions()->where('type', 'withdraw')->latest()->get();
        return view('user.wallet.withdraw', compact('transactions'));
    }

    public function withdrawPost(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'note' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Must be unlocked by admin
        if (!$user->is_withdraw_unlocked) {
            return back()->withErrors(['amount' => 'Pengeluaran anda masih dikunci. Sila hubungi admin untuk membuka akses pengeluaran.']);
        }

        // Must have bank details
        if (empty($user->bank_name) || empty($user->bank_account)) {
            return back()->withErrors(['bank' => 'Sila lengkapkan maklumat bank di halaman Profil terlebih dahulu.']);
        }

        $amount = (float) $request->amount;

        if ($user->wallet->balance < $amount) {
            return back()->withErrors(['amount' => 'Baki tidak mencukupi. Baki anda: RM ' . number_format($user->wallet->balance, 2) . '.']);
        }

        // Store withdrawal request (pending admin approval)
        $user->transactions()->create([
            'currency' => 'MYR',
            'type' => 'withdraw',
            'status' => 'pending',
            'amount' => $amount,
            'note' => 'Bank: ' . $user->bank_name . ' | Akaun: ' . $user->bank_account . ($request->note ? ' | Nota: ' . $request->note : ''),
        ]);

        // Immediate deduction
        $user->wallet->decrement('balance', $amount);

        return redirect()->route('dashboard')->with('ok', 'Permintaan pengeluaran RM ' . number_format($amount, 2) . ' telah dihantar.');
    }

    // ── Transfer ──
    public function transfer()
    {
        $transactions = Auth::user()->transactions()->whereIn('type', ['transfer_in', 'transfer_out'])->latest()->get();
        return view('user.wallet.transfer', compact('transactions'));
    }

    public function transferPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $sender = Auth::user();
        $amount = (float) $request->amount;
        $fee = round($amount * self::FEE_RATE, 2);
        $totalDeduction = $amount + $fee;

        // Cannot transfer to self
        if (strtolower($request->email) === strtolower($sender->email)) {
            return back()->withErrors(['email' => 'Anda tidak boleh memindahkan dana kepada diri sendiri.'])->withInput();
        }

        // Find recipient
        $receiver = User::where('email', $request->email)->first();
        if (!$receiver) {
            return back()->withErrors(['email' => 'Pengguna dengan emel ini tidak ditemui.'])->withInput();
        }

        // Balance check
        if ($sender->wallet->balance < $totalDeduction) {
            return back()->withErrors(['amount' => 'Baki tidak mencukupi. Jumlah tolakan termasuk caj 30% ialah RM ' . number_format($totalDeduction, 2) . '.'])->withInput();
        }

        DB::transaction(function () use ($sender, $receiver, $amount, $fee, $totalDeduction, $request) {
            // Deduct from sender (amount + fee)
            $sender->wallet->decrement('balance', $totalDeduction);

            // Credit to receiver (amount only)
            $receiver->wallet->increment('balance', $amount);

            // Transaction record: sender
            $sender->transactions()->create([
                'currency' => 'MYR',
                'type' => 'transfer_out',
                'status' => 'approved',
                'amount' => $totalDeduction,
                'note' => 'Pindahan ke ' . $receiver->email . ' (RM ' . number_format($amount, 2) . ' + Caj RM ' . number_format($fee, 2) . ')' . ($request->note ? ' | ' . $request->note : ''),
            ]);

            // Transaction record: receiver
            $receiver->transactions()->create([
                'currency' => 'MYR',
                'type' => 'transfer_in',
                'status' => 'approved',
                'amount' => $amount,
                'note' => 'Pindahan dari ' . $sender->email . ($request->note ? ' | ' . $request->note : ''),
            ]);

            // Fee record
            $sender->transactions()->create([
                'currency' => 'MYR',
                'type' => 'fee',
                'status' => 'approved',
                'amount' => $fee,
                'note' => 'Caj pindahan 30% untuk RM ' . number_format($amount, 2),
            ]);
        });

        return redirect()->route('wallet.transfer')->with('ok', 'Pindahan RM ' . number_format($amount, 2) . ' kepada ' . $receiver->email . ' berjaya (caj: RM ' . number_format($fee, 2) . ').');
    }
}
