@extends('layouts.public')
@section('title', __('Contact us'))
@section('description', __('Get in touch with Cambo Hub for account help, business inquiries, feedback, and general questions.'))
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    <header class="max-w-2xl animate-reveal">
        <x-ui.eyebrow>{{ __('LET’S START A CONVERSATION') }}</x-ui.eyebrow>
        <h1 class="mt-4 text-4xl leading-tight font-bold tracking-tight sm:text-5xl">{{ __('Good connections') }}<br><span class="text-primary">{{ __('start with hello.') }}</span></h1>
        <p class="mt-4 text-base text-ink-muted">{{ __('Have a question, an idea, or something to share? We’d like to hear from you.') }}</p>
    </header>

    <div class="mt-10 grid items-start gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:gap-16">
        <aside class="space-y-6">
            <figure class="skeleton overflow-hidden rounded-3xl bg-primary-soft">
                <img src="{{ asset('images/backgrounds/customer-support.webp') }}" width="387" height="386" alt="{{ __('Headphones and conversation symbols representing customer support') }}" fetchpriority="high" data-placeholder class="aspect-[16/10] w-full object-cover lg:aspect-[4/3]">
            </figure>
            <h2 class="text-xl font-semibold">{{ __('How can we help?') }}</h2>
            <ul class="space-y-3">
                @foreach([
                    ['icon' => 'profile', 'title' => 'Account & access', 'body' => 'Questions about signing in or managing your account.', 'link' => route('password.request'), 'link_label' => 'Reset your password'],
                    ['icon' => 'business', 'title' => 'Business & partnerships', 'body' => 'Tell us about your business or a collaboration idea.'],
                    ['icon' => 'messages', 'title' => 'Ideas & feedback', 'body' => 'Help shape a better Cambo Hub experience.'],
                ] as $topic)
                    <li class="flex gap-4 rounded-2xl border border-line bg-surface p-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-icon-tile"><img src="{{ asset('images/icons/'.$topic['icon'].'-blue.svg') }}" width="22" height="22" alt=""></span>
                        <div>
                            <h3 class="text-sm font-semibold">{{ __($topic['title']) }}</h3>
                            <p class="mt-0.5 text-sm text-ink-muted">{{ __($topic['body']) }}</p>
                            @isset($topic['link'])<a href="{{ $topic['link'] }}" class="mt-1.5 inline-block text-xs font-semibold text-primary hover:underline">{{ __($topic['link_label']) }} <span aria-hidden="true">→</span></a>@endisset
                        </div>
                    </li>
                @endforeach
            </ul>
        </aside>

        <x-ui.card as="section" aria-labelledby="contact-form-heading">
            <x-ui.eyebrow>{{ __('WE’RE LISTENING') }}</x-ui.eyebrow>
            <h2 id="contact-form-heading" class="mt-3 text-2xl font-bold tracking-tight">{{ __('Send us a message') }}</h2>
            <p class="mt-1 text-sm text-ink-muted">{{ __('Choose a topic and tell us how we can help.') }}</p>

            @if(session('contact_status'))
                <x-ui.alert variant="success" class="mt-5">{{ __(session('contact_status')) }}</x-ui.alert>
            @endif
            <x-ui.error-summary class="mt-5" />

            <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-5" data-loading>
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('Full name')" :value="auth()->user()?->name" autocomplete="name" maxlength="120" required />
                    <x-ui.input name="email" type="email" :label="__('Email address')" :value="auth()->user()?->email" autocomplete="email" maxlength="255" required />
                </div>
                <x-ui.select name="topic" :label="__('What is this about?')" :placeholder="__('Select a topic')" required
                    :options="collect(['general' => 'General question', 'account' => 'Account & access', 'business' => 'Business & partnerships', 'feedback' => 'Ideas & feedback'])->map(fn ($label) => __($label))->all()" />
                <x-ui.input name="subject" :label="__('Subject')" maxlength="180" required />
                <x-ui.textarea name="message" :label="__('Your message')" :hint="__('10–5,000 characters. Please don’t include passwords or payment details.')" rows="6" minlength="10" maxlength="5000" required />
                <x-ui.button class="w-full" size="lg" :loading-text="__('Send message').'…'">{{ __('Send message') }} <span aria-hidden="true">→</span></x-ui.button>
                <p class="text-center text-xs text-ink-subtle">{{ __('We’ll use these details to respond to your inquiry. Fields marked * are required.') }}</p>
            </form>
        </x-ui.card>
    </div>

    <section class="mt-14 border-t border-line pt-10 text-center">
        <h2 class="text-2xl font-semibold">{{ __('New to Cambo Hub?') }}</h2>
        <p class="mt-2 text-sm text-ink-muted">{{ __('Get to know the purpose behind our platform.') }}</p>
        <div class="mt-5 flex flex-wrap justify-center gap-3">
            <x-ui.button :href="route('mission')" variant="secondary">{{ __('Our mission') }} <span aria-hidden="true">→</span></x-ui.button>
            <x-ui.button :href="route('vision')" variant="secondary">{{ __('Our vision') }} <span aria-hidden="true">→</span></x-ui.button>
        </div>
    </section>
</div>
@endsection
