{{-- Live service counts in a single quiet bar beneath the hero. --}}
@props(['stats' => []])
@if($stats !== [])
    <section class="home-statistics" aria-label="{{ __('Cambo Hub in numbers') }}">
        <ul class="grid grid-cols-2 lg:flex lg:divide-x lg:divide-line">
            @foreach($stats as $stat)
                <li class="min-w-0 lg:flex-1">
                    <a href="{{ $stat['url'] }}" class="group flex h-full items-center gap-3 rounded-xl p-4 transition hover:bg-primary-soft sm:px-6 sm:py-5">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-icon-tile"><x-ui.icon :name="$stat['icon']" :size="21" /></span>
                        <span class="min-w-0">
                            <strong class="block text-2xl font-bold tracking-tight text-ink tabular-nums" data-count-to="{{ $stat['value'] }}">{{ number_format($stat['value']) }}</strong>
                            <span class="block text-xs leading-relaxed text-ink-muted group-hover:text-primary">{{ $stat['label'] }}</span>
                        </span>
                        <span class="ml-auto hidden text-primary transition group-hover:translate-x-1 sm:block motion-reduce:transform-none" aria-hidden="true">&nearr;</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
