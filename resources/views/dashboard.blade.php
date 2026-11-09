@extends('layouts.app')
@section('title', 'Dashboard · Cambo Hub')
@section('content')
<section class="card">
    <div class="eyebrow">YOUR DASHBOARD</div>
    <h1>Welcome, {{ auth()->user()->name }}.</h1>
    <p>You're signed in and ready to get started.</p>
    <div class="profile"><strong>{{ auth()->user()->name }}</strong><br>{{ auth()->user()->email }}</div>
    @if(auth()->user()->isAdmin())
        <p class="switch"><a href="{{ route('admin.dashboard') }}">Go to admin panel &rarr;</a></p>
    @endif
    <p class="switch"><a href="{{ route('account.edit') }}">Manage your account &rarr;</a></p>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
</section>
@endsection
