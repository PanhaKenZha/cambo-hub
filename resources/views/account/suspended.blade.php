@extends('layouts.app')
@section('title', __('Account suspended'))
@section('page')
<x-ui.alert variant="error" :title="__('Your account is suspended')">
    {{ $user->suspension_reason ?: __('Your account broke the platform rules.') }}
    {{ __('You cannot sign in until the suspension ends or an appeal is accepted.') }}
</x-ui.alert>
<div class="mt-8">
    @include('account._decisions', [
        'violations' => $violations->filter(fn ($violation) => in_array($violation->type, $blockingTypes, true)),
        'rejectedSubmissions' => collect(),
        'appealRoute' => route('account.suspended.appeal'),
    ])
</div>
<p class="mt-8 text-sm text-ink-muted">
    {{ __('Need help?') }} <a href="{{ route('contact') }}" class="font-semibold text-primary hover:underline">{{ __('Contact us') }}</a>
</p>
@endsection
