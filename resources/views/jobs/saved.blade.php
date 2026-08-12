@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Saved Jobs')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Saved Jobs</h1>
    <p class="text-body-md text-muted-rp">Jobs you bookmarked for later.</p>
</div>

<div class="rp-card rp-card-lg">
    @forelse($saved as $item)
        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
            <div>
                <a href="{{ route('jobs.show', $item->jobPosting) }}" class="fw-semibold text-decoration-none">{{ $item->jobPosting->title }}</a>
                <div class="text-muted-rp">{{ $item->jobPosting->company->name }}</div>
            </div>
            <form method="POST" action="{{ route('jobs.unsave', $item->jobPosting) }}">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Remove</button>
            </form>
        </div>
    @empty
        <p class="text-muted-rp p-3 mb-0">No saved jobs yet.</p>
    @endforelse
    <div class="p-3">{{ $saved->links() }}</div>
</div>
@endsection
