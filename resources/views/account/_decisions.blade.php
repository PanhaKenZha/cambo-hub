{{--
    Moderation decisions about a user, each with an appeal form.
    Needs: $violations, $rejectedSubmissions, $appeals, $appealRoute.
--}}
@php
    $pendingAppeal = fn ($subject) => $subject->appeals->firstWhere('status', \App\Enums\AppealStatus::Pending);
    $decisions = collect();
    foreach ($violations as $violation) {
        $decisions->push([
            'subject' => 'violation', 'model' => $violation,
            'title' => __($violation->type->label()),
            'reason' => $violation->reason,
            'meta' => $violation->expires_at ? __('Until :date', ['date' => $violation->expires_at->translatedFormat('j M Y, H:i')]) : null,
        ]);
    }
    foreach ($rejectedSubmissions as $submission) {
        $decisions->push([
            'subject' => 'submission', 'model' => $submission,
            'title' => __('Not approved: :title', ['title' => $submission->title]),
            'reason' => $submission->rejection_reason,
            'meta' => $submission->reviewed_at?->translatedFormat('j M Y'),
        ]);
    }
@endphp

@if(session('status'))
    <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
@endif
<x-ui.error-summary bag="appeal" class="mb-6" />

@if($decisions->isEmpty())
    <x-ui.card>
        <p class="font-semibold text-ink">{{ __('Your account is in good standing.') }}</p>
        <p class="mt-1 text-sm text-ink-muted">{{ __('There are no warnings or rejected posts on your account.') }}</p>
    </x-ui.card>
@else
    <ul class="space-y-4">
        @foreach($decisions as $decision)
            @php($pending = $pendingAppeal($decision['model']))
            <li>
                <x-ui.card padding="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h3 class="font-semibold text-ink">{{ $decision['title'] }}</h3>
                        @if($decision['meta'])<span class="text-xs text-ink-subtle">{{ $decision['meta'] }}</span>@endif
                    </div>
                    @if($decision['reason'])
                        <p class="mt-2 text-sm text-ink-muted">{{ __('Reason') }}: {{ $decision['reason'] }}</p>
                    @endif

                    @if($pending)
                        <p class="mt-4 rounded-lg bg-warning-soft px-3 py-2 text-sm font-medium text-warning">{{ __('Your appeal is being reviewed. We will email you the result.') }}</p>
                    @else
                        <details class="mt-4" @if(old('subject') === $decision['subject'] && (int) old('id') === $decision['model']->id) open @endif>
                            <summary class="cursor-pointer list-none text-sm font-semibold text-primary hover:underline">{{ __('Appeal this decision') }}</summary>
                            <form method="POST" action="{{ $appealRoute }}" class="mt-3 space-y-3" data-loading>
                                @csrf
                                <input type="hidden" name="subject" value="{{ $decision['subject'] }}">
                                <input type="hidden" name="id" value="{{ $decision['model']->id }}">
                                <x-ui.textarea name="message" :id="'appeal-'.$decision['subject'].'-'.$decision['model']->id" :label="__('Why is this decision wrong?')" rows="4" bag="appeal" required
                                    :hint="__('A different moderator will review your appeal.')" />
                                <x-ui.button size="sm" :loading-text="__('Sending…')">{{ __('Send appeal') }}</x-ui.button>
                            </form>
                        </details>
                    @endif
                </x-ui.card>
            </li>
        @endforeach
    </ul>
@endif

@if($appeals->where('status', '!=', \App\Enums\AppealStatus::Pending)->isNotEmpty())
    <h2 class="mt-10 text-lg font-semibold text-ink">{{ __('Past appeals') }}</h2>
    <ul class="mt-3 divide-y divide-line rounded-2xl border border-line bg-surface">
        @foreach($appeals->where('status', '!=', \App\Enums\AppealStatus::Pending) as $appeal)
            <li class="p-4 text-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-ink">{{ $appeal->subjectLabel() }}</span>
                    <x-ui.badge :variant="$appeal->status === \App\Enums\AppealStatus::Overturned ? 'success' : 'neutral'">{{ __($appeal->status->label()) }}</x-ui.badge>
                </div>
                @if($appeal->decision_note)<p class="mt-1 text-ink-muted">{{ $appeal->decision_note }}</p>@endif
            </li>
        @endforeach
    </ul>
@endif
