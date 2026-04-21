<?php

namespace App\Http\Controllers;

use App\Models\InvestmentPlan;
use App\Traits\AutoMaturity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    use AutoMaturity;

    public function index()
    {
        $user = Auth::user();

        // Trigger auto maturity check
        $this->checkMaturity($user);

        $activeInvestments = $user->investments()->where('status', 'active')->get();

        if ($activeInvestments->isNotEmpty()) {
            return view('user.investment_active', compact('activeInvestments'));
        }

        $tiers = InvestmentPlan::where('status', true)->orderBy('sort_order', 'asc')->get()->groupBy('tier');
        return view('user.investment', ['tiers' => $tiers]);
    }

    public function invest(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:investment_plans,id',
        ]);

        $plan = InvestmentPlan::find($request->plan_id);
        $user = Auth::user();

        if ($user->wallet->balance < $plan->price) {
            return back()->withErrors(['amount' => 'Baki tidak mencukupi untuk pelaburan ini.']);
        }

        // Deduct balance
        $user->wallet->decrement('balance', $plan->price);

        // Create transaction
        $user->transactions()->create([
            'currency' => 'MYR',
            'type' => 'investment',
            'status' => 'approved',
            'amount' => $plan->price,
            'note' => "Pelaburan dalam pelan {$plan->name} ({$plan->tier})",
        ]);

        // Create investment
        $user->investments()->create([
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'amount' => $plan->price,
            'target_return' => $plan->target_return,
            'duration_days' => $plan->duration_days,
            'start_at' => now(),
            'end_at' => now()->addHours(rand(3, 6)),
            'status' => 'active',
        ]);

        return redirect()->route('dashboard')->with('ok', "Pelaburan {$plan->name} berjaya dimulakan!");
    }
}
