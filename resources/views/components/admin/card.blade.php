@props(['title' => null, 'description' => null])

<section {{ $attributes->class('rounded-2xl bg-white shadow-sm ring-1 ring-slate-200') }}>
    @if($title)
        <header class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
            @if($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div class="p-6">{{ $slot }}</div>
</section>
