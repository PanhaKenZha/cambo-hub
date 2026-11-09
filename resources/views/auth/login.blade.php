@extends('layouts.app')
@section('title', 'Sign in · Cambo Hub')
@section('content')
<section class="card">
    <div class="eyebrow">WELCOME BACK</div>
    <h1>Sign in to your account</h1>
    <p>Enter your details to continue to your dashboard.</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password')<div class="error" role="alert">{{ $message }}</div>@enderror
        <div class="forgot"><a href="{{ route('password.request') }}">Forgot password?</a></div>
        <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Keep me signed in</label>
        <button type="submit">Sign in &rarr;</button>
    </form>
    <p class="switch">New here? <a href="{{ route('register') }}">Create an account</a></p>
</section>
@endsection
