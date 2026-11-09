@extends('layouts.app')
@section('title', 'Account settings')
@section('page')
<div class="space-y-6">
    <div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline"><span aria-hidden="true">←</span> Dashboard</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight">Account settings</h1>
    </div>

    <x-ui.card as="section" aria-labelledby="profile-heading">
        <h2 id="profile-heading" class="text-lg font-semibold">Your profile</h2>
        <p class="mt-1 text-sm text-ink-muted">Keep your name and email up to date.</p>
        <x-ui.error-summary class="mt-5" :ids="['current_password' => 'profile_password']" />
        <form method="POST" action="{{ route('account.update') }}" class="mt-6 space-y-5" data-loading>
            @csrf @method('PATCH')
            <x-ui.input name="name" label="Full name" :value="auth()->user()->name" autocomplete="name" maxlength="255" required />
            <x-ui.input name="email" type="email" label="Email address" :value="auth()->user()->email" autocomplete="email" required />
            <x-ui.input name="current_password" id="profile_password" type="password" label="Current password" hint="Required to change email." autocomplete="current-password" />
            <x-ui.button loading-text="Saving…">Save profile</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card as="section" aria-labelledby="password-heading">
        <h2 id="password-heading" class="text-lg font-semibold">Change password</h2>
        <x-ui.error-summary class="mt-5" bag="password" :ids="['current_password' => 'current_password', 'password' => 'new_password']" />
        <form method="POST" action="{{ route('account.password') }}" class="mt-6 space-y-5" data-loading>
            @csrf @method('PUT')
            <x-ui.input name="current_password" type="password" label="Current password" bag="password" autocomplete="current-password" required />
            <x-ui.input name="password" id="new_password" type="password" label="New password" hint="Use at least 8 characters." bag="password" autocomplete="new-password" minlength="8" required />
            <x-ui.input name="password_confirmation" type="password" label="Confirm new password" bag="password" autocomplete="new-password" required />
            <x-ui.button loading-text="Updating…">Update password</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card as="section" class="border-danger/40" aria-labelledby="delete-heading">
        <h2 id="delete-heading" class="text-lg font-semibold text-danger">Delete account</h2>
        <p class="mt-1 text-sm text-ink-muted">This permanently deletes your account. Enter your password to confirm.</p>
        <form method="POST" action="{{ route('account.destroy') }}" class="mt-6 space-y-5" data-loading>
            @csrf @method('DELETE')
            <x-ui.input name="password" id="delete_password" type="password" label="Current password" bag="deletion" autocomplete="current-password" required />
            <x-ui.checkbox id="confirm_deletion" required>I understand this permanently deletes my account.</x-ui.checkbox>
            <x-ui.button variant="danger" loading-text="Deleting…">Permanently delete account</x-ui.button>
        </form>
    </x-ui.card>
</div>
@endsection
