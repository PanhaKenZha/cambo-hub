@extends('layouts.admin')
@section('title', 'Issue violation')
@section('content')
<a href="{{ route('admin.violations.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; User violations</a>
<h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Issue a violation</h1>
<p class="mt-1 text-sm text-slate-500">The user is emailed the reason and can appeal. Suspensions and bans sign them out immediately.</p>

<x-admin.card class="mt-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.violations.store') }}" class="space-y-5">
        @csrf
        <x-admin.input name="email" type="email" label="User's email" :value="$email" required />
        @error('user')<p class="-mt-3 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror

        <fieldset>
            <legend class="text-sm font-medium text-slate-700">Penalty</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach(\App\Enums\ViolationType::cases() as $type)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-slate-200 has-checked:bg-brand-50/50 has-checked:ring-brand-500">
                        <input type="radio" name="type" value="{{ $type->value }}" class="mt-0.5 accent-brand-600" required @checked(old('type', 'warning') === $type->value)
                               onchange="document.getElementById('suspension-days').hidden = this.value !== 'suspension'">
                        <span>
                            <span class="block text-sm font-medium text-slate-900">{{ $type->label() }}</span>
                            <span class="block text-xs text-slate-500">{{ match ($type) {
                                \App\Enums\ViolationType::Warning => 'A formal notice. No restrictions.',
                                \App\Enums\ViolationType::Strike => 'Counts towards repeat-offender history.',
                                \App\Enums\ViolationType::Suspension => 'Blocks sign-in for a set number of days.',
                                \App\Enums\ViolationType::Ban => 'Blocks sign-in permanently.',
                            } }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('type')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        </fieldset>

        <div id="suspension-days" @if(old('type') !== 'suspension') hidden @endif>
            <x-admin.input name="suspension_days" type="number" label="Suspension length (days)" min="1" max="365" :value="7" />
        </div>

        <div>
            <label for="reason" class="block text-sm font-medium text-slate-700">Reason</label>
            <textarea id="reason" name="reason" rows="4" required minlength="5" class="mt-1.5 block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm shadow-sm ring-1 ring-slate-300 ring-inset focus:ring-2 focus:ring-brand-500 focus:outline-none" placeholder="Which rule was broken? The user will see this.">{{ old('reason') }}</textarea>
            @error('reason')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
            <x-admin.button variant="secondary" :href="route('admin.violations.index')">Cancel</x-admin.button>
            <x-admin.button variant="danger">Issue violation</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
