@props(['layout' => 'desktop'])
@inject('cms', 'App\Services\CmsService')
@foreach($cms->setting('navigation', []) as $item)
    @php($isCurrent = isset($item['route']) && request()->routeIs($item['route']))
    <a href="{{ $cms->linkUrl($item) }}" @if($isCurrent) aria-current="page" @endif
        @class([
            'group flex items-center gap-2 rounded-xl font-medium transition',
            'px-3 py-2 text-[13px] text-ink-muted hover:bg-surface-muted hover:text-ink aria-[current=page]:bg-primary-soft aria-[current=page]:text-primary' => $layout === 'desktop',
            'px-4 py-3 text-sm text-ink hover:bg-surface-muted aria-[current=page]:bg-primary-soft aria-[current=page]:text-primary' => $layout === 'mobile',
        ])>
        @if(! empty($item['icon']))<x-ui.icon :name="$item['icon']" :size="18" class="size-[18px] opacity-70 group-aria-[current=page]:opacity-100" />@endif
        <span>{{ __($item['label']) }}</span>
    </a>
@endforeach
