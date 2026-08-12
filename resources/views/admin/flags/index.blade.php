@extends('layouts.dashboard', ['sidebar' => 'admin'])
@section('title', 'Feature Flags')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Feature Flags</h1>
<table class="table rp-table rp-card"><thead><tr><th>Key</th><th>Label</th><th>Enabled</th><th></th></tr></thead>
<tbody>
@foreach($flags as $flag)
<tr>
<td><code>{{ $flag->key }}</code></td><td>{{ $flag->label }}</td>
<td>{{ $flag->enabled ? 'Yes' : 'No' }}</td>
<td><form method="POST" action="{{ route('admin.flags.toggle', $flag) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-rp-outline">Toggle</button></form></td>
</tr>
@endforeach
</tbody></table>
@endsection
