@extends('layouts.dashboard', ['sidebar' => 'admin'])
@section('title', 'Plans')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Company Plans & Feature Limits</h1>
<table class="table rp-table rp-card"><thead><tr><th>Company</th><th>Owner</th><th>Plan</th><th>Seats</th><th>Update</th></tr></thead>
<tbody>
@foreach($companies as $c)
<tr>
<td>{{ $c->name }}</td><td>{{ $c->owner?->email }}</td><td>{{ $c->planLabel() }}</td><td>{{ $c->max_team_seats }}</td>
<td>
<form method="POST" action="{{ route('admin.plans.update', $c) }}" class="d-flex gap-1">@csrf @method('PATCH')
<select name="plan" class="form-select form-select-sm"><option value="free" @selected($c->plan==='free')>Free</option><option value="pro" @selected($c->plan==='pro')>Pro</option><option value="enterprise" @selected($c->plan==='enterprise')>Enterprise</option></select>
<input type="number" name="max_team_seats" class="form-control form-control-sm" value="{{ $c->max_team_seats }}" min="1" style="width:80px">
<button class="btn btn-sm btn-rp-primary">Save</button>
</form>
</td>
</tr>
@endforeach
</tbody></table>
{{ $companies->links() }}
@endsection
