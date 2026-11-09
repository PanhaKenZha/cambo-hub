@extends('layouts.admin')
@section('title', 'Issue violation')
@section('content')
<a href="{{ route('admin.violations.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; User violations</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-ink">Issue a violation</h1>
<p class="mt-1 text-sm text-ink-muted">The user is emailed the reason and can appeal. Suspensions and bans sign them out immediately.</p>

<x-admin.card class="mt-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.violations.store') }}" class="space-y-5" data-loading>
        <x-ui.error-summary :ids="['user' => 'email']" />
        @csrf
        <x-admin.input name="email" type="email" label="User's email" :value="$email" required
                       :aria-invalid="$errors->has('user') && ! $errors->has('email') ? 'true' : null"
                       :aria-describedby="$errors->has('user') && ! $errors->has('email') ? 'user-error' : null" />
        @error('user')<p id="user-error" class="-mt-3 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror

        <fieldset id="type" tabindex="-1" class="focus:outline-none" @error('type') aria-describedby="type-error" @enderror>
            <legend class="text-sm font-medium text-ink">Penalty</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach(\App\Enums\ViolationType::cases() as $type)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-line-strong hover:bg-surface-muted has-checked:bg-primary-soft has-checked:ring-primary">
                        <input type="radio" name="type" value="{{ $type->value }}" class="mt-0.5 accent-brand-600 dark:accent-brand-300" required @checked(old('type', 'warning') === $type->value)
                               onchange="document.getElementById('suspension-days').hidden = this.value !== 'suspension'">
                        <span>
                            <span class="block text-sm font-medium text-ink">{{ $type->label() }}</span>
                            <span class="block text-xs text-ink-muted">{{ match ($type) {
                                \App\Enums\ViolationType::Warning => 'A formal notice. No restrictions.',
                                \App\Enums\ViolationType::Strike => 'Counts towards repeat-offender history.',
                                \App\Enums\ViolationType::Suspension => 'Blocks sign-in for a set number of days.',
                                \App\Enums\ViolationType::Ban => 'Blocks sign-in permanently.',
                            } }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('type')<p id="type-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
        </fieldset>

        <div id="suspension-days" @if(old('type') !== 'suspension') hidden @endif>
            <x-admin.input name="suspension_days" type="number" label="Suspension length (days)" min="1" max="365" :value="7" />
        </div>

        <div>
            <label for="reason" class="block text-sm font-medium text-ink">Reason</label>
            <textarea id="reason" name="reason" rows="4" required minlength="5" @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror class="form-control mt-1.5 rounded-lg shadow-sm" placeholder="Which rule was broken? The user will see this.">{{ old('reason') }}</textarea>
            @error('reason')<p id="reason-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-5">
            <x-admin.button variant="secondary" :href="route('admin.violations.index')">Cancel</x-admin.button>
            <x-admin.button variant="danger" loading-text="Issuing…">Issue violation</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
