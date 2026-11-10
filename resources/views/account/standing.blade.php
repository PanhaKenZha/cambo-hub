@extends('layouts.app')
@section('title', __('Account standing'))
@section('page')
<div>
    <a href="{{ route('account.edit') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline"><span aria-hidden="true">←</span> {{ __('Account settings') }}</a>
    <h1 class="mt-3 text-3xl font-bold tracking-tight">{{ __('Account standing') }}</h1>
    <p class="mt-2 text-ink-muted">{{ __('Warnings on your account and posts that were not approved. If you think a decision is wrong, you can appeal it.') }}</p>
</div>
<div class="mt-8">
    @include('account._decisions', ['appealRoute' => route('appeals.store')])
</div>
@endsection
