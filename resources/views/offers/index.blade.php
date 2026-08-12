@extends('layouts.dashboard', ['sidebar' => auth()->user()->isStudent() ? 'student' : 'recruiter'])
@section('title', 'Offers')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Job Offers</h1>
@if(auth()->user()->isRecruiter() || auth()->user()->isAdmin())
<form method="POST" action="{{ route('offers.store') }}" class="rp-card rp-card-lg p-3 mb-4">
@csrf
<div class="row g-3">
  <div class="col-md-4">
    <select name="application_id" class="form-select" required>
      <option value="">Select application</option>
      @foreach($applications as $app)
      <option value="{{ $app->id }}">{{ $app->applicant->name }} — {{ $app->jobPosting->title }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3"><input name="title" class="form-control" placeholder="Offer title" required></div>
  <div class="col-md-2"><input name="salary" class="form-control" placeholder="Salary"></div>
  <div class="col-md-3"><input type="date" name="start_date" class="form-control"></div>
  <div class="col-12"><textarea name="body" class="form-control" rows="4" required placeholder="Offer letter body"></textarea></div>
  <div class="col-12"><button class="btn btn-rp-primary">Send Offer</button></div>
</div>
</form>
@endif
@foreach($offers as $offer)
<div class="rp-card rp-card-lg mb-3 p-3">
  <div class="d-flex justify-content-between">
    <div>
      <h3 class="text-title-lg mb-1">{{ $offer->title }}</h3>
      <div class="text-muted-rp">{{ $offer->application->jobPosting->title }} · {{ $offer->application->applicant->name }}</div>
      <div class="mt-2">{{ $offer->body }}</div>
      <div class="text-label-md mt-2">Salary: {{ $offer->salary ?? '—' }} · Status: {{ $offer->status }}</div>
    </div>
  </div>
  @if(auth()->id() === $offer->application->user_id && $offer->status === 'sent')
  <form method="POST" action="{{ route('offers.respond', $offer) }}" class="mt-3 d-flex gap-2">
    @csrf
    <input name="candidate_signature" class="form-control" placeholder="Type full name to e-sign" required>
    <button name="decision" value="accepted" class="btn btn-rp-primary">Accept & Sign</button>
    <button name="decision" value="declined" class="btn btn-outline-danger" formnovalidate>Decline</button>
  </form>
  @elseif($offer->candidate_signature)
  <div class="text-success mt-2">Signed by: {{ $offer->candidate_signature }}</div>
  @endif
</div>
@endforeach
{{ $offers->links() }}
@endsection
