@extends('layouts.member')

@section('title', 'Create Investment Plan | KWSP Admin')

@section('content')
<div class="gmtd-pagehead">
    <h1>Create New Plan</h1>
    <p>Define investment parameters for members.</p>
</div>

<div class="gmtd-card" style="padding: 24px;">
    <form action="{{ route('admin.plans.store') }}" method="POST" class="gmtd-form">
        @csrf
        
        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Tier</label>
                <select name="tier" class="gmtd-input" style="background-color: white;" required>
                    <option value="BASIC">BASIC</option>
                    <option value="GOLD">GOLD</option>
                    <option value="DIAMOND">DIAMOND</option>
                    <option value="VVIP">VVIP</option>
                </select>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Plan Name</label>
                <input type="text" name="name" class="gmtd-input" placeholder="e.g. BASIC 1" required>
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Price (RM)</label>
                <input type="number" step="0.01" name="price" class="gmtd-input" placeholder="500.00" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Target Return (RM)</label>
                <input type="number" step="0.01" name="target_return" class="gmtd-input" placeholder="15000.00" required>
            </div>
        </div>

        <div class="gmtd-row">
            <div class="gmtd-field">
                <label class="gmtd-label">Duration (Days)</label>
                <input type="number" name="duration_days" class="gmtd-input" value="7" required>
            </div>
            <div class="gmtd-field">
                <label class="gmtd-label">Sort Order</label>
                <input type="number" name="sort_order" class="gmtd-input" value="0">
            </div>
        </div>

        <div class="gmtd-field">
            <label class="gmtd-label">Description</label>
            <textarea name="description" class="gmtd-textarea" placeholder="Describe this plan..."></textarea>
        </div>

        <div class="gmtd-actions" style="margin-top: 10px;">
            <button type="submit" class="gmtd-btn gmtd-btn--primary">Create Plan</button>
            <a href="{{ route('admin.plans.index') }}" class="gmtd-btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
