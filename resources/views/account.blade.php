@extends('layouts.app')
@section('title', 'Account settings - Cambo Hub')
@section('content')
<div class="settings">
    <a href="{{ route('dashboard') }}">&larr; Dashboard</a>
    <h1>Account settings</h1>
    <section class="card">
        <h2>Your profile</h2>
        <p>Keep your name and email up to date.</p>
        <form method="POST" action="{{ route('account.update') }}">
            @csrf @method('PATCH')
            <label for="name">Full name</label>
            <input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" autocomplete="name" maxlength="255" required>
            @error('name')<div class="error" role="alert">{{ $message }}</div>@enderror
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="email" required>
            @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
            <label for="profile_password">Current password (required to change email)</label>
            <input id="profile_password" type="password" name="current_password" autocomplete="current-password">
            @error('current_password')<div class="error" role="alert">{{ $message }}</div>@enderror
            <button type="submit">Save profile</button>
        </form>
    </section>
    <section class="card">
        <h2>Change password</h2>
        <form method="POST" action="{{ route('account.password') }}">
            @csrf @method('PUT')
            <label for="current_password">Current password</label>
            <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
            @error('current_password', 'password')<div class="error" role="alert">{{ $message }}</div>@enderror
            <label for="new_password">New password (at least 8 characters)</label>
            <input id="new_password" type="password" name="password" autocomplete="new-password" minlength="8" required>
            @error('password', 'password')<div class="error" role="alert">{{ $message }}</div>@enderror
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            <button type="submit">Update password</button>
        </form>
    </section>
    <section class="card danger-zone">
        <h2>Delete account</h2>
        <p>This permanently deletes your account. Enter your password to confirm.</p>
        <form method="POST" action="{{ route('account.destroy') }}">
            @csrf @method('DELETE')
            <label for="delete_password">Current password</label>
            <input id="delete_password" type="password" name="password" autocomplete="current-password" required>
            @error('password', 'deletion')<div class="error" role="alert">{{ $message }}</div>@enderror
            <label class="check"><input type="checkbox" required> I understand this permanently deletes my account.</label>
            <button class="danger" type="submit">Permanently delete account</button>
        </form>
    </section>
</div>
@endsection
