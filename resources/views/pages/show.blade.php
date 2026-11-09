@extends('layouts.app')
@section('title', $title.' · Cambo Hub')
@section('content')
@if($isPreview)
    <div class="status" role="status">
        Preview{{ isset($page) ? ' · '.$page->stateLabel() : '' }} — visitors {{ isset($page) && $page->isLive() ? 'can' : 'cannot yet' }} see this page.
    </div>
@endif
<article class="card cms-page">
    <h1>{{ $title }}</h1>
    <div class="cms-body">{!! $html !!}</div>
</article>
@endsection
