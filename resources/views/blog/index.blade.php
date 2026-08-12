@extends('layouts.app')
@section('title', 'Career Blog')
@section('content')
<div class="container-rp mx-auto px-3 py-5">
<h1 class="text-headline-md text-primary-rp mb-4">{{ __('Career Tips & News') }}</h1>
@forelse($posts as $post)
<article class="rp-card rp-card-lg mb-3 p-3">
  <h2 class="text-title-lg"><a href="{{ route('blog.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a></h2>
  <p class="text-muted-rp">{{ $post->excerpt }}</p>
  <div class="text-label-md">{{ $post->published_at?->format('M d, Y') }}</div>
</article>
@empty
<p class="text-muted-rp">No posts yet.</p>
@endforelse
{{ $posts->links() }}
</div>
@endsection
