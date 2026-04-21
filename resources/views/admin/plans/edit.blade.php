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
                <label class="gmtd-label">Tier</label>
                <select name="tier" class="gmtd-input" style="background-color: white;" required>
                    <option value="BASIC" {{ $plan->tier == 'BASIC' ? 'selected' : '' }}>BASIC</option>
                    <option value="GOLD" {{ $plan->tier == 'GOLD' ? 'selected' : '' }}>GOLD</option>
                    <option value="DIAMOND" {{ $plan->tier == 'DIAMOND' ? 'selected' : '' }}>DIAMOND</option>
                    <option value="VVIP" {{ $plan->tier == 'VVIP' ? 'selected' : '' }}>VVIP</option>
                </select>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Plan Name</label>
                <input type="text" name="name" class="gmtd-input" value="{{ $plan->name }}" required>
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Price (RM)</label>
                <input type="number" step="0.01" name="price" class="gmtd-input" value="{{ $plan->price }}" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Target Return (RM)</label>
                <input type="number" step="0.01" name="target_return" class="gmtd-input" value="{{ $plan->target_return }}" required>
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
