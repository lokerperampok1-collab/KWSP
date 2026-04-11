@extends('layouts.member')

@section('title', 'Manage Investment Plans | KWSP Admin')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-briefcase"></i> Investment Plans</h1>
    <p>Manage daily ROI plans for members.</p>
</div>

<div class="gmtd-actions" style="margin-bottom: 20px;">
    <a href="{{ route('admin.plans.create') }}" class="gmtd-btn gmtd-btn--primary">
        <i class="fa fa-plus"></i> Create New Plan
    </a>
</div>

<div class="gmtd-tablewrap">
    <table class="gmtd-table">
        <thead>
            <tr>
                <th>Plan Name</th>
                <th>Daily ROI (%)</th>
                <th>Duration</th>
                <th>Min. Amount</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
            <tr>
                <td><b>{{ $plan->name }}</b></td>
                <td>{{ number_format($plan->roi_daily_percent, 2) }}%</td>
                <td>{{ $plan->duration_days }} Days</td>
                <td>RM {{ number_format($plan->min_amount, 2) }}</td>
                <td>{{ $plan->sort_order }}</td>
                <td>
                    @if($plan->status)
                        <span class="gmtd-badge gmtd-badge--ok">Active</span>
                    @else
                        <span class="gmtd-badge gmtd-badge--bad">Disabled</span>
                    @endif
                </td>
                <td>
                    <div class="gmtd-actions" style="gap: 5px;">
                        <a href="{{ route('admin.plans.edit', $plan->id) }}" class="gmtd-btn" style="padding: 6px 10px; font-size: 12px;">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Delete this plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="gmtd-btn" style="padding: 6px 10px; font-size: 12px; border-color: #ff5c7a; color: #ff5c7a;">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
