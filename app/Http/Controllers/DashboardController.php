<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\AutoMaturity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use AutoMaturity;

    public function index()
    {
        $user = Auth::user();
        
        // Trigger auto maturity check
        $this->checkMaturity($user);
        
        // Ensure wallet exists
        $wallet = $user->wallet ?: $user->wallet()->create(['currency' => 'MYR', 'balance' => 0.00]);

        $totalProfit = $user->transactions()->where('type', 'profit')->where('status', 'approved')->sum('amount');
        $totalDeposit = $user->transactions()->where('type', 'deposit')->where('status', 'approved')->sum('amount');
        $totalWithdraw = $user->transactions()->where('type', 'withdraw')->where('status', 'approved')->sum('amount');
        $totalInvest = $user->transactions()->where('type', 'investment')->where('status', 'approved')->sum('amount');
        $currentInvest = $user->investments()->where('status', 'active')->sum('amount');

        return view('dashboard', [
            'wallet' => $wallet,
            'totalProfit' => $totalProfit,
            'totalDeposit' => $totalDeposit,
            'totalWithdraw' => $totalWithdraw,
            'totalInvest' => $totalInvest,
            'currentInvest' => $currentInvest,
        ]);
    }
}
