@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="container-rp mx-auto px-3 py-5 text-center" style="max-width:500px;">
    <span class="material-symbols-outlined text-muted-rp mb-3" style="font-size:80px;">search_off</span>
    <h1 class="text-headline-md text-primary-rp mb-2">404 — Page Not Found</h1>
    <p class="text-body-lg text-muted-rp mb-4">The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ route('home') }}" class="btn btn-rp-primary">Back to Home</a>
</div>
@endsection
