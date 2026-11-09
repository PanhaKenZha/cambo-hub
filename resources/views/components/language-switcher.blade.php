@props(['id' => 'language-menu'])
@inject('languageService', 'App\Services\LocaleService')
@php($locales = $languageService->languages())
@php($currentLanguage = $locales[app()->getLocale()] ?? $locales['en'])
<div {{ $attributes->class(['relative']) }}>
    <button type="button" data-disclosure aria-controls="{{ $id }}" aria-expanded="false"
        class="flex h-10 cursor-pointer items-center gap-2 rounded-xl border border-line bg-surface px-2.5 text-xs font-semibold text-ink transition hover:border-primary aria-expanded:border-primary">
        <span class="sr-only">{{ __('Change language') }}:</span>
        <img class="h-3.5 w-5 rounded-[3px] object-cover ring-1 ring-black/10" src="{{ asset($currentLanguage['flag']) }}" width="24" height="16" alt="">
        <span class="hidden sm:inline" lang="{{ app()->getLocale() }}">{{ $currentLanguage['name'] }}</span>
        <svg class="size-3.5 text-ink-subtle transition [[aria-expanded=true]>&]:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
    </button>
    <ul id="{{ $id }}" hidden class="absolute right-0 z-50 mt-2 w-56 rounded-2xl border border-line bg-surface-raised p-1.5 shadow-card-hover">
        @foreach($locales as $code => $language)
            <li>
                <a href="{{ route('locale.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}" @if(app()->getLocale() === $code) aria-current="true" @endif
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-ink transition hover:bg-surface-muted aria-[current=true]:bg-primary-soft aria-[current=true]:font-semibold aria-[current=true]:text-primary">
                    <img class="h-4 w-6 rounded-[3px] object-cover ring-1 ring-black/10" src="{{ asset($language['flag']) }}" width="24" height="16" alt="">
                    <span>{{ $language['name'] }}</span>
                    @if(app()->getLocale() === $code)<svg class="ml-auto size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>@endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
