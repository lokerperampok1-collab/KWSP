@extends('layouts.member')

@section('title', 'Edit Investment Plan | KWSP Admin')

@section('content')
<div class="gmtd-pagehead">
    <h1>Edit Plan: {{ $plan->name }}</h1>
    <p>Update investment parameters.</p>
</div>

<div class="gmtd-card" style="padding: 24px;">
    <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="gmtd-form">
        @csrf
        @method('PUT')
        
        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Plan Name</label>
                <input type="text" name="name" class="gmtd-input" value="{{ $plan->name }}" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">ROI Daily (%)</label>
                <input type="number" step="0.01" name="roi_daily_percent" class="gmtd-input" value="{{ $plan->roi_daily_percent }}" required>
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Min. Amount (RM)</label>
                <input type="number" name="min_amount" class="gmtd-input" value="{{ $plan->min_amount }}" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Max. Amount (RM)</label>
                <input type="number" name="max_amount" class="gmtd-input" value="{{ $plan->max_amount }}" placeholder="Empty for Unlimited">
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Duration (Days)</label>
                <input type="number" name="duration_days" class="gmtd-input" value="{{ $plan->duration_days }}" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Sort Order</label>
                <input type="number" name="sort_order" class="gmtd-input" value="{{ $plan->sort_order }}">
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Status</label>
                <select name="status" class="gmtd-select" required>
                    <option value="1" {{ $plan->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$plan->status ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>
            <div class="gmtd-field">
                <!-- Spacing -->
            </div>
        </div>

        <div class="gmtd-field">
            <label class="gmtd-label">Description</label>
            <textarea name="description" class="gmtd-textarea" placeholder="Describe this plan...">{{ $plan->description }}</textarea>
        </div>

        <div class="gmtd-actions" style="margin-top: 10px;">
            <button type="submit" class="gmtd-btn gmtd-btn--primary">Update Plan</button>
            <a href="{{ route('admin.plans.index') }}" class="gmtd-btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
