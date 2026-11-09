@extends('layouts.app')
@section('title', 'New password - Cambo Hub')
@section('content')
<section class="card">
    <div class="eyebrow">A FRESH START</div>
    <h1>Choose a new password</h1>
    <p>Use at least 8 characters for your new password.</p>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
        @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
        @error('token')<div class="error" role="alert">{{ $message }}</div>@enderror
        <label for="password">New password</label>
        <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
        @error('password')<div class="error" role="alert">{{ $message }}</div>@enderror
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
        <button type="submit">Reset password</button>
    </form>
    <p class="switch"><a href="{{ route('password.request') }}">Request a new link</a></p>
</section>
@endsection
