@extends('layouts.app')
@section('title', 'Create account · Cambo Hub')
@section('content')
<section class="card">
    <div class="eyebrow">GET STARTED</div>
    <h1>Create your account</h1>
    <p>A few details and you're ready to go.</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label for="name">Full name</label>
        <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
        @error('name')<div class="error" role="alert">{{ $message }}</div>@enderror
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
        <label for="password">Password (at least 8 characters)</label>
        <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
        @error('password')<div class="error" role="alert">{{ $message }}</div>@enderror
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <button type="submit">Create account &rarr;</button>
    </form>
    <p class="switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</section>
@endsection
