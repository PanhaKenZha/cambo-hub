@extends('layouts.app')
@section('title', 'Under maintenance · '.$siteName)
@section('content')
<section class="card">
    <div class="eyebrow">BACK SOON</div>
    <h1>We're doing some maintenance</h1>
    <p>{{ $message }}</p>
</section>
@endsection
