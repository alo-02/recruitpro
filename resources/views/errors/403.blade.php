@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="container-rp mx-auto px-3 py-5 text-center" style="max-width:500px;">
    <span class="material-symbols-outlined text-danger mb-3" style="font-size:80px;">block</span>
    <h1 class="text-headline-md text-primary-rp mb-2">403 — Access Denied</h1>
    <p class="text-body-lg text-muted-rp mb-4">You don't have permission to access this page.</p>
    <a href="{{ route('home') }}" class="btn btn-rp-primary">Back to Home</a>
</div>
@endsection
