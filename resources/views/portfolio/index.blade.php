@extends('layouts.dashboard', ['sidebar' => 'student'])
@section('title', 'Portfolio')
@section('content')
<div class="d-flex justify-content-between mb-4">
  <div>
    <h1 class="text-headline-md text-primary-rp mb-1">Portfolio & Resume</h1>
    <p class="text-muted-rp mb-0">Showcase projects and download a polished PDF resume.</p>
  </div>
  <a href="{{ route('resume.pdf') }}" class="btn btn-rp-primary">Download Resume PDF</a>
</div>
<form method="POST" action="{{ route('portfolio.store') }}" class="rp-card rp-card-lg p-3 mb-4">
  @csrf
  <div class="row g-3">
    <div class="col-md-4"><input name="title" class="form-control" placeholder="Project title" required></div>
    <div class="col-md-4"><input name="url" class="form-control" placeholder="Live URL"></div>
    <div class="col-md-4"><input name="repo_url" class="form-control" placeholder="GitHub URL"></div>
    <div class="col-md-8"><input name="tech_stack" class="form-control" placeholder="Tech stack (comma separated)"></div>
    <div class="col-md-4"><button class="btn btn-rp-outline w-100">Add Project</button></div>
    <div class="col-12"><textarea name="description" class="form-control" rows="2" placeholder="Description"></textarea></div>
  </div>
</form>
<div class="row g-3">
@forelse($projects as $project)
<div class="col-md-6">
  <div class="rp-card rp-card-lg">
    <h3 class="text-title-lg">{{ $project->title }}</h3>
    <p>{{ $project->description }}</p>
    <div class="mb-2">@foreach($project->tech_stack ?? [] as $t)<span class="badge bg-light text-dark me-1">{{ $t }}</span>@endforeach</div>
    <div class="d-flex gap-2">
      @if($project->url)<a href="{{ $project->url }}" target="_blank" class="btn btn-sm btn-rp-outline">Live</a>@endif
      @if($project->repo_url)<a href="{{ $project->repo_url }}" target="_blank" class="btn btn-sm btn-rp-outline">Repo</a>@endif
      <form method="POST" action="{{ route('portfolio.destroy', $project) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
    </div>
  </div>
</div>
@empty
<div class="col-12 text-muted-rp">No projects yet.</div>
@endforelse
</div>
@endsection
