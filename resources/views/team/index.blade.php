@extends('layouts.dashboard', ['sidebar' => 'recruiter'])
@section('title', 'Team Seats')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-1">Company Team</h1>
<p class="text-muted-rp mb-4">Plan: <strong>{{ $company->planLabel() }}</strong> · Seats left: {{ $company->remainingSeats() }}/{{ $company->max_team_seats }}</p>
<form method="POST" action="{{ route('team.invite') }}" class="rp-card rp-card-lg p-3 mb-4">
@csrf
<div class="row g-3">
  <div class="col-md-3"><input name="name" class="form-control" placeholder="Name" required></div>
  <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
  <div class="col-md-3">
    <select name="seat_role" class="form-select"><option value="recruiter">Recruiter</option><option value="viewer">Viewer</option></select>
  </div>
  <div class="col-md-3"><button class="btn btn-rp-primary w-100">Invite</button></div>
</div>
</form>
<table class="table rp-table rp-card rp-card-lg"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
<tbody>
@forelse($members as $m)
<tr><td>{{ $m->user->name }}</td><td>{{ $m->user->email }}</td><td>{{ $m->seat_role }}</td><td>{{ $m->accepted_at?->format('M d, Y') }}</td></tr>
@empty
<tr><td colspan="4" class="text-muted-rp">No team members yet (owner counts separately).</td></tr>
@endforelse
</tbody></table>
@endsection
