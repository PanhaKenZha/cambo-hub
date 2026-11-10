@extends('layouts.admin')
@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;
    use App\Enums\VerificationStatus;

    $isEditing = $company->exists;
    $canVerify = auth()->user()->can(PermissionArea::Jobs->permission(PermissionAction::Manage));
    $verificationColor = match ($company->verification_status) {
        VerificationStatus::Verified => 'green',
        VerificationStatus::Pending => 'amber',
        VerificationStatus::Rejected => 'red',
        default => 'gray',
    };
@endphp
@section('title', $isEditing ? $company->name : 'Add company')
@section('content')
@include('admin.jobs._nav')

<a href="{{ route('admin.jobs.companies.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Companies</a>
<div class="mt-3 flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? $company->name : 'Add company' }}</h1>
    @if($isEditing)
        <x-admin.badge :color="$verificationColor">{{ $company->isVerified() ? '✓ ' : '' }}{{ Str::headline($company->verification_status->value) }}</x-admin.badge>
        @unless($company->is_active)<x-admin.badge color="red">Inactive</x-admin.badge>@endunless
    @endif
</div>

<x-ui.error-summary class="mt-4" />

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_21rem]">
    <form method="POST" action="{{ $isEditing ? route('admin.jobs.companies.update', $company) : route('admin.jobs.companies.store') }}" enctype="multipart/form-data"
          id="company-form" class="min-w-0 space-y-6" data-loading>
        @csrf
        @if($isEditing) @method('PUT') @endif

        <x-admin.card title="Company profile">
            <div class="space-y-5">
                <x-admin.input name="name" label="Company name" :value="$company->name" maxlength="150" required />
                <x-admin.translatable-field name="description" label="About the company" type="textarea" rows="4" :value="$company->description" maxlength="2000"
                    hint="Shown on every job page from this company." />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="city" label="City / province" :value="$company->city" placeholder="e.g. Phnom Penh" />
                    <x-admin.input name="address" label="Address" :value="$company->address" />
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Contact">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.input name="website" type="url" label="Website" :value="$company->website" placeholder="https://" />
                <x-admin.input name="email" type="email" label="Email" :value="$company->email" />
                <x-admin.input name="phone" label="Phone" :value="$company->phone" />
            </div>
        </x-admin.card>

        <x-admin.card title="Account and files">
            <div class="space-y-5">
                <x-admin.input name="owner_email" type="email" label="Owner account email" :value="$company->owner?->email"
                    hint="The Cambo Hub user who manages this company and posts its jobs from the website. Leave empty for companies managed by admins." />
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="logo" class="block text-sm font-medium text-ink">Logo</label>
                        <div class="mt-1.5 flex items-center gap-3">
                            @if($company->logoUrl())
                                <img src="{{ $company->logoUrl() }}" alt="" class="size-12 shrink-0 rounded-lg object-cover ring-1 ring-line">
                            @endif
                            <input id="logo" name="logo" type="file" accept="image/*"
                                   class="block w-full text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary">
                        </div>
                        <p class="mt-1.5 text-xs text-ink-subtle">Square works best, e.g. 400 × 400 px.</p>
                        @error('logo')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="verification_document" class="block text-sm font-medium text-ink">Business registration document</label>
                        <input id="verification_document" name="verification_document" type="file" accept=".pdf,.jpg,.jpeg,.png"
                               class="mt-1.5 block w-full text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary">
                        <p class="mt-1.5 text-xs text-ink-subtle">PDF, JPG or PNG up to 10 MB. Kept private. Uploading it asks for verification.</p>
                        @error('verification_document')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
                <label class="flex items-center gap-2.5 text-sm text-ink">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="size-4 rounded accent-primary" @checked(old('is_active', $company->is_active))>
                    Active — the company can be chosen for new jobs
                </label>
            </div>
        </x-admin.card>

        <div class="flex items-center justify-between gap-3">
            <div>
                @if($isEditing)
                    @can(PermissionArea::Jobs->permission(PermissionAction::Delete))
                        <button type="submit" form="delete-form" class="cursor-pointer rounded text-sm font-semibold text-danger hover:underline">Delete company</button>
                    @endcan
                @endif
            </div>
            <div class="flex gap-3">
                <x-admin.button variant="secondary" :href="route('admin.jobs.companies.index')">Cancel</x-admin.button>
                <x-admin.button loading-text="Saving…">{{ $isEditing ? 'Save company' : 'Add company' }}</x-admin.button>
            </div>
        </div>
    </form>

    @if($isEditing)
        <div class="space-y-6">
            <x-admin.card title="Verification">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-ink-subtle">Status</dt><dd><x-admin.badge :color="$verificationColor">{{ Str::headline($company->verification_status->value) }}</x-admin.badge></dd></div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-subtle">Document</dt>
                        <dd>
                            @if($company->verification_document_path)
                                <a href="{{ route('admin.jobs.companies.document', $company) }}" class="font-semibold text-primary hover:underline">Download</a>
                            @else
                                <span class="text-ink-muted">Not uploaded</span>
                            @endif
                        </dd>
                    </div>
                    @if($company->verifier)
                        <div class="flex justify-between gap-3"><dt class="text-ink-subtle">Decided by</dt><dd class="text-right text-ink">{{ $company->verifier->name }}@if($company->verified_at)<br><span class="text-xs text-ink-subtle">{{ $company->verified_at->format('j M Y') }}</span>@endif</dd></div>
                    @endif
                </dl>
                @if($company->verification_note)
                    <p class="mt-3 rounded-lg bg-surface-muted p-3 text-sm text-ink-muted">{{ $company->verification_note }}</p>
                @endif

                @if($canVerify)
                    <form method="POST" action="{{ route('admin.jobs.companies.verify', $company) }}" class="mt-5 space-y-3 border-t border-line pt-5" data-loading>
                        @csrf
                        <div>
                            <label for="verification-note" class="block text-sm font-medium text-ink">Note</label>
                            <textarea id="verification-note" name="note" rows="2" maxlength="1000" class="form-control mt-1.5 rounded-lg shadow-sm"
                                      placeholder="Required when rejecting">{{ old('note') }}</textarea>
                            @error('note', 'verification')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($company->isVerified())
                                <x-admin.button variant="secondary" name="decision" value="revoke" class="w-full">Remove verification</x-admin.button>
                            @else
                                <x-admin.button name="decision" value="verify" class="flex-1">✓ Verify</x-admin.button>
                                <x-admin.button variant="danger" name="decision" value="reject" class="flex-1">Reject</x-admin.button>
                            @endif
                        </div>
                        @unless($company->verification_document_path || $company->isVerified())
                            <p class="text-xs text-ink-subtle">No document yet. Verify only if you have checked the business another way.</p>
                        @endunless
                    </form>
                @endif
            </x-admin.card>

            <x-admin.card title="Jobs">
                <p class="text-sm text-ink-muted">{{ $company->job_listings_count }} {{ Str::plural('job', $company->job_listings_count) }} posted.</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    @if($company->job_listings_count > 0)
                        <a href="{{ route('admin.jobs.listings.index', ['company' => $company->id]) }}" class="rounded text-sm font-semibold text-primary hover:underline">See jobs</a>
                    @endif
                    @can(PermissionArea::Jobs->permission(PermissionAction::Create))
                        <a href="{{ route('admin.jobs.listings.create', ['company' => $company->id]) }}" class="rounded text-sm font-semibold text-primary hover:underline">+ Post a job</a>
                    @endcan
                </div>
            </x-admin.card>
        </div>

        <form method="POST" action="{{ route('admin.jobs.companies.destroy', $company) }}" id="delete-form" onsubmit="return confirm('Delete this company?')">@csrf @method('DELETE')</form>
    @endif
</div>
@endsection
