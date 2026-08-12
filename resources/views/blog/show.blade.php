@extends('layouts.app')
@section('title', $post->title)
@section('content')
<div class="container-rp mx-auto px-3 py-5" style="max-width:800px">
  <a href="{{ route('blog.index') }}" class="text-label-md">← Blog</a>
  <h1 class="text-headline-md text-primary-rp mt-2">{{ $post->title }}</h1>
  <p class="text-muted-rp">{{ $post->published_at?->format('M d, Y') }}</p>
  <div class="rp-card rp-card-lg p-4" style="white-space:pre-wrap">{{ $post->body }}</div>
</div>
@endsection
