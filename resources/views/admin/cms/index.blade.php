@extends('layouts.dashboard', ['sidebar' => 'admin'])
@section('title', 'CMS')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Career Content CMS</h1>
<form method="POST" action="{{ route('admin.cms.store') }}" class="rp-card p-3 mb-4">
@csrf
<input name="title" class="form-control mb-2" placeholder="Title" required>
<input name="excerpt" class="form-control mb-2" placeholder="Excerpt">
<textarea name="body" class="form-control mb-2" rows="6" required placeholder="Body"></textarea>
<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_published" value="1" id="pub"><label for="pub" class="form-check-label">Publish now</label></div>
<button class="btn btn-rp-primary">Save Post</button>
</form>
<table class="table rp-table rp-card"><thead><tr><th>Title</th><th>Status</th><th>Published</th></tr></thead>
<tbody>@foreach($posts as $p)<tr><td><a href="{{ route('blog.show', $p) }}">{{ $p->title }}</a></td><td>{{ $p->is_published?'Published':'Draft' }}</td><td>{{ $p->published_at }}</td></tr>@endforeach</tbody></table>
{{ $posts->links() }}
@endsection
