@extends('layouts.member')

@section('title', 'Profil | KWSP Malaysia')

@section('content')
<div class="gmtd-pagehead">
    <h1><i class="fa fa-user"></i> Profil Anda</h1>
    <p>Kemas kini maklumat akaun, kata laluan dan tetapan keselamatan anda di sini.</p>
</div>

<style>
    /* Paksa background putih agar selaras dengan Withdraw */
    .gmtd-surface {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
    }

    .gmtd-surface h2, 
    .gmtd-surface h3, 
    .gmtd-surface .gmtd-title {
        color: #00458C !important;
    }

    .gmtd-surface .gmtd-label, 
    .gmtd-surface .gmtd-note,
    .gmtd-surface p {
        color: #334155 !important;
    }

    .gmtd-surface .gmtd-input {
        background: #ffffff !important;
        border-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    /* Khusus untuk teks note agar tidak terlalu terang */
    .gmtd-note {
        color: #64748b !important;
    }
</style>

<div class="gmtd-stack">
    <section class="gmtd-surface">
        @include('profile.partials.update-profile-information-form')
    </section>

    <section class="gmtd-surface">
        @include('profile.partials.update-password-form')
    </section>

    <section class="gmtd-surface">
        @include('profile.partials.delete-user-form')
    </section>
</div>
@endsection
