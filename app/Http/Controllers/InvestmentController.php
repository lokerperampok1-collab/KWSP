<?php

namespace App\Http\Controllers;

use App\Models\InvestmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function index()
    {
        $plans = InvestmentPlan::where('status', true)->orderBy('sort_order', 'asc')->get();
        return view('user.investment', ['plans' => $plans]);
    }

    public function invest(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:investment_plans,id',
            'amount' => 'required|numeric',
        ]);

        $plan = InvestmentPlan::find($request->plan_id);
        $user = Auth::user();

        if ($request->amount < $plan->min_amount) {
            return back()->withErrors(['amount' => "Minimum pelaburan adalah RM {$plan->min_amount}"]);
        }

        if ($user->wallet->balance < $request->amount) {
            return back()->withErrors(['amount' => 'Baki tidak mencukupi untuk pelaburan ini.']);
        }

        // Deduct balance
        $user->wallet->decrement('balance', $request->amount);

        // Create transaction
        $user->transactions()->create([
            'currency' => 'MYR',
            'type' => 'investment',
            'status' => 'approved',
            'amount' => $request->amount,
            'note' => "Pelaburan dalam pelan {$plan->name}",
        ]);

        // Create investment
        $user->investments()->create([
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'amount' => $request->amount,
            'roi_daily_percent' => $plan->roi_daily_percent,
            'duration_days' => $plan->duration_days,
            'start_at' => now(),
            'end_at' => now()->addDays($plan->duration_days),
            'status' => 'active',
        ]);

        return redirect()->route('dashboard')->with('ok', 'Pelaburan anda berjaya dimulakan!');
    }
}
