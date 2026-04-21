@extends('layouts.member')

@section('title', 'Profil | KWSP Malaysia')

@section('content')
<style>
    /* Profile Head - Keep current dark colors */
    .gmtd-pagehead h1, .gmtd-pagehead p {
        color: var(--text-main) !important;
    }

    /* Cards */
    .gmtd-stack .gmtd-card {
        background-color: var(--kwsp-blue-deep) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    /* Labels, Titles, Notes */
    .gmtd-card .gmtd-title,
    .gmtd-card .gmtd-note,
    .gmtd-card .gmtd-label {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    /* Specific: Padam Akaun (Last Section) */
    .gmtd-stack section:last-child .gmtd-card .gmtd-title {
        color: #ef4444 !important; /* Brighter red for visibility on dark blue */
    }
    .gmtd-stack section:last-child .gmtd-card .gmtd-title i {
        color: #ef4444 !important;
    }

    /* Inputs */
    .gmtd-card .gmtd-input {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
    }

    /* Readonly inputs */
    .gmtd-card .gmtd-input[readonly] {
        background: rgba(0, 0, 0, 0.1) !important;
        color: rgba(255, 255, 255, 0.6) !important;
    }
</style>

<div class="gmtd-pagehead">
    <h1><i class="fa fa-user"></i> Profil Anda</h1>
    <p>Kemas kini maklumat akaun, kata laluan dan tetapan keselamatan anda di sini.</p>
</div>

<div class="gmtd-stack">
    <section class="gmtd-surface">
        <div class="gmtd-card">
            @include('profile.partials.update-profile-information-form')
        </div>
    </section>

    <section class="gmtd-surface">
        <div class="gmtd-card">
            @include('profile.partials.update-password-form')
        </div>
    </section>

    <section class="gmtd-surface">
        <div class="gmtd-card">
            @include('profile.partials.delete-user-form')
        </div>
    </section>
</div>
@endsection
