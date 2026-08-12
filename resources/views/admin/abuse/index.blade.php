@extends('layouts.dashboard', ['sidebar' => 'admin'])
@section('title', 'Abuse Reports')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Abuse / Spam Reports</h1>
<table class="table rp-table rp-card"><thead><tr><th>When</th><th>Reporter</th><th>Target</th><th>Reason</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($reports as $r)
<tr>
<td>{{ $r->created_at->format('M d, Y') }}</td>
<td>{{ $r->reporter?->email }}</td>
<td>{{ class_basename($r->reportable_type) }} #{{ $r->reportable_id }}</td>
<td>{{ $r->reason }}</td>
<td>{{ $r->status }}</td>
<td>
<form method="POST" action="{{ route('admin.abuse.resolve', $r) }}" class="d-flex gap-1">@csrf @method('PATCH')
<select name="status" class="form-select form-select-sm"><option value="reviewing">Reviewing</option><option value="resolved">Resolved</option><option value="dismissed">Dismissed</option></select>
<button class="btn btn-sm btn-rp-outline">Update</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="6" class="text-muted-rp">No reports.</td></tr>
@endforelse
</tbody></table>
{{ $reports->links() }}
@endsection
