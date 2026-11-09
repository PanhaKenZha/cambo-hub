{{-- Lists every validation error with a link that jumps to the field. Pass ids when they differ from field names. --}}
@props(['bag' => 'default', 'title' => null, 'ids' => []])
@php($bagErrors = $errors->getBag($bag))
@if($bagErrors->any())
    <x-ui.alert variant="error" :title="$title ?? __('Please check the fields below and try again.')" {{ $attributes }}>
        <ul class="list-inside list-disc space-y-0.5">
            @foreach($bagErrors->messages() as $field => $messages)
                <li><a class="underline underline-offset-2 hover:text-danger" href="#{{ $ids[$field] ?? $field }}">{{ $messages[0] }}</a></li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
