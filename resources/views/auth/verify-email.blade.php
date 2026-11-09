@extends('layouts.app')
@section('title', 'Verify email - Cambo Hub')
@section('content')
<section class="card">
    <div class="eyebrow">ONE MORE STEP</div>
    <h1>Verify your email</h1>
    <p>Follow the link sent to <strong>{{ auth()->user()->email }}</strong> to open your dashboard. The link expires in 60 minutes.</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Resend verification email</button>
    </form>
    <p class="switch"><a href="{{ route('account.edit') }}">Update your email address</a></p>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="secondary" type="submit">Sign out</button></form>
</section>
@endsection
