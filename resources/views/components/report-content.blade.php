{{-- "Report" link for live content. Signed-in visitors pick a reason; guests are asked to sign in. --}}
@props(['type', 'id'])
@php($reportErrors = $errors->getBag('report'))
<div {{ $attributes->class('text-sm') }}>
    @if(session('report_status'))
        <x-ui.alert variant="success">{{ session('report_status') }}</x-ui.alert>
    @else
        <details class="group" @if($reportErrors->any()) open @endif>
            <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded font-medium text-ink-subtle hover:text-danger">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg>
                {{ __('Report a problem') }}
            </summary>
            <div class="mt-3 rounded-xl bg-surface-muted p-4">
                @auth
                    <form method="POST" action="{{ route('content.report', [$type, $id]) }}" class="space-y-3" data-loading>
                        @csrf
                        <fieldset>
                            <legend class="font-medium text-ink">{{ __('What is wrong?') }}</legend>
                            <div class="mt-2 grid gap-1.5 sm:grid-cols-2">
                                @foreach(\App\Enums\ReportReason::cases() as $reason)
                                    <label class="flex items-center gap-2 text-ink-muted">
                                        <input type="radio" name="reason" value="{{ $reason->value }}" class="accent-primary" required @checked(old('reason') === $reason->value)>
                                        {{ __($reason->label()) }}
                                    </label>
                                @endforeach
                            </div>
                            @if($reportErrors->has('reason'))<p class="mt-1.5 text-danger" role="alert">{{ $reportErrors->first('reason') }}</p>@endif
                        </fieldset>
                        <x-ui.textarea name="details" :label="__('Details (optional)')" rows="2" bag="report" maxlength="1000" />
                        <x-ui.button size="sm" variant="secondary" :loading-text="__('Sending…')">{{ __('Send report') }}</x-ui.button>
                    </form>
                @else
                    <p class="text-ink-muted"><a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">{{ __('Sign in') }}</a> {{ __('to report this.') }}</p>
                @endauth
            </div>
        </details>
    @endif
</div>
