@extends('layouts.member')

@section('title', 'Profil | KWSP Malaysia')

@section('content')
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
