@extends('layouts.member')

@section('title', 'Manage Investment Plans | KWSP Admin')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.index') }}" class="gmtd-btn" style="background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none;">
        <i class="fa fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

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
                <th>Tier</th>
                <th>Plan Name</th>
                <th>Price</th>
                <th>Target Return</th>
                <th>Duration</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
            <tr>
                <td><span class="gmtd-badge">{{ $plan->tier }}</span></td>
                <td><b>{{ $plan->name }}</b></td>
                <td>RM {{ number_format($plan->price, 2) }}</td>
                <td>RM {{ number_format($plan->target_return, 2) }}</td>
                <td>3-6 Jam</td>
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
