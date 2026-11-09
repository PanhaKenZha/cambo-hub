@extends('layouts.app')
@section('title', 'Reset password - Cambo Hub')
@section('content')
<section class="card">
    <div class="eyebrow">ACCOUNT RECOVERY</div>
    <h1>Forgot your password?</h1>
    <p>Enter your email and we'll send you a link to choose a new password.</p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
        <button type="submit">Send reset link</button>
    </form>
    <p class="switch"><a href="{{ route('login') }}">Back to sign in</a></p>
</section>
@endsection
