@extends('layouts.dashboard', ['sidebar' => 'recruiter'])
@section('title', 'Talent Pool')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-3">Talent Pool</h1>
<form class="mb-3 d-flex gap-2" method="GET">
  <input name="tag" class="form-control" value="{{ $tag }}" placeholder="Filter by tag">
  <button class="btn btn-rp-outline">Filter</button>
</form>
<form method="POST" action="{{ route('applications.bulk') }}" class="rp-card p-3 mb-4">
@csrf
<div class="row g-2 align-items-end">
  <div class="col-md-3">
    <select name="action" class="form-select" required>
      <option value="shortlist">Bulk shortlist</option>
      <option value="reject">Bulk reject</option>
      <option value="tag">Add tag</option>
    </select>
  </div>
  <div class="col-md-3"><input name="tag" class="form-control" placeholder="Tag (for add tag)"></div>
  <div class="col-md-3"><button class="btn btn-rp-primary">Apply to selected</button></div>
</div>
<div class="table-responsive mt-3">
<table class="table rp-table mb-0">
<thead><tr><th></th><th>Candidate</th><th>Job</th><th>Score</th><th>Tags</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($candidates as $app)
<tr>
  <td><input type="checkbox" name="application_ids[]" value="{{ $app->id }}"></td>
  <td>{{ $app->applicant->name }}</td>
  <td>{{ $app->jobPosting->title }}</td>
  <td>{{ $app->match_score ?? 0 }}%</td>
  <td>@foreach($app->recruiter_tags ?? [] as $t)<span class="badge bg-light text-dark me-1">{{ $t }}</span>@endforeach</td>
  <td><span class="badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span></td>
  <td>
    <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-rp-outline">Open</a>
    <form method="POST" action="{{ route('talent.blacklist') }}" class="d-inline">@csrf
      <input type="hidden" name="user_id" value="{{ $app->user_id }}">
      <button class="btn btn-sm btn-outline-danger">Blacklist</button>
    </form>
  </td>
</tr>
@empty
<tr><td colspan="7" class="text-muted-rp">No candidates.</td></tr>
@endforelse
</tbody></table>
</div>
</form>
{{ $candidates->links() }}
@if($blacklist->count())
<h2 class="text-title-lg mt-4">Blacklist</h2>
<ul>@foreach($blacklist as $b)<li>{{ $b->user->name }} — {{ $b->reason }}</li>@endforeach</ul>
@endif
@endsection
