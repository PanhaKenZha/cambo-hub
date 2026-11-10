@extends('layouts.admin')
@php
    use App\Enums\ApplyMethod;
    use App\Enums\EmploymentType;
    use App\Enums\JobStatus;
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $isEditing = $job->exists;
    $canPublish = auth()->user()->can(PermissionArea::Jobs->permission(PermissionAction::Manage));
    $status = old('status', $job->status === JobStatus::Published ? 'published' : 'draft');
    $canSubmit = in_array($job->status, [JobStatus::Draft, JobStatus::Rejected], true);
    $applyMethod = old('apply_method', $job->apply_method?->value ?? ApplyMethod::OnSite->value);
@endphp
@section('title', $isEditing ? 'Edit job' : 'Post a job')
@section('content')
@include('admin.jobs._nav')

<a href="{{ route('admin.jobs.listings.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Job listings</a>
<div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex min-w-0 flex-wrap items-center gap-3">
        <h1 class="truncate text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? $job->translate('title', 'en') : 'Post a job' }}</h1>
        @if($isEditing)<x-admin.badge :color="$job->stateColor()">{{ $job->stateLabel() }}</x-admin.badge>@endif
        @if($job->isFeaturedNow())<x-admin.badge color="amber">★ Featured until {{ $job->featured_until?->format('j M') ?? 'removed' }}</x-admin.badge>@endif
    </div>
    @if($isEditing)
        <div class="flex shrink-0 flex-wrap gap-2">
            @if($job->applications_count > 0)
                <x-admin.button variant="secondary" :href="route('admin.jobs.applications.index', ['job' => $job->id])">{{ $job->applications_count }} {{ Str::plural('applicant', $job->applications_count) }}</x-admin.button>
            @endif
            @if($job->isLive())
                <x-admin.button variant="secondary" :href="$job->publicUrl()" target="_blank">View live &nearr;</x-admin.button>
            @endif
        </div>
    @endif
</div>

@if($latestReview && $job->status === JobStatus::InReview)
    <x-ui.alert variant="warning" class="mt-4">
        Waiting for review in the <a href="{{ route('admin.approvals.show', $latestReview) }}" class="font-semibold underline">Approval center</a>. A jobs manager will publish it or send it back.
    </x-ui.alert>
@elseif($latestReview && $job->status === JobStatus::Rejected)
    <x-ui.alert variant="error" class="mt-4">
        Returned by {{ $latestReview->reviewer?->name ?? 'a reviewer' }}: {{ $latestReview->rejection_reason }}. Update the job and send it again.
    </x-ui.alert>
@endif
@if($isEditing && ! $job->company->is_active)
    <x-ui.alert variant="warning" class="mt-4">{{ $job->company->name }} is deactivated. It can't be chosen for new jobs until it is reactivated.</x-ui.alert>
@endif

<x-ui.error-summary class="mt-4" />

<form method="POST" action="{{ $isEditing ? route('admin.jobs.listings.update', $job) : route('admin.jobs.listings.store') }}"
      id="job-form" class="mt-6 grid gap-6 xl:grid-cols-[1fr_21rem]" data-loading>
    @csrf
    @if($isEditing) @method('PUT') @endif

    <div class="min-w-0 space-y-6">
        <x-admin.card title="The job">
            <div class="space-y-5">
                <x-admin.translatable-field name="title" label="Job title" :value="$job->title" maxlength="150" required placeholder="e.g. Store Supervisor" />
                <x-admin.translatable-field name="description" label="Description" type="textarea" rows="16" :value="$job->description" required
                    hint="Responsibilities, requirements and benefits. Markdown: ## Heading, **bold**, - list item. HTML is not allowed." />
            </div>
        </x-admin.card>

        <x-admin.card title="Salary" description="Leave both empty to show &ldquo;Negotiable&rdquo;.">
            <div class="grid gap-4 sm:grid-cols-4">
                <x-admin.input name="salary_min" type="number" min="0" label="From" :value="$job->salary_min" />
                <x-admin.input name="salary_max" type="number" min="0" label="To" :value="$job->salary_max" />
                <x-admin.select name="salary_currency" label="Currency">
                    @foreach(['USD' => 'USD ($)', 'KHR' => 'KHR (៛)'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('salary_currency', $job->salary_currency) === $value)>{{ $label }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.select name="salary_period" label="Per">
                    @foreach(['hour' => 'Hour', 'day' => 'Day', 'month' => 'Month', 'year' => 'Year'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('salary_period', $job->salary_period) === $value)>{{ $label }}</option>
                    @endforeach
                </x-admin.select>
            </div>
        </x-admin.card>

        <x-admin.card title="How candidates apply">
            <fieldset>
                <legend class="sr-only">Application method</legend>
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach([
                        ApplyMethod::OnSite->value => ['On Cambo Hub', 'Candidates upload a CV here. You see applications under Applications.'],
                        ApplyMethod::ExternalUrl->value => ['Company website', 'The Apply button opens the company\'s careers page.'],
                        ApplyMethod::Email->value => ['By email', 'The Apply button opens an email to the company.'],
                    ] as $value => [$label, $help])
                        <label class="flex cursor-pointer flex-col gap-1 rounded-lg p-3 ring-1 ring-line-strong has-checked:bg-primary-soft has-checked:ring-2 has-checked:ring-primary">
                            <span class="flex items-center gap-2 text-sm font-semibold text-ink">
                                <input type="radio" name="apply_method" value="{{ $value }}" class="accent-primary" @checked($applyMethod === $value)> {{ $label }}
                            </span>
                            <span class="text-xs text-ink-muted">{{ $help }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-admin.input name="apply_url" type="url" label="Careers page link" :value="$job->apply_url" placeholder="https://" hint="Needed for “Company website”." />
                <x-admin.input name="apply_email" type="email" label="Application email" :value="$job->apply_email" placeholder="jobs@company.com" hint="Needed for “By email”." />
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Publishing">
            @if($canPublish)
                <div class="space-y-4">
                    <fieldset>
                        <legend class="text-sm font-medium text-ink">Status</legend>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach(['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                                <label class="flex cursor-pointer items-center justify-center rounded-lg px-3 py-2 text-sm font-medium text-ink-muted ring-1 ring-line-strong has-checked:bg-primary-soft has-checked:text-primary has-checked:ring-primary">
                                    <input type="radio" name="status" value="{{ $value }}" class="sr-only" @checked($status === $value)> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-admin.input name="published_at" type="datetime-local" label="Publish on" :value="$job->published_at?->format('Y-m-d\TH:i')"
                        hint="Leave empty to publish now. A future date schedules the job." />
                </div>
            @else
                <p class="text-sm text-ink-muted">
                    @switch($job->status)
                        @case(JobStatus::InReview) This job is waiting for a reviewer. You can still fix mistakes. @break
                        @case(JobStatus::Published) This job is live. Your changes appear as soon as you save. @break
                        @case(JobStatus::Closed) This job is closed and no longer accepts applications. @break
                        @default Save your draft, then send it for review. Jobs from verified companies are published straight away.
                    @endswitch
                </p>
            @endif
            <div class="mt-4">
                <x-admin.input name="closes_at" type="date" label="Applications close" :value="$job->closes_at?->format('Y-m-d')"
                    :hint="'Leave empty to close '.(int) app(\App\Services\PlatformSettings::class)->get('jobs.default_listing_days', 30).' days after publishing.'" />
            </div>
        </x-admin.card>

        <x-admin.card title="Details">
            <div class="space-y-4">
                <div>
                    <x-admin.select name="company_id" label="Company" required>
                        <option value="">Choose a company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" @selected((int) old('company_id', $job->company_id) === $company->id)>{{ $company->name }}{{ $company->isVerified() ? ' ✓' : '' }}{{ $company->is_active ? '' : ' (inactive)' }}</option>
                        @endforeach
                    </x-admin.select>
                    @can(PermissionArea::Jobs->permission(PermissionAction::Create))
                        <a href="{{ route('admin.jobs.companies.create') }}" class="mt-1.5 inline-block rounded text-xs font-medium text-primary hover:text-primary-hover">+ Add a company</a>
                    @endcan
                </div>
                <x-admin.select name="job_category_id" label="Category">
                    <option value="">No category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('job_category_id', $job->job_category_id) === $category->id)>{{ $category->translate('name', 'en') }}{{ $category->is_active ? '' : ' (hidden)' }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.select name="employment_type" label="Employment type">
                    @foreach(EmploymentType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('employment_type', $job->employment_type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.input name="location" label="Location" :value="$job->location" placeholder="e.g. Phnom Penh" />
                <label class="flex items-center gap-2.5 text-sm text-ink">
                    <input type="hidden" name="is_remote" value="0">
                    <input type="checkbox" name="is_remote" value="1" class="size-4 rounded accent-primary" @checked(old('is_remote', $job->is_remote))>
                    Remote work possible
                </label>
                <div>
                    <label for="slug" class="block text-sm font-medium text-ink">Web address</label>
                    <div class="mt-1.5 flex rounded-lg bg-surface shadow-sm ring-1 ring-line-strong focus-within:ring-2 focus-within:ring-primary">
                        <span class="flex items-center pl-3 text-sm text-ink-subtle">/jobs/</span>
                        <input id="slug" name="slug" value="{{ old('slug', $job->slug) }}" placeholder="from the title" class="block w-full rounded-r-lg border-0 bg-transparent py-2.5 pr-3 text-sm text-ink focus:outline-none">
                    </div>
                    @error('slug')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.card>

        @if($isEditing)
            <x-admin.card title="Activity">
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-ink-subtle">Views</dt><dd class="font-semibold text-ink tabular-nums">{{ number_format($job->views_count) }}</dd></div>
                    <div><dt class="text-ink-subtle">Applicants</dt><dd class="font-semibold text-ink tabular-nums">{{ $job->applications_count }}</dd></div>
                    <div><dt class="text-ink-subtle">Published</dt><dd class="text-ink">{{ $job->published_at?->format('j M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-subtle">Closes</dt><dd class="text-ink">{{ $job->closes_at?->format('j M Y') ?? '—' }}</dd></div>
                </dl>
            </x-admin.card>
        @endif

        <div class="flex flex-col gap-2">
            @if($canPublish)
                <x-admin.button class="w-full py-3" loading-text="Saving…">{{ $isEditing ? 'Save job' : 'Create job' }}</x-admin.button>
            @else
                <x-admin.button variant="secondary" class="w-full py-3" loading-text="Saving…">{{ $job->status === JobStatus::Published ? 'Save changes' : 'Save draft' }}</x-admin.button>
                @if($canSubmit)
                    <x-admin.button name="intent" value="submit" class="w-full py-3" loading-text="Sending…">Save &amp; send for review</x-admin.button>
                @endif
            @endif
        </div>
    </div>
</form>

@if($isEditing)
    <div class="mt-6 flex flex-wrap items-center gap-6">
        @if($canPublish && $job->isLive())
            <form method="POST" action="{{ route('admin.jobs.listings.feature', $job) }}">
                @csrf
                <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-ink-muted hover:text-ink">{{ $job->isFeaturedNow() ? 'Stop featuring' : '★ Feature on the homepage' }}</button>
            </form>
            <form method="POST" action="{{ route('admin.jobs.listings.close', $job) }}" onsubmit="return confirm('Close this job now? It stops accepting applications.')">
                @csrf
                <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-ink-muted hover:text-ink">Close applications now</button>
            </form>
        @endif
        @can(PermissionArea::Jobs->permission(PermissionAction::Delete))
            <form method="POST" action="{{ route('admin.jobs.listings.destroy', $job) }}" onsubmit="return confirm('Delete this job and its applications? It is removed from the website, homepage and search.')">
                @csrf @method('DELETE')
                <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-danger hover:underline">Delete job</button>
            </form>
        @endcan
    </div>
@endif
@endsection
