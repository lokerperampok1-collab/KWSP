<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPlan;
use Illuminate\Http\Request;

class InvestmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = InvestmentPlan::orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tier' => 'required|string|in:BASIC,GOLD,DIAMOND,VVIP',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'target_return' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'sort_order' => 'integer',
        ]);

        InvestmentPlan::create($data);
        return redirect()->route('admin.plans.index')->with('ok', 'Plan created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvestmentPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvestmentPlan $plan)
    {
        $data = $request->validate([
            'tier' => 'required|string|in:BASIC,GOLD,DIAMOND,VVIP',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'target_return' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'sort_order' => 'integer',
            'status' => 'boolean',
        ]);

        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('ok', 'Plan updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestmentPlan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('ok', 'Plan deleted');
    }
}
